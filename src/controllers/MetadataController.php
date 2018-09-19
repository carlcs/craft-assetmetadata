<?php

namespace carlcs\assetmetadata\controllers;

use carlcs\assetmetadata\fields\AssetMetadata;
use carlcs\assetmetadata\Plugin;
use Craft;
use craft\db\Query;
use craft\elements\Asset;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use craft\web\Controller;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class MetadataController extends Controller
{
    /**
     * Returns the field value for an Asset Metadata field.
     *
     * @return Response
     */
    public function actionGetFieldValue(): Response
    {
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();
        $elementId = $request->getRequiredBodyParam('elementId');
        $fieldId = $request->getRequiredBodyParam('fieldId');

        $asset = Craft::$app->getElements()->getElementById($elementId);
        $field = Craft::$app->getFields()->getFieldById($fieldId);

        if (!$asset || !($asset instanceof Asset)) {
            throw new BadRequestHttpException("No Asset exists with the ID {$elementId}.");
        }

        if (!$field || !($field instanceof AssetMetadata)) {
            throw new BadRequestHttpException("No Asset Metadata field exists with the ID {$fieldId}.");
        }

        $value = Plugin::getInstance()->getMetadata()->getFieldValue($field, $asset);

        return $this->asJson($value);
    }

    /**
     * Fixes a field’s subfields settings.
     * Please configure this action with a config/asset-metadata-fix.php config file.
     *
     * @return string
     */
    public function actionFixSubfieldsMap(): string
    {
        $config = Craft::$app->getConfig()->getConfigFromFile('asset-metadata-fix');

        $fieldHandle = $config['fieldHandle'];
        $subfieldsMap = $config['subfieldsMap'];

        $field = (new Query())
            ->select(['settings'])
            ->from(['{{%fields}}'])
            ->where([
                'handle' => $fieldHandle,
                'type' => AssetMetadata::class,
            ])
            ->one();

        if (!$field) {
            throw new InvalidConfigException('Invalid field handle.');
        }

        $settings = Json::decode($field['settings']);

        if (!is_array($settings['subfields'])) {
            throw new InvalidConfigException('Field doesn’t have any subfields.');
        }

        $oldKeys = array_keys($settings['subfields']);
        $mapKeys = array_keys($subfieldsMap);

        if (!ArrayHelper::isSubset($oldKeys, $mapKeys)) {
            throw new InvalidConfigException('Subfields map doesn’t map all existing keys.');
        }

        $newSubfields = [];
        foreach ($settings['subfields'] as $oldKey => $subfield) {
            $newSubfields[$subfieldsMap[$oldKey]] = $subfield;
        }
        $settings['subfields'] = $newSubfields;

        Craft::$app->getDb()->createCommand()
            ->update('{{%fields}}', ['settings' => Json::encode($settings)], ['handle' => $fieldHandle])
            ->execute();

        return 'Field updated.';
    }
}
