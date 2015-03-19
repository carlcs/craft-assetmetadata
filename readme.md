# Asset Metadata plugin for Craft

Extracts all sort of metadata from your assets.

## Installation

To install the plugin, copy the assetmetadata/ folder into craft/plugins/. Then go to Settings → Plugins and click the "Install" button next to "Asset Metadata".

## Examples

JPG

```twig
{% set metadata = craft.assetMetadata.getData(asset, 'jpg.exif') %}

<span>{{ assetData.EXIF.FocalLengthIn35mmFilm ~ 'mm' }}</span>
<span>{{ 'f/' ~ assetData.EXIF.FNumber }}</span>
```

MP3

```twig
{% set metadata = craft.assetMetadata.getData(myAssetFileModel) %}

<span itemprop="name">{{ metadata.title }}</span>
<span itemprop="duration" content="{{ metadata.playtime_ISO8601 }}">{{ metadata.playtime_string }}</span>
```
