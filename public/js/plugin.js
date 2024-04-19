class NtrigaSeoCore {
    constructor() {
        this.ready = true;
        this.configuration = {};
        this.dataQueue = [];

        if (!String.prototype.format){
            String.prototype.format = function (){
                const args = arguments;
                return this.replace(/{(\d+)}/g, function (match, number) {
                    return typeof args[number] != 'undefined' ? args[number] : match;
                })
            }
        }
    }

    getClassName() {
        return 'pimcore.plugin.NtrigaSeo';
    }

    init(){
        Ext.Ajax.request({
            url: '/admin/ntrigaseo/meta-data/get-meta-data-definitions',
            success: function (response) {
                const resp = Ext.decode(response.responseText);

                this.ready = true;
                this.configuration = true;
            }.bind(this)
        });
    }

    postOpenDocument(ev){
        const document = ev.detail.document;

        if (this.ready){
            this.processElement(document, 'page')
        } else {
            this.addElementToQueue(document, 'page');
        }
    }

    addElementToQueue(obj, type){
        this.dataQueue.push({'obj': obj, 'type': type});
    }

    processElement(obj, type){
        // if (type === 'page' && this.configuration.documents.enabled === true && ['page'].indexOf(obj.type) !== -1){
            obj.seoPanel = new NtrigaSeo.MetaData.DocumentMetaDataPanel(obj, this.configuration);
            // obj.seoPanel.setup(type, this.configuration.documents.hide_pimcore_default_seo_panel);
            obj.seoPanel.setup(type, 'false');

        // }
    }
}

const seoCoreHandler = new NtrigaSeoCore()

document.addEventListener(pimcore.events.pimcoreReady, seoCoreHandler.init.bind(seoCoreHandler));
document.addEventListener(pimcore.events.postOpenDocument, seoCoreHandler.postOpenDocument.bind(seoCoreHandler));
