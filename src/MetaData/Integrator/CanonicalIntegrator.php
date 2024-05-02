<?php

namespace Ntriga\PimcoreSeoBundle\MetaData\Integrator;

use Ntriga\PimcoreSeoBundle\Model\SeoMetaDataInterface;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CanonicalIntegrator extends AbstractIntegrator implements IntegratorInterface
{
    protected array $configuration;

    public function getBackendConfiguration(mixed $element): array
    {
        $defaultCanonical = $this->getDefaultCanonical($element);

        return [
            'defaultCanonical' => $defaultCanonical,
            'hasLivePreview' => false,
            'livePreviewTemplates' => [],
            'useLocalizedFields' => $element instanceof DataObject
        ];
    }

    private function getDefaultCanonical(mixed $element): ?string
    {
        if ($element instanceof Document){
            return $element->getFullPath();
        }

        return null;
    }

    public function validateBeforeBackend(string $elementType, int $elementId, array $data): array
    {
        return $data;
    }

    public function updateMetaData(mixed $element, array $data, ?string $locale, SeoMetaDataInterface $seoMetaData): void
    {
        $defaultUrl = $this->getDefaultCanonical($element);


        if (!empty($data['canonical']) && $data['canonical'] !== $defaultUrl){
            $seoMetaData->setCanonicalUrl($data['canonical']);
        } else{
            $seoMetaData->setCanonicalUrl($defaultUrl);
        }


    }

    public function validateBeforePersist(string $elementType, int $elementId, array $data, ?array $previousData = null, bool $merge = false): ?array
    {

        if (empty($data['canonical'])){
            return null;
        }

        $data['canonical'] = $this->normalizeCanonicalUrl($data['canonical']);


        return $data;
    }

    /**
     * Ensures that canonical URL starts with "https://", if not, prepends it.
     * @param string $url
     * @return string
     */
    private function normalizeCanonicalUrl(string $url): string
    {
        if (!preg_match('/^https?:\/\//', $url)){
            $url = 'https://' . $url;
        }

        return $url;
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
