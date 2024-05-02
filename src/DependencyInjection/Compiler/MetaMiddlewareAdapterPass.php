<?php

namespace Ntriga\PimcoreSeoBundle\DependencyInjection\Compiler;

use http\Exception\InvalidArgumentException;
use Ntriga\PimcoreSeoBundle\Middleware\MiddlewareDispatcher;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MetaMiddlewareAdapterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(MiddlewareDispatcher::class);

        foreach ($container->findTaggedServiceIds('seo.meta_data.middleware.adapter', true) as $serviceId => $attributes){
            foreach ($attributes as $attribute){
                if (!isset($attribute['identifier'])){
                    throw new InvalidArgumentException(sprintf('Attribute "identifier" missing for meta middleware "%s".', $serviceId));
                }
                $definition->addMethodCall('registerMiddlewareAdapter', [$attribute['identifier'], new Reference($serviceId)]);
            }
        }
    }
}
