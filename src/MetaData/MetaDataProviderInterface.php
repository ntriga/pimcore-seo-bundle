<?php

namespace Ntriga\PimcoreSeoBundle\MetaData;

interface MetaDataProviderInterface
{
    public function updateSeoElement(mixed $element, ?string $locale): void;
}
