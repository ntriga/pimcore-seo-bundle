<?php

namespace Ntriga\PimcoreSeoBundle\MetaData\Integrator;

use Ntriga\PimcoreSeoBundle\Model\SeoMetaDataInterface;
use Pimcore\Model\DataObject;
use Pimcore\Tool;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IndexIntegrator extends AbstractIntegrator implements IntegratorInterface
{
    protected array $configuration;

    public function getBackendConfiguration(mixed $element): array
    {
        return [
            'hasLivePreview' => false,
            'livePreviewTemplates' => [],
            'useLocalizedFields' => $element instanceof DataObject
        ];
    }

    protected function findLocaleAwareData(mixed $data, ?string $requestedLocale, string $returnType = 'scalar'): mixed
    {
        if ($requestedLocale === null) {
            return null;
        }

        $value = $this->findData($data, $requestedLocale, $returnType);

        if ($value !== null){
            return $value;
        }

        foreach (Tool::getFallbackLanguagesFor($requestedLocale) as $fallbackLocale) {
            if (null !== $fallBackValue = $this->findData($data, $fallBackLocale, $returnType)) {
                return $fallBackValue;
            }
        }

        return null;
    }

    public function validateBeforeBackend(string $elementType, int $elementId, array $data): array
    {
        return $data;
    }

    public function updateMetaData(mixed $element, array $data, ?string $locale, SeoMetaDataInterface $seoMetaData): void
    {
        if (null !== $value = $this->findLocaleAwareData($data['index'] ?? null, $locale)) {
            $seoMetaData->setIndexPage($value === 'false');
        }
    }

    public function validateBeforePersist(string $elementType, int $elementId, array $data, ?array $previousData = null, bool $merge = false): ?array
    {
        if (empty($data['index'])){
            return null;
        }

        return $data;
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
