<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_tj_inputtypeclue_fail".
 *
 * @property string $id
 * @property integer $salesman_id
 * @property integer $shop_id
 * @property integer $area_id
 * @property integer $company_id
 * @property string $create_date
 * @property integer $input_type_id
 * @property integer $num
 */
class TjInputtypeclueFail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_tj_inputtypeclue_fail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['salesman_id', 'shop_id', 'area_id', 'company_id', 'input_type_id', 'num'], 'integer'],
            [['create_date'], 'required'],
            [['create_date'], 'safe'],
            [['salesman_id', 'create_date', 'input_type_id', 'shop_id'], 'unique', 'targetAttribute' => ['salesman_id', 'create_date', 'input_type_id', 'shop_id'], 'message' => 'The combination of Salesman ID, Shop ID, Create Date and Input Type ID has already been taken.'],
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
            'create_date' => 'Create Date',
            'input_type_id' => 'Input Type ID',
            'num' => 'Num',
        ];
    }
}
