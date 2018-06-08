<?php

namespace carlcs\assetmetadata\events;

use carlcs\assetmetadata\fields\AssetMetadata;
use craft\elements\Asset;
use yii\base\Event;

class MetadataEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var array The metadata
     */
    public $metadata;

    /**
     * @var Asset The Asset the metadata is extracted from
     */
    public $asset;

    /**
     * @var AssetMetadata|null The field the metadata is stored into.
     */
    public $field;
}
