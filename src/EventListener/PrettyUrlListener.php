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

        // Skip root documents and system pages
        if ($this->shouldSkipDocument($document)) {
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

    private function shouldSkipDocument(Page $document): bool
    {
        // Skip root documents (ID = 1 or parent ID = 0)
        if ($document->getId() === 1 || $document->getParentId() === 0) {
            return true;
        }

        // Skip if document key suggests it's a system/root page
        $key = $document->getKey();
        if (in_array($key, ['home', 'root', 'index'], true)) {
            return true;
        }

        // Skip if document is at root level (path is just "/key")
        $path = $document->getRealFullPath();
        if ($path && substr_count($path, '/') <= 1) {
            return true;
        }

        return false;
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

        // Get locale from document property (don't inherit from parent)
        $locale = $this->getDocumentLocale($document);

        if (!empty($locale)) {
            $prettyUrl .= $locale . '/';
        }

        // Ensure slug doesn't start with slash and add it to the URL
        $prettyUrl .= ltrim($slug, '/');

        return $prettyUrl;
    }

    private function getDocumentLocale(Page $document): ?string
    {
        // Get locale directly from document properties (not inherited)
        $locale = $document->getProperty('language', false); // false = don't inherit

        if (empty($locale) || !is_string($locale)) {
            return null;
        }

        $locale = trim($locale);

        // Validate locale format and normalize
        if (preg_match('/^[a-z]{2}(?:[-_][A-Z]{2})?$/i', $locale)) {
            // Normalize to lowercase with dash separator
            $locale = strtolower($locale);
            $locale = str_replace('_', '-', $locale);

            // Additional validation: ensure it's not the same as parent's locale
            // This prevents inheriting parent language when it shouldn't
            if ($this->isInheritedLocale($document, $locale)) {
                return null;
            }

            return $locale;
        }

        return null;
    }

    private function isInheritedLocale(Page $document, string $locale): bool
    {
        $parent = $document->getParent();

        if (!$parent instanceof Page) {
            return false;
        }

        // Check if parent has the same locale
        $parentLocale = $parent->getProperty('language', false);

        if (!empty($parentLocale) && is_string($parentLocale)) {
            $parentLocale = strtolower(str_replace('_', '-', trim($parentLocale)));
            return $parentLocale === $locale;
        }

        return false;
    }
}
