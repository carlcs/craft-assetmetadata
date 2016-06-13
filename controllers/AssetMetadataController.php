<?php
namespace Craft;

class AssetMetadataController extends BaseController
{
    /**
     * Returns the default values for metadata subfields.
     *
     * @return null
     */
    public function actionGetDefaultValues()
    {
        $fieldId = craft()->request->getPost('fieldId');
        $elementId = craft()->request->getPost('elementId');

        $field = craft()->fields->getFieldById($fieldId);
        $element = craft()->elements->getElementById($elementId);

        if ($field && $element) {
            $fieldType = craft()->fields->populateFieldType($field, $element);

            $defaultValues = craft()->assetMetadata_fieldType->getDefaultValues($fieldType);

            $this->returnJson($defaultValues);
        }
    }
}
