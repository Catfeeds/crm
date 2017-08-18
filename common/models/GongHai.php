<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%gonghai}}".
 *
 * @property integer $id
 * @property integer $customer_id
 * @property string $customer_name
 * @property string $customer_phone
 * @property integer $intention_id
 * @property string $intention_des
 * @property integer $reason_id
 * @property string $reason_des
 * @property integer $create_time
 * @property integer $area_id
 * @property string $area_name
 * @property integer $chexing_id
 * @property string $chexing_des
 * @property integer $follow_up
 * @property integer $defeat_num
 */
class GongHai extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%gonghai}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'intention_id', 'reason_id', 'create_time', 'area_id', 'chexing_id', 'follow_up', 'defeat_num'], 'integer'],
            [['customer_phone'], 'required'],
            [['customer_name'], 'string', 'max' => 50],
            [['customer_phone'], 'string', 'max' => 15],
            [['intention_des', 'reason_des', 'area_name', 'chexing_des'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_id' => 'Customer ID',
            'customer_name' => 'Customer Name',
            'customer_phone' => 'Customer Phone',
            'intention_id' => 'Intention ID',
            'intention_des' => 'Intention Des',
            'reason_id' => 'Reason ID',
            'reason_des' => 'Reason Des',
            'create_time' => 'Create Time',
            'area_id' => 'Area ID',
            'area_name' => 'Area Name',
            'chexing_id' => 'Chexing ID',
            'chexing_des' => 'Chexing Des',
            'follow_up' => 'Follow Up',
            'defeat_num' => 'Defeat Num',
        ];
    }
}
