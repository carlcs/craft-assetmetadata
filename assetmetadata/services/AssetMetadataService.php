<?php
namespace Craft;

use getID3;
use getid3_lib;

class AssetMetadataService extends BaseApplicationComponent
{
        // Public Methods
        // =========================================================================

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
                if (!($asset instanceof AssetFileModel))
                {
                        throw new Exception('(Asset Metadata) The plugin only works with asset models.');
                }

                // TODO: Support remote asset sources.
                $path = $this->_getLocalImageSource($asset);

                $getId3 = $this->_getGetId3();
                $metadata = $getId3->analyze($path);

                // Merges ID3 tags and stores them in a "comments" property.
                getid3_lib::CopyTagsToComments($metadata);

                // Removes troublesome properties.
                $this->_removeProperties($metadata);

                return $this->_getValueByKey($property, $metadata);
        }

        // Private Methods
        // =========================================================================

        /**
         * Returns the path of an asset.
         *
         * @param AssetFileModel $asset
         *
         * @return string
         */
        private function _getLocalImageSource(AssetFileModel $asset)
        {
                $sourceType = craft()->assetSources->getSourceTypeById($asset->sourceId);
                $imageSourcePath = $sourceType->getImageSourcePath($asset);

                if ($sourceType->isRemote())
                {
                        throw new Exception('(Asset Metadata) The plugin currently works with local assets only.');
                }

                return $imageSourcePath;
        }

        /**
         * Returns a new, configured getID3 instance.
         *
         * @return \getID3
         */
        private function _getGetId3()
        {
                $getId3 = new getID3();

                $settings = craft()->config->get('getId3', 'assetmetadata');

                foreach ($settings as $setting => $value)
                {
                        $getId3->{$setting} = $value;
                }

                return $getId3;
        }

        /**
         * Removes properties listed in the "excludeProperties" setting from the metadata array.
         *
         * @param array &$array
         *
         * @return null
         */
        private function _removeProperties(&$array)
        {
                $properties = craft()->config->get('excludeProperties', 'assetmetadata');

                foreach ($properties as $property)
                {
                        $data = &$array;

                        foreach (explode('.', $property) as $key)
                        {
                                $data = &$data[$key];
                        }

                        $data = NULL;
                }
        }

        /**
         * Traverse an array using dot notation.
         * https://selv.in/blog/traversing-arrays-using-dot-notation
         *
         * @param string $property
         * @param array  $data
         *
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
                        foreach (explode('.', $property) as $key)
                        {
                                if (!array_key_exists($key, $data))
                                {
                                        return null;
                                }

                                // Continue traversing the array.
                                $data = $data[$key];
                        }

                        return $data;
                }

                return array_key_exists($property, $data) ? $data[$property] : null;
        }
}
