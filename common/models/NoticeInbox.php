<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%notice_inbox}}".
 *
 * @property string $id
 * @property integer $send_person_id
 * @property integer $get_person_id
 * @property string $addressee_des
 * @property string $title
 * @property string $content
 * @property integer $send_time
 * @property integer $send_person_name
 * @property integer $is_read
 * @property integer $send_id
 */
class NoticeInbox extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%notice_inbox}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['send_person_id', 'get_person_id', 'send_time', 'send_person_name', 'is_read', 'send_id'], 'integer'],
            [['content'], 'string'],
            [['addressee_des', 'title'], 'string', 'max' => 255],
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
            'get_person_id' => 'Get Person ID',
            'addressee_des' => 'Addressee Des',
            'title' => 'Title',
            'content' => 'Content',
            'send_time' => 'Send Time',
            'send_person_name' => 'Send Person Name',
            'is_read' => 'Is Read',
            'send_id' => 'Send ID',
        ];
    }
}
