<?php

namespace Ntriga\PimcoreSeoBundle\MetaData;

use Ntriga\PimcoreSeoBundle\Middleware\MiddlewareDispatcherInterface;
use Ntriga\PimcoreSeoBundle\Model\SeoMetaData;
use Ntriga\PimcoreSeoBundle\Registry\MetaDataExtractorRegistryInterface;
use Ntriga\PimcoreSeoBundle\Tool\UrlGenerator;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Pimcore\Twig\Extension\Templating\HeadMeta;
use Pimcore\Twig\Extension\Templating\HeadTitle;
use Ntriga\PimcoreSeoBundle\MetaData\Extractor\ExtractorInterface;
class MetaDataProvider implements MetaDataProviderInterface
{
    public function __construct(
        protected HeadMeta                           $headMeta,
        protected HeadTitle                          $headTitle,
        protected UrlGenerator                       $urlGenerator,
        protected MetaDataExtractorRegistryInterface $extractorRegistry,
        protected MiddlewareDispatcherInterface      $middlewareDispatcher
    )
    {}

    public function updateSeoElement($element, ?string $locale): void
    {
        $seoMetadata = $this->getSeoMetaData($element, $locale);

        $defaultCanonical = $this->generateDefaultCanonical($element);
        if ($seoMetadata->getCanonicalUrl() !== null || $defaultCanonical !== null){
            $canonicalUrl = (!empty($seoMetadata->getCanonicalUrl()))
                ? $seoMetadata->getCanonicalUrl()
                : $defaultCanonical;

            $canonicalTag = '<link rel="canonical" href="' . htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') . '" />';
            $this->headMeta->addRaw($canonicalTag);
        }

        if ($seoMetadata->getIndexPage() !== null && $seoMetadata->getIndexPage() === false ){
            $indexTag = '<meta name="robots" content="noindex">';
            $this->headMeta->addRaw($indexTag);
        }

        if ($extraProperties = $seoMetadata->getExtraProperties()) {
            foreach ($extraProperties as $key => $value) {
                $this->headMeta->appendProperty($key, $value);
            }
        }

        if ($extraNames = $seoMetadata->getExtraNames()) {
            foreach ($extraNames as $key => $value) {
                $this->headMeta->appendName($key, $value);
            }
        }

        if ($extraHttp = $seoMetadata->getExtraHttp()) {
            foreach ($extraHttp as $key => $value) {
                $this->headMeta->appendHttpEquiv($key, $value);
            }
        }

        if ($schemaBlocks = $seoMetadata->getSchema()) {
            foreach ($schemaBlocks as $schemaBlock) {
                if (is_array($schemaBlock)) {
                    $schemaTag = sprintf('<script type="application/ld+json">%s</script>', json_encode($schemaBlock, JSON_UNESCAPED_UNICODE));
                    $this->headMeta->addRaw($schemaTag);
                }
            }
        }

        if ($raw = $seoMetadata->getRaw()) {
            foreach ($raw as $rawValue) {
                $this->headMeta->addRaw($rawValue);
            }
        }

        $title = $seoMetadata->getTitle();
        if (!$title){
            $title = $this->generateDefaultTitle($element);
        }
        if ($title) {
            $this->headTitle->prepend($title);
        }

        if ($seoMetadata->getMetaDescription()) {
            $this->headMeta->setDescription($seoMetadata->getMetaDescription());
        }
    }

    protected function getSeoMetaData(mixed $element, ?string $locale): SeoMetaData
    {
        $seoMetaData = new SeoMetaData($this->middlewareDispatcher);
        $extractors = $this->getExtractorsForElement($element);
        foreach ($extractors as $extractor) {
            $extractor->updateMetadata($element, $locale, $seoMetaData);
            $this->middlewareDispatcher->dispatchTasks($seoMetaData);
        }

        $this->middlewareDispatcher->dispatchMiddlewareFinisher($seoMetaData);

        return $seoMetaData;
    }

    /**
     * @return array<int, ExtractorInterface>
     */
    protected function getExtractorsForElement($element): array
    {
        return array_filter($this->extractorRegistry->getAll(), static function (ExtractorInterface $extractor) use ($element) {
            return $extractor->supports($element);
        });
    }

    protected function generateDefaultCanonical(mixed $element): ?string
    {
        if ($element instanceof Document || $element instanceof DataObject){
            return $this->urlGenerator->generate($element);
        }

        return null;
    }

    protected function generateDefaultTitle(mixed $element): ?string
    {
        if ($element instanceof Document){
            if ($element->hasProperty('navigation_title')) {
                $title = $element->getProperty('navigation_title');
                if ($title) {
                    return $title;
                }
            }

            if ($element->hasProperty('navigation_name')) {
                $title = $element->getProperty('navigation_name');
                if ($title) {
                    return $title;
                }
            }

            if (method_exists($element, 'getKey')) {
                $title = $element->getKey();
                if ($title) {
                    return $title;
                }
            }
        }

        if ($element instanceof DataObject){
            if (method_exists($element, 'getTitle')){
                $title = $element->getTitle();

                if ($title) {
                    return $title;
                }
            }

            if (method_exists($element, 'getName')){
                $title = $element->getName();

                if ($title) {
                    return $title;
                }
            }

            if (method_exists($element, 'getKey')){
                $title = $element->getKey();

                if ($title) {
                    return $title;
                }
            }
        }

        return null;
    }
}
