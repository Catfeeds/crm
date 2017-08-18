<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%clue_pending}}".
 *
 * @property integer $id
 * @property integer $create_time
 * @property string $customer_name
 * @property string $customer_phone
 * @property string $location
 * @property integer $car_brand_son_type_id
 * @property string $car_brand_son_type_name
 * @property integer $intention_id
 * @property string $intention_des
 * @property integer $type
 */
class CluePending extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%clue_pending}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'create_time', 'car_brand_son_type_id', 'intention_id', 'is_type'], 'integer'],
            [['customer_name', 'location', 'car_brand_son_type_name', 'intention_des'], 'string', 'max' => 255],
            [['customer_phone'], 'string', 'max' => 15],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'create_time' => 'Create Time',
            'customer_name' => 'Customer Name',
            'customer_phone' => 'Customer Phone',
            'location' => 'Location',
            'car_brand_son_type_id' => 'Car Brand Son Type ID',
            'car_brand_son_type_name' => 'Car Brand Son Type Name',
            'intention_id' => 'Intention ID',
            'intention_des' => 'Intention Des',
            'is_type' => 'is_type',
        ];
    }
}
