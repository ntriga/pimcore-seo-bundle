pimcore.registerNS('NtrigaSeo.MetaData.Integrator.AbstractIntegrator');
NtrigaSeo.MetaData.Integrator.AbstractIntegrator = Class.create({

    fieldSetTitle: 'Abstract Integrator',
    iconClass: false,

    previewContainerIsLoading: false,
    elementType: null,
    elementId: null,

    type: null,
    configuration: null,
    availableLocales: null,
    data: null,

    formPanel: null,
    fieldSet: null,
    previewContainerItem: null,
    previewContainerTemplate: null,
    delayedRefreshTask: null,
    renderAsTab: false,
    isInShutdownMode: false,

    initialize: function (elementType, elementId, type, configuration, availableLocales, data, renderAsTab){
        this.elementType =  elementType;
        this.elementId = elementId;
        this.type = type;
        this.configuration = configuration;
        this.availableLocales = availableLocales;
        this.data = data;
        this.renderAsTab = renderAsTab;
        // this.delayedRefreshTask = new Ext.util.DelayedTask(this.refreshLivePreview.bind(this));

    },

    getType: function () {
        return this.type;
    },

    getConfiguration: function () {
        return this.configuration;
    },

    getAvailableLocales: function () {
        return this.availableLocales;
    },

    hasData: function () {
        return this.data !== null;
    },

    isEmptyValue: function (value) {
        return value === '' || value === null;
    },

    hasLivePreview: function () {
        let configuration = this.getConfiguration();

        if (configuration === null){
            return false;
        }

        if (!configuration.hasOwnProperty('hasLivePreview')){
            return false;
        }

        return configuration.hasLivePreview === true;
    },

    getLivePreviewTemplates: function () {
        let configuration = this.getConfiguration();

        if (configuration === null){
            return null;
        }

        if (!configuration.hasOwnProperty('livePreviewTemplates')){
            return null;
        }

        if (!Ext.isArray(configuration.livePreviewTemplates) || configuration.livePreviewTemplates.length === 0){
            return null;
        }

        return configuration.livePreviewTemplates;
    },

    /**
     * @abstract
     */
    isCollapsed: function () {
        return false;
    },

    /**
     * @abstract
     */
    getValues: function (fieldSetTitle) {
        return [];
    },

    /**
     * @abstract
     */
    getValuesForPreview: function () {
        return [];
    },

    /**
     * @abstract
     */
    getStoredValue: function (name, locale) {
        return null;
    },

    /**
     * @abstract
     */
    buildLayout: function () {
        let panelItems;

        this.formPanel = new Ext.form.Panel({
            title: this.renderAsTab ? this.fieldSetTitle : false,
            iconCls: this.renderAsTab ? this.iconClass : false,
            style: {
                padding: this.renderAsTab ? '20px' : 0
            }
        });

        this.fieldSet = new Ext.form[this.renderAsTab ? 'Panel' : 'FieldSet']({
            title: this.renderAsTab ? false : this.fieldSetTitle,
            iconCls: this.renderAsTab ? false : this.iconClass,
            layout: {
                type: 'hbox'
            },
            collapsible: !this.renderAsTab,
            collapsed: this.renderAsTab ? false : this.isCollapsed(),
            defaults: {
                labelWidth: 200
            }
        });

        panelItems = [{
            xtype: 'panel',
            flex: 4,
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            items: this.buildPanel()
        }];

        // panelItems = this.generateLivePreviewPanel(panelItems);

        this.fieldSet.add(panelItems);

        this.formPanel.add(this.fieldSet);
        this.formPanel.on('destroy', function () {
            this.isInShutDownMode = true;
        }.bind(this));

        return this.formPanel;
    },




})
