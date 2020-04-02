# Changelog

## 3.0.0 - 2020-04-02

### Added
- Added GraphQL support.

### Fixed
- Fixed a bug where fields weren’t editable even if the “Make read-only” setting was disabled.

## 3.0.0-beta.2 - 2018-09-19

### Fixed
- Fixed an issue with the Craft 3 migration.

## 3.0.0-beta.1 - 2018-06-10

### Added
- Added Craft 3 compatibility.
- Added the `EVENT_AFTER_GET_METADATA` event to `carlcs\assetmetadata\services\Metadata`.

### Changed
- The field always shows the “Refresh” button now when its “Make read-only” field setting isn’t enabled.
- Extracting metadata from Assets on remote Asset Volumes can now be configured with the `downloadChunkSize` and `fakeCompleteFileSize` config settings. The `remoteAssetSources` config setting was removed.
- Updated getID3 to version 1.9.15

### Removed
- Removed support to add the field to an element’s field layout if it’s not an Asset Volume.
- Removed the ability to set the field’s Translation Method, it defaults to “none” now.
- The “Custom Metadata“ and the “Show refresh button” field settings were removed.

## 2.1.4 - 2016-06-07

### Added
- The plugin now includes the following Twig filters from the [Helpers](https://github.com/carlcs/craft-helpers) plugin: `unitPrefix`, `fractionToFloat` and `floatToFraction`.

### Changed
- Twig errors that might occur when rendering values for subfields are now logged in the assetmetadata.log file.

## 2.1.3 - 2016-06-07

### Changed
- Warnings and errors from getID3 are now logged in the assetmetadata.log file.
- Updated getID3 to the latest commit (52483a2).

### Fixed
- The search index is now updated correctly with the content of the asset metadata.

## 2.1.2 - 2016-04-21

### Changed
- Fields set to “read-only” are now grayed out a bit.

## 2.1.1 - 2016-02-02

### Added
- Added Composer support

## 2.1 - 2016-01-19

### Added
- Added a refresh button to the field type.
- Added a setting to auto-refresh the field on every element save.
- Added a setting to make field read-only.

## 2.0.2 - 2016-01-14

### Changed
- Maximum amount of data downloaded to analyse remote files can now be configured. Partially downloaded files can be padded to match the original files size.

## 2.0.1 - 2016-01-14

### Added
- Added support for remote sources.

### Fixed
- Fixed a bug where the Asset index view didn't load.

## 2.0 - 2015-12-02

### Added
- Added Craft 2.5 plugin configurations.
- Added an Asset Metadata field type to extract and store metadata from an asset on element save.

### Changed
- getID3 can now be configured from the plugin's assetmetadata.php configuration file.

## 1.0 - 2015-03-19

### __other__
- First release
