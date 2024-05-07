# Integrators
Before you can use integrators, you need to add them in your product setup:

```yaml
ntriga_pimcore_seo:
    meta_data_configuration:
        meta_data_integrator:
            enabled_integrator:
                -   integrator_name: title_description
                -   integrator_name: open_graph
                    integrator_config:
                        facebook_image_thumbnail: 'socialThumb'
                -   integrator_name: schema
                -   integrator_name: canonical
                -   integrator_name: index
```

## Available Integrators

- [Title & Description Integrator](./Integrator/10_TitleDescriptionIntegrator.md)
- [Open Graph Integrator](./Integrator/11_OpenGraphIntegrator.md)
- [Schema Integrator](./Integrator/12_SchemaIntegrator.md)
- [Canonical Integrator](./Integrator/13_CanonicalIntegrator.md)
- [Index Integrator](./Integrator/14_IndexIntegrator.md)
