<?php

namespace Ntriga\PimcoreSeoBundle\MetaData\Integrator;

use Ntriga\PimcoreSeoBundle\Helper\ArrayHelper;
use Ntriga\PimcoreSeoBundle\Model\SeoMetaDataInterface;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document\Page;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TitleDescriptionIntegrator extends AbstractIntegrator implements IntegratorInterface
{
    protected array $configuration;

    public function getBackendConfiguration($element): array
    {
        return [
            'hasLivePreview'       => false,
            'livePreviewTemplates' => [],
            'useLocalizedFields'   => $element instanceof DataObject
        ];
    }

    public function getPreviewParameter(mixed $element, ?string $template, array $data): array
    {
        $url = 'http://localhost';

        try {
            $url = $element instanceof Page ? $element->getUrl() : 'http://localhost';
        } catch (\Exception $e) {
            // fail silently
        }

        $author = 'John Doe';
        $title = $data['title'] ?? 'This is a title';
        $description = $data['description'] ?? 'This is a very long description which should be not too long.';

        return [
            'path'   => '@Seo/preview/titleDescription/preview.html.twig',
            'params' => [
                'url'         => $url,
                'author'      => $author,
                'title'       => $title,
                'description' => $description,
                'date'        => date('d.m.Y')
            ]
        ];
    }

    public function validateBeforeBackend(string $elementType, int $elementId, array $data): array
    {
        return $data;
    }

    public function validateBeforePersist(string $elementType, int $elementId, array $data, ?array $previousData = null, bool $merge = false): ?array
    {
        if ($elementType === 'object') {
            $data = $this->mergeStorageAndEditModeLocaleAwareData($data, $previousData, $merge);
        }

        if (isset($data['title'])) {
            if (is_array($data['title'])) {
                foreach ($data['title'] as $locale => $titleString) {
                    if (is_string($titleString)) {
                        $data['title'][$locale] = strip_tags($titleString);
                    }
                }
            } elseif (is_string($data['title'])) {
                $data['title'] = strip_tags($data['title']);
            }
        }

        if (empty($data['title']) && empty($data['description'])) {
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
                'title'       => $arrayModifier->cleanEmptyLocaleRows($data['title']),
                'description' => $arrayModifier->cleanEmptyLocaleRows($data['description'])
            ];
        }

        $newData = $mergeWithPrevious ? $previousData : [];

        foreach (['title', 'description'] as $type) {

            $rebuildRow = $previousData[$type] ?? [];

            if (!isset($data[$type]) || !is_array($data[$type])) {
                $newData[$type] = $rebuildRow;
                continue;
            }

            $newData[$type] = $arrayModifier->rebuildLocaleValueRow($data[$type], $rebuildRow, $mergeWithPrevious);
        }

        return $newData;
    }

    public function updateMetaData(mixed $element, array $data, ?string $locale, SeoMetaDataInterface $seoMetadata): void
    {
        if (null !== $value = $this->findLocaleAwareData($data['description'] ?? null, $locale)) {
            $seoMetadata->setMetaDescription($value);
        }

        if (null !== $value = $this->findLocaleAwareData($data['title'] ?? null, $locale)) {
            $seoMetadata->setTitle($value);
        }
    }

    public function setConfiguration(array $configuration): void
    {
        $this->configuration = $configuration;
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        // no options here.
    }
}
