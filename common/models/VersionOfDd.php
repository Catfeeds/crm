<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%version_of_dd}}".
 *
 * @property integer $id
 * @property string $dd__table_name
 * @property string $dd_name
 * @property integer $dd_version
 */
class VersionOfDd extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%version_of_dd}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['dd__table_name', 'dd_name'], 'required'],
            [['dd_version'], 'integer'],
            [['dd__table_name', 'dd_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '自增主键',
            'dd__table_name' => '数据字典的表明',
            'dd_name' => '数据字典名称',
            'dd_version' => '数据字典版本号',
        ];
    }
}
