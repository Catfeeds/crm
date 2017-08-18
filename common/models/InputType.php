<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%dd_input_type}}".
 *
 * @property string $id
 * @property string $name
 * @property string $des
 * @property integer $status
 */
class InputType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dd_input_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status','is_yuqi'], 'integer'],
            [['yuqi_time'], 'number'],
            [['name', 'des'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '自增主键',
            'name' => '名称',
            'des' => '描述',
            'status' => '状态',
        ];
    }
}
