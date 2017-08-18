<?php
/**
 * 功    能：与车城线上对接的接口相关功能api控制器
 * 作    者：王雕
 * 修改日期：2017-3-21
 */

namespace frontend\modules\thirdpartyapi\controllers;

use common\fixtures\User;
use common\helpers\Helper;
use yii;
use yii\rest\Controller;
use common\models\Clue;
use common\models\Customer;
use common\models\Order;
use common\logic\NoticeTemplet;
use common\logic\JxcApi;
use common\common\PublicMethod;
use common\models\Intention;
use common\models\Talk;
use common\models\PutTheCar;

class CheController extends Controller
{

    private $arrStatus = [
        1 => '处理中',
        2 => '客户未支付',
        3 => '财务到账',
        4 => '失败',
        5 => '客户已支付',
        6 => '已交车'
    ];

    private $arrClom = [
        'color_configure',
        'deposit',
        'buy_type',
        'loan_period',
        'predict_car_delivery_time',
        'delivery_price',
        'discount_price',
        'give',
        'is_insurance',
        'insurance_time',
        'frame_number',
        'engine_code',
        'car_number',
        'is_add',
        'add_content',
        'car_owner_name',
        'car_owner_phone',
    ];

    /**
     * 功    能：构造函数,负责解析参数
     * 参    数：无
     * 返    回：无
     * 作    者：王雕
     * 修改日期：2017-3-21
     */
    public function __construct($id, $module, $config = array())
    {
        parent::__construct($id, $module, $config);
        $checkArray = Yii::$app->request->get();
    }

    /**
     * 功    能：订单状态更新接口 （接口）
     */
    public function actionUpdateOrder()
    {
        $jsonStr = Yii::$app->request->getRawBody();
        $obj = json_decode($jsonStr, true);

        if ($obj['command'] == 'SALE_STATUS_NOTICE') {//SALE_STATUS_NOTICE crm接口

            $jxc = new JxcApi();
            $token = $jxc->b_token;
            $sign = $obj['sign'];//保存签名
            unset($obj['sign']);//去掉对象签名
            ksort($obj);//通过key正序排序

            $str = null;
            foreach ($obj as $v) {
                $str .= $v;
            }
            $str .= '_tk' . $token;

//            if (md5($str) == $sign) {//签名验证  验签逻辑双方调试有问题，暂时不使用验签功能
            if (1) {
                // edited by liujx 2017-08-02 响应400 状态 处理方式 start :
                if ($obj['status'] == 400) {
                    // 记录日志返回
                    Helper::logs('thirdpartyapi/che/'.date('Ymd').'-update-order.log', [
                        'time' => date('Y-m-d H:i:s'),
                        'ip' => Helper::getIpAddress(),
                        'request' => $obj,
                        'result' => PutTheCar::updateMention($obj['clueNo'])
                    ]);

                    $this->echoData(1, 'success');
                }

                // end


                $this->writeErrorLog('推送成功' . $jsonStr);
                //检测订单信息
                $orderModel = Order::find()->where(['order_id' => $obj['clueNo']])->one();

                if (!empty($orderModel)) {
                    /* @var $orderModel \common\models\Order */
                    $orderModel->che_order_id = $obj['onlineSaleNo'];//车城订单号
                    $orderModel->last_pudate_time = time();//订单最后更新时间
                    $orderModel->car_type_name = $obj['modelName'];//库里面文字描述保存到车型，进销存提供的// $obj['seriesName'];//车系名称
                    $orderModel->car_type_id = $obj['seriesId'];//车系id
                    $orderModel->color_configure = $obj['outColor'];//颜色和配置
                    $orderModel->car_owner_name = $obj['cusName'];//车主姓名
                    $orderModel->car_owner_phone = $obj['cusMobile'];//车主电话
                    $orderModel->delivery_price = $obj['totalPrice'];//成交价格
                    $orderModel->fail_reason = $obj['failReason'];//失败原因
                    //最后一次通知补全订单信息（获取订单详情，通知接口中的字段不够全）
//                    if($obj['status'] == 110)
//                    {
                    $url = $jxc->url . 'api/sale/detailByClueNo';
                    $arrPost = [
                        'clueNo' => $orderModel->order_id,
                        'sign' => md5($orderModel->order_id . "_tk" . $jxc->z_token),
                    ];
                    $jsonData = json_decode(PublicMethod::http_post($url, $arrPost), true);
                    $this->writeErrorLog("\n拉取详情：\n" . $jsonStr . "\n");
                    if ($jsonData['statusCode'] == 1) {
                        $orderModel->deposit = $jsonData['content']['downpayment'];//订金
                        $orderModel->frame_number = $jsonData['content']['frameNo'];//车架号
                        $orderModel->engine_code = $jsonData['content']['engineNo'];//发动机编号
                        $orderModel->buy_type = $jsonData['content']['payType'];//payType 1全款 2贷款 CRM和ERP中意义一致
                    }
                    //    }

                    $db = Yii::$app->db;
                    $transaction = $db->beginTransaction();

                    try {

                        $orderModel->last_pudate_time = time();//订单最后更新时间

                        //查找顾问id
                        $clue = Clue::find()->select('salesman_id')->where("id={$orderModel['clue_id']}")->asArray()->one();

                        /**
                         * 900 > 订单失败
                         * 901 > 订单失败
                         * 200 > 客户已支付
                         * 300 > 财务到账
                         * 105 > 财务到账
                         * 130 > 已交车
                         * 400 > 采购申请完成
                         */
                        $noticeTemplet = new NoticeTemplet();

                        if ($obj['status'] == 105 || $obj['status'] == 300) {
                            if (empty($orderModel->cai_wu_dao_zhang_time)) {//如果电商已经通知了财务到账时间，erp通知不更改财务到账时间
                                $orderModel->cai_wu_dao_zhang_time = time();
                            }
                            $orderModel->status = 3;
                            $this->writeErrorLog('进入状态' . $obj['status'] . '验证');
                            $noticeTemplet->financialConfirmationOrderNotice($clue['salesman_id'], $orderModel['id']);
                        } else if ($obj['status'] == 900 || $obj['status'] == 901) {
                            $orderModel->status = 4;
                            $this->writeErrorLog('进入状态' . $obj['status'] . '验证');
                            $noticeTemplet->processingFailureNotice($clue['salesman_id'], $orderModel['id'],$obj['failReason']);
                        } else if ($obj['status'] == 200) {
                            $orderModel->status = 5;
                            $this->writeErrorLog('进入状态' . $obj['status'] . '验证');
                            $noticeTemplet->paidOrderNotice($clue['salesman_id'], $orderModel['id']);
                        } else if ($obj['status'] == 130) {
                            $orderModel->status = 6;
                            $this->writeErrorLog('进入状态' . $obj['status'] . '验证');
                            $orderModel->car_delivery_time = time();//实际交车时间
                            $noticeTemplet->invoicingDeliveryCarNotice($clue['salesman_id'], $orderModel['id']);
                        }


                        if ($orderModel->save()) {
                            $this->writeErrorLog('订单数据状态更新已成功！');
                            //900失败 - 修改客户变为意向客户 //130交车 -修改意向表变为交车
                            if ($obj['status'] == 900 || $obj['status'] == 901 || $obj['status'] == 130) {
                                $clueModel = \common\models\Clue::findOne(['id' => $orderModel->clue_id]);
                                $this->writeErrorLog('进入线索表操作！');
                                if ($clueModel) {

                                    $talk = new Talk();
                                    if ($obj['status'] == 900 || $obj['status'] == 901)//战败
                                    {
                                        //战败更新提车任务表
                                        PutTheCar::updateTheCar($orderModel->clue_id,3);

                                        // erp战败客户 修改线索状态为意向 等级变成5
                                        $clueModel->status = 1;
                                        $clueModel->intention_level_id = 5;
                                        $clueModel->intention_level_des = Intention::findOne(5)->name;

                                        //商谈类型 25 ERP终止合同-客户转为意向
                                        $talk->talk_type = 25;
                                        //增加add_infomation
                                        $endDate = date('Y-m-d H:i');

                                        $add_infomation = [
                                            '终止时间' => $endDate,
                                            '终止原因' => empty($obj['failReason']) ? '--' : $obj['failReason'],
                                            '意向等级' => Intention::findOne(5)->name
                                        ];
                                    } else {

                                        // 商谈类型 26 ERP确认交车
                                        $talk->talk_type = 26;
                                        $clueModel->intention_level_id = 8;
                                        $clueModel->intention_level_des = Intention::findOne(8)->name; //交车
                                        $clueModel->status = 3;
                                        $car = $obj['brandName'].$obj['seriesName'].$obj['modelName'];
                                        $add_infomation = [
                                            '交车时间' => date('Y-m-d H:i:s',$orderModel->car_delivery_time),
                                            '购车车型' => $car,
                                            '成交价格' => $obj['totalPrice'],
                                            '车架号' => $jsonData['content']['frameNo'],
                                            '意向等级' => $clueModel->intention_level_des
                                        ];

                                        //交车更新提车任务表
                                        PutTheCar::updateTheCar($orderModel->clue_id,2);

                                    }

                                    $talk->castomer_id = $clueModel->customer_id;
                                    $talk->clue_id = $clueModel->id;
                                    $talk->salesman_id = $clueModel->salesman_id;
                                    $talk->shop_id = $clueModel->shop_id;
                                    $talk->create_time = time();
                                    $talk->start_time = $talk->create_time;
                                    $talk->talk_date = date('Y-m-d');
                                    $talk->is_intention_change = 1;
                                    $talk->order_id = $obj['clueNo'];
                                    $talk->add_infomation = json_encode($add_infomation, 320);
                                    if(!$talk->save()){
                                        $this->writeErrorLog('增加商谈失败！'.$talk->errors);
                                    }
                                    if ($clueModel->save()) {
                                        $transaction->commit();
                                        $this->writeErrorLog('更新意向状态成功！');

                                    } else {
                                        $transaction->rollBack();
                                        $this->writeErrorLog('更新意向状态失败！');
                                        $this->echoData(0, '更新意向状态失败');
                                    }
                                }
                            } else {
                                $this->writeErrorLog('订单数据事务提交！');
                                $transaction->commit();
                            }

                            $this->echoData(1, '操作成功！');

                        } else {
                            $this->writeErrorLog('订单数据状态更新失败！');
                            $this->echoData(0, '订单数据状态更新失败');
                        }
                    } catch (\Exception $e) {
                        $this->writeErrorLog('catch异常！' . $e->getMessage());
                        $this->echoData(0, 'catch异常' . $e->getMessage());
                        $transaction->rollBack();
                    }
                } else {
                    $this->writeErrorLog('没有此订单！');
                    $this->echoData(0, '没有此订单');
                }

            } else {
                $this->writeErrorLog('进销存验证签名失败' . md5($str) . '<----->' . $sign);
                $this->echoData(0, '进销存验证签名失败');
            }

        } else {
            // $this->writeErrorLog('进销存'.$obj['command'].'错误');
            $this->echoData(0, $obj['command'] . '不符合crm接口');
        }
    }


    public function writeErrorLog($error)
    {
        $rootPath = \Yii::getAlias('@frontend/runtime/logs/');
        file_put_contents($rootPath . 'jxc.log', date("Y-m-d H:i:s") . "\t" .
            'url=>' . \Yii::$app->request->url . "\t" .
            'mes=>' . $error . "\n"
            , FILE_APPEND);

    }

    //第三方接口错误日志

    private function echoData($code = 1, $msg = '', $data = [])
    {
        $outString = json_encode([
            "statusCode" => intval($code),
            "content" => strval($msg),
        ]);
        // 输出结果
        header("Content-type: application/json");
        die($outString);
    }

    /**
     * 功    能：创建CRM中的订单号
     * 参    数：无
     * 返    回：       string      crm的订单号
     * 作    者：王雕
     * 修改日期：2017-3-21
     */
    private function createCrmOrderId()
    {
        return date('YmdHis') . rand(100, 999) . rand(100, 999);
    }

    private function dump($data)
    {
        echo "<pre>";
        print_r($data);
        exit;
    }
}
