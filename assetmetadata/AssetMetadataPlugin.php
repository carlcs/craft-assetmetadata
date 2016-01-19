<?php
namespace Craft;

class AssetMetadataPlugin extends BasePlugin
{
        function getName()
        {
                return 'Asset Metadata';
        }

        function getVersion()
        {
                return '2.1';
        }

        function getSchemaVersion()
        {
                return '2.1';
        }

        function getDeveloper()
        {
                return 'carlcs';
        }

        function getDeveloperUrl()
        {
                return 'https://github.com/carlcs/craft-assetmetadata';
        }

        function getDocumentationUrl()
        {
                return 'https://github.com/carlcs/craft-assetmetadata';
        }

        function getReleaseFeedUrl()
        {
                return 'https://github.com/carlcs/craft-assetmetadata/raw/master/releases.json';
        }

        public function init()
        {
                require __DIR__.'/vendor/autoload.php';

                if (craft()->request->isCpRequest())
                {
                        craft()->templates->includeJsResource('assetmetadata/fieldtypes/assetmetadata/input.js');
                        craft()->templates->includeCssResource('assetmetadata/fieldtypes/assetmetadata/input.css');
                }
        }

        public function addTwigExtension()
        {
                Craft::import('plugins.assetmetadata.twigextensions.AssetMetadataTwigExtension');
                return new AssetMetadataTwigExtension();
        }

        /**
         * Defines additional columns that can be shown in Table Views.
         *
         * @return array The table attributes.
         */
        public function defineAdditionalAssetTableAttributes()
        {
                $fields = craft()->fields->getFieldsByElementType('asset');
                $attributes = array();

                foreach ($fields as $field)
                {
                        $fieldType = $field->getFieldType();

                        if ($fieldType && $fieldType->getClassHandle() == 'AssetMetadata')
                        {
                                $fieldSettings = $fieldType->getSettings();

                                foreach ($fieldSettings->subfields as $subfield)
                                {
                                        $key = 'field:'.$field->id.':'.$subfield['handle'];

                                        $attributes[$key] = array(
                                                'label' => Craft::t($subfield['name'])
                                        );
                                }
                        }
                }

                return $attributes;
        }

        /**
         * Returns the HTML that should be shown for a given element's attribute in Table View.
         *
         * @param BaseElementModel $element   The element.
         * @param string           $attribute The attribute name.
         *
         * @return string
         */
        public function getAssetTableAttributeHtml($element, $attribute)
        {
                if (array_key_exists($attribute, $this->defineAdditionalAssetTableAttributes()))
                {
                        $parts = explode(':', $attribute);

                        $field = craft()->fields->getFieldById($parts[1]);
                        $fieldHandle = $field->getAttribute('handle');

                        if (is_array($element->$fieldHandle))
                        {
                                return $element[$fieldHandle][$parts[2]];
                        }

                        return '';
                }
        }
}
