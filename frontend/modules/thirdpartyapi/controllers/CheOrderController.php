<?php
/**
* CRM下单跳转H5
 */

namespace frontend\modules\thirdpartyapi\controllers;
use common\models\Clue;
use common\models\Customer;
use common\models\Order;
use Yii;
use yii\rest\Controller;


class CheOrderController extends Controller
{

    /**
     * 订车
     * @return array
     */
    public function actionTheCar()
    {

        $data    =  \Yii::$app->request->post();
        if (empty($data['clue_id']) || empty($data['salesman_id'])) {
            $this->echoData(0,'参数错误');
        }
        $clue_id = $data['clue_id'];
        $salesman_id = $data['salesman_id'];

        //1.检测当前订单是否存在
        $order = Order::find()->where("clue_id = {$clue_id} and (status != 4 and status != 6 )")->one();

        //获取意向信息
        $clue = Clue::find()->select('shop_id,salesman_id,customer_id')->where("id = {$clue_id}")->asArray()->one();

        //获取会员id
        $customer = Customer::find()->select('member_id')->where("id={$clue['customer_id']}")->asArray()->one();


        $qrUrl = \Yii::$app->params['che_com']['jumpurl'];
        if (empty($customer['member_id']))
            $this->echoData(0, '没有找到会员id', $qrUrl);

        $channel = 0;
        $shopArr = [];
        if (YII_ENV == 'dev') {//开发
            $shopArr = [229];
        }else if (YII_ENV == 'test') {//测试
            $shopArr = [229,156];
        }else if (YII_ENV == 'prod') {//正式
            $shopArr = [156];
        }
        if (in_array($clue['shop_id'],$shopArr)) {//天猫店
            $channel = 1;
        }

        $qrUrl .= '?uid=' . $customer['member_id'];//会员id
        $qrUrl .= '&storeId=' . $clue['shop_id'];//门店id
        $qrUrl .= '&subChannel=' . $salesman_id;//销售id
        $qrUrl .= '&clueId=' . $clue_id;//线索id
        $qrUrl .= '&channel='.$channel; //0crm跳转车城订单 1天猫订单
        $qrUrl .= '&apiAddr=' . \Yii::$app->params['apiAddrUrl']['url'];;//本地接收的接口地址

        if (!empty($order)) {
            $this->echoData(0, '已经下过单了,不能重复下单', $qrUrl);
            $qrUrl .= '&clueNo=' . $order->order_id;//订单号
        } else {
            $orderId = Order::createOrderId();
            $qrUrl .= '&clueNo=' . $orderId;//订单号
        }

        $this->writeErrorLog($qrUrl);
        //跳转车城地址
        $this->echoData(1, '数据正常', $qrUrl);

    }

    //记录跳转H5订车页面日志
    public function writeErrorLog($qrUrl) {
        $rootPath = \Yii::getAlias('@frontend/runtime/logs/');
        file_put_contents($rootPath.'t_che.log',date("Y-m-d H:i:s")."\t".
            'url=>'. \Yii::$app->request->url."\t".
            'mes=>'.$qrUrl."\n"
            , FILE_APPEND);
    }

    /**
     * @param $data
     * @param $car_type_name
     * @param $qrUrl
     * @return bool|Order
     * @throws \Exception
     */
    public function insertOrder($data, $orderId)
    {
        $db          = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            //更新线索状态
            $clue = Clue::findOne($data['clue_id']);
            \Yii::$app->cache->set('intention_des' . $clue->id, $clue->intention_level_des);
            $clue->status = 2;
            if ($clue->save()) {
                //新增订单
                $order                = new Order();
                $order->salesman_id   = $data['salesman_id'];
                $order->order_id      = $orderId;
                $order->create_time   = time();
                $order->customer_id   = $clue['customer_id'];
                $order->shop_id       = $clue['shop_id'];
                $order->clue_id       = $data['clue_id'];
                $order->status        = 1;
                $order->talk_id       = 0;
                if ($order->save()) {
                    $transaction->commit();
                    return $order;
                }
                return false;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

    }

    private function echoData($code = 1, $msg = '', $data = null)
    {
        $outString = json_encode([
            "statusCode" => intval($code),
            "content" => strval($msg),
            "data" => $data
        ]);
        // 输出结果
        header("Content-type: application/json");
        die($outString);
    }

    private function dump($data)
    {
        echo "<pre>";
        print_r($data);
        exit;
    }
}