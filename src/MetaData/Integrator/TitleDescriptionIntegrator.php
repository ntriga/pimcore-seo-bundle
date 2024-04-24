<?php

namespace Ntriga\PimcoreSeoBundle\MetaData\Integrator;

use Ntriga\PimcoreSeoBundle\Helper\ArrayHelper;
use Ntriga\PimcoreSeoBundle\Model\SeoMetaDataInterface;
use Pimcore\Model\DataObject;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TitleDescriptionIntegrator extends AbstractIntegrator implements IntegratorInterface
{
    protected array $configuration;

    public function getBackendConfiguration($element): array
    {
        return [
            'hasLivePreview' => false,
            'livePreviewTemplates' => [],
            'useLocalizedFields' => $element instanceof DataObject
        ];
    }

    public function getPreviewParameter(mixed $element, ?string $template, array $data): array
    {
        // TODO: Implement getPreviewParameter() method.
    }

    public function validateBeforeBackend(string $elementType, int $elementId, array $data): array
    {
        return $data;
    }

    public function validateBeforePersist(string $elementType, int $elementId, array $data, ?array $previousData = null, bool $merge = false): ?array
    {
        if ($elementType === 'object'){
            $data = $this->mergeStorageAndEditModeLocaleAwareData($data, $previousData, $merge);
        }

        if (empty($data['title']) && empty($data['description'])){
            return null;
        }

        return $data;
    }

    protected function mergeStorageAndEditModeLocaleAwareData(array $data, ?array $previousData, bool $mergeWithPrevious = false): array
    {
        $arrayModifier = new ArrayHelper();

        if (!is_array($previousData) || count($previousData) === 0){
            return [
                'title' => $arrayModifier->cleanEmptyLocaleRows($data['title']),
                'description' => $arrayModifier->cleanEmptyLocaleRows($data['description'])
            ];
        }

        $newData = $mergeWithPrevious ? $previousData : [];

        foreach (['title', 'description'] as $type){
            $rebuildRow = $previousData[$type] ?? [];

            if (!isset($data[$type]) || !is_array($data[$type])){
                $newData[$type] = $rebuildRow;
                continue;
            }

            $newData[$type] = $arrayModifier->rebuildLocaleValueRow($data[$type], $rebuildRow, $mergeWithPrevious);
        }

        return $newData;
    }

    public function updateMetaData(mixed $element, array $data, ?string $locale, SeoMetaDataInterface $seoMetaData)
    {
        if (null !== $value = $this->findLocaleAwareData($data['title'] ?? null, $locale)){
            $seoMetaData->setTitle($value);
        }

        if (null !== $value = $this->findLocaleAwareData($data['description'] ?? null, $locale)){
            $seoMetaData->setMetaDescription($value);
        }
    }

    public function setConfiguration(array $configuration): void
    {
        $this->configuration = $configuration;
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        // No options
    }

}
