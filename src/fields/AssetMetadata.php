<?php

namespace carlcs\assetmetadata\fields;

use carlcs\assetmetadata\gql\AssetMetadataTypeGenerator;
use carlcs\assetmetadata\Plugin;
use carlcs\assetmetadata\web\assets\FieldAsset;
use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\elements\Asset;
use craft\helpers\Json;
use yii\db\Schema;

/**
 * @property string $contentColumnType
 * @property mixed $contentGqlType
 * @property mixed $settingsHtml
 */
class AssetMetadata extends Field
{
    // Static
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('asset-metadata', 'Asset Metadata');
    }

    /**
     * @inheritdoc
     */
    public static function supportedTranslationMethods(): array
    {
        return [
            self::TRANSLATION_METHOD_NONE,
        ];
    }

    // Properties
    // =========================================================================

    /**
     * @var array|null
     */
    public $subfields;

    /**
     * @var bool
     */
    public $refreshOnElementSave = false;

    /**
     * @var bool
     */
    public $readOnly = false;

    /**
     * @var bool
     */
    private $_isSaved = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_TEXT;
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('asset-metadata/field/settings', [
            'field' => $this,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $view = Craft::$app->getView();
        $view->registerAssetBundle(FieldAsset::class);

        if (!$element || !($element instanceof Asset)) {
            return $view->renderTemplate('asset-metadata/field/input-error', [
                'error' => Craft::t('asset-metadata', 'Asset Metadata fields only work when added to an Asset Volume’s field layout.'),
            ]);
        }

        return $view->renderTemplate('asset-metadata/field/input', [
            'id'      => $view->formatInputId($this->handle),
            'name'    => $this->handle,
            'value'   => $value,
            'field'   => $this,
            'element' => $element,
            'readOnly' => (bool)$this->readOnly,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        if (is_string($value) && !empty($value)) {
            $value = Json::decode($value);
        }

        // Make the subfield’s values accessible from their handles
        if (is_array($value) && !empty($this->subfields)) {
            foreach ($value as $colId => $col) {
                if (isset($this->subfields[$colId])) {
                    $value[$this->subfields[$colId]['handle']] = $col;
                }
            }

            return $value;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        // Drop the subfield’s handle values
        if (is_array($value) && !empty($this->subfields)) {
            foreach ($this->subfields as $colId => $col) {
                if ($col['handle']) {
                    unset($value[$col['handle']]);
                }
            }
        }

        return parent::serializeValue($value, $element);
    }

    /**
     * @inheritdoc
     */
    public function beforeElementSave(ElementInterface $asset, bool $isNew): bool
    {
        if (!($asset instanceof Asset)) {
            return true;
        }

        if (!$this->_isSaved && ($isNew || $this->refreshOnElementSave)) {
            $this->_isSaved = true;

            $value = Plugin::getInstance()->getMetadata()->getFieldValue($this, $asset);
            $asset->setFieldValue($this->handle, $value);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getContentGqlType()
    {
        $typeArray = AssetMetadataTypeGenerator::generateTypes($this);
        return array_pop($typeArray);
    }
}
