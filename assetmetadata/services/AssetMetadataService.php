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

		// Add playtime property as DateInterval object.
		$data['playtime_ISO8601'] = DateInterval::fromSeconds($data['playtime_seconds']);

		return $this->getValueByKey($property, $data);
	}

	// Protected Methods
	// =========================================================================

	/**
	 * Traverse an array using dot notation.
	 * https://selv.in/blog/traversing-arrays-using-dot-notation
	 *
	 * @param string $property
	 * @param array $data
	 * @return array|string|null
	 */
	protected function getValueByKey($property, array $data)
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
	 * Join tag metadata elements into a string.
	 *
	 * @param array &$data
	 * @return null
	 */
	private function _flattenTags(&$data)
	{
		foreach ($data['comments_html'] as $property => $tags)
		{
			$tag = implode($this->_settings['tagSeparator'], $tags);
			$data['tags']['all'][$property] = $tag;
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
		foreach ($data['tags']['all'] as $property => $tag)
		{
			$data[$property] = $tag;
		}
	}
}
