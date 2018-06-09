<?php

namespace carlcs\assetmetadata\web\twig;

use carlcs\assetmetadata\helpers\ExifHelper;
use carlcs\commons\helpers\NumberHelper;

class Extension extends \Twig_Extension
{
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
            new \Twig_Filter('numeralSystem', [NumberHelper::class, 'numeralSystem']),
            new \Twig_Filter('unitPrefix', [NumberHelper::class, 'unitPrefix']),
            new \Twig_Filter('fractionToFloat', [NumberHelper::class, 'fractionToFloat']),
            new \Twig_Filter('floatToFraction', [NumberHelper::class, 'floatToFraction']),
        ];
    }
}
