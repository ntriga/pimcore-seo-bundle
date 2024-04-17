<?php

namespace Ntriga\PimcoreSeoBundle\ResourceProcessor;

use Ntriga\PimcoreSeoBundle\Model\QueueEntryInterface;
use Ntriga\PimcoreSeoBundle\Worker\WorkerResponseInterface;

interface ResourceProcessorInterface
{
    public function supportsWorker(string $workerIdentifier): bool;

    public function supportsResource(mixed $resource): bool;

    public function generateQueueContext(mixed $resource): mixed;

    public function processQueueEntry(QueueEntryInterface $queueEntry, string $workerIdentifier, array $context, mixed $resource): ?QueueEntryInterface;

    /**
     * @throws \Exception
     * @param WorkerResponseInterface $workerResponse
     * @return mixed
     */
    public function processWorkerResponse(WorkerResponseInterface $workerResponse);
}
