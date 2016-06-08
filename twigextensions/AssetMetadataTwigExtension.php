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
            new \Twig_SimpleFunction('getAssetMetadata', array(craft()->assetMetadata, 'getAssetMetadata')),
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
            new \Twig_SimpleFilter('formatExifDate', array(craft()->assetMetadata_helpers, 'formatExifDate')),
            new \Twig_SimpleFilter('formatExifGpsCoordinates', array(craft()->assetMetadata_helpers, 'formatExifGpsCoordinates'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('formatExifGpsCoordinate', array(craft()->assetMetadata_helpers, 'formatExifGpsCoordinate'), array('is_safe' => array('html'))),

            new \Twig_SimpleFilter('unitPrefix', array(craft()->assetMetadata_helpers, 'unitPrefix')),
            new \Twig_SimpleFilter('fractionToFloat', array(craft()->assetMetadata_helpers, 'fractionToFloat')),
            new \Twig_SimpleFilter('floatToFraction', array(craft()->assetMetadata_helpers, 'floatToFraction')),
        );
    }
}
