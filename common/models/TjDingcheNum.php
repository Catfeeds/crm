<?php
/**
 * 订车数统计表
 */
namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_tj_dingche_num".
 *
 * @property string $id
 * @property integer $salesman_id
 * @property integer $shop_id
 * @property integer $area_id
 * @property integer $company_id
 * @property integer $input_type_id
 * @property string $year_and_month
 * @property integer $num
 */
class TjDingcheNum extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_tj_dingche_num';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['salesman_id', 'shop_id', 'area_id', 'company_id', 'input_type_id', 'num'], 'integer'],
            [['year_and_month'], 'string', 'max' => 7],
            [['salesman_id', 'input_type_id', 'year_and_month', 'shop_id'], 'unique', 'targetAttribute' => ['salesman_id', 'input_type_id', 'year_and_month', 'shop_id'], 'message' => 'The combination of Salesman ID, Shop ID, Input Type ID and Year And Month has already been taken.'],
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
            'shop_id' => 'Shop ID',
            'area_id' => 'Area ID',
            'company_id' => 'Company ID',
            'input_type_id' => 'Input Type ID',
            'year_and_month' => 'Year And Month',
            'num' => 'Num',
        ];
    }
}
