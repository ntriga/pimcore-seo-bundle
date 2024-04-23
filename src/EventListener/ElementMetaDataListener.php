<?php

namespace Ntriga\PimcoreSeoBundle\EventListener;

use Ntriga\PimcoreSeoBundle\Manager\ElementMetaDataManagerInterface;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\DocumentEvents;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Event\Model\DocumentEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ElementMetaDataListener implements EventSubscriberInterface
{
    public function __construct(protected ElementMetaDataManagerInterface $elementMetaDataManager)
    {}

    public static function getSubscribedEvents(): array
    {
        return [
            DataObjectEvents::PRE_DELETE => 'handleObjectDeletion',
            DocumentEvents::PRE_DELETE => 'handleDocumentDeletion',
        ];
    }

    public function handleDocumentDeletion(DocumentEvent $event): void
    {
        $this->elementMetaDataManager->deleteElementData('document', $event->getDocument()->getId());
    }

    public function handleObjectDeletion(DataObjectEvent $event): void
    {
        $this->elementMetaDataManager->deleteElementData('object', $event->getObject()->getId());
    }
}
