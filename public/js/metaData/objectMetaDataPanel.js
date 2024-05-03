pimcore.registerNS('NtrigaSeo.MetaData.ObjectMetaDataPanel');
NtrigaSeo.MetaData.ObjectMetaDataPanel = Class.create(NtrigaSeo.MetaData.AbstractMetaDataPanel, {

    elementType: null,

    setup: function (elementType) {
        this.elementType = elementType;

        this.buildSeoMetaDataTab();
        this.generateMetaDataFields();
    },

    getElementType: function () {
        return 'object';
    },

    getElementId: function () {
        return this.getElement().id;
    },

    generateMetaDataFields: function () {
        // tbd
    },
});
