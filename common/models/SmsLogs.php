<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_sms_logs".
 *
 * @property string $id
 * @property string $phones
 * @property string $content
 * @property integer $respcode
 * @property string $resmsg
 * @property string $create_time
 */
class SmsLogs extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_sms_logs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phones', 'content', 'respcode', 'resmsg'], 'required'],
            [['content'], 'string'],
            [['respcode'], 'integer'],
            [['create_time'], 'safe'],
            [['phones', 'resmsg'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phones' => 'Phones',
            'content' => 'Content',
            'respcode' => 'Respcode',
            'resmsg' => 'Resmsg',
            'create_time' => 'Create Time',
        ];
    }
}
