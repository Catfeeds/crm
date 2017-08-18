<?php
/**
 * 订单
 * Created by PhpStorm.
 * User: yukai
 * Date: 2017/3/17
 */

namespace frontend\modules\sales\controllers;

use Yii;
use frontend\modules\sales\logic\OrderLogic;
use common\models\Order;
use common\logic\JxcApi;
use common\common\PublicMethod;
class OrderController extends AuthController
{

    /**
     * 交车订单信息
     */
    public function actionGetInfo() {


        $pData = $this->getPData();

        if (!isset($pData['order_id'])) {
            $this->paramError();exit;
        }

        //验证进销存是否可以交车
        $jxc  = new JxcApi();
        $sign = md5($pData['order_id']."_tk".$jxc->z_token);
        $url = $jxc->url.'api/sale/check';
        $url .= '?clueNo='.$pData['order_id'].'&sign='.$sign;
        $res = json_decode(file_get_contents($url));

        if ($res->statusCode == 0) {
            Yii::$app->params['code'] = '-1003';
            Yii::$app->params['message'] = '进销存错误=》'.$res->content;
            return [];

        }

        //拉去ERP订单信息 补全crm订单数据
        $url = $jxc->url . 'api/sale/detailByClueNo';
        $arrPost = [
            'clueNo' => $pData['order_id'],
            'sign' => md5($pData['order_id'] . "_tk" . $jxc->z_token),
        ];
        $jsonData = json_decode(PublicMethod::http_post($url, $arrPost), true);
        $orderModel = Order::find()->where("order_id='{$pData['order_id']}'")->one();
        $car = null;
        if($jsonData['statusCode'] == 1)
        {
            $orderModel->deposit        = $jsonData['content']['downpayment'];//订金
            $orderModel->frame_number   = $jsonData['content']['frameNo'];//车架号
            $orderModel->engine_code    = $jsonData['content']['engineNo'];//发动机编号
            $orderModel->buy_type       = $jsonData['content']['payType'];//payType 1全款 2贷款 CRM和ERP中意义一致
            $car = $jsonData['content']['brandName'].$jsonData['content']['seriesName'];
            $orderModel->save();
        }
        
        //检测订单号是否存在
        $count = Order::find()->select('id')->where(['order_id'=>$pData['order_id']])->count();
        if($count < 1) {

            Yii::$app->params['code'] = '-1001';
            Yii::$app->params['message'] = '订单号不存在！';
            return [];
        }
        $orderLog = new OrderLogic();
        $order = $orderLog->getInfo($pData['order_id']);
        $order->car_type_name = $car.$order->car_type_name;
        return $order;
    }

    /**
     *修改交车信息   暂时废弃
     */
    public function actionUpdateSave()
    {
        $pData = $this->getPData();
        if (!isset($pData['order_id'])) {
            $this->paramError();
        }

       if (empty($pData['color_configure']) ||
           empty($pData['buy_type']) ||
           empty($pData['delivery_price']) ||
           empty($pData['discount_price'])||
           empty($pData['car_owner_name'])||
           empty($pData['car_owner_phone'])){

           Yii::$app->params['code'] = '-1000';
           Yii::$app->params['message'] = '请填写不可为空值！';
           return [];

       }

       //检测订单号是否存在
       $count = Order::find()->select('id')->where(['order_id'=>$pData['order_id']])->count();
        if($count < 1) {

            Yii::$app->params['code'] = '-1001';
            Yii::$app->params['message'] = '订单号不存在！';
            return [];
        }

        $orderLog = new OrderLogic();
        if($orderLog->updateSave($pData)){
            Yii::$app->params['code'] = '200';
            Yii::$app->params['message'] = '保存成功！';
            return [];
        }else{
            Yii::$app->params['code'] = '300';
            Yii::$app->params['message'] = '保存失败！';
            return [];
        }
    }

    /**
     * 功能：CRM在车城下单，销售助手返回商谈页面检测订单是否生成
     *
     */
    public function actionCheckOrder() {
        $pData = $this->getPData();
        if (empty($pData['clue_id'])) {
            $this->paramError();
        }
        $order = Order::find()->where(['clue_id'=>$pData['clue_id']])->orderBy('id desc')->asArray()->one();
        if (empty($order)) {
            Yii::$app->params['code'] = '300';
            Yii::$app->params['message'] = '无订单！';
            return [];
        }
        return $order;
    }


}