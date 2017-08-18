<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%app_self_update}}".
 *
 * @property string $id
 * @property string $ios_or_android
 * @property integer $app_id
 * @property string $app_name
 * @property integer $create_time
 * @property integer $versionCode
 * @property string $versionName
 * @property integer $is_forced_update
 * @property string $content
 * @property string $tips
 * @property string $file_url
 */
class AppSelfUpdate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%app_self_update}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['app_id', 'create_time', 'versionCode', 'is_forced_update'], 'integer'],
            [['content'], 'string'],
            [['ios_or_android'], 'string', 'max' => 10],
            [['app_name'], 'string', 'max' => 30],
            [['versionName', 'tips', 'file_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ios_or_android' => 'Ios Or Android',
            'app_id' => 'App ID',
            'app_name' => 'App Name',
            'create_time' => 'Create Time',
            'versionCode' => 'Version Code',
            'versionName' => 'Version Name',
            'is_forced_update' => 'Is Forced Update',
            'content' => 'Content',
            'tips' => 'Tips',
            'file_url' => 'File Url',
        ];
    }
}
