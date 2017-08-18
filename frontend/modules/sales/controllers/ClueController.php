<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/3/6
 * Time: 16:35
 */

namespace frontend\modules\sales\controllers;

use common\logic\ClaimClueLogic;
use common\logic\DataDictionary;
use common\models\Customer;
use common\models\FailTags;
use frontend\modules\sales\logic\GongHaiGic;
use frontend\modules\sales\models\Clue;
use common\models\Intention;
use frontend\modules\sales\logic\ClueLogic;
use frontend\modules\sales\logic\CustomerLogic;
use common\models\Order;
use frontend\modules\sales\models\Task;
use Yii;
use common\logic\CarBrandAndType;
use yii\helpers\ArrayHelper;
use common\logic\JxcApi;
use common\models\ClueWuxiao;
use common\logic\ClueValidate;
use common\models\GongHai;
use common\models\PutTheCar;

/**
 * 任务控制器
 * Class TaskController
 * @package frontend\modules\v1\controllers
 */
class ClueController extends AuthController
{
    /**
     * 线索首页
     */
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;
        $clue_num = Clue::find()->where([
            'salesman_id' => $user->getId(),
            'shop_id' => $user->shop_id,
            'status' => 0,
            'is_fail' => 0
        ])->count();
        $date = date('Y-m-d');

        //任务表crm_task中获取当天未完成的任务数（电话回访 到店接待 上门拜访）
        $arrTaskWhere = [
            'and',
            ['=', 'salesman_id', $user->getId()],
            ['=', 'task_date', $date],
            ['=', 'shop_id', $user->shop_id],
            ['<>', 'is_cancel', 1],
            ['<>', 'is_finish', 2]
        ];
        $arrTaskData = Task::find()->select(['task_type', 'count(*) as num'])->where($arrTaskWhere)->groupBy('task_type')->asArray()->all();
        $data = ArrayHelper::map($arrTaskData, 'task_type', 'num');

        /**
         * edited by liujx 2017-08-07 电话任务记录数存在问题修改 start:
         * 具体问题为： 查询的时候没有过滤到没有线索的电话任务
         */
        $intNumber = Task::find()->from(Task::tableName().' t')
            ->select(['task_type', 'count(*) as num'])
            ->innerJoin(Clue::tableName().' AS c', 't.clue_id = c.id')
            ->where([
                'and',
                ['=', 't.salesman_id', $user->getId()],
                ['=', 't.task_date', $date],
                ['=', 't.shop_id', $user->shop_id],
                ['<>', 't.is_cancel', 1],
                ['<>', 't.is_finish', 2],
                ['=', 't.task_type', 1],
            ])
            ->count();
        // end;


        $returnArr = [
            'clue_num' => $clue_num,
            'phone_num' => $intNumber,
            'shop_num' => isset($data['2']) ? $data['2'] : 0,
            'home_num' => isset($data['3']) ? $data['3'] : 0,
        ];
        $returnArr['sub_num'] = 0;
        $returnArr['car_num'] = 0;
        $returnArr['yes_car_num'] = 0;

        //调用进销存start
        $jxc = new JxcApi();
        $sign = md5($user->getId() . "105110125_tk" . $jxc->b_token);//签名
        $url = $jxc->url . 'api/sale/count';

        $arr = [
            'sellerId' => $user->getId(),
            'status' => '105,110,125',
            'sign' => $sign
        ];

        $res = json_decode(JxcApi::send_post($url, $arr));

        if (!empty($res)) {
            if ($res->statusCode == 1) {
                $this->writeErrorLog('调取待处理订单ok=>' . json_encode($res));
                foreach ($res->content as $k => $v) {
                    if ($k == 105) {
                        $returnArr['sub_num'] = $v;//订单确认
                    } elseif ($k == 110) {
                        $returnArr['car_num'] = $v;//车辆到店
                    } elseif ($k == 125) {
                        $returnArr['yes_car_num'] = $v;//确认交车
                    }
                }

            } else {
                $this->writeErrorLog($res->content);
            }
        }
        //调用进销存end

        //查询门店未分配线索数量
        $shop_id = $user->org_id;
        $claim_clue_ligic = new ClaimClueLogic();
        $list = $claim_clue_ligic->getClaimClue($shop_id);

        if (is_array($list)) {
            $returnArr['unassign_num'] = count($list);
        } else {
            $returnArr['unassign_num'] = 0;
        }

        $returnArr['putTheCar_num'] = PutTheCar::getPutTheCarCount($user->shop_id);//接待提车客户数
        //获取公海数量
        $returnArr['gonghai_num'] = (int)GongHai::find()->count();
        return $returnArr;
    }

    /**
     * 添加线索
     * @return array
     */
    public function actionAdd()
    {
        $rst = ClueLogic::instance()->add($this->getPData());
        if (!$rst) {
            Yii::$app->params['code'] = ClueLogic::instance()->getErrorCode();
            Yii::$app->params['message'] = ClueLogic::instance()->getError();
            return [];
        }
        Yii::$app->params['code'] = 200;
        Yii::$app->params['message'] = '添加成功';
        return [];
    }

    /**
     * 查看线索 和 线索编辑页面用
     *
     * @return array
     */
    public function actionView()
    {
        $data = $this->getPData();
        if (!isset($data['clue_id']) || !$data['clue_id']) {
            return $this->paramError();
        }

        $user = Yii::$app->user->identity;

        $clue = ClueLogic::instance()->getClueById($data['clue_id'], $user);
        if (!empty($data['order_id'])) {//只有订车跟交车才会有订单order_id
            $clue['puTheCarSalesmanName'] = $clue['salesman_name'];
            $clue['puTheCarShopName'] = $clue['shopName'];
            $clue['isDistribution'] = 0;
            //获取提车门店与顾问
            $putTheCar = PutTheCar::find()->select('old_salesman_id,new_shop_name,new_salesman_name')
                ->where(
                    [
                        'order_id' => $data['order_id'],
                    ]
                )
                ->asArray()
                ->one();

            if (!empty($putTheCar)) {
                $clue['puTheCarSalesmanName'] = $putTheCar['new_salesman_name'];
                $clue['puTheCarShopName'] = $putTheCar['new_shop_name'];
                if ($putTheCar['old_salesman_id'] == $user->getId()) {
                    $clue['isDistribution'] = 1;
                }
            }
        }


        $intention_level_des = null;
        if (!empty($clue['intention_level_id']))
            $intention_level_des = Intention::find()->select('name')->where(['id' => $clue['intention_level_id']])->one()['name'];
        $clue['intention_level_des'] = empty($intention_level_des) ? '' : $intention_level_des;

        if (!$clue) {
            Yii::$app->params['code'] = ClueLogic::instance()->getErrorCode();
            Yii::$app->params['message'] = ClueLogic::instance()->getError();
            return [];
        }
        return $clue;
    }

    /**
     * 提交更新线索信息
     */
    public function actionUpdate()
    {
        $data = $this->getPData();

        if (!isset($data['clue_id']) || !$data['clue_id']) {
            return $this->paramError();
        }

        $rst = ClueLogic::instance()->update($data);
        if (!$rst) {
            Yii::$app->params['code'] = ClueLogic::instance()->getErrorCode();
            Yii::$app->params['message'] = ClueLogic::instance()->getError();
            return [];
        }
        return true;
    }

    /**
     * 线索列表
     */
    public function actionList()
    {
        //获取当前用户门店id
        $user = Yii::$app->user->identity;
        $shop_id = $user->org_id;
        $logic = new ClaimClueLogic();
        //退回超时30分钟的线索信息
        $logic->getClaimClue($shop_id);
        return ClueLogic::instance()->getClueList($this->getPData());
    }

    /**
     * 战败
     *
     * @return array | boolean
     */
    public function actionIsFailed()
    {
        $data = $this->getPData();
        $id = isset($data['clue_id']) ? $data['clue_id'] : 0;
        $failTags = isset($data['fail_tags']) ? $data['fail_tags'] : 0;

        if (!$id || !$failTags) {
            return $this->paramError();
        }

        //获取当前用户门店id
        $user = Yii::$app->user->identity;
        $shop_id = $user->org_id;
        $logic = new ClaimClueLogic();
        //退回超时30分钟的线索信息
        if (!$logic->checkHandleClue($id, $user->id, $shop_id)) {
            Yii::$app->params['code'] = 400;
            Yii::$app->params['message'] = '线索超时未跟进，已回到门店线索列表！';
            return [];
        }

        $clue = Clue::findOne($id);
        if (empty($clue)) {
            Yii::$app->params['code'] = 400;
            Yii::$app->params['message'] = '线索不存在';
            return [];
        }
        if ($clue->salesman_id != Yii::$app->user->identity->getId()) {
            Yii::$app->params['code'] = 400;
            Yii::$app->params['message'] = '线索不存在！';
            return [];
        }
        $clue->fail_tags = (string)$failTags;
        $clue->fail_reason = FailTags::findOne($failTags)->name;
        $clue->last_fail_time = $_SERVER['REQUEST_TIME'];
        $clue->is_fail = 1;
        $clue->intention_level_id = 7; //战败标签
        $clue->intention_level_des = Intention::findOne(7)->name;

        //逾期操作
        $clueLog = new ClueLogic();
        $clueLog->updateYuQi($id);
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {

            /**
             * edited by liujx 2017-7-04 指定设置无效线索原因投入公海 start:
             *
             * 10 区域不符合、18车型未上市、9购车时间6个月以上
             */
            if (in_array($failTags, Yii::$app->params['arrInvalidReason'])) {
                GongHaiGic::addGongHai($clue, 6);
            }

            // end;

            if ($clue->save()) {
                // 增加无效线索表
                $wuxiao = new ClueWuxiao();
                foreach ($clue as $k => $v) {
                    $wuxiao->$k = $v;
                }
                if ($wuxiao->save()) {

                    //移除线索表 无效线索信息
                    if ($clue->delete($id)) {
                        $transaction->commit();
                        return true;
                    }
                }

            }
            $transaction->rollBack();
            Yii::$app->params['code'] = 400;
            Yii::$app->params['message'] = '提交失败';
            return [];

        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

    }

    /**
     * 转换为意向客户
     *
     * @return array|bool
     */
    public function actionToIntent()
    {
        $pData = $this->getPData();

        $customer = $pData['customer'];
        $talk = isset($pData['talk']) ? $pData['talk'] : null;
        $clueLog = new ClueLogic();

        if (!isset($pData['customer'])) {
            return $this->paramError();
        }

        // edited by liujx 2017-06-27 start:
        // 不存在线索ID
        if (empty($pData['customer']['clue_id'])) {
            return $this->paramError();
        }

        // 不存在线索信息
        $clue = Clue::findOne((int)$pData['customer']['clue_id']);
        if (!$clue) {
            \Yii::$app->params['code'] = 400;
            \Yii::$app->params['message'] = '不存在线索信息';
            return false;
        }

        // 如果该客户之前存在没有战败线索信息，并且线索状态为意向或者订车 不允许转化现在的线索
        $isExists = ClueValidate::validateExists([
            'and',
            ['=', 'customer_id', $clue->customer_id],
            ['!=', 'id', $clue->id],
            ['=', 'is_fail', 0],
            ['in', 'status', [1, 2]]
        ]);

        // 存在线索
        if ($isExists) {
            \Yii::$app->params['code'] = 400;
            \Yii::$app->params['message'] = '该客户已经被其他顾问跟进,无法转换，请至为无效';
            return false;
        }

        // end;

        //包含交谈记录的
        if (!empty($customer) && !empty($talk)) {
            if (!CustomerLogic::instance()->ClueToCustomerTalk($customer, $talk)) {
                Yii::$app->params['code'] = CustomerLogic::instance()->errorCode;
                Yii::$app->params['message'] = CustomerLogic::instance()->getError();
                return false;
            }
            //逾期操作
            $clueLog->updateYuQi($customer['clue_id']);
            return true;
        }
        $user = \Yii::$app->user->identity;
        //不包含交谈记录
        if (ClueLogic::instance()->toIntent($pData, $user)) {
            //逾期操作
            $clueLog->updateYuQi($customer['clue_id']);
            return true;
        }


        Yii::$app->params['code'] = ClueLogic::instance()->getErrorCode();
        Yii::$app->params['message'] = ClueLogic::instance()->getError();
        return false;
    }

    /**
     * 详情
     * @return array
     */
    public function actionGetInfo()
    {

        $data = $this->getPData();

        $id = $data['customer_id'];
        if (empty($id)) {
            return $this->paramError();
        }

        $clue = new ClueLogic();
        return $clue->getInfo($id);

    }


    /**
     * 订车
     * @return array
     */
    public function actionTheCar()
    {

        $data = $this->getPData();
        $clue_id = $data['clue_id'];

        if (empty($clue_id)) {
            return $this->paramError();
        }

        //1.检测当前订单是否存在
        $order = Order::find()->where("clue_id = {$clue_id} and (status != 4 and status != 6 )")->one();

        //获取意向信息
        $clue = Clue::find()->select('shop_id,salesman_id,customer_id')->where("id = {$clue_id}")->asArray()->one();

        //获取会员id
        $customer = Customer::find()->select('member_id')->where("id={$clue['customer_id']}")->asArray()->one();

        $ischeck = true;

        $qrUrl = Yii::$app->params['che_com']['jumpurl'];

        $qrUrl .= '?uid=' . $customer['member_id'];//会员id
        $qrUrl .= '&storeId=' . $clue['shop_id'];//门店id
        $qrUrl .= '&subChannel=' . $clue['salesman_id'];//销售id

        if (!empty($order)) {
            $qrUrl .= '&clueNo=' . $order->order_id;//订单号
        } else {
            $orderId = $this->createOrderId();
            $qrUrl .= '&clueNo=' . $orderId;//订单号

            //没有数据新增数据
            if (!$order = $this->insertOrder($data, $orderId)) {
                $ischeck = false;
            }
        }

        if ($ischeck) {
            //跳转车城地址
            header('Location:' . $qrUrl);

        } else {
            \Yii::$app->params['code'] = '-1001';
            \Yii::$app->params['message'] = '操作失败！';
            return [];
        }


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
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            //更新线索状态
            $clue = Clue::findOne($data['clue_id']);
            Yii::$app->cache->set('intention_des' . $clue->id, $clue->intention_level_des);
            $clue->status = 2;
            if ($clue->save()) {
                //新增订单
                $user = Yii::$app->user->identity;
                $order = new Order();
                $order->salesman_id = $user->getId();
                $order->order_id = $orderId;
                $order->create_time = time();
                $order->customer_id = $clue['customer_id'];
                $order->shop_id = $clue['shop_id'];
                $order->clue_id = $data['clue_id'];
                $order->status = 1;
                $order->talk_id = 0;
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


    /**
     * 线索客户来电，增加联系次数
     * @return array
     */
    public function actionCall()
    {
        $pData = $this->getPData();
        if (!isset($pData['clue_id']) || !$pData['clue_id']) {
            return $this->paramError();
        }
        $clue = Clue::findOne($pData['clue_id']);

        //获取当前用户id
        $user = Yii::$app->user->identity;
        $shop_id = $user->org_id;
        $logic = new ClaimClueLogic();
        //退回超时30分钟的线索信息
        if (!$logic->checkHandleClue($pData['clue_id'], $user->id, $shop_id)) {
            \Yii::$app->params['code'] = 4010;
            \Yii::$app->params['message'] = '线索超时跟进，已回到门店线索列表！';
            return [];
        }

        //逾期操作
        $clueLog = new ClueLogic();
        $clueLog->updateYuQi($pData['clue_id']);

        if (!empty($clue)) {

            //保存电话录音
            if ($_FILES) {
                $data = [];
                foreach ($_FILES as $k => $file) {
                    $aacToMp3 = ($k === 'voices');
                    $files = $clueLog->saveFiles($file, $k, $aacToMp3);
                    $data[$k] = $files;
                }
                $talk = new \common\models\Talk();
                $talk->shop_id = $clue->shop_id;
                $talk->salesman_id = $clue->salesman_id;
                $talk->clue_id = $clue->id;
                $talk->talk_date = date('Y-m-d');
                $talk->castomer_id = $clue->customer_id;
                $talk->create_time = time();
                $talk->talk_type = 3;//去电
                if (isset($data['voices']) && isset($pData['voice_duration'])) {
                    $talk->start_time = (time() - intval($pData['voice_duration']));
                    $talk->end_time = time();
                    $talk->voices = $data['voices'];
                    $talk->voices_times = intval($pData['voice_duration']);
                }
                $talk->save();
            }

            $clue->view_times += 1;
            $clue->last_view_time = time();
            if ($clue->save()) {
                Yii::$app->params['message'] = '增加成功';
                return [];
            } else {
                Yii::$app->params['code'] = '4010';
                Yii::$app->params['message'] = '增加失败';
                return [];
            }
        } else {
            Yii::$app->params['code'] = '4010';
            Yii::$app->params['message'] = '未找到该线索';
            return [];
        }
    }


    /**
     * 门店未分配线索
     * @return array
     */
    public function actionUnassignList()
    {
        //获取当前用户门店id
        $user = Yii::$app->user->identity;
        $shop_id = $user->shop_id;

        $pData = $this->getPData();

        if (empty($pData['perPage']) || empty($pData['currentPage'])) {
            return $this->paramError();
        }

        $perPage = $pData['perPage'];
        $currentPage = $pData['currentPage'];

        $logic = new ClaimClueLogic();

        $list = $logic->getClaimClue($shop_id);

        $list_page_arr = array_chunk($list, $perPage);

        $list_page = empty($list_page_arr[$currentPage - 1]) ? [] : $list_page_arr[$currentPage - 1];

        $cb_logic = new CarBrandAndType();

        $clue_list = [];
        foreach ($list_page as $item) {
            $info = [];
            $info['clue_id'] = intval($item['clue_id']);
            $info['customer_name'] = strval($item['customer_name']);
            $info['customer_phone'] = strval($item['customer_phone']);
            $info['create_time'] = intval($item['create_time']);
            $car_brand_info = $cb_logic->getBrandAndFactoryInfoByTypeId($item['intention_id']);

            $intention_des = (isset($car_brand_info[$item['intention_id']]['brand_name']) ? $car_brand_info[$item['intention_id']]['brand_name'] . '-' : '') . $item['intention_des'];
            $info['intention_des'] = $intention_des;
            $info['des'] = strval($item['des']);
            $info['clue_source'] = intval($item['clue_source']);
            $info['clue_input_type'] = intval($item['clue_input_type']);
            $clue_list[] = $info;
        }

        $pages['totalCount'] = count($list);
        $pages['pageCount'] = count($list_page_arr);
        $pages['currentPage'] = $currentPage;
        $pages['perPage'] = $perPage;

        $data['models'] = $clue_list;
        $data['pages'] = $pages;

        return $data;
    }

    /**
     * 认领线索
     * @return array
     */
    public function actionClueClaim()
    {
        //获取当前用户id
        $user = Yii::$app->user->identity;
        $user_id = $user->id;
        $user_name = $user->name;

        $shop_id = $user->org_id;

        //接收参数
        $pData = $this->getPData();
        if (empty($pData['clue_id'])) {
            return $this->paramError();
        }
        $clue_id = $pData['clue_id'];

        //判断当前线索是否已被认领
        $logic = new ClaimClueLogic();
        $rtn = $logic->checkClaimClue($clue_id, $shop_id);

        if ($rtn) {  //已被认领
            \Yii::$app->params['code'] = 10001;
            \Yii::$app->params['message'] = '下手慢了，该条线索已经被其他顾问抢走了。';
            return [];
        }

        $clue = \common\models\Clue::findOne($clue_id);

        $clue->salesman_id = $user_id;
        $clue->salesman_name = $user_name;
        $clue->who_assign_name = '个人认领';
        $clue->who_assign_id = $user_id;
        $clue->assign_time = time();
        $clue->is_assign = 1;

        if ($clue->save()) {
            return [];
        } else {
            \Yii::$app->params['code'] = 400;
            \Yii::$app->params['message'] = '认领失败';
            return [];
        }
    }
}
