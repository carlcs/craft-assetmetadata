# Asset Metadata plugin for Craft CMS

![Asset Metadata](https://github.com/carlcs/craft-assetmetadata/blob/v2/resources/screenshot.png)

The plugin provides a configurable field type to extract and store metadata from an asset on element save into subfields. The subfields are user editable, so it's possible to complete missing data or correct values to your own requirements.

## Installation

The plugin is available on Packagist and can be installed using Composer. You can also download the [latest release][0] and copy the files into craft/plugins/assetmetadata/.

```
$ composer require carlcs/craft-assetmetadata
```


  [0]: https://github.com/carlcs/craft-assetmetadata/releases/latest

## Data Structure

When you access the "metadata" variable from the field type or directly use the plugin's `getAssetMetadata` function in the template, you are dealing with data returned from getID3 by James Heinrich. The complete data structure is [documented in its repository][1] on GitHub.


  [1]: https://github.com/JamesHeinrich/getID3/blob/v2/structure.txt

## Asset Metadata Field Type

The field type consists of a group of subfields to hold asset metadata. You can attach an Asset Metadata field to an asset source, and it will be available to any assets within that source.

The field gets popoluted with metadata right after uploading the asset and can then be reviewed and edited.

(It's also possible to add a Asset Metadata field to an element that doesn't directly hold an asset. See [Metadata Variable](#metadata-variable) settings on how to configure the setup.)

### Settings

#### Metadata Properties

Define the metadata properties that will be available as "sub-fields" to your Assets Metadata field.

- Name – The name that is displayed in the CP
- Handle – How you'll refer to this "sub-field" from your templates
- Default Value – The value that is saved for this property

Default Value is a full Twig template that will be parsed when you save the element the field is attached to. You can access the asset's metadata via a "metadata" variable and the element itself is accessible via an "object" variable.

**Example:**

```twig
{{ metadata.playtime_string is defined ? metadata.playtime_string : '--' }}
```

[More example code to access common metadata properties.][3]


  [3]: _examples/common-properties.md

#### Metadata Variable

By default the field gets the metadata from the asset the field is added to and makes a variable "metadata" available for use in the Default Values configuration column.

If the field is used with another element type, you need to manually set a "metadata" variable. Use the plugin's `getAssetMetadata` function and pass in the asset model you want to get the data from. Here's how you would get the data from an assets field “myImages”.

```twig
{% set metadata = getAssetMetadata(object.myImages.first()) %}
```

### Templating

You can access metadata properties from a Asset Metadata field using the "sub-field" handles:

```twig
{{ asset.metadata.playtime }}
```

## Twig filters and functions

In addition to the filters and functions listed below, the plugin also includes `unitPrefix`, `fractionToFloat` and `floatToFraction` from the [Helpers][4] plugin.

  [4]: https://github.com/carlcs/craft-helpers

#### getAssetMetadata( asset, property )

Returns metadata for an asset. Please consider caching the function together with its output to save resources. Or use the Asset Metadata field type instead.

- **`asset`** (required) – The asset model to parse.
- **`property`** (default `null`) – String in dot notation that sets the root metadata property.

```twig
{% set metadata = getAssetMetadata(asset, 'jpg.exif') %}

<span>{{ metadata.EXIF.FocalLengthIn35mmFilm ~ ' mm' }}</span>
<span>{{ 'f/' ~ metadata.EXIF.FNumber }}</span>
```

#### formatExifGpsCoordinates( gpsSecDecimals, gpsSecDecPoint )

Converts an EXIF GPS point location into sexagesimal format (ISO 6709).

- **`gpsSecDecimals`** (default `0`) – Number of digits after the decimal point for the seconds value.
- **`gpsSecDecPoint`** (default `.`) – The character used as the decimal point.

#### formatExifDate

Converts an EXIF date value into a DateTime object.

## Requirements

- PHP 5.4 or later
- Craft CMS 2.6.2778 or later
