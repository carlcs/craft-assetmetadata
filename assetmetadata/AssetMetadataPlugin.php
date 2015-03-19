<?php
namespace Craft;

class AssetMetadataPlugin extends BasePlugin
{
	function getName()
	{
		return 'Asset Metadata';
	}

	function getVersion()
	{
		return '1.0';
	}

	function getDeveloper()
	{
		return 'carlcs';
	}

	function getDeveloperUrl()
	{
		return 'https://github.com/carlcs/craft-assetmetadata';
	}

	protected function defineSettings()
	{
		return array(
			'tagSeparator'   => array(AttributeType::String, 'default' => ' | '),
			'copyTagsToRoot' => array(AttributeType::Bool, 'default' => true),
			'extraInfo'      => array(AttributeType::Bool, 'default' => true),
			'md5Data'        => array(AttributeType::Bool, 'default' => false),
			'sha1Data'       => array(AttributeType::Bool, 'default' => false),
		);
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('assetmetadata/_settings', array(
			'settings' => $this->getSettings(),
		));
	}

	public function addTwigExtension()
	{
		Craft::import('plugins.assetmetadata.twigextensions.AssetMetadataTwigExtension');
		return new AssetMetadataTwigExtension();
	}
}
