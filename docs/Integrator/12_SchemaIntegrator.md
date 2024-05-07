# Schema Integrator

The Schema Integrator is quite simple. Just add one or more valid JSON-LD blocks.
The snippet needs to start with `<script type="application/ld+json">` and has to end with `</script>`. Otherwise, it won't get stored.

## Note!
If a dynamic extractor adds JSON-LD data to the given element, the schema integrator is not able to replace the values!
That said, you need to take extra care implementing additional schema blocks - this could lead to dangerous duplicate entries otherwise!

## Configuration
This is the most basic configuration you need to enable this integrator.

```yaml
ntriga_pimcore_seo:
    meta_data_configuration:
        meta_data_integrator:
            enabled_integrator:
                -   integrator_name: schema
```
