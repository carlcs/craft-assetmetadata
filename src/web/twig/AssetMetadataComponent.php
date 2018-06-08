<?php

namespace carlcs\assetmetadata\web\twig;

use carlcs\assetmetadata\Plugin;
use craft\elements\Asset;
use yii\di\ServiceLocator;

class AssetMetadataComponent extends ServiceLocator
{
    /**
     * Extracts the metadata from an Asset.
     *
     * @param Asset $asset
     * @param string|null $key
     * @return array|string
     */
    public function extract(Asset $asset, $key = null)
    {
        return Plugin::getInstance()->getMetadata()->extract($asset, $key);
    }
}
