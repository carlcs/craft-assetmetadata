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

                if (count($date) > 1)
                {
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
                if (isset($gpsData['GPSLatitudeRef']) && isset($gpsData['GPSLatitude']) && (count($gpsData['GPSLatitude']) == 3))
                {
                        $latitude = $this->formatExifGpsCoordinate($gpsData['GPSLatitude'], $gpsSecDecimals, $gpsSecDecPoint);
			$latitude .= Craft::t($gpsData['GPSLatitudeRef']);

                }

                if (isset($gpsData['GPSLongitudeRef']) && isset($gpsData['GPSLongitude']) && (count($gpsData['GPSLongitude']) == 3))
                {
                        $longitude = $this->formatExifGpsCoordinate($gpsData['GPSLongitude'], $gpsSecDecimals, $gpsSecDecPoint);
			$longitude .= Craft::t($gpsData['GPSLongitudeRef']);
                }

		if (!$latitude || !$longitude)
		{
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
		foreach ($coordinate as $key => $value)
		{
			$coordinate[$key] = $this->_fractionToFloat($value);
		}

		$coordinate[1] += 60 * ($coordinate[0] - floor($coordinate[0]));
  		$coordinate[0] = floor($coordinate[0]);

		$coordinate[2] += 60 * ($coordinate[1] - floor($coordinate[1]));
  		$coordinate[1] = floor($coordinate[1]);

		// We don't want minute or second values larger than 60.
		if($coordinate[2] >= 60)
		{
			$coordinate[1] += floor($coordinate[2] / 60);
			$coordinate[2] -= 60 * floor($coordinate[2] / 60);
		}

		if($coordinate[1] >= 60)
		{
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

        // Private Methods
	// =========================================================================

        /**
	 * Converts a fraction to a decimal number.
	 *
	 * @param string  $fraction  The number to convert
         * @param integer $precision The precision the returned number gets rounded to
	 *
	 * @return float The converted number
	 */
	private function _fractionToFloat($fraction, $precision = 4)
	{
                if ($this->_isFloat($fraction))
                {
                        return $fraction;
                }

                if ($this->_isFraction($fraction))
                {
                        list($numerator, $denominator) = explode('/', $fraction);

        	        $float = $numerator / ($denominator ? $denominator : 1);

                        return round($float, $precision);
                }

                return 0;
	}

        /**
	 * Checks if a number is a fraction.
	 *
	 * @param string $fraction Number to test
         *
	 * @return boolean
	 */
        private function _isFraction($fraction)
        {
                return preg_match('/^[-+]?[0-9]*\.?[0-9]+[ ]?\/[ ]?[-+]?[0-9]*\.?[0-9]+$/', $fraction);
        }

        /**
	 * Checks if a number is a rational number.
	 *
	 * @param float $float Number to test
         *
	 * @return boolean
	 */
        private function _isFloat($float)
        {
                return preg_match('/^[-+]?[0-9]*\.?[0-9]+$/', $float);
        }
}
