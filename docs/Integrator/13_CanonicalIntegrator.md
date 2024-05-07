# Canonical Integrator
This Integrator automatically generates a canonical for every page in your project. It also allows you to change the default url and set your own.
For the default canonical to be automatically generated, make sure your [link generators](https://pimcore.com/docs/5.x/Development_Documentation/Objects/Object_Classes/Class_Settings/Link_Generator.html) for objects are up and running! 

## configuration

### Default Configuration
This is the most basic configuration you need to enable this integrator.

```yaml
ntriga_pimcore_seo:
    meta_data_configuration:
        meta_data_integrator:
            enabled_integrator:
                -   integrator_name: canonical
```
