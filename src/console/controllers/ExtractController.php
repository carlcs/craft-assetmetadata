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
    public ?string $key = null;

    // Public Methods
    // =========================================================================

    public function options($actionID): array
    {
        $options = parent::options($actionID);
        $options[] = 'key';

        return $options;
    }

    /**
     * Extracts and displays metadata for an Asset.
     */
    public function actionIndex(int $elementId)
    {
        $asset = Craft::$app->getElements()->getElementById($elementId);

        if (!($asset instanceof Asset)) {
            $this->stdout("No Asset exists with the ID {$elementId}.".PHP_EOL, Console::FG_YELLOW);
            return;
        }

        $metadata = Plugin::getInstance()->getMetadata()->extract($asset, $this->key);

        $this->stdout(print_r($metadata, true).PHP_EOL);
    }
}
