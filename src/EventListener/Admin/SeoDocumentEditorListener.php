<?php

namespace Ntriga\PimcoreSeoBundle\EventListener\Admin;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class SeoDocumentEditorListener implements EventSubscriberInterface
{
    public function __construct(
        protected RequestStack $requestStack,
    )
    {
    }
}
