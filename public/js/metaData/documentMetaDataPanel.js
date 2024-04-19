pimcore.registerNS('NtrigaSeo.MetaData.DocumentMetaDataPanel');
NtrigaSeo.MetaData.DocumentMetaDataPanel = Class.create(NtrigaSeo.MetaData.AbstractMetaDataPanel, {

    elementType: null,
    hidePimcoreDefaultSeoPanel: false,

    setup: function (elementType, hidePimcoreDefaultSeoPanel){
        this.elementType = elementType;
        this.hidePimcoreDefaultSeoPanel = hidePimcoreDefaultSeoPanel;

        if (this.hidePimcoreDefaultSeoPanel === true){
            this.tweakPimcoreDefaultSeoPanel();
        }

        this.buildSeoMetaDataTab();
    },

    getElementType: function (){
        return 'document';
    },

    getElementId: function () {
        return this.getElement().id
    },

    tweakPimcoreDefaultSeoPanel: function () {
        let tabPanels, tabPanel;

        tabPanels = this.getElement().tab.query()('tabpanel');
        if (tabPanels.length === 0){
            return;
        }

        tabPanel = tabPanels[0];
        if (!tabPanel.hasOwnProperty('items') || !Ext.isObject(tabPanel.items)){
            return;
        }

        tabPanel.cls = 'seo-pimcore-legacy-tab-panel ' + (tabPanel.cls !== undefined ? tabPanel.cls : '');
        if (tabPanel.hasOwnProperty('layout')){
            tabPanel.layout.deferredRender = false
        }

        tabPanel.items.each(function (tab){
            if (tab.iconCls.indexOf('page_settings') !== -1){
                tab.setTitle(t('settings'));
                tab.items.each(function (tabItem) {
                    if (tabItem.itemId === 'metaDataPanel'){
                        tab.remove(tabItem);
                        tab.insert(0, {
                            xtype: 'label',
                            flex: 1,
                            style: 'display: block; background: #eaeaea; padding: 10px; border: 1px solid #cecece; margin: 20px 0 10px 0;',
                            text: t('seo_bundle.panel.default_pimcore_disabled')
                        })
                    }
                })
            }
        })

    }
})
