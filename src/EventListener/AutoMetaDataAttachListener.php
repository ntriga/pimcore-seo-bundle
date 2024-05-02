<?php

namespace Ntriga\PimcoreSeoBundle\EventListener;

use Ntriga\PimcoreSeoBundle\MetaData\MetaDataProviderInterface;
use Pimcore\Http\Request\Resolver\DocumentResolver;
use Pimcore\Http\Request\Resolver\PimcoreContextResolver;
use Pimcore\Http\RequestHelper;
use Pimcore\Model\Document\Page;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AutoMetaDataAttachListener implements EventSubscriberInterface
{
    public function __construct(
        protected array $configuration,
        protected MetaDataProviderInterface $metaDataProvider,
        protected RequestHelper $requestHelper,
        protected PimcoreContextResolver $pimcoreContextResolver,
        protected DocumentResolver $documentResolverService
    )
    {}

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', -255]
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (php_sapi_name() === 'cli'){
            return;
        }

        if ($this->configuration['auto_detect_documents'] === false){
            return;
        }

        if ($event->isMainRequest() === false){
            return;
        }

        if ($this->pimcoreContextResolver->matchesPimcoreContext($request, PimcoreContextResolver::CONTEXT_ADMIN)){
            return;
        }

        if (!$this->pimcoreContextResolver->matchesPimcoreContext($request, PimcoreContextResolver::CONTEXT_DEFAULT)){
            return;
        }

        if ($this->requestHelper->isFrontendRequestByAdmin($request)){
            return;
        }

        $document = $this->documentResolverService->getDocument($request);
        if (!$document instanceof Page){
            return;
        }

        $this->metaDataProvider->updateSeoElement($document, $request->getLocale());
    }
}
