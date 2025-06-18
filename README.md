# Pimcore SEO Bundle

Seo bundle for pimcore.

## Features

-   **Title & description:** Easily manage the title and meta description tags of your pages to improve your site's SEO and click-through rates from search engine results.
-   **Open Graph:** Enhance your content's social media visibility with Open Graph tags. This feature allows you to define how your content appears when shared on social platforms, improving engagement and reach.
-   **Schema markup:** Implement structured data using Schema.org markup to help search engines understand your content better and enhance your site's presence in rich snippets and other search enhancements.
-   **Canonical:** Resolve duplicate content issues by specifying the canonical version of your pages, guiding search engines to prioritize the correct page in search results.
-   **Set search engine indexing:** Gain control over search engine indexing with the ability to set 'noindex' tags, preventing specific pages from being indexed and appearing in search results.
-   **Auto Pretty URLs:** Automatically generate SEO-friendly pretty URLs for pages based on their title or key, improving URL structure and user experience.

### Dependencies

| Release | Supported Pimcore Versions | Supported Symfony Versions | Maintained     | Branch |
| ------- | -------------------------- | -------------------------- | -------------- | ------ |
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

## Default configuration

Default configuration for the bundle can look like this:

```yaml
ntriga_pimcore_seo:
    meta_data_configuration:
        meta_data_provider:
            auto_detect_documents: true
        meta_data_integrator:
            documents:
                enabled: true
                hide_pimcore_default_seo_panel: true
            objects:
                enabled: true
                data_classes:
                    - Product
            enabled_integrator:
                - integrator_name: title_description
                - integrator_name: open_graph
                  integrator_config:
                      facebook_image_thumbnail: "socialThumb"
                - integrator_name: schema
                - integrator_name: canonical
                - integrator_name: index
    pretty_url:
        auto_generate: true
```

## Further configuration

For more information about the setup, check [Setup](./docs/00_Setup.md)

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

-   [Joey Daems](https://github.com/JoeyNtriga)
-   [DACHCOM.DIGITAL](https://github.com/dachcom-digital)
-   [All contributors](../../contributors)

## License

GNU General Public License version 3 (GPLv3). Please see [License File](./LICENSE.md) for more information.
