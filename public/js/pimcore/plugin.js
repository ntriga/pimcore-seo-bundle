class NtrigaSeoCore {
    constructor() {
        this.ready = false;
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
        console.log('Hallo van de SEO bundel')
    }
}
