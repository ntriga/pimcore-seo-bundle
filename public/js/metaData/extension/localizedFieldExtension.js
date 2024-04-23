pimcore.registerNS('PimcoreSeo.MetaData.Extension.LocalizedFieldExtension');
PimcoreSeo.MetaData.Extension.LocalizedFieldExtension = Class.create({

    lfIdentifier: null,
    availableLocales: null,
    previewGridConfig: null,
    gridStore: null,
    localizedFieldPanels: {},
    editorWindow: null,
    params: null,

    initialize: function (lfIdentifier, availableLocales){
        this.lfIdentifier = lfIdentifier;
        this.availableLocales = availableLocales;
        this.previewGridConfig = null;
        this.gridStore = null;
        this.localizedFieldPanels = {};
    },

    getLocales: function () {
        if (!Ext.isArray(this.availableLocales)){
            return [];
        }

        return this.availableLocales;
    },

    generateLocalizedField: function (params) {
        let grid, gridColumns = [], storeSelectionFields = ['locale'];

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

        if (typeof this.params.onGridStoreRequest === 'function'){
            this.previewGridConfig = this.params.onGridStoreRequest.call(this, this.lfIdentifier);
            if (Ext.isArray(this.previewGridConfig)){
                Ext.Array.each(this.previewGridConfig)
            }
        }
    }
})
