<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_tj_dingche_date_inputtype".
 *
 * @property string $id
 * @property integer $salesman_id
 * @property integer $shop_id
 * @property integer $area_id
 * @property integer $company_id
 * @property integer $input_type_id
 * @property string $create_date
 * @property integer $num
 */
class TjDingcheDateInputtype extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_tj_dingche_date_inputtype';
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
            [['salesman_id', 'input_type_id', 'create_date', 'shop_id'], 'unique', 'targetAttribute' => ['salesman_id', 'input_type_id', 'create_date', 'shop_id'], 'message' => 'The combination of Salesman ID, Shop ID, Input Type ID and Create Date has already been taken.'],
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
            'create_date' => 'Create Date',
            'num' => 'Num',
        ];
    }
}
