<?php
namespace Craft;

class AssetMetadataPlugin extends BasePlugin
{
    public function getName()
    {
        return 'Asset Metadata';
    }

    public function getVersion()
    {
        return '2.1.4';
    }

    public function getSchemaVersion()
    {
        return '2.1';
    }

    public function getDeveloper()
    {
        return 'carlcs';
    }

    public function getDeveloperUrl()
    {
        return 'https://github.com/carlcs';
    }

    public function getDocumentationUrl()
    {
        return 'https://github.com/carlcs/craft-assetmetadata';
    }

    public function getReleaseFeedUrl()
    {
        return 'https://github.com/carlcs/craft-assetmetadata/raw/master/releases.json';
    }

    /**
     * Initializes the plugin.
     */
    public function init()
    {
        require __DIR__.'/vendor/autoload.php';

        if (craft()->request->isCpRequest()) {
            craft()->templates->includeJsResource('assetmetadata/fieldtypes/assetmetadata/input.js');
            craft()->templates->includeCssResource('assetmetadata/fieldtypes/assetmetadata/input.css');
        }

        $this->_initEventListeners();
    }

    /**
     * Make sure requirements are met before installation.
     *
     * @return bool
     * @throws Exception
     */
    public function onBeforeInstall()
    {
        if (version_compare(craft()->getBuild(), '2778', '<')) {
            throw new Exception($this->getName().' plugin requires Craft 2.6.2778 or later.');
        }

        if (!defined('PHP_VERSION') || version_compare(PHP_VERSION, '5.4', '<')) {
            throw new Exception($this->getName().' plugin requires PHP 5.4 or later.');
        }
    }

    /**
     * Registers the Twig extension.
     *
     * @return PreparseFieldTwigExtension
     */
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

        foreach ($fields as $field) {
            $fieldType = $field->getFieldType();

            if ($fieldType && $fieldType->getClassHandle() == 'AssetMetadata') {
                $fieldSettings = $fieldType->getSettings();

                foreach ($fieldSettings->subfields as $subfield) {
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
        if (array_key_exists($attribute, $this->defineAdditionalAssetTableAttributes())) {
            $parts = explode(':', $attribute);

            $field = craft()->fields->getFieldById($parts[1]);
            $fieldHandle = $field->getAttribute('handle');

            if (is_array($element->$fieldHandle)) {
                return $element[$fieldHandle][$parts[2]];
            }

            return '';
        }
    }

    // Private Methods
    // =========================================================================

    /**
     * Initializes event listeners
     */
    private function _initEventListeners()
    {
        craft()->on('elements.onBeforeSaveElement', function(Event $event) {
            $element = $event->params['element'];
            $isNewElement = $event->params['isNewElement'];

            $fieldLayout = $element->getFieldLayout();

            if ($fieldLayout) {
                foreach ($fieldLayout->getFields() as $fieldLayoutField) {
                    $field = $fieldLayoutField->getField();

                    if ($field) {
                        $fieldType = $field->getFieldType();

                        if ($fieldType && $fieldType->getClassHandle() === 'AssetMetadata') {
                            if ($isNewElement || $fieldType->getSettings()->refreshOnElementSave) {
                                $fieldType->element = $element;

                                $defaultValues = craft()->assetMetadata_fieldType->getDefaultValues($fieldType);

                                $element->setContentFromPost(array(
                                    $field->handle => $defaultValues
                                ));
                            }
                        }
                    }
                }
            }
        });
    }
}
