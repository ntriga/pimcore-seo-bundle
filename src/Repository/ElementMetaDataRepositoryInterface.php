<?php

namespace Ntriga\PimcoreSeoBundle\Repository;

use Ntriga\PimcoreSeoBundle\Model\ElementMetaDataInterface;

interface ElementMetaDataRepositoryInterface
{
    /**
     * @param string $elementType
     * @param int $elementId
     * @return array<int, ElementMetaDataInterface>
     */
    public function findAll(string $elementType, int $elementId): array;

    public function findByIntegrator(string $elementType, int $elementId, string $integrator): ?ElementMetaDataInterface;
}
