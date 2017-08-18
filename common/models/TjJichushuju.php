<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_tj_jichushuju".
 *
 * @property string $id
 * @property string $salesman_name
 * @property integer $salesman_id
 * @property string $shop_name
 * @property integer $shop_id
 * @property string $area_name
 * @property integer $area_id
 * @property string $company_name
 * @property integer $company_id
 * @property string $create_date
 * @property integer $chengjiao_num
 * @property integer $fail_num
 * @property integer $new_clue_num
 * @property integer $phone_task_num
 * @property integer $finish_phone_task_num
 * @property integer $new_intention_num
 * @property integer $talk_num
 * @property integer $lai_dian_num
 * @property integer $qu_dian_num
 * @property integer $to_shop_num
 * @property integer $to_home_num
 * @property integer $ding_che_num
 */
class TjJichushuju extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_tj_jichushuju';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['salesman_id', 'create_date'], 'required'],
            [['salesman_id', 'shop_id', 'area_id', 'company_id', 'chengjiao_num', 'fail_num', 'new_clue_num', 'phone_task_num', 'finish_phone_task_num', 'new_intention_num', 'talk_num', 'lai_dian_num', 'qu_dian_num', 'to_shop_num', 'to_home_num', 'ding_che_num'], 'integer'],
            [['create_date'], 'safe'],
            [['salesman_name', 'shop_name', 'area_name', 'company_name'], 'string', 'max' => 255],
            [['salesman_id', 'create_date', 'shop_id'], 'unique', 'targetAttribute' => ['salesman_id', 'create_date', 'shop_id'], 'message' => 'The combination of Salesman ID, Shop ID and Create Date has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'salesman_name' => 'Salesman Name',
            'salesman_id' => 'Salesman ID',
            'shop_name' => 'Shop Name',
            'shop_id' => 'Shop ID',
            'area_name' => 'Area Name',
            'area_id' => 'Area ID',
            'company_name' => 'Company Name',
            'company_id' => 'Company ID',
            'create_date' => 'Create Date',
            'chengjiao_num' => 'Chengjiao Num',
            'fail_num' => 'Fail Num',
            'new_clue_num' => 'New Clue Num',
            'phone_task_num' => 'Phone Task Num',
            'finish_phone_task_num' => 'Finish Phone Task Num',
            'new_intention_num' => 'New Intention Num',
            'talk_num' => 'Talk Num',
            'lai_dian_num' => 'Lai Dian Num',
            'qu_dian_num' => 'Qu Dian Num',
            'to_shop_num' => 'To Shop Num',
            'to_home_num' => 'To Home Num',
            'ding_che_num' => 'Ding Che Num',
        ];
    }
}
