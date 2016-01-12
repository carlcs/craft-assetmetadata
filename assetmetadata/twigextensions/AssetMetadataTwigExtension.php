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
         * Returns a list of functions to add to the existing list.
         *
         * @return array An array of functions
         */
        public function getFunctions()
        {
                return array(
                        new \Twig_SimpleFunction('getAssetMetadata', array($this, 'getAssetMetadataFunction')),
                );
        }

        /**
         * Returns a list of filters to add to the existing list.
         *
         * @return array An array of filters
         */
        public function getFilters()
        {
                return array(
                        new \Twig_SimpleFilter('formatExifDate', array($this, 'formatExifDateFilter')),
                        new \Twig_SimpleFilter('formatExifGpsCoordinates', array($this, 'formatExifGpsCoordinatesFilter'), array('is_safe' => array('html'))),
                );
        }

        /**
         * Returns metadata for an asset.
         *
         * @param AssetFileModel $asset    The asset model to parse
         * @param string         $property String in dot notation that sets the root metadata property
         *
         * @return array|string The asset's metadata
         */
        public function getAssetMetadataFunction($asset, $property = null)
        {
                return craft()->assetMetadata->getAssetMetadata($asset, $property);
        }

        /**
         * Converts an EXIF date/time value into a DateTime object.
         *
         * @param array $date
         *
         * @return DateTime/null
         */
        public function formatExifDateFilter($date)
        {
                return craft()->assetMetadata_helpers->formatExifDate($date);
        }

        /**
         * Converts an EXIF GPS point location into sexagesimal format (ISO 6709).
         *
         * @param array $gpsData
         *
         * @return string|null
         */
        public function formatExifGpsCoordinatesFilter($gpsData, $gpsSecDecimals = 0, $gpsSecDecPoint = '.')
        {
                return craft()->assetMetadata_helpers->formatExifGpsCoordinates($gpsData, $gpsSecDecimals, $gpsSecDecPoint);
        }
}
