<?php
namespace Craft;

class AssetMetadataTwigExtension extends \Twig_Extension
{
	/**
	 * Returns the name of the extension.
	 *
	 * @return string The extension name
	 */
	public function getName()
	{
		return 'Asset Metadata';
	}

	/**
	 * Returns a list of filters to add to the existing list.
	 *
	 * @return array An array of filters
	 */
	public function getFilters()
	{
		return array(
			new \Twig_SimpleFilter('assetMetadata', array($this, 'assetMetadataFilter')),
		);
	}

	/**
	 * Twig filter to return metadata for an asset.
	 *
	 * @param string $asset The asset file model to parse
	 * @param string $property String in dot notation that sets the root metadata property
	 * @return array|string The asset's metadata
	 */
	public function assetMetadataFilter($asset, $property = null)
	{
		return craft()->assetMetadata->getAssetMetadata($asset, $property);
	}
}
