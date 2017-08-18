<?php
/**
 * 激励log表
 */
namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_excitation_log".
 *
 * @property string $id
 * @property integer $e_id
 * @property integer $shop_id
 * @property integer $area_id
 * @property integer $company_id
 * @property integer $salesman_id
 * @property integer $type_id
 * @property string $e_money
 * @property string $addtime
 */
class ExcitationLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_excitation_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['e_id', 'shop_id', 'area_id', 'company_id', 'salesman_id', 'type_id'], 'integer'],
            [['salesman_id', 'type_id'], 'required'],
            [['e_money'], 'number'],
            [['addtime'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'e_id' => 'E ID',
            'shop_id' => 'Shop ID',
            'area_id' => 'Area ID',
            'company_id' => 'Company ID',
            'salesman_id' => 'Salesman ID',
            'type_id' => 'Type ID',
            'e_money' => 'E Money',
            'addtime' => 'Addtime',
        ];
    }
}
