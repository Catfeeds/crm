<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%log_clue}}".
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
 * @property string $initial_intention_level
 * @property integer $clue_id
 * @property integer $created_at
 */
class LogClue extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%log_clue}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id'], 'required'],
            [[
                'customer_id', 'intention_id', 'buy_type',
                'planned_purchase_time_id', 'spare_intention_id',
                'shop_id', 'salesman_id', 'is_assign',
                'assign_time', 'who_assign_id', 'intention_level_id',
                'create_card_time', 'create_type', 'create_time',
                'last_view_time', 'last_fail_time', 'is_fail',
                'status', 'is_star', 'star_time',
                'clue_source', 'clue_input_type', 'view_times',
                'phone_view_times', 'to_home_view_times', 'to_shop_view_times',
                'clue_id', 'created_at'
            ], 'integer'],
            [['sales_promotion_content', 'des'], 'string'],
            [[
                'intention_des', 'quoted_price', 'contrast_intention_id',
                'shop_name', 'salesman_name', 'who_assign_name',
                'intention_level_des', 'create_person_name', 'fail_tags',
                'fail_reason', 'customer_name'
            ], 'string', 'max' => 255],
            [['customer_phone'], 'string', 'max' => 15],
            [['initial_intention_level'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '自增主键',
            'customer_id' => '该条意向关联的客户id',
            'intention_des' => '意向车系 - 文字描述（冗余字段，供搜索功能使用）',
            'intention_id' => '意向车系的id',
            'buy_type' => '购车方式id',
            'planned_purchase_time_id' => '拟购时间id',
            'quoted_price' => '报价信息',
            'sales_promotion_content' => '促销内容',
            'spare_intention_id' => '备选车型id',
            'contrast_intention_id' => '对比车型 - 文字描述（由于调整的原因 ，字段名不变）',
            'shop_name' => '店铺名称，冗余字段 用于后台搜索',
            'shop_id' => '对接的门店id',
            'salesman_name' => '对接的销售人员姓名',
            'salesman_id' => '对接的销售人员id',
            'is_assign' => '是否分配了：0 - 未分配 1 - 已分配',
            'assign_time' => '分配时间',
            'who_assign_name' => '分配线索的人的姓名',
            'who_assign_id' => '谁分配的，记录分配人员的id',
            'customer_phone' => '客户的手机号',
            'intention_level_des' => '意向等级的描述（冗余字段 - 搜索用）',
            'intention_level_id' => '意向等级id',
            'create_card_time' => '建卡时间：由线索转换为意向客户的时候的时间',
            'create_type' => '线索穿件的方式：0 - 默认 1 - 后台导入excel 2 - 后台手动创建 3-接口导入 4-电商下单成功 5-电商支付超时 6-公海 7-车型分享',
            'create_person_name' => '创建人姓名',
            'create_time' => '该条线索（意向）创建时间',
            'last_view_time' => '最后一次联系时间',
            'last_fail_time' => '上次战败的时间',
            'is_fail' => '是否战败：0 - 没有战败 1 - 战败',
            'fail_tags' => '战败时选取的标签, 多个用逗号分隔',
            'fail_reason' => '战败原因',
            'status' => '状态：0 - 线索状态 1 - 转化为了意向客户 2 - 订车客户（下了订单）3 - 成交客户 （成交这步需要车城接口触发，修改状态）',
            'des' => '线索的描述',
            'customer_name' => '客户姓名 （冗余字段，供搜索用）',
            'is_star' => '是否收藏：0 - 没有收藏 1 - 收藏 （按照收藏时间排序）',
            'star_time' => '收藏时间',
            'clue_source' => '信息来源',
            'clue_input_type' => '渠道来源',
            'view_times' => '总的交谈次数 （电话 + 到店 + 上门）',
            'phone_view_times' => '电话交谈次数',
            'to_home_view_times' => '上门交谈次数',
            'to_shop_view_times' => '到店交谈次数',
            'initial_intention_level' => '首次意向等级',
            'clue_id' => '线索ID',
            'created_at' => '日志记录时间',
        ];
    }

    /**
     * 创建日志
     * @param array $insert 创建数据信息
     * @return bool 成功返回true
     */
    public static function create($insert)
    {
        $one = new LogClue();
        $one->attributes = $insert;
        $one->clue_id = $insert['id'];
        $one->created_at = time();
        return $one->save();
    }
}
