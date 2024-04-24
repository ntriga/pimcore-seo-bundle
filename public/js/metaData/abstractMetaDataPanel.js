pimcore.registerNS('NtrigaSeo.MetaData.AbstractMetaDataPanel');
NtrigaSeo.MetaData.AbstractMetaDataPanel = Class.create({
    configuration: null,
    element: null,
    integrator: [],

    layout: null,
    tabPanel: null,
    renderAsTab: false,

    initialize: function (element, configuration) {
        this.configuration = configuration;
        this.element = element;
        this.integrator = [];

        if (this.configuration.hasOwnProperty('integrator_rendering_type')){
            this.renderAsTab = this.configuration.integrator_rendering_type === 'tab';
        }
    },

    getElement: function () {
        return this.element;
    },

    buildSeoMetaDataTab: function () {
        this.layout = new Ext.FormPanel({
            title: 'SEO',
            iconCls: 'pimcore_material_icon',
            border: false,
            autoScroll: true,
            bodyStyle: this.renderAsTab ? 'padding: 10px;' : 'padding: 0 10px',
        });

        if (this.renderAsTab === true){
            this.tabPanel = new Ext.TabPanel({
                activeTab: 0,
                layout: 'anchor',
                width: '100%',
                defaults: {
                    autoHeight: true,
                },
            });

            this.layout.add(this.tabPanel);
        }

        this.element.tabbar.add(this.layout);

        this.loadElementMetaData();
    },

    loadElementMetaData: function () {
        Ext.Ajax.request({
            url: '/admin/seo/meta-data/get-element-meta-data-configuration',
            params: {
                elementType: this.getElementType(),
                elementId: this.getElementId()
            },
            success: function (response) {
                const resp = Ext.decode(response.responseText);
                if (resp.success === false){
                    Ext.Msg.alert('error', resp.message);
                    return;
                }
                this.buildMetaDataIntegrator(resp.data, resp.configuration, resp.availableLocales);
            }.bind(this),
            failure: function () {
                Ext.Msg.alert('error', t('seo_bundle.panel.error_fetch_data'));
            }.bind(this)
        });
    },

    buildMetaDataIntegrator: function (data, configuration, availableLocales) {

        console.log('BLA');
        console.log(configuration);

        Ext.Array.each(this.configuration.enabled_integrator, function (integrator) {
            let integratorClass,
                integratorName = integrator['integrator_name'],
                integratorClassName = this.getIntegratorClassName(integratorName),
                integratorConfiguration = configuration !== null && configuration.hasOwnProperty(integratorName) ? configuration[integratorName] : null,
                integratorData = data !== null && data.hasOwnProperty(integratorName) ? data[integratorName] : null;

            if (NtrigaSeo.MetaData.Integrator.hasOwnProperty(integratorClassName)){
                integratorClass = new NtrigaSeo.MetaData.Integrator[integratorClassName](this.getElementType(), this.getElementId(), integratorName, integratorConfiguration, availableLocales, integratorData, this.renderAsTab);
                console.log(integratorConfiguration);
                this.integrator.push(integratorClass);
                this[this.renderAsTab === true ? 'tabPanel' : 'layout'].add(integratorClass.buildLayout());
            } else{
                console.warn('Integrator class NtrigaSeo.MetaData.Integrator.' + integratorClassName + ' not found!');
            }
        }.bind(this));

        if (this.renderAsTab === true){
            this.tabPanel.setActiveTab(0);
        }
    },

    save: function () {
        console.log('SAVE');
    },

    getIntegratorValues: function () {
        let values = {};
        Ext.Array.each(this.integrator, function (integrator) {
            values[integrator.getType()] = integrator.getValues();
        });

        return values;
    },

    getIntegratorClassName: function (integratorName) {
        const name = integratorName.replace(/(\_\w)/g, function (m) {
            return m[1].toUpperCase();
        });

        return ucfirst(name) + 'Integrator';
    }
})
