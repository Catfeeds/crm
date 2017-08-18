<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%feedback}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $user_name
 * @property string $user_phone
 * @property string $user_ip
 * @property string $org_name
 * @property string $content
 * @property string $imgs
 * @property integer $app_id
 * @property integer $create_time
 */
class Feedback extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'app_id', 'create_time'], 'integer'],
            [['content'], 'string'],
            [['user_name'], 'string', 'max' => 16],
            [['user_phone'], 'string', 'max' => 15],
            [['user_ip'], 'string', 'max' => 32],
            [['org_name'], 'string', 'max' => 128],
            [['imgs'], 'string', 'max' => 1000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'user_name' => 'User Name',
            'user_phone' => 'User Phone',
            'user_ip' => 'User Ip',
            'org_name' => 'Org Name',
            'content' => 'Content',
            'imgs' => 'Imgs',
            'app_id' => 'App ID',
            'create_time' => 'Create Time',
        ];
    }
}
