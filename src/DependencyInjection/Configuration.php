<?php

namespace Ntriga\PimcoreSeoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('seo');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode->append($this->createIndexProviderConfigurationNode());

        return $treeBuilder;
    }

    private function createIndexProviderConfigurationNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('index_provider_configuration');
        $node = $treeBuilder->getRootNode();

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('pimcore_element_watcher')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('enabled_worker')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('worker_name')->cannotBeEmpty()->isRequired()->end()
                            ->variableNode('worker_config')->defaultValue([])->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }
}
