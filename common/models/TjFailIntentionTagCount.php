<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_tj_fail_intention_tag_count".
 *
 * @property string $id
 * @property integer $salesman_id
 * @property integer $shop_id
 * @property integer $area_id
 * @property integer $company_id
 * @property string $create_date
 * @property integer $input_type_id
 * @property integer $tag_id
 * @property integer $num
 * @property integer $fail_type
 */
class TjFailIntentionTagCount extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_tj_fail_intention_tag_count';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['salesman_id', 'shop_id', 'area_id', 'company_id', 'input_type_id', 'tag_id', 'num', 'fail_type'], 'integer'],
            [['create_date', 'tag_id', 'num'], 'required'],
            [['create_date'], 'safe'],
            [['salesman_id', 'create_date', 'input_type_id', 'tag_id', 'shop_id'], 'unique', 'targetAttribute' => ['salesman_id', 'create_date', 'input_type_id', 'tag_id', 'shop_id'], 'message' => 'The combination of Salesman ID, Shop ID, Create Date, Input Type ID and Tag ID has already been taken.'],
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
            'tag_id' => 'Tag ID',
            'num' => 'Num',
            'fail_type' => 'Fail Type',
        ];
    }
}
