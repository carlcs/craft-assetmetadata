<?php

namespace carlcs\assetmetadata\services;

use carlcs\assetmetadata\events\MetadataEvent;
use carlcs\assetmetadata\fields\AssetMetadata;
use carlcs\assetmetadata\Plugin;
use carlcs\commons\helpers\ArrayHelper;
use Craft;
use craft\base\Component;
use craft\base\LocalVolumeInterface;
use craft\elements\Asset;
use craft\helpers\FileHelper;
use getID3;
use getid3_lib;
use yii\base\Event;

/**
 * @property getID3 $getId3
 */
class Metadata extends Component
{
    // Constants
    // =========================================================================

    /**
     * @event AfterAnalyseEvent The event that is triggered after an Asset’s metadata is extracted.
     */
    const EVENT_AFTER_EXTRACT = 'afterExtract';

    // Properties
    // =========================================================================

    /**
     * @var getID3|null
     */
    private $_getId3;

    // Public Methods
    // =========================================================================

    /**
     * Returns the field value for an Asset Metadata field.
     *
     * @param AssetMetadata $field
     * @param Asset $asset
     * @return array
     */
    public function getFieldValue(AssetMetadata $field, Asset $asset): array
    {
        $view = Craft::$app->getView();
        $oldTemplateMode = $view->getTemplateMode();
        $view->setTemplateMode($view::TEMPLATE_MODE_SITE);

        $metadata = $this->extract($asset, null, $field);

        $value = [];

        foreach ($field->subfields as $id => $subfield) {
            try {
                $twig = '{% autoescape false %}'.$subfield['template'].'{% endautoescape %}';
                $variables = ['object' => $asset, 'metadata' => $metadata];

                $value[$id] = $view->renderString($twig, $variables);
            } catch (\Exception $e) {
                Craft::error('Error rendering the template for "'.$field->handle.':'.$subfield['handle'].'": '.$e->getMessage(), __METHOD__);
            }
        }

        $view->setTemplateMode($oldTemplateMode);

        return $value;
    }

    /**
     * Extracts the metadata from an Asset.
     *
     * @param Asset $asset
     * @param string|null $key
     * @param AssetMetadata|null $field
     * @return array|string
     */
    public function extract(Asset $asset, $key = null, $field = null)
    {
        $volume = $asset->getVolume();
        $deleteTempFile = false;

        if ($asset->tempFilePath !== null) {
            // New Asset currently being uploaded
            $path = $asset->tempFilePath;
        } elseif ($volume instanceof LocalVolumeInterface) {
            // Asset on a local Asset Volume
            $path = FileHelper::normalizePath($volume->getRootPath().DIRECTORY_SEPARATOR.$asset->getPath());
        } else {
            // Asset on a remote Asset Volume
            $path = $this->getTempCopyOfFile($asset);
            $deleteTempFile = true;
        }

        $getId3 = $this->getGetId3();
        $metadata = $getId3->analyze($path);

        if ($deleteTempFile) {
            unlink($path);
        }

        if (isset($metadata['error'])) {
            Craft::error('There was a problem analysing the file: '.print_r($metadata['error'], true), __METHOD__);
        }

        if (isset($metadata['warning'])) {
            Craft::warning('There was a problem analysing the file: '.print_r($metadata['warning'], true), __METHOD__);
        }

        // Merges all available tags into one array
        // @see https://github.com/JamesHeinrich/getID3/blob/master/structure.txt
        getid3_lib::CopyTagsToComments($metadata);

        $event = new MetadataEvent(compact('metadata', 'asset', 'field'));
        Event::trigger(self::class, self::EVENT_AFTER_EXTRACT, $event);
        $metadata = $event->metadata;

        if ($key !== null) {
            return ArrayHelper::getValueByKey($key, $metadata);
        }

        return $metadata;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Returns a temporary copy of an Asset’s file, or a chunk of it.
     *
     * @param Asset $asset
     * @return string
     */
    protected function getTempCopyOfFile(Asset $asset): string
    {
        $settings = Plugin::getInstance()->getSettings();
        $downloadChunkSize = $settings->downloadChunkSize;

        if ($downloadChunkSize === false || $downloadChunkSize >= $asset->size) {
            return $asset->getCopyOfFile();
        }

        // Create a file in storage/runtime/temp/
        $tempFilename = uniqid(pathinfo($asset->filename, PATHINFO_FILENAME), true).'.'.$asset->getExtension();
        $tempPath = Craft::$app->getPath()->getTempPath().DIRECTORY_SEPARATOR.$tempFilename;
        $tempStream = fopen($tempPath, 'wb');

        $fakeCompleteFileSize = $settings->fakeCompleteFileSize;

        if (
            $fakeCompleteFileSize === true ||
            (is_array($fakeCompleteFileSize) && in_array($asset->kind, $fakeCompleteFileSize, true))
        ) {
            // Make sure the temp file is the same size as the Asset’s file
            fseek($tempStream, $asset->size - 1);
            fwrite($tempStream, 'a');
            fseek($tempStream, 0);
        }

        // Copy some bytes from the Asset’s file
        $assetStream = $asset->getStream();
        stream_copy_to_stream($assetStream, $tempStream, $downloadChunkSize);

        fclose($assetStream);
        fclose($tempStream);

        return $tempPath;
    }

    /**
     * Returns a configured getID3 instance.
     *
     * @return getID3
     */
    protected function getGetId3(): getID3
    {
        if ($this->_getId3 !== null) {
            return $this->_getId3;
        }

        $this->_getId3 = new getID3();

        foreach (Plugin::getInstance()->getSettings()->getId3 as $setting => $value) {
            $this->_getId3->{$setting} = $value;
        }

        return $this->_getId3;
    }
}
