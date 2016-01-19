<?php
namespace Craft;

class m160119_000000_assetMetadata_newSettings extends BaseMigration
{
        public function safeUp()
        {
                $fields = craft()->db->createCommand()->select('*')->from('fields')->where('type=:type', array(':type'=>'AssetMetadata'))->queryAll();

                foreach ($fields as $field)
                {
                        $settings = $field['settings'];
                        $settings = str_replace('"properties"', '"subfields"', $settings);
                        $settings = str_replace('"customMetadataVariable"', '"useCustomMetadataVar"', $settings);
                        $settings = str_replace('"metadataVariable"', '"customMetadataVar"', $settings);

                        $columns = array('settings' => $settings);

                        craft()->db->createCommand()->update('fields', $columns, 'id = :id', array(':id' => $field['id']));
                }

                return true;
        }
}
