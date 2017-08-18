<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_dd_source".
 *
 * @property string $id
 * @property string $name
 * @property string $des
 * @property integer $status
 */
class Source extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_dd_source';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['status'], 'integer'],
            [['name', 'des'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '细分来源名称',
            'des' => '来源描述',
            'status' => '状态',
            'is_yuqi'=>'逾期状态',
            'is_yuqi'=>'逾期时间',
        ];
    }
}
