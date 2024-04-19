<?php

namespace Ntriga\PimcoreSeoBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Ntriga\PimcoreSeoBundle\DependencyInjection\Compiler\ResourceProcessorPass;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\PimcoreBundleAdminClassicInterface;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class NtrigaPimcoreSeoBundle extends AbstractPimcoreBundle
{
    use PackageVersionTrait;

    public const PACKAGE_NAME = 'ntriga/pimcore-seo-bundle';

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ResourceProcessorPass());
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    protected function getComposerPackageName(): string
    {
        return self::PACKAGE_NAME;
    }

    protected function configureDoctrineExtension(ContainerBuilder $container): void
    {
        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                [$this->getNamespacePath() => $this->getNamespaceName()],
                ['seo.persistence.doctrine.manager'],
                'pimcore-seo.persistence.doctrine.enabled'
            )
        );
    }

    public function getNamespaceName(): string
    {
        return 'Ntriga\PimcoreSeoBundle\Model';
    }

    protected function getNamespacePath(): string
    {
        return sprintf(
            '%s/config/doctrine/%s',
            $this->getPath(),
            'model'
        );
    }

}
