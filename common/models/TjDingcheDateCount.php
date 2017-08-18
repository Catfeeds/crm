<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_tj_dingche_date_count".
 *
 * @property string $id
 * @property integer $salesman_id
 * @property integer $shop_id
 * @property integer $area_id
 * @property integer $company_id
 * @property integer $num
 * @property integer $date_type
 * @property string $create_date
 */
class TjDingcheDateCount extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_tj_dingche_date_count';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['salesman_id', 'shop_id', 'area_id', 'company_id', 'num', 'date_type'], 'integer'],
            [['company_id', 'create_date'], 'required'],
            [['create_date'], 'safe'],
            [['salesman_id', 'create_date', 'date_type', 'shop_id'], 'unique', 'targetAttribute' => ['salesman_id', 'create_date', 'date_type', 'shop_id'], 'message' => 'The combination of Salesman ID, Shop ID, Date Type and Create Date has already been taken.'],
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
            'num' => 'Num',
            'date_type' => 'Date Type',
            'create_date' => 'Create Date',
        ];
    }
}
