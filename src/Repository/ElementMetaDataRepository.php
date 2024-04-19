<?php

namespace Ntriga\PimcoreSeoBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Ntriga\PimcoreSeoBundle\Model\ElementMetaData;
use Ntriga\PimcoreSeoBundle\Model\ElementMetaDataInterface;

class ElementMetaDataRepository implements ElementMetaDataRepositoryInterface
{
    protected EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(ElementMetaData::class);
    }

    public function findAll(string $elementType, int $elementId): array
    {
        return $this->repository->findBy([
            'elementType' => $elementType,
            'elementId' => $elementId
        ]);
    }

    public function findByIntegrator(string $elementType, int $elementId, string $integrator): ?ElementMetaDataInterface
    {
        return $this->repository->findOneBy([
            'elementType' => $elementType,
            'elementId' => $elementId,
            'integrator' => $integrator
        ]);
    }


}
