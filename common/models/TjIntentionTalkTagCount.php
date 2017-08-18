<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_tj_intention_talk_tag_count".
 *
 * @property string $id
 * @property integer $salesman_id
 * @property integer $shop_id
 * @property integer $area_id
 * @property integer $company_id
 * @property integer $tag_id
 * @property integer $num
 */
class TjIntentionTalkTagCount extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_tj_intention_talk_tag_count';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['salesman_id'], 'required'],
            [['salesman_id', 'shop_id', 'area_id', 'company_id', 'tag_id', 'num'], 'integer'],
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
            'tag_id' => 'Tag ID',
            'num' => 'Num',
        ];
    }
}
