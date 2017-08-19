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
        return [
            new \Twig_SimpleFunction('getAssetMetadata', [craft()->assetMetadata, 'getAssetMetadata']),
        ];
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('formatExifDate', [craft()->assetMetadata_helpers, 'formatExifDate']),
            new \Twig_SimpleFilter('formatExifGpsCoordinates', [craft()->assetMetadata_helpers, 'formatExifGpsCoordinates'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('formatExifGpsCoordinate', [craft()->assetMetadata_helpers, 'formatExifGpsCoordinate'], ['is_safe' => ['html']]),

            new \Twig_SimpleFilter('unitPrefix', [craft()->assetMetadata_helpers, 'unitPrefix']),
            new \Twig_SimpleFilter('fractionToFloat', [craft()->assetMetadata_helpers, 'fractionToFloat']),
            new \Twig_SimpleFilter('floatToFraction', [craft()->assetMetadata_helpers, 'floatToFraction']),
        ];
    }
}
