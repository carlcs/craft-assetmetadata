<?php

namespace carlcs\assetmetadata\controllers;

use carlcs\assetmetadata\fields\AssetMetadata;
use carlcs\assetmetadata\Plugin;
use Craft;
use craft\elements\Asset;
use craft\web\Controller;
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
}
