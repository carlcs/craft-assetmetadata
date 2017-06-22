<?php
namespace Craft;

class AssetMetadata_HelpersService extends BaseApplicationComponent
{
    // Public Methods
    // =========================================================================

    /**
     * Converts an EXIF date/time value into a DateTime object.
     *
     * @param array $date
     *
     * @return DateTime/null
     */
    public function formatExifDate($date)
    {
        $date = explode(' ', $date);

        if (count($date) > 1) {
            $date[0] = str_replace(':', '-', $date[0]);
            $date = implode($date, ' ');
        }

        $date = date($date);

        return $date ? $date : null;
    }

    /**
     * Converts an EXIF GPS point location into sexagesimal format (ISO 6709).
     *
     * @param array $gpsData
     *
     * @return string|null
     */
    public function formatExifGpsCoordinates($gpsData, $gpsSecDecimals = 0, $gpsSecDecPoint = '.')
    {
        if (isset($gpsData['GPSLatitudeRef']) && isset($gpsData['GPSLatitude']) && (count($gpsData['GPSLatitude']) == 3)) {
            $latitude = $this->formatExifGpsCoordinate($gpsData['GPSLatitude'], $gpsSecDecimals, $gpsSecDecPoint);
            $latitude .= Craft::t($gpsData['GPSLatitudeRef']);
        }

        if (isset($gpsData['GPSLongitudeRef']) && isset($gpsData['GPSLongitude']) && (count($gpsData['GPSLongitude']) == 3)) {
            $longitude = $this->formatExifGpsCoordinate($gpsData['GPSLongitude'], $gpsSecDecimals, $gpsSecDecPoint);
            $longitude .= Craft::t($gpsData['GPSLongitudeRef']);
        }

        if (!isset($latitude) || !isset($longitude)) {
            return;
        }

        return $latitude.' '.$longitude;
    }

    /**
     * Converts an EXIF GPS coordinate into sexagesimal format (ISO 6709).
     * http://stackoverflow.com/questions/2526304/php-extract-gps-exif-data
     *
     * @param array $coordinate
     *
     * @return string
     */
    public function formatExifGpsCoordinate(array $coordinate, $gpsSecDecimals = 0, $gpsSecDecPoint = '.')
    {
        foreach ($coordinate as $key => $value) {
            $coordinate[$key] = $this->fractionToFloat($value);
        }

        $coordinate[1] += 60 * ($coordinate[0] - floor($coordinate[0]));
        $coordinate[0] = floor($coordinate[0]);

        $coordinate[2] += 60 * ($coordinate[1] - floor($coordinate[1]));
        $coordinate[1] = floor($coordinate[1]);

        // We don't want minute or second values larger than 60.
        if ($coordinate[2] >= 60) {
            $coordinate[1] += floor($coordinate[2] / 60);
            $coordinate[2] -= 60 * floor($coordinate[2] / 60);
        }

        if ($coordinate[1] >= 60) {
            $coordinate[0] += floor($coordinate[1] / 60);
            $coordinate[1] -= 60 * floor($coordinate[1] / 60);
        }

        // Add leading and trailing zeros
        $coordinate[2] = number_format($coordinate[2], $gpsSecDecimals, $gpsSecDecPoint, '');

        $charsTotal = 2 + (($gpsSecDecimals != 0) ? strlen($gpsSecDecPoint) : 0) + $gpsSecDecimals;
        $coordinate[2] = sprintf('%0'.$charsTotal.'s', $coordinate[2]);

        $coordinate[1] = sprintf('%02s', $coordinate[1]);

        return $coordinate[0].'°'.$coordinate[1].'\''.$coordinate[2].'"';
    }

    /**
     * Formats a number with unit prefixes.
     *
     * @param float $float
     * @param mixed $system
     * @param int $decimals
     * @param bool $trailingZeros
     * @param string $decPoint
     * @param string $thousandsSep
     * @param string $unitSep
     *
     * @return string The prefixed number
     */
    public function unitPrefix($float, $system = 'decimal', $decimals = 1, $trailingZeros = false, $decPoint = '.', $thousandsSep = '', $unitSep = ' ')
    {
        if (is_string($system)) {
            $system = $this->_getUnitPrefixSettings($system);
        }

        if (!array_key_exists('map', $system)) {
            return $float;
        }

        $base = array_key_exists('base', $system) ? $system['base'] : 10;
        $map = $system['map'];

        foreach ($map as $exp => $prefix) {
            if ($float >= pow($base, $exp)) {
                $number = $float / pow($base, $exp);

                $number = number_format($number, $decimals, $decPoint, $thousandsSep);

                if (!$trailingZeros) {
                    $number = $this->trimTrailingZeroes($number, $decPoint);
                }

                return $number.$unitSep.Craft::t($prefix);
            }
        }

        return $float;
    }

    /**
     * Converts a fraction to a decimal number.
     *
     * @param string $fraction
     * @param integer $precision
     *
     * @return float
     */
    public function fractionToFloat($fraction, $precision = 4)
    {
        if ($this->isFloat($fraction)) {
            return $fraction;
        }

        if ($this->isFraction($fraction)) {
            list($numerator, $denominator) = explode('/', $fraction);

            $float = $numerator / ($denominator ? $denominator : 1);

            return round($float, $precision);
        }

        return 0;
    }

    /**
     * Converts a decimal number to a fraction.
     * http://jonisalonen.com/2012/converting-decimal-numbers-to-ratios/
     *
     * @param float $float
     * @param float $tolerance
         *
     * @return string
     */
    public function floatToFraction($float, $tolerance = 0.001)
    {
        if (!$this->isFloat($float)) {
            return 0;
        }

        $h1 = 1;
        $h2 = 0;
        $k1 = 0;
        $k2 = 1;
        $b = 1 / $float;

        do {
            $b = 1 / $b;
            $a = floor($b);
            $aux = $h1;
            $h1 = $a * $h1 + $h2;
            $h2 = $aux;
            $aux = $k1;
            $k1 = $a * $k1 + $k2;
            $k2 = $aux;
            $b = $b - $a;
        } while (abs($float - $h1 / $k1) > $float * $tolerance);

        if (true && ($h1 == $k1)) {
            return $h1;
        }

        return $h1.'/'.$k1;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Checks if a number is a fraction.
     *
     * @param string $fraction
     *
     * @return boolean
     */
    protected function isFraction($fraction)
    {
        return preg_match('/^[-+]?[0-9]*\.?[0-9]+[ ]?\/[ ]?[-+]?[0-9]*\.?[0-9]+$/', $fraction);
    }

    /**
     * Checks if a number is a rational number.
     *
     * @param float $float
     *
     * @return boolean
     */
    protected function isFloat($float)
    {
        return preg_match('/^[-+]?[0-9]*\.?[0-9]+$/', $float);
    }

    /**
     * Trims trailing zeroes.
     *
     * @param integer $float
     * @param string $decPoint
     *
     * @return string
     */
    protected function trimTrailingZeroes($float, $decPoint = '.')
    {
        return strpos($float, $decPoint) !== false ? rtrim(rtrim($float, '0'), $decPoint) : $float;
    }

    // Private Methods
    // =========================================================================

    /**
     * Returns configuration settings for unit prefixes.
     *
     * @param string $preset
     *
     * @return array
     */
    private function _getUnitPrefixSettings($preset)
    {
        $settings = [];

        switch ($preset) {
            case 'names':
                $settings['map'] = [12 => 'trillion', 9 => 'billion', 6 => 'million', 3 => 'thousand', 2 => 'hundred', 0 => ''];
                break;
            case 'decimal':
            case 'decimalSymbol':
                $settings['map'] = [15 => 'P', 12 => 'T', 9 => 'G', 6 => 'M', 3 => 'k', 0 => '', -2 => 'c', -3 => 'm', -6 => 'µ', -9 => 'n'];
                break;
            case 'decimalNames':
                $settings['map'] = [15 => 'peta', 12 => 'tera', 9 => 'giga', 6 => 'mega', 3 => 'kilo', 0 => '', -2 => 'centi', -3 => 'milli', -6 => 'micro', -9 => 'nano'];
                break;
            case 'binary':
            case 'binarySymbol':
                $settings['base'] = 2;
                $settings['map'] = [50 => 'Pi', 40 => 'Ti', 30 => 'Gi', 20 => 'Mi', 10 => 'Ki', 0 => ''];
                break;
            case 'binaryNames':
                $settings['base'] = 2;
                $settings['map'] = [50 => 'pebi', 40 => 'tebi', 30 => 'gibi', 20 => 'mebi', 10 => 'kibi', 0 => ''];
                break;
        }

        return $settings;
    }
}
