<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_log_pc_caozuo".
 *
 * @property string $id
 * @property integer $type_id
 * @property string $type_name
 * @property string $content
 * @property integer $create_time
 * @property integer $user_id
 * @property string $user
 * @property string $phone
 * @property string $ip
 * @property integer $org_id
 * @property string $org_name
 */
class LogPcCaozuo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_log_pc_caozuo';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type_id', 'content', 'create_time', 'user', 'phone', 'ip', 'org_name'], 'required'],
            [['type_id', 'create_time', 'user_id', 'org_id'], 'integer'],
            [['content'], 'string'],
            [['type_name', 'phone'], 'string', 'max' => 15],
            [['user', 'ip', 'org_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type_id' => 'Type ID',
            'type_name' => 'Type Name',
            'content' => 'Content',
            'create_time' => 'Create Time',
            'user_id' => 'User ID',
            'user' => 'User',
            'phone' => 'Phone',
            'ip' => 'Ip',
            'org_id' => 'Org ID',
            'org_name' => 'Org Name',
        ];
    }
}
