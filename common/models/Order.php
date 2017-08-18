<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_order".
 *
 * @property integer $id
 * @property string $order_id
 * @property integer $clue_id
 * @property integer $customer_id
 * @property integer $shop_id
 * @property string $che_order_id
 * @property integer $status
 * @property string $pre_order_id
 * @property integer $create_time
 * @property integer $last_pudate_time
 * @property integer $create_type
 * @property integer $car_type_id
 * @property string $color_configure
 * @property string $deposit
 * @property string $car_type_name
 * @property integer $buy_type
 * @property integer $loan_period
 * @property integer $predict_car_delivery_time
 * @property integer $car_delivery_time
 * @property string $delivery_price
 * @property string $discount_price
 * @property string $give
 * @property integer $is_insurance
 * @property integer $insurance_time
 * @property string $frame_number
 * @property string $engine_code
 * @property string $car_number
 * @property integer $is_add
 * @property string $add_content
 * @property string $car_owner_name
 * @property string $car_owner_phone
 * @property string $qr_url
 * @property integer $salesman_id
 * @property integer $talk_id
 * @property integer $order_type
 * @property integer $che_car_id
 * @property integer $cai_wu_dao_zhang_time
 * @property string $che_car_name
 */
class Order extends \yii\db\ActiveRecord
{
    /**
     * 订单支付状态
     */
    const ORDER_STATUS_PAYMENT_WAIT = 1;      // 待客户支付
    const ORDER_STATUS_PAYMENT_CARRY_OUT = 2; // 客户已支付

    /**
     * 订单类型
     */
    const ORDER_TYPE_CRM = 0;           // crm 跳转到车城
    const ORDER_TYPE_TIANMAO = 1;       // 天猫下单
    const ORDER_TYPE_BATCH_PURCHASE = 2; // 批采
    const ORDER_TYPE_E_COMMERCE = 3;     // 电商直接下单

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'clue_id', 'customer_id', 'shop_id',  'status', 'talk_id'], 'required'],
            [
                [
                    'clue_id', 'customer_id', 'shop_id',
                    'status', 'create_time', 'last_pudate_time',
                    'create_type', 'car_type_id', 'buy_type',
                    'loan_period', 'predict_car_delivery_time', 'car_delivery_time',
                    'is_insurance', 'insurance_time', 'is_add',
                    'salesman_id','order_type', 'che_car_id'
                ],
                'integer'
            ],
            [['deposit', 'delivery_price', 'discount_price'], 'number'],
            [['give'], 'string'],
            [['order_id', 'pre_order_id'], 'string', 'max' => 20],
            [['che_order_id'], 'string', 'max' => 30],
            [['che_car_name'], 'string', 'max' => 100],
            [['color_configure', 'frame_number', 'engine_code', 'car_number', 'add_content', 'car_owner_name', 'car_owner_phone','car_type_name', 'qr_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '自增主键',
            'order_id' => 'crm中的订单号',
            'clue_id' => '该订单关联的线索id',
            'customer_id' => '订单关联的客户id',
            'shop_id' => '订单关联的门店id',
            'che_order_id' => '关联的车城线上的订单id',
            'status' => '订单状态：1 - 生成订单，未支付2 - 订单已支付3 - 订单已取消/失败，换车的时候原来的订单取消掉4 - 已交车',
            'pre_order_id' => '原来的订单id，换车时取消原来的订单，该字段才有值',
            'create_time' => '下单时间,订车日期',
            'last_pudate_time' => '订单最后更新时间',
            'create_type' => '订单的创建方式1 - 默认方式（车助手中下单）2 - 线上订单，分配到车助手中处理',
            'car_type_id' => '车系id',
            'color_configure' => '颜色和配置',
            'deposit' => '订金',
            'buy_type' => '购车方式，对应购车方式数据字典中的id',
            'loan_period' => '贷款期限,多少年',
            'predict_car_delivery_time' => '预计交车日期',
            'car_delivery_time' => '实际交车时间',
            'delivery_price' => '成交价格',
            'discount_price' => '优惠金额',
            'give' => '赠送内容',
            'is_insurance' => '是否本店投保',
            'insurance_time' => '保险日期',
            'frame_number' => '车架号',
            'engine_code' => '发动机编号',
            'car_number' => '车牌号',
            'is_add' => '是否加装',
            'add_content' => '精品装饰内容',
            'car_owner_name' => '车主姓名',
            'car_owner_phone' => '车主电话',
            'car_type_name' => '车系名称',
            'cai_wu_dao_zhang_time' => '财务到账时间',
            'order_type' => '类型',
            'che_car_id' => '车型ID',
            'che_car_name' => '车型名称',
        ];
    }

    public function getClue()
    {
        return $this->hasOne(Clue::className(),['id' => 'clue_id']);
    }


    /**
     * 订车记录
     *
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if($this->status == 6) {
            //商谈记录客户相关信息存入redis
            if (Yii::$app->cache->get('addTalk'.Yii::$app->user->getId())) {
                $data = json_decode(Yii::$app->cache->get('talk_change_' . Yii::$app->user->getId()), true) ?: [];
                $data['购车车型'] = $this->car_type_name;
                $data['成交价格'] = $this->delivery_price;
                $data['车架号'] = $this->frame_number;
                Yii::$app->cache->set('talk_change_' . Yii::$app->user->getId(), json_encode($data));
            }
        }
    }

    /**
     * createOrderId() 生成订单号
     * @return string
     */
    public static function createOrderId()
    {
        return date('YmdHis') . mt_rand(100, 999) . mt_rand(100, 999);
    }

    /**
     * 获取订单状态说明信息
     *
     * @param null $status
     * @return array|mixed|null
     */
    public static function getOrderStatusDesc($status = null)
    {
        $mixReturn = [
            self::ORDER_STATUS_PAYMENT_WAIT => '待客户支付',
            self::ORDER_STATUS_PAYMENT_CARRY_OUT => '客户已支付',
        ];

        // 获取单个状态值的说明
        if ($status !== null) {
            if (isset($mixReturn[$status])) {
                $mixReturn = $mixReturn[$status];
            } else {
                $mixReturn = null;
            }
        }

        return $mixReturn;
    }
}
