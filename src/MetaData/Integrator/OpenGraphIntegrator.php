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

        $arrayModifier = new ArrayHelper();

        if ($elementType === 'object'){
            $newData = $arrayModifier->mergeLocaleAwareArrays($data, $previousData, 'property', 'value', $merge);
        } else{
            $newData = $arrayModifier->mergeNonLocaleAwareArrays($data, $previousData, 'property', $merge);
        }

        if (is_array($newData) && count($newData) === 0){
            return null;
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

//        $defaultPresets = $this->getDefaultPresets();
        $defaultProperties = $this->getDefaultProperties();

        $defaultProperties = array_map(static function ($translatable, $key) {
            return [$key, $key, $translatable];
        }, $defaultProperties, array_keys($defaultProperties));

        $additionalProperties = array_map(static function (array $row) {
            return count($row) === 2 ? [$row[0], $row[1], false] : $row;
        }, $configuration['properties']);

//        $configuration['presets'] = array_merge($defaultPresets, $configuration['presets']);
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
