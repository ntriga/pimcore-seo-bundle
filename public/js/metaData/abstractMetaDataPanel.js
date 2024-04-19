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

        console.log(this.configuration.hasOwnProperty('integrator_rendering_type'));

        // if (this.configuration.hasOwnProperty('integrator_rendering_type')){
            this.renderAsTab = this.configuration.integrator_rendering_type === 'tab';
        // }
    },

    getElement: function () {
        return this.element;
    },

    buildSeoMetaDataTab: function () {
        console.log('BUILDING');
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
    }
})
