<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_dd_car_type".
 *
 * @property string $id
 * @property string $name
 * @property integer $pid
 * @property integer $level
 */
class CarType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_dd_car_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pid', 'level'], 'integer'],
            [['name', 'logo'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'pid' => 'Pid',
            'level' => 'Level',
            'logo' => 'Logo',
        ];
    }
}
