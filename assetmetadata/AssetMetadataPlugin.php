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
                return '2.0';
        }

        function getSchemaVersion()
        {
                return '2.0';
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

                                foreach ($fieldSettings->properties as $property)
                                {
                                        $key = 'field:'.$field->id.':'.$property['handle'];

                                        $attributes[$key] = array(
                                                'label' => Craft::t($property['name'])
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

                        return $element[$fieldHandle][$parts[2]];
                }
        }
}
