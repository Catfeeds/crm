<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%gonghai_log}}".
 *
 * @property integer $id
 * @property string $customer_phone
 * @property integer $start_time
 * @property integer $end_time
 * @property string $salesman_name
 * @property integer $reason_id
 * @property string $reason_name
 * @property string $defeated_reason
 * @property integer $shop_id
 * @property string $shop_des
 * @property integer $clue_id
 */
class GongHaiLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%gonghai_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_phone', 'start_time', 'end_time', 'salesman_name', 'reason_id'], 'required'],
            [['start_time', 'end_time', 'reason_id', 'shop_id', 'clue_id'], 'integer'],
            [['customer_phone'], 'string', 'max' => 11],
            [['salesman_name', 'reason_name', 'defeated_reason'], 'string', 'max' => 50],
            [['shop_des'], 'string', 'max' => 60],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_phone' => '客户手机号',
            'start_time' => '开始跟进时间',
            'end_time' => '结束跟进时间',
            'salesman_name' => '顾问名',
            'reason_id' => '进入原因id',
            'reason_name' => '进入原因',
            'defeated_reason' => '战败原因',
            'shop_id' => '门店信息',
            'shop_des' => '门店信息',
            'clue_id' => '线索ID',
        ];
    }
}
