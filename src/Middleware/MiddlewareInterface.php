<?php

namespace Ntriga\PimcoreSeoBundle\Middleware;

interface MiddlewareInterface
{
    public function addTask(callable $callback): void;
}
