<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_car_factory".
 *
 * @property string $factory_id
 * @property string $factory_name
 */
class CarFactory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_car_factory';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['factory_id'], 'required'],
            [['factory_id'], 'integer'],
            [['factory_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'factory_id' => 'Factory ID',
            'factory_name' => 'Factory Name',
        ];
    }
}
