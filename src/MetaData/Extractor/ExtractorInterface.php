<?php

namespace Ntriga\PimcoreSeoBundle\MetaData\Extractor;

use Ntriga\PimcoreSeoBundle\Model\SeoMetaDataInterface;

interface ExtractorInterface
{
    public function supports(mixed $element): bool;

    public function updateMetaData(mixed $element, ?string $locale, SeoMetaDataInterface $seoMetaData): void;
}
