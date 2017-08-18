<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%dd_source}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $des
 * @property integer $status
 * @property integer $is_special
 */
class DdSource extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dd_source}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'is_special'], 'integer'],
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
            'status' => '状态：0 - 禁用 1 - 启用中',
            'is_special' => '预设类型: 0 - 非预设 1 - 预设',
        ];
    }
}
