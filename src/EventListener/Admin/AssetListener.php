<?php

namespace Ntriga\PimcoreSeoBundle\EventListener\Admin;

use Pimcore\Event\BundleManager\PathsEvent;
use Pimcore\Event\BundleManagerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AssetListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            BundleManagerEvents::CSS_PATHS => 'addCssFiles',
            BundleManagerEvents::JS_PATHS => 'addJsFiles',
        ];
    }

    public function addCssFiles(PathsEvent $event): void
    {
        $event->addPaths([
            '/bundles/ntrigapimcoreseo/css/admin.css'
        ]);
    }

    public function addJsFiles(PathsEvent $event): void
    {
        $event->addPaths([
            '/bundles/ntrigapimcoreseo/js/plugin.js',
            '/bundles/ntrigapimcoreseo/js/metaData/abstractMetaDataPanel.js',
            '/bundles/ntrigapimcoreseo/js/metaData/documentMetaDataPanel.js',
            '/bundles/ntrigapimcoreseo/js/metaData/integrator/abstractIntegrator.js',
            '/bundles/ntrigapimcoreseo/js/metaData/integrator/titleDescriptionIntegrator.js',
            '/bundles/ntrigapimcoreseo/js/metaData/extension/integratorValueFetcher.js',
            '/bundles/ntrigapimcoreseo/js/metaData/extension/localizedFieldExtension.js',
        ]);
    }
}
