<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%notice_send}}".
 *
 * @property string $id
 * @property integer $send_person_id
 * @property string $addressee_id
 * @property string $addressee_des
 * @property string $title
 * @property string $send_person_name
 * @property string $content
 * @property integer $send_time
 * @property string $huawei_request_fail_des
 */
class NoticeSend extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%notice_send}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['send_person_id', 'send_time'], 'integer'],
            [['content'], 'string'],
            [['addressee_id', 'addressee_des', 'title', 'send_person_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'send_person_id' => 'Send Person ID',
            'addressee_id' => 'Addressee ID',
            'addressee_des' => 'Addressee Des',
            'title' => 'Title',
            'send_person_name' => 'Send Person Name',
            'content' => 'Content',
            'send_time' => 'Send Time',
        ];
    }
}
