<?php

namespace Ntriga\PimcoreSeoBundle\Registry;

use Ntriga\PimcoreSeoBundle\ResourceProcessor\ResourceProcessorInterface;

interface ResourceProcessorRegistryInterface
{
    public function has(string $identifier): bool;

    /**
     * @throws \Exception
     * @param string $identifier
     * @return ResourceProcessorInterface
     */
    public function get(string $identifier): ResourceProcessorInterface;

    /**
     * @return array
     */
    public function getAll(): array;

}
