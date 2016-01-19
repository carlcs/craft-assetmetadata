<?php
namespace Craft;

class AssetMetadataController extends BaseController
{
        /**
         * Renders and returns the body of a KmlImportModal.
         *
         * @return null
         */
        public function actionGetDefaultValues()
        {
                $fieldId = craft()->request->getPost('fieldId');
                $elementId = craft()->request->getPost('elementId');

                $element = craft()->elements->getElementById($elementId);

                if ($element)
                {
                        $field = craft()->fields->populateFieldType(craft()->fields->getFieldById($fieldId), $element);
                        $defaultValues = craft()->assetMetadata_fieldType->getDefaultValues($field);

                        $this->returnJson($defaultValues);
                }
        }
}
