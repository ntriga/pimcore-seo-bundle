<?php

namespace Ntriga\PimcoreSeoBundle\Manager;

interface QueueManagerInterface
{
    public function addToQueue(string $processType, mixed $resource): void;

    public function processQueue(): void;
}
