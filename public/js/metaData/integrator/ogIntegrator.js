pimcore.registerNS('NtrigaSeo.MetaData.Integrator.OpenGraphIntegrator');
NtrigaSeo.MetaData.Integrator.OpenGraphIntegrator = Class.create(NtrigaSeo.MetaData.Integrator.AbstractIntegrator, {
    fieldSetTitle: t('seo_bundle.integrator.og.title'),
    iconClass: 'seo_integrator_icon_icon_og',
    integratorValueFetcher: null,
    imageAwareTypes: ['og:image'],
    fieldTypeProperty: 'og:type',
    previewFields: {
        'og:description': 'description',
        'og:title': 'title',
        'og:image': 'image',
    },

    buildPanel: function () {
        let configuration = this.getConfiguration(),
            lfExtension, params;

        this.integratorValueFetcher = new NtrigaSeo.MetaData.Extension.IntegratorValueFetcher();


        return [{
            xtype: 'panel',
            layout: 'form',
            items: this.generateFields(configuration)

        }];

    },

    generateFields: function (configuration) {
        this.form = new Ext.form.Panel({
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            border: false,
            style: {
                padding: '5px',
            }
        });

        Ext.Array.each(configuration.properties, function (propertyType, propertyId) {
            this.form.add(this.getFieldContainer(propertyType[0], propertyId));
        }.bind(this));

        return this.form;
    },

    getFieldContainer: function (propertyType, propertyId) {
        let propertyTypeStore,
            configuration = this.getConfiguration(),
            field = this.getContentFieldBasedOnType(propertyType, propertyId),
            user = pimcore.globalmanager.get('user');


        return {
            xtype: 'fieldcontainer',
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            style: {
                marginTop: '5px',
                paddingBottom: '5px',
                borderBottom: '1px solid #b1n1n1;'
            },
            items: [
                field
            ]
        }
    },


    getContentFieldBasedOnType: function (propertyTypeValue) {
        let lfExtension, params;

        if (propertyTypeValue === this.fieldTypeProperty){
            return this.generateTypeField();
        } else if (this.imageAwareTypes.indexOf(propertyTypeValue) !== -1){
            return this.generateImageField();
        }

        if (this.configuration.useLocalizedFields === false){
            return this.generateContentField(propertyTypeValue, false, false, null);
        }

        lfExtension = new NtrigaSeo.MetaData.Extension.LocalizedFieldExtension(this.id, this.getAvailableLocales());


        params = {
            showFieldLabel: true,
            fieldLabel: propertyTypeValue,
            editorWindowWidth: 700,
            editorWindowHeight: 300,
            onGridRefreshRequest: function () {
                // this.refreshFieldCallback.call(this)
            }.bind(this),
            onGridStoreRequest: function (locale) {
                return this.onLocalizedGridStoreRequest(this.lfIdentifier, propertyTypeValue, locale)
            }.bind(this),
            onLayoutRequest: this.generateContentField.bind(this, propertyTypeValue, true, true)
        };


        return lfExtension.generateLocalizedField(params);
    },

    generateTypeField: function () {
        const typeStore = new Ext.data.ArrayStore({
            fields: ['label', 'key'],
            data: this.configuration.hasOwnProperty('types') ? this.configuration.types : []
        });

        return {
            xtype: 'combo',
            name: 'og:type',
            value: this.getStoredValue('og:type', null),
            fieldLabel: t('seo_bundle.integrator.property.label_type'),
            displayField: 'label',
            valueField: 'key',
            labelAlign: 'top',
            queryMode: 'local',
            triggerAction: 'all',
            editable: false,
            allowBlank: true,
            width: '100%',
            store: typeStore
        }
    },

    generateImageField: function () {
        let fieldConfig,
            hrefField,
            storagePathHref,
            value = this.getStoredValue('og:image', null);

        fieldConfig = {
            label: t('seo_bundle.integrator.property.asset_path'),
            id: 'og:image',
            config: {
                types: ['asset'],
                subTypes: {
                    asset: ['image']
                }
            }
        };

        hrefField = new NtrigaSeo.MetaData.Extension.HrefFieldExtension(fieldConfig, value, null);
        storagePathHref = hrefField.getHref();

        storagePathHref.on({
            change: function () {
                //this.refreshFieldCallback.call(this);
            }.bind(this)
        });

        return storagePathHref;
    },

    generateContentField: function (type, returnAsArray, isProxy, lfIdentifier, locale) {

        let value = this.getStoredValue(type, locale),
            field = {
                xtype: 'textfield',
                fieldLabel: type,
                labelAlign: 'top',
                name: type,
                value: value,
                width: '100%',
                enableKeyEvents: true,
                listeners: isProxy ? {} : {
                    keyup: function () {
                        //this.refreshFieldCallback.call(this)
                    }.bind(this)
                }
            };

        return returnAsArray ? [field] : field;
    },

    onLocalizedGridStoreRequest: function (lfIdentifier, propertyTypeValue) {
        return [
            {
                title: t('seo_bundle.integrator.property.label_content'),
                storeIdentifier: propertyTypeValue,
                onFetchStoredValue: function (locale) {
                    return this.getStoredValue(propertyTypeValue, locale);
                }.bind(this)
            },
        ];
    },

    getStoredValue: function (name, locale) {
        this.integratorValueFetcher.setStorageData(this.data);
        this.integratorValueFetcher.setEditData(this.getValues());

        return this.integratorValueFetcher.fetch(name, locale);
    },

    getValues: function () {

        let formValues;

        if (this.form === null) {
            return null;
        }

        formValues = this.form.getForm().getValues();

        return this.filterValues(formValues);
    },

    filterValues: function (values) {
        let filteredValues =  {};

        Object.keys(values).forEach(key => {
            if (values[key] !== null && values[key] !== "") {
                filteredValues[key] = values[key];
            }
        });

        return filteredValues;
    }
})
