# Common Metadata Properties

Example code to access common metadata properties from an Asset Metadata field's "metadata" variable, or the `getAssetMetadata` function's return data. The complete data structure is [documented in the getID3 repository][1].

`floatToFraction` and `unitPrefix` Twig filters are available in the [Number Convert][2] plugin.


  [1]: https://github.com/JamesHeinrich/getID3/blob/master/structure.txt
  [2]: https://github.com/carlcs/craft-numberconvert

## Audio Metadata

Title

    {{ metadata.comments.title[0] is defined ? metadata.comments.title[0] }}

Artist

    {{ metadata.comments.artist[0] is defined ? metadata.comments.artist[0] }}

Album

    {{ metadata.comments.album[0] is defined ? metadata.comments.album[0] }}

Year

    {{ metadata.comments.year[0] is defined ? metadata.comments.year[0] }}

Playtime

    {{ metadata.playtime_string is defined ? metadata.playtime_string }}

Channels

    {{ metadata.audio.channels is defined ? metadata.audio.channels }}

Sample Rate

    {{ metadata.audio.sample_rate is defined ? metadata.audio.sample_rate }}

Bitrate

    {{ metadata.audio.bitrate is defined ? metadata.audio.bitrate|unitPrefix ~ 'bps' }}

Bitrate Mode

    {{ metadata.audio.bitrate_mode is defined ? metadata.audio.bitrate_mode }}

## Image Metadata

Camera Make

    {{ metadata.jpg.exif.IFD0.Make is defined ? metadata.jpg.exif.IFD0.Make }}

Camera Model

    {{ metadata.jpg.exif.IFD0.Model is defined ? metadata.jpg.exif.IFD0.Model }}

ISO

    {{ metadata.jpg.exif.EXIF.ISOSpeedRatings is defined ? 'ISO ' ~ metadata.jpg.exif.EXIF.ISOSpeedRatings }}

Focal Length

    {{ metadata.jpg.exif.EXIF.FocalLengthIn35mmFilm is defined ? metadata.jpg.exif.EXIF.FocalLengthIn35mmFilm ~ ' mm'  }}

Aperture

    {{ metadata.jpg.exif.EXIF.FNumber is defined ? 'f/' ~ metadata.jpg.exif.EXIF.FNumber }}

Shutter Speed

    {{ metadata.jpg.exif.EXIF.ExposureTime is defined ? metadata.jpg.exif.EXIF.ExposureTime|floatToFraction }}

Original Date

    {{ metadata.jpg.exif.IFD0.DateTime is defined ? metadata.jpg.exif.IFD0.DateTime|formatExifDate }}

GPS

    {{ metadata.jpg.exif.GPS is defined ? metadata.jpg.exif.GPS|formatExifGpsCoordinates }}
