# Pimcore SEO Bundle

Seo bundle for pimcore.

## Features

- **Title & description:**  Easily manage the title and meta description tags of your pages to improve your site's SEO and click-through rates from search engine results.
- **Open Graph:** Enhance your content's social media visibility with Open Graph tags. This feature allows you to define how your content appears when shared on social platforms, improving engagement and reach.
- **Schema markup:** Implement structured data using Schema.org markup to help search engines understand your content better and enhance your site's presence in rich snippets and other search enhancements.
- **Canonical:** Resolve duplicate content issues by specifying the canonical version of your pages, guiding search engines to prioritize the correct page in search results.
- **Set search engine indexing:** Gain control over search engine indexing with the ability to set 'noindex' tags, preventing specific pages from being indexed and appearing in search results.

### Dependencies

| Release | Supported Pimcore Versions | Supported Symfony Versions | Maintained     | Branch |
|---------|----------------------------|----------------------------|----------------|--------|
| **1.x** | `11.0`                     | `6.2`                      | Feature Branch | master |

## Installation

You can install the package via composer:

```bash
composer require ntriga/pimcore-seo-bundle
```

Add Bundle to `bundles.php`:

```php
return [
    Ntriga\PimcoreSeoBundle\NtrigaPimcoreSeoBundle::class => ['all' => true],
];
```

Execute the following command to install the bundle:

```bash
bin/console pimcore:bundle:install NtrigaPimcoreSeoBundle
```
