<?php

namespace carlcs\assetmetadata\web\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class FieldAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = __DIR__.'/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = ['assetmetadata.js'];
        $this->css = ['assetmetadata.css'];

        parent::init();
    }
}
