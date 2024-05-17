<?php

namespace Ntriga\PimcoreSeoBundle\MetaData\Integrator;

use Ntriga\PimcoreSeoBundle\Helper\ArrayHelper;
use Ntriga\PimcoreSeoBundle\Model\SeoMetaDataInterface;
use Ntriga\PimcoreSeoBundle\Tool\UrlGenerator;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Pimcore\Tool;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CanonicalIntegrator extends AbstractIntegrator implements IntegratorInterface
{
    protected array $configuration;
    protected UrlGenerator $urlGenerator;

    public function __construct(UrlGenerator $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function getBackendConfiguration(mixed $element): array
    {
        $canonicalUrls = [];
        if ($element instanceof DataObject){
            $validLanguages = Tool::getValidLanguages();
            foreach ($validLanguages as $language){
//                $url = $this->getDefaultCanonical($element, $language);
                $canonicalUrls[$language] = null;
            }
        } else{
            $canonicalUrls['default'] = $this->getDefaultCanonical($element);
        }

        return [
            'defaultCanonical' => $canonicalUrls,
            'hasLivePreview' => false,
            'livePreviewTemplates' => [],
            'useLocalizedFields' => $element instanceof DataObject
        ];
    }

    private function getDefaultCanonical(mixed $element, ?string $locale = null): ?string
    {
        return $this->urlGenerator->generate($element , $locale !== null ? ['_locale' => $locale] : []);
    }

    public function validateBeforeBackend(string $elementType, int $elementId, array $data): array
    {
        return $data;
    }

    public function updateMetaData(mixed $element, array $data, ?string $locale, SeoMetaDataInterface $seoMetaData): void
    {
        $defaultUrl = $this->getDefaultCanonical($element);


        if (null !== $value = $this->findLocaleAwareData($data['canonical'] ?? null, $locale)){
            $seoMetaData->setCanonicalUrl($value);
        } else{
            $seoMetaData->setCanonicalUrl($defaultUrl);
        }

    }

    public function validateBeforePersist(string $elementType, int $elementId, array $data, ?array $previousData = null, bool $merge = false): ?array
    {
        if (empty($data['canonical'])) {
            return null;
        }

        if ($elementType === 'object') {
            $data = $this->mergeStorageAndEditModeLocaleAwareData($data, $previousData, $merge);
            if ($data['canonical'] === null) {
                return null;
            }
            foreach ($data['canonical'] as $key => $value) {
                if ($value['value'] !== null) {
                    $data['canonical'][$key] = ['locale' => $value['locale'], 'value' => $value['value']];
                }
            }
        }

        return $data;
    }

    protected function mergeStorageAndEditModeLocaleAwareData(array $data, ?array $previousData, bool $mergeWithPrevious = false): array
    {
        $arrayModifier = new ArrayHelper();

        if (!is_array($previousData) || count($previousData) === 0) {
            return [
                'canonical' => $arrayModifier->cleanEmptyLocaleRows($data['canonical']),
            ];
        }

        $newData = $mergeWithPrevious ? $previousData : [];

        $rebuildRow = $previousData['canonical'] ?? [];

        if (!isset($data['canonical']) || !is_array($data['canonical'])) {
            $newData['canonical'] = $rebuildRow;
        }

        $newData['canonical'] = $arrayModifier->rebuildLocaleValueRow($data['canonical'], $rebuildRow, $mergeWithPrevious);

        return $newData;
    }

    public function setConfiguration(array $configuration): void
    {
        $this->configuration = $configuration;
    }

    /**
     * @return array
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function getPreviewParameter(mixed $element, ?string $template, array $data): array
    {
        return [];
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        // No options
    }
}
