<?php
namespace Craft;

class AssetMetadata_FieldTypeService extends BaseApplicationComponent
{
    // Public Methods
    // =========================================================================

    /**
     * Returns the default values for metadata subfields.
     *
     * Gets an asset's metadata object and assigns each property configured in the field settings
     * its value by rendering the property's default value Twig.
     *
     * @param AssetMetadataFieldType $field
     *
     * @return array
     */
    public function getDefaultValues(AssetMetadataFieldType $field)
    {
        $settings = $field->getSettings();
        $element = $field->element;

        $oldTemplateMode = craft()->templates->getTemplateMode();
        craft()->templates->setTemplateMode(TemplateMode::Site);

        if ($settings->useCustomMetadataVar) {
            $twig = $settings->customMetadataVar;
            $twig .= '{{ metadata|json_encode|raw }}';

            $metadata = craft()->templates->renderString($twig, ['object' => $element]);
            $metadata = json_decode($metadata, true);
        } else {
            $metadata = craft()->assetMetadata->getAssetMetadata($element);
        }

        $defaultValues = [];

        foreach ($settings->subfields as $id => $subfield) {
            try {
                $variables = ['object' => $element, 'metadata' => $metadata];
                $defaultValues[$id] = craft()->templates->renderString($subfield['defaultValue'], $variables);
            } catch (\Exception $e) {
                AssetMetadataPlugin::log('Could not render value for subfield “'.$subfield['handle'].'” ('.$e->getMessage().').', LogLevel::Error);
            }
        }

        craft()->templates->setTemplateMode($oldTemplateMode);

        return $defaultValues;
    }
}
