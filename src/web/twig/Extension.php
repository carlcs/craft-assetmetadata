<?php

namespace carlcs\assetmetadata\web\twig;

use carlcs\assetmetadata\helpers\ExifHelper;
use carlcs\assetmetadata\helpers\NumberHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class Extension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('convertExifDate', [ExifHelper::class, 'convertExifDate']),
            new TwigFilter('convertExifGpsCoordinates', [ExifHelper::class, 'convertExifGpsCoordinates']),
            new TwigFilter('convertExifGpsCoordinate', [ExifHelper::class, 'convertExifGpsCoordinate']),
            new TwigFilter('formatGpsCoordinate', [ExifHelper::class, 'formatGpsCoordinate']),
            new TwigFilter('numeralSystem', [NumberHelper::class, 'numeralSystem']),
            new TwigFilter('unitPrefix', [NumberHelper::class, 'unitPrefix']),
            new TwigFilter('fractionToFloat', [NumberHelper::class, 'fractionToFloat']),
            new TwigFilter('floatToFraction', [NumberHelper::class, 'floatToFraction']),
        ];
    }
}
