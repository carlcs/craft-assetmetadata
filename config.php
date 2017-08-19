<?php

return [
    'getId3' => [
        // Settings to configure getID3 are documented in
        // assetmetadata/vendor/james-heinrich/getid3/getid3/getid3.php
        'option_extra_info' => true,
        'option_tags_html' => false,
        'option_save_attachments' => false,
    ],
    'excludeProperties' => [
        // ID3 tags might cause character encoding problems. It's recommended to access
        // tags via the UTF-8 encoded "comments" property.
        'id3v1', 'tags.id3v1', 'tags_html.id3v1',
        'id3v2', 'tags.id3v2', 'tags_html.id3v2'
    ],
    'remoteAssetSources' => [
        'downloadSize' => 32 * 1024,
        'padTruncatedFiles' => true,
    ],
];
