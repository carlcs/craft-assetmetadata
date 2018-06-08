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

        this.$field = $('#'+this.settings.id+'-field');
        this.$refreshBtn = this.$field.find('.assetmetadata-refresh');
        this.$spinner = this.$field.find('.spinner');

        this.addListener(this.$refreshBtn, 'activate', 'updateField');
    },

    updateField: function() {
        this.$spinner.removeClass('hidden');

        var params = {
            fieldId: this.settings.fieldId,
            elementId: this.settings.elementId
        };

        Craft.postActionRequest('asset-metadata/metadata/get-field-value', params, $.proxy(function(response, textStatus) {
            $.each(response, $.proxy(function(subfieldId, value) {
                this.$field.find('input[name="'+this.settings.name+'['+subfieldId+']"]').val(value);
            }, this));

            this.$spinner.addClass('hidden');
        }, this));
    },
});

})(jQuery);
