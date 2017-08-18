<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_clue_wuxiao".
 *
 * @property string $id
 * @property integer $customer_id
 * @property string $intention_des
 * @property integer $intention_id
 * @property integer $buy_type
 * @property integer $planned_purchase_time_id
 * @property string $quoted_price
 * @property string $sales_promotion_content
 * @property integer $spare_intention_id
 * @property string $contrast_intention_id
 * @property string $shop_name
 * @property integer $shop_id
 * @property string $salesman_name
 * @property integer $salesman_id
 * @property integer $is_assign
 * @property integer $assign_time
 * @property string $who_assign_name
 * @property integer $who_assign_id
 * @property string $customer_phone
 * @property string $intention_level_des
 * @property integer $intention_level_id
 * @property integer $create_card_time
 * @property integer $create_type
 * @property string $create_person_name
 * @property integer $create_time
 * @property integer $last_view_time
 * @property integer $last_fail_time
 * @property integer $is_fail
 * @property string $fail_tags
 * @property string $fail_reason
 * @property integer $status
 * @property string $des
 * @property string $customer_name
 * @property integer $is_star
 * @property integer $star_time
 * @property integer $clue_source
 * @property integer $clue_input_type
 * @property integer $view_times
 * @property integer $phone_view_times
 * @property integer $to_home_view_times
 * @property integer $to_shop_view_times
 */
class ClueWuxiao extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_clue_wuxiao';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'intention_id'], 'required'],
            [['customer_id', 'intention_id', 'buy_type', 'planned_purchase_time_id', 'spare_intention_id', 'shop_id', 'salesman_id', 'is_assign', 'assign_time', 'who_assign_id', 'intention_level_id', 'create_card_time', 'create_type', 'create_time', 'last_view_time', 'last_fail_time', 'is_fail', 'status', 'is_star', 'star_time', 'clue_source', 'clue_input_type', 'view_times', 'phone_view_times', 'to_home_view_times', 'to_shop_view_times'], 'integer'],
            [['sales_promotion_content', 'des'], 'string'],
            [['intention_des', 'quoted_price', 'contrast_intention_id', 'shop_name', 'salesman_name', 'who_assign_name', 'intention_level_des', 'create_person_name', 'fail_tags', 'fail_reason', 'customer_name'], 'string', 'max' => 255],
            [['customer_phone'], 'string', 'max' => 15],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_id' => 'Customer ID',
            'intention_des' => 'Intention Des',
            'intention_id' => 'Intention ID',
            'buy_type' => 'Buy Type',
            'planned_purchase_time_id' => 'Planned Purchase Time ID',
            'quoted_price' => 'Quoted Price',
            'sales_promotion_content' => 'Sales Promotion Content',
            'spare_intention_id' => 'Spare Intention ID',
            'contrast_intention_id' => 'Contrast Intention ID',
            'shop_name' => 'Shop Name',
            'shop_id' => 'Shop ID',
            'salesman_name' => 'Salesman Name',
            'salesman_id' => 'Salesman ID',
            'is_assign' => 'Is Assign',
            'assign_time' => 'Assign Time',
            'who_assign_name' => 'Who Assign Name',
            'who_assign_id' => 'Who Assign ID',
            'customer_phone' => 'Customer Phone',
            'intention_level_des' => 'Intention Level Des',
            'intention_level_id' => 'Intention Level ID',
            'create_card_time' => 'Create Card Time',
            'create_type' => 'Create Type',
            'create_person_name' => 'Create Person Name',
            'create_time' => 'Create Time',
            'last_view_time' => 'Last View Time',
            'last_fail_time' => 'Last Fail Time',
            'is_fail' => 'Is Fail',
            'fail_tags' => 'Fail Tags',
            'fail_reason' => 'Fail Reason',
            'status' => 'Status',
            'des' => 'Des',
            'customer_name' => 'Customer Name',
            'is_star' => 'Is Star',
            'star_time' => 'Star Time',
            'clue_source' => 'Clue Source',
            'clue_input_type' => 'Clue Input Type',
            'view_times' => 'View Times',
            'phone_view_times' => 'Phone View Times',
            'to_home_view_times' => 'To Home View Times',
            'to_shop_view_times' => 'To Shop View Times',
        ];
    }
}
