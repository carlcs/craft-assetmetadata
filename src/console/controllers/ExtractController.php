<?php

namespace carlcs\assetmetadata\console\controllers;

use carlcs\assetmetadata\Plugin;
use Craft;
use craft\elements\Asset;
use craft\helpers\Console;
use yii\console\Controller;

/**
 * Display metadata for Asset elements.
 */
class ExtractController extends Controller
{
    // Properties
    // =========================================================================

    /**
     * @var string|null The key to access the metadata array with dot notation.
     */
    public $key;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function options($actionID): array
    {
        $options = parent::options($actionID);
        $options[] = 'key';

        return $options;
    }

    /**
     * Extracts and displays metadata for an Asset.
     *
     * @param int $elementId The Asset’s element ID
     */
    public function actionIndex(int $elementId)
    {
        $asset = Craft::$app->getElements()->getElementById($elementId);

        if (!$asset || !($asset instanceof Asset)) {
            $this->stdout("No Asset exists with the ID {$elementId}.".PHP_EOL, Console::FG_YELLOW);
            return;
        }

        $metadata = Plugin::getInstance()->getMetadata()->extract($asset, $this->key);

        $this->stdout(print_r($metadata, true).PHP_EOL);
    }
}
