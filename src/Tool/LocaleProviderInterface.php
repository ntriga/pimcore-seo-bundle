<?php

namespace Ntriga\PimcoreSeoBundle\Tool;

use Pimcore\Model\DataObject;

interface LocaleProviderInterface
{
    public function getAllowedLocalesForObject(?DataObject\AbstractObject $object): array;
}
