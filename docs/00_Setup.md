# Setup

After the instalation is complete and the bundle is enabled, there are some steps to make sure everything works correctly.

## Frontend
### Default layout
Your default layout metadata head should look like this. If you've enabled the `auto_detect_documents` feature, all documents will automatically be rendered with the correct metadata.

```twig
{# layout.html.twig #}

<!DOCTYPE html>
<html>
<head>
    {% block metadata %}
        {{ pimcore_head_title() }}
        {{ pimcore_head_meta() }}
        {{ pimcore_head_link() }}
    {% endblock metadata %}
</head>
<body>
{% block content %}
    <p>Hello world.</p>
{% endblock content %}
</body>
</html>
```

### Sub-Layout
On a special route - mostly routes generated for DataObjects - you need to inform the meta provider to extract metadata from your object.

```twig
{# product/detail.html.twig #}

{% extends 'layout.html.twig' %}

{% block metadata %}
    {% do seo_update_metadata(object, app.request.locale) %}
    {{ parent() }}
{% endblock metadata %}

{% block content %}
    {# my object layout #}
{% endblock content %}
```

## Hide Default Pimcore SEO Features
When you use this bundle you may not want to use the default SEO panel in the document editor.
To disable the panel in the backend you can set `hide_pimcore_default_seo_panel` to `true`.

> **Note**: Already populated fields like title, description and all meta fields will automatically moved to the seo bundle context. No migration needed! 

```yaml
ntriga_pimcore_seo:
    meta_data_configuration:
        meta_data_integrator:
            documents:
                enabled: true
                hide_pimcore_default_seo_panel: true
```

## Configuration
This is an example of how the configuration could look like:

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
                -   integrator_name: title_description
                -   integrator_name: open_graph
                    integrator_config:
                        facebook_image_thumbnail: 'socialThumb'
                -   integrator_name: schema
                -   integrator_name: canonical
                -   integrator_name: index
```

