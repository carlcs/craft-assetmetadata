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
     * @param AssetFileModel $asset    The asset file model to parse
     * @param string         $property String in dot notation that sets the root metadata property
     *
     * @return array|string The asset's metadata
     */
    public function getAssetMetadata($asset, $property = null)
    {
        $getId3 = $this->_getGetId3();

        if (!($asset instanceof AssetFileModel)) {
            throw new Exception('(Asset Metadata) The plugin only works with asset file models.');
        }

        $sourceType = craft()->assetSources->getSourceTypeById($asset->sourceId);

        if ($sourceType->isRemote()) {
            $config = craft()->config->get('remoteAssetSources', 'assetmetadata');

            // Makes a local copy of the file if it's from a remote source.
            $path = AssetsHelper::getTempFilePath($asset->getExtension());
            $this->_downloadRemoteFile($asset->getUrl(), $path, $config['downloadSize']);

            // Pad truncated file to original size.
            if ($config['padTruncatedFiles'] && $config['downloadSize'] && $asset->size > $config['downloadSize']) {
                $this->_padFile($path, $asset->size - $config['downloadSize']);
            }

            $metadata = $getId3->analyze($path);
            IOHelper::deleteFile($path);
        } else {
            $folder = craft()->assets->getFolderById($asset->folderId);
            $path = $sourceType->getBasePath().$folder->path.$asset->filename;
            $path = IOHelper::getRealPath($path);

            $metadata = $getId3->analyze($path);
        }

        // Log errors
        if (isset($metadata['error'])) {
            AssetMetadataPlugin::log('There was a problem parsing the metadata: '.print_r($metadata['error'], true), LogLevel::Error);
        }

        if (isset($metadata['warning'])) {
            AssetMetadataPlugin::log('There was a problem parsing the metadata: '.print_r($metadata['warning'], true), LogLevel::Warning);
        }

        // Merges ID3 tags and stores them in a "comments" property.
        getid3_lib::CopyTagsToComments($metadata);

        // Removes troublesome properties.
        $this->_removeProperties($metadata);

        return $this->_getValueByKey($property, $metadata);
    }

    // Private Methods
    // =========================================================================

    /**
     * Partially downloads a file from a remote source
     *
     * @param string $url  The file's URL.
     * @param string $path The path to write the file to.
     * @param int    $size The max. bytes to be downloaded before the file gets truncated.
     *
     * @return bool
     */
    private function _downloadRemoteFile($url, $path, $size)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($size != null) {
            curl_setopt($ch, CURLOPT_RANGE, '0-'.$size);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        IOHelper::writeToFile($path, $response);

        return true;
    }

    /**
     * Pads a file until it reaches a size
     *
     * @param string $path The file's path.
     * @param int    $size The max. bytes the file is padded to.
     *
     * @return bool
     */
    private function _padFile($path, $size)
    {
        $fh = fopen($path, 'a');
        $chunk = 1024;

        while ($size > 0) {
            fputs($fh, str_pad('', min($chunk, $size)));
            $size -= $chunk;
        }

        fclose($fh);

        return true;
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

        foreach ($settings as $setting => $value) {
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

        foreach ($properties as $property) {
            $data = &$array;

            foreach (explode('.', $property) as $key) {
                $data = &$data[$key];
            }

            $data = null;
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
        if (!is_string($property) || empty($property)) {
            return $data;
        }

        if (strpos($property, '.') !== false) {
            foreach (explode('.', $property) as $key) {
                if (!array_key_exists($key, $data)) {
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
