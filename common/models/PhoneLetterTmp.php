<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "crm_dd_phone_letter_tmp".
 *
 * @property string $id
 * @property string $title
 * @property string $content
 * @property integer $type
 * @property integer $status
 * @property integer $addtime
 */
class PhoneLetterTmp extends ActiveRecord
{
    /**
     * 状态
     */
    const STATUS_ENABLED = 1; // 启用
    const STATUS_DISABLED = 0; // 禁用

    /**
     * 模板类型
     */
    const TYPE_TEXT_MESSAGE = 1; // 文字短信
    const TYPE_VOICE_MESSAGE = 3; // 语音短信
    const TYPE_APP_MESSAGE = 2; // APP 推送

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_dd_phone_letter_tmp';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content'], 'string'],
            [['type', 'status', 'addtime'], 'integer'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'content' => 'Content',
            'type' => 'Type',
            'status' => 'Status',
            'addtime' => 'Addtime',
        ];
    }
}
