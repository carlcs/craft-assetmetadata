<?php
namespace Craft;

class AssetMetadataVariable
{
        /**
	 * Twig variable to return metadata for an asset.
         * Allow both syntaxes `getData()` and `data()`
	 *
	 * @param string $asset The asset file model to parse
	 * @param string $property String in dot notation that sets the root metadata property
	 * @return array|string The asset's metadata
	 */
        public function getData($asset, $property = null)
	{
		return craft()->assetMetadata->getAssetMetadata($asset, $property);
	}

        public function data($asset, $property = null)
	{
		return craft()->assetMetadata->getAssetMetadata($asset, $property);
	}
}
