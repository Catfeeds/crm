<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_user_history_clue".
 *
 * @property string $id
 * @property integer $clue_id
 * @property integer $customer_id
 * @property integer $salesman_id
 * @property string $reason
 * @property integer $operator_id
 * @property string $operator_name
 * @property integer $create_time
 */
class UserHistoryClue extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_user_history_clue';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['clue_id', 'customer_id', 'salesman_id', 'reason', 'operator_id', 'operator_name', 'create_time'], 'required'],
            [['clue_id', 'customer_id', 'salesman_id', 'operator_id', 'create_time'], 'integer'],
            [['reason', 'operator_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'clue_id' => 'Clue ID',
            'customer_id' => 'Customer ID',
            'salesman_id' => 'Salesman ID',
            'reason' => 'Reason',
            'operator_id' => 'Operator ID',
            'operator_name' => 'Operator Name',
            'create_time' => 'Create Time',
        ];
    }
}
