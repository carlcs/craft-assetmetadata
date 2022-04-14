<?php

namespace carlcs\assetmetadata\events;

use carlcs\assetmetadata\fields\AssetMetadata;
use craft\elements\Asset;
use yii\base\Event;

class MetadataEvent extends Event
{
    // Properties
    // =========================================================================

    public array $metadata;
    public Asset $asset;
    public ?AssetMetadata $field = null;
}
