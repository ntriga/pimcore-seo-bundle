Ext.define('NtrigaSeo.HrefTextField', {
    extend: 'Ext.form.TextField',

    href: null,
    hrefLocale: null,
    customProperties: {},

    setHrefLocale: function (locale) {
        this.hrefLocale = locale;
    },

    /**
     * @returns {string|null}
     */
    getHrefLocale: function () {
        return this.hrefLocale;
    },

    setHrefObject: function (href) {
        this.href = href;
        this.lastValue = null;
        this.setValue(this.href.hasOwnProperty('path') ? this.href.path : null);
    },

    /**
     * @returns {string|null}
     */
    getValue: function () {
        return this.href;
    },

    getSubmitData: function () {
        let data = {};
        data[this.getName()] = this.href;
        return data;
    }
})
