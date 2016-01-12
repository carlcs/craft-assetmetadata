<?php
namespace Craft;

class AssetMetadataVariable
{
        /**
         * Returns metadata for an asset.
         *
         * @param AssetFileModel $asset    The asset model to parse
         * @param string         $property String in dot notation that sets the root metadata property
         *
         * @return array|string The asset's metadata
         */
        public function getAssetMetadata($asset, $property = null)
        {
                return craft()->assetMetadata->getAssetMetadata($asset, $property);
        }
}
