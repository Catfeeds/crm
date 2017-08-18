<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%yuqi}}".
 *
 * @property integer $clue_id
 * @property string $start_time
 * @property string $end_time
 * @property integer $is_lianxi
 * @property string $lianxi_time
 */
class Yuqi extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%yuqi}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['clue_id'], 'required'],
            [['clue_id', 'is_lianxi'], 'integer'],
            [['start_time', 'end_time', 'lianxi_time'], 'safe'],
            [['clue_id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'clue_id' => '线索id',
            'start_time' => '开始时间',
            'end_time' => '最后日期',
            'is_lianxi' => '是否联系（0否1是）',
            'lianxi_time' => '联系时间',
        ];
    }
}
