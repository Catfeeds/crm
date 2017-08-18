<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_excitation_shop".
 *
 * @property integer $e_id
 * @property integer $shop_id
 * @property integer $area_id
 * @property integer $company_id
 */
class ExcitationShop extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_excitation_shop';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['e_id', 'shop_id', 'area_id', 'company_id'], 'required'],
            [['e_id', 'shop_id', 'area_id', 'company_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'e_id' => 'E ID',
            'shop_id' => 'Shop ID',
            'area_id' => 'Area ID',
            'company_id' => 'Company ID',
        ];
    }
}
