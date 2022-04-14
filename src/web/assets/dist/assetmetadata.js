(function($){

if (typeof Craft.AssetMetadata === typeof undefined) {
    Craft.AssetMetadata = {};
}

Craft.AssetMetadata.Field = Garnish.Base.extend({
    $field: null,
    $refreshBtn: null,
    $spinner: null,

    init: function(settings) {
        this.setSettings(settings);

        this.$field = $(`#${this.settings.id}-field`);
        this.$refreshBtn = this.$field.find('.assetmetadata-refresh');
        this.$spinner = this.$field.find('.spinner');

        this.addListener(this.$refreshBtn, 'activate', 'updateField');
    },

    updateField: function() {
        this.$spinner.removeClass('hidden');

        const data = {
            fieldId: this.settings.fieldId,
            elementId: this.settings.elementId,
        };

        Craft.sendActionRequest('POST', 'asset-metadata/metadata/get-field-value', { data })
            .then(({ data }) => {
                this.$spinner.addClass('hidden');
                data.forEach((value, index) => {
                    const input = this.$field.find(`input[name="${this.settings.name}[${index}]"]`);
                    input.val(value);
                });
            });
    },
});

})(jQuery);
