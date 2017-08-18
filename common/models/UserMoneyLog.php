<?php
/**
 * 销售人员的钱包收支明细表
 */
namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_user_money_log".
 *
 * @property string $id
 * @property integer $salesman_id
 * @property integer $type
 * @property string $money
 * @property string $e_name
 * @property string $des
 * @property integer $status
 * @property string $addtime
 * @property string $last_update_time
 */
class UserMoneyLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_user_money_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['salesman_id', 'type', 'status'], 'integer'],
            [['money'], 'number'],
            [['addtime', 'last_update_time'], 'safe'],
            [['e_name', 'des'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'salesman_id' => 'Salesman ID',
            'type' => 'Type',
            'money' => 'Money',
            'e_name' => 'E Name',
            'des' => 'Des',
            'status' => 'Status',
            'addtime' => 'Addtime',
            'last_update_time' => 'Last Update Time',
        ];
    }
}
