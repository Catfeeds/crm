<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_tj_intention_genjinzhong".
 *
 * @property string $id
 * @property integer $salesman_id
 * @property integer $shop_id
 * @property integer $area_id
 * @property integer $company_id
 * @property integer $num
 */
class TjIntentionGenjinzhong extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_tj_intention_genjinzhong';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['salesman_id', 'shop_id', 'area_id', 'company_id', 'num'], 'integer'],
            [['salesman_id', 'shop_id'], 'unique', 'targetAttribute' => ['salesman_id', 'shop_id'], 'message' => 'The combination of Salesman ID and Shop ID has already been taken.'],
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
        ];
    }
}
