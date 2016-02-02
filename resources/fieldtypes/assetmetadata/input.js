(function($){

Craft.AssetMetadataInput = Garnish.Base.extend(
{
  id: null,
  name: null,

  fieldId: null,
  elementId: null,

  $field: null,
  $refreshBtn: null,
  $spinner: null,

  init: function(id, name, fieldId, elementId)
  {
    this.id = id;
    this.name = name;

    this.fieldId = fieldId;
    this.elementId = elementId;

    this.$field = $('#'+id+'-field');
    this.$refreshBtn = this.$field.find('.refresh');
    this.$spinner = this.$field.find('.spinner');

    this.addListener(this.$refreshBtn, 'activate', 'updateField');
  },

  updateField: function()
  {
    this.$spinner.removeClass('hidden');

    var params = {
      fieldId: this.fieldId,
      elementId: this.elementId
    };

    Craft.postActionRequest('assetMetadata/getDefaultValues', params, $.proxy(function(response, textStatus)
    {
      $.each(response, $.proxy(function(subfieldId, value)
      {
        this.$field.find('input[name="'+this.name+'['+subfieldId+']"]').val(value);
      }, this));

      this.$spinner.addClass('hidden');
    }, this));
  },

});

})(jQuery);
