<?php

namespace Ntriga\PimcoreSeoBundle\Repository;

use Ntriga\PimcoreSeoBundle\Model\QueueEntryInterface;

interface QueueEntryRepositoryInterface
{
    /**
     * @param array|null $orderBy
     * @return array<int, QueueEntryInterface>
     */
    public function findAll(?array $orderBy = null): array;

    /**
     * @param string $workerName
     * @param array|null $orderBy
     * @return array<int, QueueEntryInterface>
     */
    public function findAllForWorker(string $workerName, ?array $orderBy = null): array;

    public function findAtLeastOneForWorker(string $workerName): ?QueueEntryInterface;
}
