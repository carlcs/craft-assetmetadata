<?php

namespace carlcs\assetmetadata\helpers;

use Craft;
use DateTime;
use DateTimeZone;
use yii\base\InvalidArgumentException;

class ExifHelper
{
    const GPS_LAT = 'lat';
    const GPS_LONG = 'long';

    /**
     * Converts an EXIF date/time string into a DateTime object.
     */
    public static function convertExifDate(string $dateString, DateTimeZone $timezone = null): DateTime
    {
        if (!preg_match('/(\d{4}:\d{2}:\d{2}) (\d{2}:\d{2}:\d{2})/', $dateString, $matches)) {
            throw new InvalidArgumentException('$dateString should be a valid EXIF date/time string.');
        }

        $dateString = str_replace(':', '-', $matches[1]).' '.$matches[2];

        return new DateTime($dateString, $timezone);
    }

    /**
     * Converts a GPS point location from EXIF GPS data.
     */
    public static function convertExifGpsCoordinates(array $exif, bool|string $format): array|string
    {
        $latitude = self::convertExifGpsCoordinate($exif, self::GPS_LAT, $format);
        $longitude = self::convertExifGpsCoordinate($exif, self::GPS_LONG, $format);

        if ($format === false) {
            return compact('latitude', 'longitude');
        }

        return "$latitude $longitude";
    }

    /**
     * Converts a single GPS coordinate from EXIF GPS data to decimal format or
     * sexagesimal format (ISO 6709).
     */
    public static function convertExifGpsCoordinate(array $exif, string $axis, bool|string $format): float|int|string
    {
        $coordMap = [
            self::GPS_LAT => ['dms' => 'GPSLatitude', 'ref' => 'GPSLatitudeRef'],
            self::GPS_LONG => ['dms' => 'GPSLongitude', 'ref' => 'GPSLongitudeRef'],
        ];

        if (($coord = $coordMap[$axis] ?? false) === false) {
            throw new InvalidArgumentException('$axis should be a either “lat” or “long”.');
        }

        if (
            ($dms = $exif[$coord['dms']] ?? false) === false ||
            ($ref = $exif[$coord['ref']] ?? false) === false
        ) {
            throw new InvalidArgumentException('$exif should be a valid EXIF GPS data array.');
        }

        $deg = NumberHelper::fractionToFloat($dms[0]);
        $min = NumberHelper::fractionToFloat($dms[1]);
        $sec = NumberHelper::fractionToFloat($dms[2]);

        $value = $deg + ($min * 60 + $sec) / 3600;

        $value = in_array($ref, ['S', 'W']) ? $value * -1 : $value;

        if ($format !== false) {
            $format = $format !== true ? $format : null;
            $value = self::formatGpsCoordinate($value, $axis, $format);
        }

        return $value;
    }

    /**
     * Formats a single GPS coordinate in sexagesimal format (ISO 6709).
     */
    public static function formatGpsCoordinate(float $value, string $axis, string $format = null): string
    {
        $map = [['S', 'N'], ['W', 'E']];
        $ref = $map[$axis === self::GPS_LAT ? 0 : 1][$value < 0 ? 0 : 1];

        $value = abs($value);
        $deg = floor($value);
        $value = ($value - $deg) * 60;
        $min = floor($value);
        $sec = ($value - $min) * 60;

        $format = $format ?: "%d°%02d'%04.1f\"%s";

        return sprintf($format, $deg, $min, $sec, Craft::t('asset-metadata', $ref));
    }
}
