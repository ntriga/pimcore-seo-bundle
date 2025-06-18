<?php

namespace Ntriga\PimcoreSeoBundle\EventListener;

use Pimcore\Event\DocumentEvents;
use Pimcore\Event\Model\DocumentEvent;
use Pimcore\Model\Document\Page;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class PrettyUrlListener implements EventSubscriberInterface
{
    public function __construct(
        protected bool $enabled,
        protected SluggerInterface $slugger
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            DocumentEvents::PRE_ADD => 'onDocumentPreSave',
            DocumentEvents::PRE_UPDATE => 'onDocumentPreSave',
        ];
    }

    public function onDocumentPreSave(DocumentEvent $event): void
    {
        if (!$this->enabled) {
            return;
        }

        $document = $event->getDocument();

        // Only process pages
        if (!$document instanceof Page) {
            return;
        }

        // Skip if pretty URL is already set
        $existingPrettyUrl = $document->getPrettyUrl();
        if (!empty($existingPrettyUrl) && trim($existingPrettyUrl) !== '') {
            return;
        }

        // Generate slug from title or key as fallback
        $source = $this->getSourceForSlug($document);

        if (empty($source)) {
            return;
        }

        try {
            $slug = $this->slugger->slug($source)->lower()->toString();

            if (empty($slug)) {
                return;
            }

            $prettyUrl = $this->buildPrettyUrl($document, $slug);
            $document->setPrettyUrl($prettyUrl);
        } catch (\Exception $e) {
            return;
        }
    }

    private function getSourceForSlug(Page $document): ?string
    {
        // Try title first, then key, then fallback to ID
        $source = $document->getTitle();

        if (empty($source) || trim($source) === '') {
            $source = $document->getKey();
        }

        if (empty($source) || trim($source) === '') {
            $source = 'page-' . $document->getId();
        }

        return $source;
    }

    private function buildPrettyUrl(Page $document, string $slug): string
    {
        $prettyUrl = '/';

        // Get locale from document property
        $locale = $document->getProperty('language');

        if (!empty($locale) && is_string($locale) && trim($locale) !== '') {
            $locale = trim($locale);
            // Validate locale format (basic validation)
            if (preg_match('/^[a-z]{2}(?:[-_][A-Z]{2})?$/', $locale)) {
                $prettyUrl .= strtolower($locale) . '/';
            }
        }

        // Ensure slug doesn't start with slash and add it to the URL
        $prettyUrl .= ltrim($slug, '/');

        return $prettyUrl;
    }
}
