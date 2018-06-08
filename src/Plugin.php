<?php

namespace carlcs\assetmetadata;

use carlcs\assetmetadata\fields\AssetMetadata as AssetMetadataField;
use carlcs\assetmetadata\services\Metadata;
use carlcs\assetmetadata\web\twig\AssetMetadataComponent;
use carlcs\assetmetadata\web\twig\Extension;
use Craft;
use craft\base\Element;
use craft\elements\Asset;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterElementTableAttributesEvent;
use craft\events\SetElementTableAttributeHtmlEvent;
use craft\services\Fields;
use craft\web\twig\variables\CraftVariable;
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

    /**
     * @inheritDoc
     */
    public $schemaVersion = '3.0.0';

    /**
     * @inheritdoc
     */
    public $minVersionRequired = '2.1.1';

    /**
     * @var array
     */
    private $_tableAttributes = [];

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->set('metadata', Metadata::class);

        if (!Craft::$app->getRequest()->isConsoleRequest) {
            Craft::$app->getView()->registerTwigExtension(new Extension());
        }

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, [$this, 'handleRegisterFieldTypes']);
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, [$this, 'extendCraftVariable']);
        Event::on(Asset::class, Element::EVENT_REGISTER_TABLE_ATTRIBUTES, [$this, 'handleRegisterAssetTableAttributes']);
        Event::on(Asset::class, Element::EVENT_SET_TABLE_ATTRIBUTE_HTML, [$this, 'handleSetAssetTableAttributeHtml']);
    }

    /**
     * Registers the field types.
     *
     * @param RegisterComponentTypesEvent $event
     */
    public function handleRegisterFieldTypes(RegisterComponentTypesEvent $event)
    {
        $event->types[] = AssetMetadataField::class;
    }

    /**
     * Extends the global Craft template variable.
     *
     * @param Event $e
     */
    public function extendCraftVariable(Event $e)
    {
        /** @var CraftVariable $variable */
        $variable = $e->sender;
        $variable->set('assetMetadata', AssetMetadataComponent::class);
    }

    /**
     * Registers the asset table attributes.
     *
     * @param RegisterElementTableAttributesEvent $event
     */
    public function handleRegisterAssetTableAttributes(RegisterElementTableAttributesEvent $event)
    {
        foreach (Craft::$app->getFields()->getFieldsByElementType(Asset::class) as $field) {
            if ($field instanceof AssetMetadataField && is_array($field->subfields)) {
                foreach ($field->subfields as $subfield) {
                    $key = "field:{$field->handle}:{$subfield['handle']}";
                    $this->_tableAttributes[$key] = ['label' => Craft::t('asset-metadata', $subfield['name'])];
                }
            }
        }

        $event->tableAttributes = array_merge($event->tableAttributes, $this->_tableAttributes);
    }

    /**
     * Sets the asset table attribute HTML.
     *
     * @param SetElementTableAttributeHtmlEvent $event
     */
    public function handleSetAssetTableAttributeHtml(SetElementTableAttributeHtmlEvent $event)
    {
        if (isset($this->_tableAttributes[$event->attribute])) {
            list(, $fieldHandle, $subfieldHandle) = explode(':', $event->attribute);

            $event->html = '';
            $event->handled = true;

            /** @var Asset $asset */
            $asset = $event->sender;
            $fieldValue = $asset->getFieldValue($fieldHandle);

            if (is_array($fieldValue) && isset($fieldValue[$subfieldHandle])) {
                $event->html = $fieldValue[$subfieldHandle];
            }
        }
    }

    /**
     * Returns the metadata service.
     *
     * @return Metadata
     */
    public function getMetadata(): Metadata
    {
        return $this->get('metadata');
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): Settings
    {
        return new Settings();
    }
}
