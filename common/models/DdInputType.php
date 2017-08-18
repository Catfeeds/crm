<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%dd_input_type}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $des
 * @property integer $status
 * @property integer $is_special
 * @property integer $is_yuqi
 * @property double $yuqi_time
 */
class DdInputType extends \yii\db\ActiveRecord
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
            [['status', 'is_special', 'is_yuqi'], 'integer'],
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
            'status' => '状态:
0 - 禁用
1 - 使用',
            'is_special' => '预设类型:
0 - 非预设
1 - 预设',
            'is_yuqi' => '逾期 0没有 1有',
            'yuqi_time' => '逾期时间（小时）',
        ];
    }
}
