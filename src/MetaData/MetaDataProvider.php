<?php

namespace Ntriga\PimcoreSeoBundle\MetaData;

use Ntriga\PimcoreSeoBundle\Middleware\MiddlewareDispatcherInterface;
use Ntriga\PimcoreSeoBundle\Model\SeoMetaData;
use Ntriga\PimcoreSeoBundle\Registry\MetaDataExtractorRegistryInterface;
use Pimcore\Twig\Extension\Templating\HeadMeta;
use Pimcore\Twig\Extension\Templating\HeadTitle;
use Ntriga\PimcoreSeoBundle\MetaData\Extractor\ExtractorInterface;
class MetaDataProvider implements MetaDataProviderInterface
{
    public function __construct(
        protected HeadMeta $headMeta,
        protected HeadTitle $headTitle,
        protected MetaDataExtractorRegistryInterface $extractorRegistry,
        protected MiddlewareDispatcherInterface $middlewareDispatcher
    )
    {}

    public function updateSeoElement($element, ?string $locale): void
    {
        $seoMetaData = $this->getSeoMetaData($element, $locale);

        if ($extraProperties = $seoMetaData->getExtraProperties()){
            foreach ($extraProperties as $key => $value){
                $this->headMeta->appendProperty($key, $value);
            }
        }

        if ($extraNames = $seoMetaData->getExtraNames()){
            foreach ($extraNames as $key => $value){
                $this->headMeta->appendName($key, $value);
            }
        }

        if ($extraHttp = $seoMetaData->getExtraHttp()){
            foreach ($extraHttp as $key => $value){
                $this->headMeta->appendHttpEquiv($key, $value);
            }
        }

        if ($schemaBlocks = $seoMetaData->getSchema()){
            foreach ($schemaBlocks as $schemaBlock){
                if (is_array($schemaBlock)){
                    $schemaTag = sprintf('<script type="application/ld+json">%s</script>', json_encode($schemaBlock, JSON_UNESCAPED_UNICODE));
                    $this->headMeta->addRaw($schemaTag);
                }
            }
        }

        if ($seoMetaData->getTitle()){
            $this->headTitle->set($seoMetaData->getTitle());
        }

        if ($seoMetaData->getMetaDescription()){
            $this->headMeta->setDescription($seoMetaData->getMetaDescription());
        }
    }

    protected function getSeoMetaData(mixed $element, ?string $locale): SeoMetaData
    {
        $seoMetaData = new SeoMetaData($this->middlewareDispatcher);
        $extractors = $this->getExtractorsForElement($element);
        foreach ($extractors as $extractor){
            $extractor->updateMetaData($element, $locale, $seoMetaData);
            $this->middlewareDispatcher->dispatchTasks($seoMetaData);
        }

        $this->middlewareDispatcher->dispatchMiddlewareFinisher($seoMetaData);

        return $seoMetaData;
    }

    /**
     * @param $element
     * @return array<int, ExtractorInterface>
     */
    protected function getExtractorsForElement($element): array
    {
        return array_filter($this->extractorRegistry->getAll(), static function (ExtractorInterface $extractor) use ($element) {
            return $extractor->supports($element);
        });
    }
}
