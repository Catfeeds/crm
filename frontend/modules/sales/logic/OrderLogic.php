<?php
/**
 * 订单
 * Created by PhpStorm.
 * User: 于凯
 * Date: 2017/3/17
 */

namespace frontend\modules\sales\logic;

use common\models\Order;
use common\logic\JxcApi;
use frontend\modules\sales\models\Clue;
use yii\db\Exception;
use common\models\Intention;

class OrderLogic extends BaseLogic
{
    /**
     * 用户字段
     * @var array
     */
    public $arrOrderNames = [
        'car_type_id',//车型id
        'car_type_name',//车型名称
        'color_configure',//颜色/配置
        'deposit',//订金
        'buy_type',//购买方式
        'loan_period',//贷款期限
        'predict_car_delivery_time',//预计交车
        'delivery_price',//成交价格
        'discount_price',//车价优惠
        'give',//赠送
        'is_insurance',//是否本店投保0否 1是
        'insurance_time',//保险到期日
        'engine_code',//发动机号
        'frame_number',//车架号
        'car_number',//车牌号
        'is_add',//是否加装0否1是
        'add_content',//精品装饰
        'car_owner_name',//车主姓名
        'car_owner_phone'//车主电话

    ];

    /**
     * 交车保存订单
     *
     * @param $order
     * @return bool
     * @throws Exception
     */
    public  function orderSave($orderId)
    {
        try {
            $db = \Yii::$app->db;
            $transaction = $db->beginTransaction();
            $orderModel = Order::findOne(['order_id' => $orderId]);
            $orderModel->status = 6;
            $orderModel->car_delivery_time = time();//实际交车时间

            $clue = Clue::findOne(['id' => $orderModel->clue_id]);
            $clue->status = 3;
            $clue->intention_level_id = 8;
            $clue->intention_level_des = Intention::findOne(8)->name;

            if ($clue->save()) {
                if ($orderModel->save()) {
                    $transaction->commit();
                    return true;
                }
            }
            $transaction->rollBack();
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getInfo($order_id)
    {
        $order = Order::find()->select($this->arrOrderNames)->where("order_id='{$order_id}'")->one();
        return $order;
    }

    /**
     * 修改交车信息
     * @param $pData 数据集合
     * @return bool
     */
    public function updateSave($pData)
    {
        $order = Order::find()->where("order_id='{$pData['order_id']}'")->one();
        foreach ($pData as $k => $v) {
            if (in_array($k, $this->arrOrderNames)) {
                $order->$k = $v;
            }
        }
        $order->status = 6;
        if ($order->save()) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * 订车保存订单
     *
     * @param $order
     * @return bool
     * @throws Exception
     */
    public function orderTheCar($theCarOrder)
    {
        //没有本地订车 全部走H5下单
        return true;
//        if ($theCarOrder['order_type'] > 0) {
//            $orderModel = new Order();
//            $clue = Clue::find()->select('customer_id,shop_id')->where(['id'=>$theCarOrder['clue_id']])->asArray()->one();
//
//            $orderModel->status = 1;
//            $orderModel->clue_id = $theCarOrder['clue_id'];
//            $orderModel->customer_id = $clue['customer_id'];
//            $orderModel->shop_id = $clue['shop_id'];
//            $orderModel->order_id = date('YmdHis') . rand(100,999) . rand(100, 999);
//            $orderModel->create_time = time();
//            $orderModel->salesman_id = \Yii::$app->user->identity->getId();
//            $orderModel->talk_id = 1;
//            $orderModel->order_type = $theCarOrder['order_type'];
//            foreach ($theCarOrder as $k => $v){
//
//                if (in_array($k,$this->arrOrderNames)){
//                    $orderModel->$k = $v;
//                }
//            }
//            if($orderModel->save()){
//                $strFile = \Yii::getAlias('@frontend/runtime/logs/talk_dingche_.log');
//                $strLog =   "[" . date('Y-m-d H:i:s') . "] : " . json_encode($theCarOrder,JSON_UNESCAPED_UNICODE) . "\n";
//                file_put_contents($strFile, $strLog, FILE_APPEND);
//                return true;
//            }else{
//
//                throw new Exception("订单保存失败", $theCarOrder->errors);
//            }
//        }
    }


}
