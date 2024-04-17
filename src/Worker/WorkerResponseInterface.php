<?php

namespace Ntriga\PimcoreSeoBundle\Worker;

use SeoBundle\Model\QueueEntryInterface;

interface WorkerResponseInterface
{
    public function getStatus(): int;

    public function getMessage(): string;

    public function getQueueEntry(): QueueEntryInterface;

    public function getRawResponse(): mixed;

    public function isDone(): bool;
}
