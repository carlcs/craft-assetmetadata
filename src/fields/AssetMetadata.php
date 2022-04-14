<?php

namespace carlcs\assetmetadata\fields;

use carlcs\assetmetadata\gql\AssetMetadataTypeGenerator;
use carlcs\assetmetadata\Plugin;
use carlcs\assetmetadata\web\assets\FieldAsset;
use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\elements\Asset;
use craft\helpers\Html;
use craft\helpers\Json;
use GraphQL\Type\Definition\Type;
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

    public static function displayName(): string
    {
        return Craft::t('asset-metadata', 'Asset Metadata');
    }

    public static function supportedTranslationMethods(): array
    {
        return [
            self::TRANSLATION_METHOD_NONE,
        ];
    }

    // Properties
    // =========================================================================

    public ?array $subfields = null;
    public bool $refreshOnElementSave = false;
    public bool $readOnly = false;

    private bool $_isSaved = false;

    // Public Methods
    // =========================================================================

    public function getContentColumnType(): array|string
    {
        return Schema::TYPE_TEXT;
    }

    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('asset-metadata/field/settings', [
            'field' => $this,
        ]);
    }

    public function getInputHtml(mixed $value, ?ElementInterface $element = null): string
    {
        $view = Craft::$app->getView();
        $view->registerAssetBundle(FieldAsset::class);

        if (!($element instanceof Asset)) {
            return $view->renderTemplate('asset-metadata/field/input-error', [
                'error' => Craft::t('asset-metadata', 'Asset Metadata fields only work when added to an Asset Volume’s field layout.'),
            ]);
        }

        return $view->renderTemplate('asset-metadata/field/input', [
            'id' => Html::id($this->handle),
            'name' => $this->handle,
            'value' => $value,
            'field' => $this,
            'element' => $element,
            'readOnly' => $this->readOnly,
        ]);
    }

    public function normalizeValue(mixed $value, ?ElementInterface $element = null): mixed
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

    public function serializeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        // Drop the subfield’s handle values
        if (is_array($value) && !empty($this->subfields)) {
            foreach ($this->subfields as $col) {
                if ($col['handle']) {
                    unset($value[$col['handle']]);
                }
            }
        }

        return parent::serializeValue($value, $element);
    }

    public function beforeElementSave(ElementInterface $element, bool $isNew): bool
    {
        if (!($element instanceof Asset)) {
            return true;
        }

        if (!$this->_isSaved && ($isNew || $this->refreshOnElementSave)) {
            $this->_isSaved = true;

            $value = Plugin::getInstance()->getMetadata()->getFieldValue($this, $element);
            $element->setFieldValue($this->handle, $value);
        }

        return true;
    }

    public function getContentGqlType(): array|Type
    {
        $typeArray = AssetMetadataTypeGenerator::generateTypes($this);
        return array_pop($typeArray);
    }
}
