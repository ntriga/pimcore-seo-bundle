<?php

namespace Ntriga\PimcoreSeoBundle\MetaData\Integrator;

use Ntriga\PimcoreSeoBundle\Helper\ArrayHelper;
use Ntriga\PimcoreSeoBundle\Model\SeoMetaDataInterface;
use Ntriga\PimcoreSeoBundle\Tool\UrlGeneratorInterface;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OpenGraphIntegrator extends AbstractIntegrator implements IntegratorInterface
{
    protected array $configuration;

    public function __construct(protected UrlGeneratorInterface $urlGenerator)
    {}

    public function getBackendConfiguration(mixed $element): array
    {
        return [
            'hasLivePreview' => false,
            'livePreviewTemplates' => [
                ['facebook']
            ],
            'presets' => $this->configuration['presets'],
            'properties' => $this->configuration['properties'],
            'types' => $this->configuration['types'],
            'useLocalizedFields' => $element instanceof DataObject
        ];
    }

    public function getPreviewParameter(mixed $element, ?string $template, array $data): array
    {
        // TODO: Implement getPreviewParameter() method.
    }

    public function validateBeforeBackend(string $elementType, int $elementId, array $data): array
    {
        foreach ($data as $key => &$ogField){
            if ($key === 'og:image' && isset($ogField['thumbPath'])){
                unset($ogField['thumbPath']);
            }
        }

        return $data;
    }

    public function validateBeforePersist(string $elementType, int $elementId, array $data, ?array $previousData = null, bool $merge = false): ?array
    {
        if ($elementType === 'object'){
            $data = $this->mergeStorageAndEditModeLocaleAwareData($data, $previousData, $merge);
        }

        if (count($data) === 0){
            return null;
        }

        return $data;
    }

    protected function mergeStorageAndEditModeLocaleAwareData(array $data, ?array $previousData, bool $mergeWithPrevious = false): array
    {
        $arrayModifier = new ArrayHelper();

        // nothing to merge, just clean up
        if (!is_array($previousData) || count($previousData) === 0) {
            return [
                'og:title'       => $arrayModifier->cleanEmptyLocaleRows($data['og:title']),
                'og:description' => $arrayModifier->cleanEmptyLocaleRows($data['og:description']),
                'og:image.alt' => $arrayModifier->cleanEmptyLocaleRows($data['og:image.alt'])
            ];
        }

        $newData = $mergeWithPrevious ? $previousData : [];

        foreach (['og:title', 'og:description', 'og:image.alt'] as $type) {

            $rebuildRow = $previousData[$type] ?? [];

            if (!isset($data[$type]) || !is_array($data[$type])) {
                $newData[$type] = $rebuildRow;
                continue;
            }

            $newData[$type] = $arrayModifier->rebuildLocaleValueRow($data[$type], $rebuildRow, $mergeWithPrevious);
        }

        return $newData;
    }

    public function updateMetaData(mixed $element, array $data, ?string $locale, SeoMetaDataInterface $seoMetaData)
    {

        if (count($data) === 0){
            return;
        }

        $addedItems = 0;
        foreach ($data as $key => $ogItemValue){
            if (empty($key) || empty($ogItemValue)){
                continue;
            }

            $propertyName = $key;
            $propertyValue = $ogItemValue;

            if ($propertyName === 'og:image'){
                $value = isset($propertyValue['id']) && is_numeric($propertyValue['id']) ? $this->getImagePath($propertyValue) : null;
            } else{
                $value = $this->findLocaleAwareData($propertyValue, $locale);
            }

            if ($value === null){
                continue;
            }

            $addedItems++;
            $seoMetaData->addExtraProperty($propertyName, $value);
        }

        if ($addedItems > 0 && null !== $elementUrl = $this->urlGenerator->generate($element)){
            $seoMetaData->addExtraProperty('og:url', $elementUrl);
        }
    }

    public function setConfiguration(array $configuration): void
    {
        $defaultTypes = array_map(static function ($value) {
            return [$value['name'], $value['tag']];
        }, $this->getDefaultTypes());

        $defaultProperties = $this->getDefaultProperties();

        $defaultProperties = array_map(static function ($translatable, $key) {
            return [$key, $key, $translatable];
        }, $defaultProperties, array_keys($defaultProperties));

        $additionalProperties = array_map(static function (array $row) {
            return count($row) === 2 ? [$row[0], $row[1], false] : $row;
        }, $configuration['properties']);

        $configuration['types'] = array_merge($defaultTypes, $configuration['types']);
        $configuration['properties'] = array_merge($defaultProperties, $additionalProperties);

        $this->configuration = $configuration;
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'facebook_image_thumbnail' => null,
            'presets' => [],
            'types' => [],
            'properties' => []
        ]);

        $resolver->setRequired(['facebook_image_thumbnail']);
        $resolver->setAllowedTypes('facebook_image_thumbnail', ['string']);
        $resolver->setAllowedTypes('presets', ['array']);
        $resolver->setAllowedTypes('types', ['array']);
        $resolver->setAllowedTypes('properties', ['array']);
    }

    protected function getImagePath(array $data): ?string
    {
        if (!array_key_exists('id', $data)){
            return null;
        }

        $asset = Asset::getById($data['id']);

        if (!$asset instanceof Asset){
            return null;
        }

        return $this->urlGenerator->generate($asset, ['thumbnail' => $this->configuration['facebook_image_thumbnail']]);
    }

    protected function getDefaultTypes(): array
    {
        return [
            [
                'name' => 'Article',
                'tag' => 'article'
            ],
            [
                'name' => 'Website',
                'tag' => 'website'
            ]
        ];
    }

    protected function getDefaultProperties(): array
    {
        return [
            'og:type' => true,
            'og:title'       => true,
            'og:description' => true,
            'og:image'       => true,
            'og:image.alt'   => true,
        ];
    }
}
