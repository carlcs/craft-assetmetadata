<?php
namespace Craft;

class AssetMetadataService extends BaseApplicationComponent
{
	// Properties
	// =========================================================================

	/**
	 * @var
	 */
	private $_settings;

	// Public Methods
	// =========================================================================

	/**
	 * Initializes the plugin service.
	 *
	 * @return null
	 */
	public function init()
	{
		$this->_settings = craft()->plugins->getPlugin('assetmetadata')->getSettings();
	}

	/**
	 * Returns metadata for an asset.
	 *
	 * @param string $asset The asset file model to parse
	 * @param string $property String in dot notation that sets the root metadata property
	 * @return array|string The asset's metadata
	 */
	public function getAssetMetadata($asset, $property)
	{
		// TODO: Support cloud-based asset sources.
		$path = $this->_getLocalImageSource($asset);

		$getId3 = $this->_getGetId3();
		$data = $getId3->analyze($path);

		// Merge tags, then copy to `$data['comments_html']`.
		\GetId3\Lib\Helper::CopyTagsToComments($data);

		// Flatten tags, then copy tags to `$data['tags']['all']`.
		$this->_flattenTags($data);

		// Copy tags to `$data` root.
		if ($this->_settings['copyTagsToRoot'])
		{
			$this->_copyTagsToRoot($data);
		}

		// Add properties in pretty formats.
		$this->_addBeautifiedProperties($data);

		return $this->_getValueByKey($property, $data);
	}

	// Private Methods
	// =========================================================================

	/**
	 * Returns the path of an asset.
	 *
	 * @param AssetFileModel $asset
	 * @return string
	 */
	private function _getLocalImageSource(AssetFileModel $asset)
	{
		$sourceType = craft()->assetSources->getSourceTypeById($asset->sourceId);
		$imageSourcePath = $sourceType->getImageSourcePath($asset);

		if ($sourceType->isRemote())
		{
			throw new Exception(Craft::t('Asset Metadata plugin currently works with local assets only.'));
		}

		return $imageSourcePath;
	}

	/**
	 * Returns a new, configured GetId3 instance.
	 *
	 * @return \GetId3
	 */
	private function _getGetId3()
	{
		if (!class_exists('\GetId3\GetId3Core'))
		{
			require_once craft()->path->getPluginsPath().'assetmetadata/vendor/autoload.php';
		}

		$getId3 = new \GetId3\GetId3Core();

		$getId3->encoding = 'UTF-8';
		$getId3->option_extra_info = $this->_settings['extraInfo'];
		$getId3->option_md5_data = $this->_settings['md5Data'];
		$getId3->option_sha1_data = $this->_settings['sha1Data'];

		return $getId3;
	}

	/**
	 * Traverse an array using dot notation.
	 * https://selv.in/blog/traversing-arrays-using-dot-notation
	 *
	 * @param string $property
	 * @param array $data
	 * @return array|string|null
	 */
	private function _getValueByKey($property, array $data)
	{
		if (!is_string($property) || empty($property))
		{
			return $data;
		}

		if (strpos($property, '.') !== false)
		{
			$keys = explode('.', $property);

			foreach ($keys as $innerKey)
			{
				if (!array_key_exists($innerKey, $data))
				{
					return null;
				}

				// Continue traversing the array.
				$data = $data[$innerKey];
			}

			return $data;
		}

		return array_key_exists($property, $data) ? $data[$property] : null;
	}

	/**
	 * Join tag metadata elements into a string.
	 *
	 * @param array &$data
	 * @return null
	 */
	private function _flattenTags(&$data)
	{
		if (array_key_exists('comments_html', $data))
		{
			foreach ($data['comments_html'] as $property => $tags)
			{
				$tag = implode($this->_settings['tagSeparator'], $tags);
				$data['tags']['all'][$property] = $tag;
			}
		}
	}

	/**
	 * Copy all tag properties to the array root.
	 *
	 * @param array &$data
	 * @return null
	 */
	private function _copyTagsToRoot(&$data)
	{
		if (array_key_exists('tags', $data))
		{
			foreach ($data['tags']['all'] as $property => $tag)
			{
				$data[$property] = $tag;
			}
		}
	}

	/**
	 * Add properties in pretty formats.
	 *
	 * @param array &$data
	 * @return null
	 */
	private function _addBeautifiedProperties(&$data)
	{
		// Add `playtime_ISO8601` property (Craft DateInterval object).
		if (isset($data['playtime_seconds']))
		{
			$data['playtime_ISO8601'] = DateInterval::fromSeconds($data['playtime_seconds']);
		}

		// Add `EXIF.ExposureTimeRatio` property (string).
		foreach (array('jpg', 'tiff') as $fileType)
		{
			if (isset($data[$fileType]['exif']['EXIF']['ExposureTime']))
			{
				$data[$fileType]['exif']['EXIF']['ExposureTimeRatio'] = $this->_floatToFraction($data[$fileType]['exif']['EXIF']['ExposureTime']);
			}
		}

		// Add `GPS.computed.latitude_sexagesimal` property (string).
		if (isset($data['jpg']['exif']['GPS']))
		{
			$gpsData = $data['jpg']['exif']['GPS'];

			if (isset($gpsData['GPSLatitudeRef']) && isset($gpsData['GPSLatitude']) && (count($gpsData['GPSLatitude']) == 3))
			{
				$data['jpg']['exif']['GPS']['computed']['latitude_sexagesimal'] = $this->_formatGpsCoordinate($gpsData['GPSLatitude']) .
											  Craft::t($gpsData['GPSLatitudeRef']);

			}

			if (isset($gpsData['GPSLongitudeRef']) && isset($gpsData['GPSLongitude']) && (count($gpsData['GPSLongitude']) == 3))
			{
				$data['jpg']['exif']['GPS']['computed']['longitude_sexagesimal'] = $this->_formatGpsCoordinate($gpsData['GPSLongitude']) .
											   Craft::t($gpsData['GPSLongitudeRef']);
			}
		}
	}

	/**
	 * Converts an EXIF GPS coordinate into sexagesimal format (ISO 6709).
	 * http://stackoverflow.com/questions/2526304/php-extract-gps-exif-data
	 *
	 * @param array $coordinate
	 * @return string
	 */
	private function _formatGpsCoordinate(array $coordinate)
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
		$coordinate[2] = number_format($coordinate[2], $this->_settings['gpsSecDecimals'], $this->_settings['gpsSecDecPoint'], '');

		$charsTotal = 2 + (($this->_settings['gpsSecDecimals'] != 0) ? strlen($this->_settings['gpsSecDecPoint']) : 0) + $this->_settings['gpsSecDecimals'];
		$coordinate[2] = sprintf('%0'.$charsTotal.'s', $coordinate[2]);

		return $coordinate[0].'°'.$coordinate[1].'\''.$coordinate[2].'"';
	}

	/**
	 * Converts a fraction to a decimal number.
	 *
	 * @param string $fraction
	 * @return float
	 */
	private function _fractionToFloat($fraction)
	{
		list($numerator, $denominator) = explode('/', $fraction);

	        return $numerator / ($denominator ? $denominator : 1);
	}

	/**
	 * Converts a decimal number to a fraction.
	 * http://jonisalonen.com/2012/converting-decimal-numbers-to-ratios/
	 *
	 * @param float $float
	 * @param float $tolerance
	 * @return string
	 */
	private function _floatToFraction($float, $tolerance = 1.e-6)
	{
		$h1 = 1; $h2 = 0;
		$k1 = 0; $k2 = 1;
		$b = 1 / $float;

		do
		{
			$b = 1 / $b;
			$a = floor($b);
			$aux = $h1; $h1 = $a * $h1 + $h2; $h2 = $aux;
			$aux = $k1; $k1 = $a * $k1 + $k2; $k2 = $aux;
			$b = $b - $a;
		}
		while (abs($float - $h1 / $k1) > $float * $tolerance);

		return $h1.'/'.$k1;
	}
}
