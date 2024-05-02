<?php

namespace Ntriga\PimcoreSeoBundle\Registry;

use Ntriga\PimcoreSeoBundle\Worker\IndexWorkerInterface;

interface IndexWorkerRegistryInterface
{
    public function has(string $identifier): bool;

    public function get(string $identifier): IndexWorkerInterface;
}
