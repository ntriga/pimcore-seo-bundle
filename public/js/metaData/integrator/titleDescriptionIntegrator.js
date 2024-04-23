pimcore.registerNS('NtrigaSeo.MetaData.Integrator.TitleDescriptionIntegrator');
NtrigaSeo.MetaData.Integrator.TitleDescriptionIntegrator = Class.create(NtrigaSeo.MetaData.Integrator.AbstractIntegrator, {
    fieldSetTitle: t('seo_bundle.integrator.title_description.title'),
    iconClass: 'seo_integrator_icon_title_description',
    integratorValueFetcher: null,
    legacyDocumentTitleField: null,
    legacyDocumentDescriptionField: null,

    initLegacyDocumentFields: function (panel) {
        let documentPanel;

        if (this.elementType !== 'document'){
            return;
        }

        documentPanel = panel.up('panel[id=document_' + this.elementId +']').query('tabpanel[cls~=seo-pimcore-legacy-tab-panel]');

        if (documentPanel.length === 0){
            return;
        }

        documentPanel[0].items.each(function (tab) {
            if (tab.iconCls.indexOf('page_settings') !== -1){
                this.legacyDocumentTitleField = new Ext.form.Hidden({
                    name: 'title',
                    value: this.getStoredValue('title', null)
                });
                this.legacyDocumentDescriptionField = new Ext.form.Hidden({
                    name: 'description',
                    value: this.getStoredValue('description', null)
                });
                tab.add([this.legacyDocumentTitleField, this.legacyDocumentTitleField]);
            }
        }.bind(this));
    },

    buildPanel: function () {
        let configuration = this.getConfiguration(), lfExtension, params;

        this.integratorValueFetcher = new NtrigaSeo.MetaData.Extension.IntegratorValueFetcher();

        //TODO: integrate live preview refresh
        this.fieldSet.on('afterrender', this.initLegacyDocumentFields.bind(this));

        if (configuration.useLocalizedFields === false){
            return [{
                xtype: 'panel',
                layout: 'form',
                items: this.generateFields(false, null)
            }];
        }

        lfExtension =

    }
})
