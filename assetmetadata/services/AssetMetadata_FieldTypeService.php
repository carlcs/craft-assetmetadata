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

        $oldPath = craft()->path->getTemplatesPath();
        craft()->path->setTemplatesPath(craft()->path->getSiteTemplatesPath());

        if ($settings->useCustomMetadataVar) {
            $twig = $settings->customMetadataVar;
            $twig .= '{{ metadata|json_encode|raw }}';

            $metadata = craft()->templates->renderString($twig, array('object' => $element));
            $metadata = json_decode($metadata, true);
        } else {
            $metadata = craft()->assetMetadata->getAssetMetadata($element);
        }

        $defaultValues = array();

        foreach ($settings->subfields as $id => $subfield) {
            $variables = array('object' => $element, 'metadata' => $metadata);

            $defaultValues[$id] = craft()->templates->renderString($subfield['defaultValue'], $variables);
        }

        craft()->path->setTemplatesPath($oldPath);

        return $defaultValues;
    }
}
