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
        let storedCanonical = this.getStoredValue('canonical', locale) ?? null,
            configuration = this.getConfiguration(),
            defaultKey = configuration['useLocalizedFields'] ? locale : 'default',
            canonicalValue = storedCanonical !== null ? storedCanonical : configuration['defaultCanonical'][defaultKey];

        return [
            {
                xtype: 'textfield',
                fieldLabel: t('seo_bundle.integrator.canonical.title'),
                name: 'canonical',
                itemId: 'canonical',
                maxLength: 255,
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
        let formValues, configuration = this.getConfiguration();

        if (this.formPanel === null){
            return {};
        }

        formValues = this.formPanel.form.getValues();
        const defaultCanonical = configuration['defaultCanonical'];
        let outputValues = {canonical: []};

        if (configuration['useLocalizedFields'] === true && formValues['canonical']) {
            Ext.Array.each(formValues['canonical'], function (localizedInput) {
                if (localizedInput && localizedInput.value !== defaultCanonical[localizedInput.locale]) {
                    outputValues['canonical'].push({
                        locale: localizedInput.locale,
                        value: localizedInput.value
                    });
                } else {
                    outputValues['canonical'].push({
                        locale: localizedInput.locale,
                        value: ''
                    });
                }
            });
        } else {
            if (defaultCanonical['default'] === formValues['canonical']) {
                outputValues['canonical'] = '';
            } else {
                outputValues['canonical'] = formValues['canonical'];
            }
        }

        return outputValues;
    }
})
