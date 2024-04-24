pimcore.registerNS('NtrigaSeo.MetaData.Extension.LocalizedFieldExtension');
NtrigaSeo.MetaData.Extension.LocalizedFieldExtension = Class.create({

    lfIdentifier: null,
    availableLocales: null,
    previewGridConfig: null,
    gridStore: null,
    localizedFieldPanels: {},
    editorWindow: null,
    params: null,

    initialize: function (lfIdentifier, availableLocales) {
        this.lfIdentifier = lfIdentifier;
        this.availableLocales = availableLocales;
        this.previewGridConfig = null;
        this.gridStore = null;
        this.localizedFieldPanels = {};
    },

    getLocales: function () {

        if (!Ext.isArray(this.availableLocales)) {
            return [];
        }

        return this.availableLocales;
    },

    generateLocalizedField: function (params) {

        let grid,
            gridColumns = [],
            storeSelectionFields = ['locale'];

        this.params = {
            showFieldLabel: params.hasOwnProperty('showFieldLabel') ? params.showFieldLabel : false,
            fieldLabel: params.hasOwnProperty('fieldLabel') ? params.fieldLabel : null,
            editorWindowWidth: params.hasOwnProperty('editorWindowWidth') ? params.editorWindowWidth : 600,
            editorWindowHeight: params.hasOwnProperty('editorWindowHeight') ? params.editorWindowHeight : 400,
            onSave: params.hasOwnProperty('onSave') ? params.onSave : null,
            onGridRefreshRequest: params.hasOwnProperty('onGridRefreshRequest') ? params.onGridRefreshRequest : null,
            onLayoutRequest: params.hasOwnProperty('onLayoutRequest') ? params.onLayoutRequest : null,
            onGridStoreRequest: params.hasOwnProperty('onGridStoreRequest') ? params.onGridStoreRequest : null,
        };

        if (typeof this.params.onGridStoreRequest === 'function') {
            this.previewGridConfig = this.params.onGridStoreRequest.call(this, this.lfIdentifier);
            if (Ext.isArray(this.previewGridConfig)) {
                Ext.Array.each(this.previewGridConfig, function (fieldConfigRow) {
                    storeSelectionFields.push(fieldConfigRow.storeIdentifier);
                    gridColumns.push({
                        flex: 2,
                        text: fieldConfigRow.title,
                        dataIndex: fieldConfigRow.storeIdentifier,
                        sortable: false,
                        hideable: false,
                        menuDisabled: true,
                        editor: new Ext.form.TextField({}),
                        renderer: function (v) {
                            let cleanValue = v;

                            if (fieldConfigRow.hasOwnProperty('renderer') && typeof fieldConfigRow.renderer === 'function') {
                                return fieldConfigRow.renderer(v);
                            }

                            if (typeof cleanValue === 'string' || typeof cleanValue === 'number') {
                                cleanValue = this.truncate(cleanValue.toString(), 30);
                            }
                            return (cleanValue === '' || cleanValue === null) ? '--' : cleanValue;
                        }.bind(this)
                    });
                }.bind(this));
            }
        }

        this.gridStore = new Ext.data.Store({
            autoDestroy: true,
            data: {
                data: this.getGridStoreData()
            },
            fields: storeSelectionFields,
            proxy: {
                type: 'memory',
                reader: {
                    type: 'json',
                    rootProperty: 'data'
                }
            },
        });

        grid = {
            xtype: 'grid',
            title: false,
            isFormField: true,
            store: this.gridStore,
            columnLines: true,
            stripeRows: true,
            style: {
                marginBottom: '20px'
            },
            viewConfig: {
                markDirty: false,
            },
            listeners: {
                edit: function () {
                    if (typeof this.params.onGridRefreshRequest === 'function') {
                        this.params.onGridRefreshRequest.call(this);
                    }
                }.bind(this)
            },
            isDirty: function () {
                return true;
            },
            getValue: function (grid) {
                let data = {};

                if (!Ext.isArray(this.previewGridConfig)) {
                    return data;
                }

                Ext.Array.each(this.previewGridConfig, function (fieldConfigRow) {
                    let rowValues = [];
                    grid.getStore().each(function (record) {
                        let rowValue = record.get(fieldConfigRow.storeIdentifier),
                            rowLocale = record.get('locale');

                        rowValues.push({locale: rowLocale, value: rowValue === '' ? null : rowValue});

                    }.bind(this));
                    if (rowValues.length > 0) {
                        data[fieldConfigRow.storeIdentifier] = rowValues
                    }
                }.bind(this));

                return data;
            }.bind(this),
            getSubmitData: function () {
                return this.getValue(this);
            },
            isValid: function () {
                return true
            },
            plugins: [
                Ext.create('Ext.grid.plugin.CellEditing', {
                    clicksToEdit: 1
                })
            ],
            columns: Ext.Array.merge([{
                text: t('seo_bundle.integrator.localized.locale'),
                sortable: false,
                hideable: false,
                menuDisabled: true,
                flex: 1,
                dataIndex: 'locale'
            }], gridColumns),
            bbar: [
                '->',
                {
                    xtype: 'button',
                    scale: 'small',
                    text: t('seo_bundle.integrator.localized.edit'),
                    iconCls: 'pimcore_icon_edit',
                    handler: this.openEditor.bind(this)
                }
            ],
        };

        if (this.params.showFieldLabel === false) {
            return grid;
        }

        return {
            xtype: 'fieldcontainer',
            labelAlign: 'top',
            fieldLabel: this.params.showFieldLabel ? this.params.fieldLabel : false,
            items: [
                grid
            ]
        };
    },

    getGridStoreData: function () {

        let storeData = [];

        if (this.previewGridConfig === null) {
            return [];
        }

        if (!Ext.isArray(this.previewGridConfig)) {
            return [];
        }

        Ext.Array.each(this.getLocales(), function (locale) {
            let record = {locale: locale};
            Ext.Array.each(this.previewGridConfig, function (fieldConfigRow) {
                let value = null;
                if (fieldConfigRow.hasOwnProperty('onFetchStoredValue') && typeof fieldConfigRow.onFetchStoredValue === 'function') {
                    value = fieldConfigRow.onFetchStoredValue.call(this, locale);
                }
                record[fieldConfigRow.storeIdentifier] = value;
            }.bind(this));
            storeData.push(record);
        }.bind(this));

        return storeData;
    },

    openEditor: function () {

        this.editorWindow = new Ext.Window({
            width: this.params.editorWindowWidth,
            height: this.params.editorWindowHeight,
            iconCls: 'pimcore_icon',
            layout: 'fit',
            closeAction: 'destroy',
            plain: true,
            autoScroll: true,
            preventRefocus: true,
            cls: 'localized-field-editor',
            modal: true,
            buttons: [
                {
                    text: t('seo_bundle.integrator.localized.save'),
                    iconCls: 'pimcore_icon_save',
                    handler: this.saveEditorDataAndClose.bind(this)
                },
                {
                    text: t('seo_bundle.integrator.localized.cancel'),
                    iconCls: 'pimcore_icon_cancel',
                    handler: function () {
                        this.editorWindow.close();
                    }.bind(this)
                }
            ]
        });

        this.addDataToEditor();

        this.editorWindow.show();
    },

    addDataToEditor: function () {

        let tabs = [],
            editorField;

        Ext.Array.each(this.getLocales(), function (locale) {

            let layoutFields = [];
            if (typeof this.params.onLayoutRequest === 'function') {
                layoutFields = this.params.onLayoutRequest.call(this, this.lfIdentifier, locale);
            }

            Ext.Array.each(layoutFields, function (field, i) {
                if (field.hasOwnProperty('width') && typeof field.width === 'number') {
                    delete layoutFields[i].width;
                    layoutFields[i].flex = 1;
                    layoutFields[i].forceFit = true;
                }
            }.bind(this));

            let fieldPanel = new Ext.form.FormPanel({
                layout: 'form',
                anchor: '100%',
                items: layoutFields
            });

            tabs.push({
                title: pimcore.available_languages[locale],
                iconCls: 'pimcore_icon_language_' + locale.toLowerCase(),
                layout: 'fit',
                items: [fieldPanel]
            });

            this.localizedFieldPanels[locale] = fieldPanel;

        }.bind(this));

        editorField = new Ext.form.FieldSet({
            cls: 'localized_field',
            layout: 'anchor',
            hideLabel: false,
            items: [{
                xtype: 'tabpanel',
                activeTab: 0,
                layout: 'anchor',
                width: '100%',
                defaults: {
                    autoHeight: true,
                },
                items: tabs
            }]
        });

        this.editorWindow.add(editorField);
    },

    saveEditorDataAndClose: function () {

        Ext.Object.each(this.localizedFieldPanels, function (locale, panel) {
            let values = panel.getForm().getValues(),
                storeRecord = this.gridStore.findRecord('locale', locale);
            if (Ext.isObject(values) && storeRecord) {
                Ext.Object.each(values, function (name, data) {
                    storeRecord.set(name, data);
                });

                storeRecord.commit();
            }
        }.bind(this));

        if (typeof this.params.onGridRefreshRequest === 'function') {
            this.params.onGridRefreshRequest.call(this);
        }

        this.editorWindow.close();
    },

    truncate: function (text, n) {
        let subString;
        if (text.length <= n) {
            return text;
        }

        subString = text.substr(0, n - 1);
        return subString.substr(0, subString.lastIndexOf(' ')) + ' ...';
    }
});
