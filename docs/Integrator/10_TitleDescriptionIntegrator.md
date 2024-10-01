# Title & Description
This Integrator allows you to define title and description for a given document/object.

## Configuration

# Default Configuration
This is the most basic configuration you need to enable this integrator.

```yaml
seo:
    meta_data_configuration:
        meta_data_integrator:
            enabled_integrator:
                -   integrator_name: title_description
```
## Default Title
By default the bundle will try to get a title for the page.
### Document
For documents it wil try to use available values in the following order:
1. Navigation Title
2. Navigation Name
3. Document Key

### DataObject
For DataObjects the bundle will try to get the title via some common fields, if not available it will use the key.
This is the order that will be followed:
1. `getTitle()`
2. `getName()`
3. `getKey()`
