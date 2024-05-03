pimcore.registerNS('NtrigaSeo.MetaData.Integrator.IndexIntegrator');
NtrigaSeo.MetaData.Integrator.IndexIntegrator = Class.create(NtrigaSeo.MetaData.Integrator.AbstractIntegrator, {

    fieldSetTitle: t('seo_bundle.integrator.index.title'),
    iconClass: 'seo_integrator_icon_icon_index',
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
        let indexValue = this.getStoredValue('index', locale),
            configuration = this.getConfiguration();


        console.log(indexValue);

        return [
            {
                xtype: 'checkboxfield',
                fieldLabel: t('seo_bundle.integrator.index.checkbox'),
                name: 'index',
                itemId: 'index',
                maxLength: 255,
                value: indexValue !== null ? 'true' : 'false',
            }
        ]
    },

    onLocalizedGridStoreRequest: function (lfIdentifier) {
        return [
            {
                title: t('seo_bundle.integrator.index.title'),
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

        console.log('formValues', formValues);

        return formValues;
    }

})
