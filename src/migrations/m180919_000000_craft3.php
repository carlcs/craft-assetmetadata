<?php

namespace carlcs\assetmetadata\migrations;

use carlcs\assetmetadata\fields\AssetMetadata as AssetMetadataField;
use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;

class m180919_000000_craft3 extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (Craft::$app->getMigrator()->hasRun('m180529_000000_craft3')) {
            return true;
        }

        $this->update('{{%fields}}', ['type' => AssetMetadataField::class], ['type' => 'AssetMetadata']);

        $fields = (new Query())
            ->select(['id', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => AssetMetadataField::class])
            ->all($this->db);

        foreach ($fields as $field) {
            $settings = Json::decode($field['settings']);

            if (is_array($settings['subfields'])) {
                $newSubfields = [];
                foreach ($settings['subfields'] as $key => $subfield) {
                    $newSubfields[$key] = [
                        'name' => $subfield['name'],
                        'handle' => $subfield['handle'],
                        'template' => $subfield['defaultValue'],
                    ];
                }
                $settings['subfields'] = $newSubfields;
            }

            $settings['readOnly'] = $settings['readonly'];

            unset($settings['readonly'], $settings['showRefreshButton'], $settings['useCustomMetadataVar'], $settings['customMetadataVar']);

            $this->update('{{%fields}}', ['settings' => Json::encode($settings)], ['id' => $field['id']]);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180529_000000_craft3 cannot be reverted.\n";
        return false;
    }
}
