<?php

namespace carlcs\assetmetadata\web\twig;

use carlcs\assetmetadata\helpers\ExifHelper;
use Craft;

class Extension extends \Twig_Extension
{
    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return Craft::t('asset-metadata', 'Asset Metadata');
    }

    /**
     * @inheritdoc
     */
    public function getFilters(): array
    {
        return [
            new \Twig_Filter('convertExifDate', [ExifHelper::class, 'convertExifDate']),
            new \Twig_Filter('convertExifGpsCoordinates', [ExifHelper::class, 'convertExifGpsCoordinates']),
            new \Twig_Filter('convertExifGpsCoordinate', [ExifHelper::class, 'convertExifGpsCoordinate']),
            new \Twig_Filter('formatGpsCoordinate', [ExifHelper::class, 'formatGpsCoordinate']),
        ];
    }
}
