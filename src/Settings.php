<?php

namespace carlcs\assetmetadata;

use craft\base\Model;
use craft\elements\Asset;

class Settings extends Model
{
    /**
     * @var array Config settings for the getID3 library.
     * @see https://github.com/JamesHeinrich/getID3/blob/master/getid3/getid3.php
     */
    public $getId3 = [
        'option_extra_info' => true,
        'option_tags_html' => false,
        'option_save_attachments' => false,
    ];

    /**
     * @var int|false Size in bytes of the file chunk that gets downloaded from
     * files on remote Asset Volumes.
     */
    public $downloadChunkSize = 256 * 1024;

    /**
     * @var array|bool The file kinds where the complete file size is faked when
     * only a chunk of it was downloaded from a remote Asset Volume. This is required
     * to reliably extract certain metadata like the playtime of an audio file.
     */
    public $fakeCompleteFileSize = [
        Asset::KIND_AUDIO,
    ];
}
