<?php

namespace frontend\modules\sales\controllers;

use Yii;
use common\logic\JxcApi;
use common\models\Clue;
use frontend\modules\sales\logic\OrderLogic;
use common\models\PutTheCar;

/**
 * 今日待处理订单控制器
 * Class TaskController
 * @package frontend\modules\v1\controllers
 */
class PenDingOrderController extends AuthController
{

    /**
     * $status
     * 提交订单:105
     * 车辆到店:110
     * 确认交车:125
     */


    /**
     * 处理订单列表
     */
    public function actionSubOrder()
    {
        $user = Yii::$app->user->identity;
        $pData = $this->getPData();
        if (!isset($pData['currentPage']) || !isset($pData['status']) || !isset($pData['perPage'])) {
            return $this->paramError();
        }

        $pageNo = $pData['currentPage'];//页码
        $pageSize = $pData['perPage'];//每页显示数量
        $sellerId = $user->getId();//顾问id
        $status = $pData['status'];//状态

        $jxc = new JxcApi();
        $sign = md5($pageNo . $pageSize . $sellerId . $status . '_tk' . $jxc->b_token);//签名
        $url = $jxc->url . 'api/sale/list';
        $arr = [
            'pageNo' => $pageNo,
            'pageSize' => $pageSize,
            'sellerId' => $sellerId,
            'status' => $status,
            'sign' => $sign
        ];
        $res = json_decode(JxcApi::send_post($url, $arr));
        $pagination = [];

        if ($res->statusCode == 1) {
            $this->writeErrorLog('获取今日待处理订单列表ok=>' . json_encode($res, 320));
            //分页
            $pagination = [
                'totalCount' => intval($res->content->totalCount),
                'pageCount' => intval(ceil($res->content->totalCount / $res->content->pageSize)),
                'currentPage' => intval($res->content->pageNo),
                'perPage' => intval($res->content->pageSize),
            ];

            $list = $res->content->list;

            $newArr = [];
            $order_id = null;
            foreach ($list as $k => $v) {
                //拼接订单号
                $order_id .= $v->clueNo . ',';
            }

            $order_id = rtrim($order_id, ',');
            if (!empty($order_id)) {

                //查找客户信息
                $clue = Clue::find()->select('c.id,c.customer_name,c.customer_phone,c.intention_level_des,c.clue_source,o.order_id')->from('crm_clue as c')
                    ->join('inner join', 'crm_order as o', 'o.clue_id = c.id')
                    ->where("o.order_id in ({$order_id})")->asArray()->all();

                foreach ($list as $k => $v) {

                    foreach ($clue as $val) {
                        if ($v->clueNo == $val['order_id']) {
                            $newArr[$k]['customer_name'] = $val['customer_name'];//客户名
                            $newArr[$k]['customer_phone'] = $val['customer_phone'];//客户手机号
                            $newArr[$k]['clue_id'] = $val['id'];//线索id
                            $newArr[$k]['intention_level_des'] = $val['intention_level_des'];//意向等级
                            $newArr[$k]['clue_source'] = $val['clue_source'];//信息来源
                            break;
                        }
                    }
                    $newArr[$k]['seriesName'] = $v->seriesName;//车系
                    $newArr[$k]['modelName'] = $v->modelName;//车型
                    $newArr[$k]['createTime'] = $v->createTime;//提交时间
                    $newArr[$k]['totalPrice'] = $v->totalPrice;//订单金额
                    $newArr[$k]['cusName'] = $v->cusName;//车主姓名
                    $newArr[$k]['cusMobile'] = $v->cusMobile;//车主手机
                    $newArr[$k]['pay_type'] = $v->payType;//1全款 2贷款
                    $newArr[$k]['order_id'] = $v->clueNo;//crm订单号

                }
            }

            return [
                'models' => $newArr,
                'pages' => $pagination,
            ];
        } else {
            $this->writeErrorLog($res->content);
            die(json_encode(['code' => -1002, 'message' => '进销存error:' . $res->content]));
        }
    }

    /**
     * 处理订单列表详情
     */
    public function actionInfo()
    {
        $get = Yii::$app->request->post();

        $jxc = new JxcApi();
        $sign = md5($get['order_id'] . '_tk' . $jxc->b_token);//签名
        $url = $jxc->url . 'api/sale/detailByClueNo';
        $arr = [
            'clueNo' => $get['order_id'],
            'sign' => $sign
        ];
        $res = json_decode(JxcApi::send_post($url, $arr));

        if ($res->statusCode == 1) {
            $res->content->url = $jxc->url;
            $res->content->sign = $sign;
            $res->content->order_id = $get['order_id'];
            $res->content->urlFile = $jxc->url;
            //验证当前订单是否是提车门店订单
            $putTheCar = PutTheCar::find()->select('old_salesman_id,new_salesman_id,old_shop_name,new_shop_name')
                ->where(
                    [
                        'order_id' => $get['order_id'],
                        'status' => 1
                    ]
                )
                ->one();

            $user = Yii::$app->user->identity;
            if (empty($putTheCar)) {
                //没有查到信息是自己的门店提车的订单
                $isCheck = 1;
            } else {
                $userId = $user->getId();
                //是自己的客户 并且 订车门店等于提车门店 为自己的单子 可以操作
                if ($userId == $putTheCar->old_salesman_id && $putTheCar->old_shop_name == $putTheCar->new_shop_name) {
                    $isCheck = 1;
                } else if ($userId == $putTheCar->old_salesman_id && $putTheCar->old_shop_name != $putTheCar->new_shop_name) {
                    //是自己的客户 但是不是本门店提车  不可以操作
                    $isCheck = 0;
                } else {
                    //分配给其他门店顾问 可以操作
                    $isCheck = 1;
                }
            }

            $res->content->isCheck = $isCheck;

            die(json_encode(['code' => 1, 'res' => $res->content]));

        } else {
            die(json_encode(['code' => 0, 'message' => '进销存error:' . $res->content]));
        }
    }

    public function actionFileSave()
    {
        $this->dump($_FILES);
    }

    /**
     * 处理订单详情按钮
     */
    public function actionApply()
    {
        $post = Yii::$app->request->post();
        $user = Yii::$app->user->identity;
        $jxc = new JxcApi();
        $sign = md5($post['order_id'] . $user->getId() . '_tk' . $jxc->b_token);//签名

        $fileList = [];
        if (!empty($post['arr'])) {
            foreach ($post['arr'] as $k => $v) {
                array_push($fileList, $v);
            }
        } else {
            die(json_encode(['code' => 0, 'message' => '请选择图片！']));
        }

        $arr = [
            'clueNo' => $post['order_id'],
            'sellerId' => $user->getId(),
            'fileList' => $fileList,
            'sign' => $sign
        ];
        $data_string = json_encode($arr);
        $des = null;
        if ($post['type'] == 1) {//提交采购

            $url = $jxc->url . 'api/sale/purchaseReq';
            $des = 'ERP提交申请';

        } else if ($post['type'] == 2) {//车辆到店

            $url = $jxc->url . 'api/sale/arrival';
            $des = 'ERP确认到店';

        } else if ($post['type'] == 3) {//确认交车

            $orderLog = new OrderLogic();
            if (!$orderLog->orderSave($post['order_id'])) {
                die(json_encode(['code' => 0, 'message' => '交车提交失败']));
            }
            $url = $jxc->url . 'api/sale/deliver';
            $des = 'ERP确认交车';
        }

        $this->writeErrorLog($des . '->' . json_encode($arr, 320));
        $res = $this->curlJson($url, $data_string);
        $this->writeErrorLog($des . '返回结果->' . json_encode($res, 320));

        if ($res->statusCode == 1) {
            die(json_encode(['code' => 1, 'message' => '操作成功！']));
        } else {
            die(json_encode(['code' => 0, 'message' => 'ERP错误:' . $res->ext]));
        }
    }

    /**
     * post请求 参数json格式
     */
    public function curlJson($url, $data_string)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );
        return json_decode(curl_exec($ch));
    }

    /**
     * 车辆状态
     */
    public function actionStatus()
    {

        $pData = $this->getPData();

        $jxc = new JxcApi();
        $sign = md5($pData['order_id'] . '_tk' . $jxc->b_token);//签名
        $url = $jxc->url . 'api/sale/statusByClueNo';
        $arr = [
            'clueNo' => $pData['order_id'],
            'sign' => $sign
        ];
        $res = json_decode(JxcApi::send_post($url, $arr));

        if ($res->statusCode == 1) {

            $this->writeErrorLog('获取车辆状态ok=>' . json_encode($res));

            $newArr = [];
            $time = null;

            foreach ($res->content as $k => $v) {
                $newArr[$k]['adminName'] = $v->adminName;
                $newArr[$k]['statusMsg'] = $v->statusMsg;
                $newArr[$k]['statusTime'] = $v->statusTime;
                $newArr[$k]['status'] = $v->status;
                if ($k == 0) {
                    $newArr[$k]['time'] = '';
                } else {
                    $time = $res->content[$k - 1]->statusTime;
                    //求时间差  java的时间戳是13为的 截取掉最后3位
                    $cle = substr($v->statusTime, 0, -3) - substr($time, 0, -3);
                    $d = floor($cle / 3600 / 24);
                    $h = floor(($cle % (3600 * 24)) / 3600);  //%取余
                    $m = floor(($cle % (3600 * 24)) % 3600 / 60);

                    $newArr[$k]['time'] = "{$d}天{$h}小时{$m}分";
                }
            }

            $count = count($newArr);
            $arrNew = [];
            $k = 0;

            for ($i = $count; $i > 0; $i--) {
                $arrNew[$k] = $newArr[$i - 1];
                $k++;
            }
            return [
                'models' => $arrNew,
            ];

        } else {
            die(json_encode(['code' => 0, 'message' => '进销存error:' . $res->content]));
        }
    }

}