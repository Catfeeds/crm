<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%announcement_send}}".
 *
 * @property string $id
 * @property string $addressee_des
 * @property string $addressee_id
 * @property string $title
 * @property string $send_person_name
 * @property integer $send_person_id
 * @property string $content
 * @property integer $send_time
 * @property integer $is_success
 */
class AnnouncementSend extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%announcement_send}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['send_person_id', 'send_time', 'is_success'], 'integer'],
            [['addressee_des', 'addressee_id','content'], 'string'],
            [[ 'title', 'send_person_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'addressee_des' => '发布对象',
            'addressee_id' => 'Addressee ID',
            'title' => '公告标题',
            'send_person_name' => '发布人',
            'send_person_id' => 'Send Person ID',
            'content' => '内容',
            'send_time' => '发布时间',
            'is_success' => '是否成功',
        ];
    }
}
