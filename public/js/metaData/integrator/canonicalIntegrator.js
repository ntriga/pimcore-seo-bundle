pimcore.registerNS('NtrigaSeo.MetaData.Integrator.CanonicalIntegrator');
NtrigaSeo.MetaData.Integrator.CanonicalIntegrator = Class.create(NtrigaSeo.MetaData.Integrator.AbstractIntegrator, {

    fieldSetTitle: t('seo_bundle.integrator.canonical.title'),
    iconClass: 'seo_integrator_icon_icon_canonical',
    schemaPanel: null,

    isCollapsed: function () {
        return !this.hasData();
    },

    buildPanel: function () {
        let configuration = this.getConfiguration(),
            lfExtension,
            params;

        this.integratorValueFetcher = new NtrigaSeo.MetaData.Extension.IntegratorValueFetcher();


        if (configuration.useLocalizedFields === false){
            return[{
                xtype: 'panel',
                layout: 'form',
                items: this.generateFields(false, null)
            }];
        }

        lfExtension = new NtrigaSeo.MetaData.Extension.LocalizedFieldExtension(null, this.getAvailableLocales());

        params = {
            showFieldLabel: false,
            onGridStoreRequest: this.onLocalizedGridStoreRequest.bind(this),
            onLayoutRequest: this.generateFields.bind(this, true)
        };

        return [lfExtension.generateLocalizedField(params)];
    },

    generateFields: function (isProxy, lfIdentifier, locale) {
        let storedCanonical = this.getStoredValue('canonical', locale),
            configuration = this.getConfiguration(),
            canonicalValue = storedCanonical !== null ? storedCanonical : configuration['defaultCanonical'];

        console.log(this.getConfiguration());

        return [
            {
                xtype: 'textarea',
                fieldLabel: t('seo_bundle.integrator.title_description.single_title'),
                name: 'canonical',
                itemId: 'canonical',
                maxLength: 255,
                height: 60,
                value: canonicalValue,
            }
        ]
    },

    onLocalizedGridStoreRequest: function (lfIdentifier) {
        return [
            {
                title: t('seo_bundle.integrator.canonical.title'),
                storeIdentifier: 'canonical',
                onFetchStoredValue: function (locale) {
                    return this.getStoredValue('canonical', locale)
                }.bind(this)
            }
        ];
    },

    getStoredValue: function (name, locale) {
        this.integratorValueFetcher.setStorageData(this.data);
        this.integratorValueFetcher.setEditData(this.getValues());

        return this.integratorValueFetcher.fetch(name, locale);
    },

    getValues: function () {
        let formValues;

        if (this.formPanel === null){
            return {};
        }

        formValues = this.formPanel.form.getValues();

        return formValues;
    }
})
