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

    public function addCssFiles(PathsEvent $event)
    {
        $event->addPaths([
            '/bundles/ntrigapimcoreseo/css/admin.css'
        ]);
    }

    public function addJsFields(PathsEvent $event)
    {
        $event->addPaths([
            '/bundles/ntrigapimcoreseo/js/pimcore/plugin.js',
            '/bundles/ntrigapimcoreseo/js/pimcore/startup.js'
        ]);
    }
}
