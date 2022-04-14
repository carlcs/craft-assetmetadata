<?php

namespace carlcs\assetmetadata;

use carlcs\assetmetadata\fields\AssetMetadata as AssetMetadataField;
use carlcs\assetmetadata\services\Metadata;
use carlcs\assetmetadata\web\twig\Extension;
use Craft;
use craft\base\Element;
use craft\base\Model;
use craft\elements\Asset;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterElementTableAttributesEvent;
use craft\events\SetElementTableAttributeHtmlEvent;
use craft\services\Fields;
use yii\base\Event;

/**
 * @property Metadata $metadata
 * @property Settings $settings
 * @method Settings getSettings()
 * @method static Plugin getInstance()
 */
class Plugin extends \craft\base\Plugin
{
    // Properties
    // =========================================================================

    public string $schemaVersion = '3.0.0';
    public string $minVersionRequired = '2.1.1';

    private array $_tableAttributes = [];

    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        $this->set('metadata', Metadata::class);

        Craft::$app->getView()->registerTwigExtension(new Extension());

        $this->_registerFieldTypes();
        $this->_registerAssetTableAttributes();
    }

    public function getMetadata(): Metadata
    {
        return $this->get('metadata');
    }

    // Protected Methods
    // =========================================================================

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    // Private Methods
    // =========================================================================

    private function _registerFieldTypes()
    {
        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = AssetMetadataField::class;
        });
    }

    private function _registerAssetTableAttributes()
    {
        Event::on(Asset::class, Element::EVENT_REGISTER_TABLE_ATTRIBUTES, function(RegisterElementTableAttributesEvent $event) {
            foreach (Craft::$app->getFields()->getAllFields() as $field) {
                if ($field instanceof AssetMetadataField && is_array($field->subfields)) {
                    foreach ($field->subfields as $subfield) {
                        $key = "field:$field->handle:{$subfield['handle']}";
                        $this->_tableAttributes[$key] = ['label' => Craft::t('asset-metadata', $subfield['name'])];
                    }
                }
            }

            $event->tableAttributes = array_merge($event->tableAttributes, $this->_tableAttributes);
        });

        Event::on(Asset::class, Element::EVENT_SET_TABLE_ATTRIBUTE_HTML, function(SetElementTableAttributeHtmlEvent $event){
            if (isset($this->_tableAttributes[$event->attribute])) {
                list(, $fieldHandle, $subfieldHandle) = explode(':', $event->attribute);

                /** @var Asset $asset */
                $asset = $event->sender;
                $fieldValue = $asset->getFieldValue($fieldHandle);

                $event->html = $fieldValue[$subfieldHandle] ?? '';
                $event->handled = true;
            }
        });
    }
}
