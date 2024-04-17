<?php

namespace Ntriga\PimcoreSeoBundle\Registry;

use Ntriga\PimcoreSeoBundle\ResourceProcessor\ResourceProcessorInterface;

class ResourceProcessorRegistry implements ResourceProcessorRegistryInterface
{
    protected array $services = [];

    public function register(mixed $service, string $identifier): void
    {
        if (!in_array(ResourceProcessorInterface::class, class_implements($service), true)){
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), \SeoBundle\ResourceProcessor\ResourceProcessorInterface::class, implode(', ', class_implements($service)))
            );
        }

        $this->services[$identifier] = $service;
    }

    public function has($identifier): bool
    {
        return isset($this->services[$identifier]);
    }

    public function get(string $identifier): ResourceProcessorInterface
    {
        if (!$this->has($identifier)){
            throw new \Exception('"' . $identifier . '" Resource Processor does not exist');
        }

        return $this->services[$identifier];
    }

    public function getAll(): array
    {
        return $this->services;
    }
}
