<?php

namespace Ntriga\PimcoreSeoBundle\Registry;

use Symfony\Component\Translation\Extractor\ExtractorInterface;

interface MetaDataExtractorRegistryInterface
{
    public function has(string $identifier): bool;

    /**
     * @throws \Exception
     */
    public function get(string $identifier): ExtractorInterface;

    /**
     * @return array<int, ExtractorInterface>
     */
    public function getAll(): array;
}
