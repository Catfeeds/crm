<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_dd_profession".
 *
 * @property string $id
 * @property string $name
 * @property string $des
 * @property integer $status
 */
class Profession extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_dd_profession';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['name', 'des'], 'string', 'max' => 255],
            [['name',], 'required']

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'name' => '职业名称',
            'des' => '职业描述',
            'status' => '状态',
        ];
    }


}
