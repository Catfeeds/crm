<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%announcement_inbox}}".
 *
 * @property string $id
 * @property integer $shop_id
 * @property string $addressee_id
 * @property string $addressee_des
 * @property string $title
 * @property string $send_person_name
 * @property integer $send_person_id
 * @property integer $content
 * @property integer $send_time
 */
class AnnouncementInbox extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%announcement_inbox}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id', 'send_person_id', 'content', 'send_time'], 'integer'],
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
            'shop_id' => 'Shop ID',
            'addressee_id' => 'Addressee ID',
            'addressee_des' => 'Addressee Des',
            'title' => 'Title',
            'send_person_name' => 'Send Person Name',
            'send_person_id' => 'Send Person ID',
            'content' => 'Content',
            'send_time' => 'Send Time',
        ];
    }
}
