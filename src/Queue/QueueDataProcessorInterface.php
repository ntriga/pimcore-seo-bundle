<?php

namespace Ntriga\PimcoreSeoBundle\Queue;

interface QueueDataProcessorInterface
{
    public function process(array $options): void;
}
