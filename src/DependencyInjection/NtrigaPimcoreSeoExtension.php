<?php

namespace Ntriga\PimcoreSeoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class NtrigaPimcoreSeoExtension extends Extension
{
    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yaml');

        $this->validateConfiguration($config);

        $persistenceConfig = $config['persistence']['doctrine'];
        $entityManagerName = $persistenceConfig['entity_manager'];

        $enabledWorkerNames = [];
        foreach ($config['index_provider_configuration']['enabled_worker'] as $enabledWorker){
            $enabledWorkerNames[] = $enabledWorker['worker_name'];
            $container->setParameter(sprintf('seo.index.worker.config.%s', $enabledWorker['worker_name']), $enabledWorker['worker_config']);
        }

        $container->setParameter('seo.persistence.doctrine.enabled', true);
        $container->setParameter('seo.persistence.doctrine.manager', $entityManagerName);
        $container->setParameter('seo.index.worker.enabled', $enabledWorkerNames);
        $container->setParameter('seo.meta_data_provider.configuration', $config['meta_data_configuration']['meta_data_provider']);
        $container->setParameter('seo.meta_data_integrator.configuration', $config['meta_data_configuration']['meta_data_integrator']);
        $container->setParameter('seo.index.pimcore_element_watcher.enabled', $config['index_provider_configuration']['pimcore_element_watcher']['enabled']);

    }

    /**
     * @throws \Exception
     */
    public function prepend(ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $enabledThirdPartyConfigs = [];

        $pimcoreSeoBundleEnabled = $container->hasExtension('pimcore_seo');

        if ($pimcoreSeoBundleEnabled){
            $enabledThirdPartyConfigs['pimcore_seo'] = 'services/third_party/pimcore_seo.yaml';
        }

        foreach ($enabledThirdPartyConfigs as $enabledThirdPartyConfig){
            $loader->load($enabledThirdPartyConfig);
        }

        $container->setParameter('seo.third_party.enabled', array_keys($enabledThirdPartyConfigs));
    }

    private function validateConfiguration(array $config): void
    {
        $enabledIntegrators = [];
        foreach ($config['meta_data_configuration']['meta_data_integrator']['enabled_integrator'] as $dataIntegrator){
            if (in_array($dataIntegrator['integrator_name'], $enabledIntegrators, true)) {
                throw new InvalidConfigurationException(sprintf('Meta data integrator "%s" already has been added', $dataIntegrator['integrator_name']));
            }

            $enabledIntegrators[] = $dataIntegrator['integrator_name'];
        }
    }
}
