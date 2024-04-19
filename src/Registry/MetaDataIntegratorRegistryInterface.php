<?php

namespace Ntriga\PimcoreSeoBundle\Registry;

use Ntriga\PimcoreSeoBundle\MetaData\Integrator\IntegratorInterface;

interface MetaDataIntegratorRegistryInterface
{
    public function has(string $identifier): bool;

    /**
     * @throws \Exception
     * @param string $identifier
     * @return mixed
     */
    public function get(string $identifier): IntegratorInterface;

    /**
     * @return array<int, IntegratorInterface>
     */
    public function getAll(): array;
}
