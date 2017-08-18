<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/3/6
 * Time: 9:36
 */

namespace frontend\modules\sales\controllers;


use common\logic\CarBrandAndType;
use common\logic\ClueValidate;
use common\models\Intention;
use common\models\User;
use frontend\modules\sales\models\Clue;
use common\models\Order;
use common\models\UserHistoryClue;
use frontend\modules\sales\logic\BaseLogic;
use frontend\modules\sales\logic\ClueLogic;
use frontend\modules\sales\logic\CustomerLogic;
use frontend\modules\sales\logic\TalkLogic;
use yii\data\Pagination;
use common\logic\ClaimClueLogic;
use common\models\GongHai;
use common\models\PutTheCar;
use yii\helpers\ArrayHelper;

/**
 * 客户管理
 * Class CustomerController
 * @package frontend\modules\v1\controllers
 */
class CustomerController extends AuthController
{
    public $status = [
        'client_intent' => 1, //意向客户
        'client_order' => 2, //订单客户
        'client_deal' => 3,//成交 客户
        'client_fail' => 4, //失败客户
        'client_keep' => 5 //保有客户

    ];

    /**
     * 直接客户添加
     *
     * POST
     */
    public function actionAdd()
    {
        $pData = $this->getPData();
        if (!isset($pData['customer'])) {
            return $this->paramError();
        }
        $user = \Yii::$app->user->identity;
        if (!CustomerLogic::instance()->addCustomer($pData, $user)) {
            \Yii::$app->params['code'] = CustomerLogic::instance()->getErrorCode();
            \Yii::$app->params['message'] = CustomerLogic::instance()->getError();
            return [];
        }
        return true;
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     * 客户列表
     */
    public function actionIndex()
    {
        $data = $this->getPData();
        if (!isset($data['status'])) {
            return $this->paramError();
        }
        $status = $this->status[$data['status']];
        $user = \Yii::$app->user->identity;

        if (!$status) {
            return $this->paramError();
        }

        $query = Clue::find();


        $clueIds = null;
        $OldclueIds = null;

        if ($status > 1) {
            $putTheCar = new PutTheCar();
            $putTheCarStatus = 1;
            switch ($status) {
                case 3 :
                    $putTheCarStatus = 2;
                    break;
                case 4 :
                    $putTheCarStatus = 3;
                    break;
            }

            if ($status != 4){//战败信息列表 不显示分配给我的信息
                //获取提车分配给自己的线索信息
                $items = $putTheCar->getTheCar($user->getId(), $putTheCarStatus);
                if (!empty($items)) {
                    $clueIds = ArrayHelper::getColumn($items, 'clue_id');
                    $query->where(['in', 'id', $clueIds]);
                }
            }

            //验证我分配给其他门店提车信息
            $oldItems = $putTheCar->getOldTheCar($user->getId(), $putTheCarStatus);
            if (!empty($oldItems)) {
                $OldclueIds = ArrayHelper::getColumn($oldItems, 'clue_id');
            }
        }

        //战败客户
        if ($status == 4) {
            return CustomerLogic::instance()->getFailCustomer($user, $query, $clueIds, $OldclueIds);
        }
        if ($status == 3 || $status == 5) {
            return CustomerLogic::instance()->getDealCustomer($status, $user, $query, $clueIds, $OldclueIds);
        }


        $query->orwhere([
            'salesman_id' => $user->getId(),
            'shop_id' => $user->shop_id,
            'status' => $status,
            'is_fail' => 0
        ]);
        $query->orderBy([
            "is_star" => SORT_DESC,
            "create_time" => SORT_DESC
        ]);

        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination([
            'totalCount' => $totalCount,
            'defaultPageSize' => 1000,
            'pageSizeLimit' => [1, 1000]
        ]);

        $models = $query->all();
        $data = [];

        $carBandAndType = new CarBrandAndType();

        foreach ($models as $k => $model) {

            if ($status == 1) {
                $data[$k]['who_assign_name'] = intval($model->who_assign_name);
                $data[$k]['who_assign_id'] = intval($model->who_assign_id);
                $data[$k]['clue_source'] = intval($model->clue_source);
                $data[$k]['create_card_time'] = intval($model->create_card_time);
                $data[$k]['intention_level_id'] = intval($model->intention_level_id);
                $data[$k]['intention_level_des'] = strval($model->intention_level_des);
                $data[$k]['is_star'] = intval($model->is_star) ? true : false;
                $data[$k]['status'] = intval($model->status);
            }
            if ($status == 2) {
                $order = Order::find()->where([
                    'clue_id' => $model->id
                ])->andWhere([
                    'NOT IN', 'status', [4, 6]
                ])->one();

                if (empty($order)) {
                    continue;
                }
                $data[$k]['isDistribution'] = 0;
                $data[$k]['distributionDes'] = '';
                //验证分配给我的提车信息
                if (!empty($clueIds)) {
                    if (in_array($model->id, $clueIds)) {
                        $data[$k]['isDistribution'] = 1;
                        $data[$k]['distributionDes'] = '提车任务';
                    }
                }

                //验证我分配给其他门店提车信息
                if (!empty($OldclueIds)) {
                    if (in_array($model->id, $OldclueIds)) {
                        $data[$k]['isDistribution'] = 2;
                        $data[$k]['distributionDes'] = '异地提车';
                    }
                }

                /* @var $order \common\models\Order */
                $data[$k]['deposit'] = $order->deposit; //定金
                $data[$k]['ordain_models'] = strval($order->car_type_name); //预定车型

                // edited by liujx 2017-07-18 添加预定车型的品牌信息 start :
                $brand = $carBandAndType->getBrandByIntentionIdOne($order->car_type_id);
                if ($brand) {
                    $data[$k]['ordain_models'] = $brand['brand_name'] . ' ' . $data[$k]['ordain_models'];
                }
                // end;

                $data[$k]['ordain_date'] = $order->create_time; //订车时间
                $data[$k]['ordain_give_data'] = $order->predict_car_delivery_time; //预计交车时间
                $data[$k]['status'] = intval($model->status);
                if ($order->status > 2)
                    $data[$k]['is_payed'] = 1; //0 未支付 1 已支付
                else
                    $data[$k]['is_payed'] = 0; //0 未支付 1 已支付

                $data[$k]['order_status'] = $order->status;
                $data[$k]['order_id'] = $order->order_id;

                switch ($order->status) {
                    case 1:
                        $data[$k]['qr_url'] = $order->qr_url;
                        $data[$k]['pay_status'] = '处理中';
                        break;
                    case 2:
                        $data[$k]['pay_status'] = '客户未支付';
                        break;
                    case 3:
                        $data[$k]['pay_status'] = '财务到账';
                        break;
                    case 4:
                        $data[$k]['pay_status'] = '失败';
                        break;
                    case 5:
                        $data[$k]['pay_status'] = '客户已支付';
                        break;
                    default:
                        $data[$k]['pay_status'] = '已交车';
                        break;
                }
            }
            $data[$k]['clue_id'] = intval($model->id);
            $data[$k]['customer_name'] = strval($model->customer_name);
            $data[$k]['customer_phone'] = strval($model->customer_phone);
            $data[$k]['last_view_time'] = intval($model->last_view_time);
            $data[$k]['intention_id'] = intval($model->intention_id);
            $data[$k]['intention_des'] = strval($model->intention_des); //意向车型
            $data[$k]['des'] = strval($model->des);
        }
        sort($data);
        if ($totalCount > 0) {
            return [
                'models' => $data,
                'pages' => BaseLogic::instance()->pageFix($pagination),
            ];
        } else {
            return null;
        }
    }

    /**
     * 客户加星
     */
    public function actionAddStar()
    {
        $data = $this->getPData();
        if (!isset($data['clue_id'])) {
            return $this->paramError();
        }
        $clueId = $data['clue_id'];
        $clue = Clue::findOne($clueId);
        $user = \Yii::$app->user->identity;
        if ($clue->salesman_id !== $user->getId()) {
            \Yii::$app->params['code'] = 400;
            \Yii::$app->params['message'] = '无权限操作';
            return false;
        }
        if ($clue->is_star == 1) {
            $clue->is_star = 0;
        } else {
            $clue->is_star = 1;
        }
        if ($clue->save()) {
            return true;
        }
        return false;
    }

    /**
     * 战败激活
     *
     * @return array|bool
     */
    public function actionToActive()
    {
        $data = $this->getPData();
        if (!isset($data['clue_id']) || !$data['clue_id'] || !isset($data['intention_level_id'])
            || !$data['intention_level_id']
        ) {
            return $this->paramError();
        }
        $clueId = $data['clue_id'];
        $clue = Clue::findOne($clueId);
        $user = \Yii::$app->user->identity;

        if ($clue->salesman_id != $user->getId()) {
            \Yii::$app->params['code'] = 400;
            \Yii::$app->params['message'] = '无权限操作';
            return false;
        }
        if ($clue->is_fail == 0) {
            \Yii::$app->params['code'] = 400;
            \Yii::$app->params['message'] = '不是战败客户';
            return false;
        }

        /**
         * edited by liujx 2017-06-27 start:
         *
         * 如果该客户之前存在没有战败线索信息，并且线索状态为意向或者订车 不允许激活现在的线索
         */
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
            \Yii::$app->params['message'] = '该客户已经被其他顾问跟进,无法激活';
            return false;
        }

        // end;

        $intention_level_des = $clue->intention_level_des;//为改变之前的意向
        $clue->is_fail = 0;
        //战败激活标签
        $clue->clue_input_type = 9;
        //战败激活，变为意向客户
        $clue->status = 1;
        $clue->intention_level_id = $data['intention_level_id'];
        if ($data['intention_level_id']) {
            $clue->intention_level_des = Intention::findOne($clue->intention_level_id)->name;
        }
        if ($clue->save()) {
            //激活成功增加商谈记录
            TalkLogic::instance()->addNewTalk([
                'talk_type' => 20,
                'add_infomation' => json_encode([
                    '激活人' => '自己激活',
                    '意向等级' => $intention_level_des . '-->' . $clue->intention_level_des
                ]),
                'start_time' => time(),
                'end_time' => time()
            ], $clue, $user);
            return true;
        }
        return false;
    }


    /**
     * 手机号检测 同一个门店
     * @return bool | array
     */
    public function actionCheckPhone()
    {
        $pData = $this->getPData();
        if ($pData['type'] == 1) {//新增线索或者客户的时候 通过手机号检测用户信息
            return ClueLogic::instance()->clueCheckPhoneInfo($pData);
        } else if ($pData['type'] == 2) {//工作台 添加到店接待\上门  通过手机号检测用户信息
            return ClueLogic::instance()->clueCheckPhoneInfo1($pData);
        }
    }

    /**
     * 根据手机号得到线索
     *
     * @return array|bool
     */
    public function actionGetPhone()
    {
        $pData = $this->getPData();
        $clue = ClueLogic::instance()->getClueById($pData['phone'], \Yii::$app->user->identity, 'phone');
        if (!$clue) {
            \Yii::$app->params['code'] = 4002;
            \Yii::$app->params['message'] = '无线索';
            return [];
        }
        return $clue;
    }

    /**
     * 历史客户数
     */
    public function actionHistory()
    {
        $models = UserHistoryClue::find()->select([
            'a.name as customer_name',
            'from_unixtime(crm_user_history_clue.create_time,"%Y-%m-%d") as allot_date',
            'crm_user_history_clue.reason',
            'crm_user_history_clue.operator_name as who_assign_name'
        ])->innerJoin(
            'crm_customer as a', 'crm_user_history_clue.customer_id = a.id'
        )->where([
            'salesman_id' => \Yii::$app->user->identity->getId(),
        ])->asArray()->all();
        return compact('models');
    }


    /**
     * 发短信客户列表
     */
    public function actionSmsList()
    {
        $user = \Yii::$app->user->identity;
        //搜索客户的时候   线索战败数据不参与，为无效线索  还不属于客户
        $params = 'salesman_id = ' . $user->getId() . ' and shop_id = ' . $user->shop_id . ' and (status > 0 or is_fail = 0) ';
        $clueList = Clue::find()->where($params)->all();
        $data = [];
        foreach ($clueList as $k => $v) {
            if ($v->is_fail == 1) {
                $data['fail'][] = [
                    'clue_id' => $v->id,
                    'customer_name' => $v->customer_name,
                    'customer_phone' => $v->customer_phone,
                    'intention_level_des' => $v->intention_level_des,
                    'status' => 4
                ];
            } else {
                if ($v->status == 1) {
                    $data['intent'][] = [
                        'clue_id' => $v->id,
                        'customer_name' => $v->customer_name,
                        'customer_phone' => $v->customer_phone,
                        'intention_level_des' => $v->intention_level_des,
                        'intention_des' => $v->intention_des,
                        'status' => $v->status
                    ];
                } elseif ($v->status == 2) {
                    $order = Order::find()->where([
                        'clue_id' => $v->id,
                    ])->andWhere([
                        '!=', 'status', 6
                    ])->one();
                    if (empty($order)) continue;
                    $data['order'][] = [
                        'clue_id' => $v->id,
                        'customer_name' => $v->customer_name,
                        'customer_phone' => $v->customer_phone,
                        'intention_level_des' => $v->intention_level_des,
                        'car_type_name' => $order->car_type_name,
                        'status' => $v->status
                    ];
                } elseif ($v->status == 3) {
                    $order = Order::find()->where([
                        'clue_id' => $v->id,
                        'status' => 6
                    ])->one();
                    if (empty($order)) continue;
                    $data['deal'][] = [
                        'clue_id' => $v->id,
                        'customer_name' => $v->customer_name,
                        'customer_phone' => $v->customer_phone,
                        'intention_level_des' => $v->intention_level_des,
                        'car_type_name' => $order->car_type_name,
                        'status' => $v->status
                    ];
                } elseif ($v->status == 0) {
                    $data['clue'][] = [
                        'clue_id' => $v->id,
                        'customer_name' => strval($v->customer_name),
                        'customer_phone' => strval($v->customer_phone),
                        'intention_level_des' => strval($v->intention_level_des),
                        'intention_des' => strval($v->intention_des),
                        'status' => $v->status
                    ];
                }
            }
        }
        return $data;
    }

    /**
     * 根据手机号验证当前客户是否是意向客户
     * 战败激活和意向转化都会使用这个方法
     * @return array
     */
    public function actionIsIntentionCustomer()
    {
        //接收参数
        $pData = $this->getPData();

        if (!isset($pData['customer_phone']) || !isset($pData['type'])) {
            return $this->paramError();
        }

        //取出顾问shop_id
        /* @var $user \common\models\User */
        $user = \Yii::$app->user->identity;
        $shop_id = $user->shop_id;

        if (isset($pData['clue_id'])) {
            $logic = new ClaimClueLogic();
            //退回超时10分钟的线索信息
            if (!$logic->checkHandleClue($pData['clue_id'], $user->id, $shop_id)) {
                \Yii::$app->params['code'] = 400;
                \Yii::$app->params['message'] = '线索超时未跟进，已回到门店线索列表！';
                return [];
            }
        }

        /**
         * edited by liujx 2017-7-04 start:
         *
         * 如果该客户之前存在没有战败线索信息，并且线索状态为意向或者订车 不允许转化或者激活现在的线索
         * 处理情况分两种
         * --- 战败激活 是不允许这个客户存在活的线索、意向、订车线索
         * --- 线索转化 是不允许这个客户存在存活的意向、订车线索、和别的顾问的活的的线索、意向、订车线索
         */

        // 这一步处理了不能有意向和订车线索
        $clue = ClueValidate::validateExists([
            'and',
            ['=', 'customer_phone', $pData['customer_phone']],
            ['=', 'is_fail', 0],
            ['in', 'status', [1, 2]]
        ]);

        // 这个条件需要根据处理情况，添加是否要验证顾问信息(转败激活不需要验证顾问)
        $where = [
            'and',
            ['=', 'customer_phone', $pData['customer_phone']],
            ['=', 'is_fail', 0],
            ['=', 'status', 0]
        ];

        // 转化需要验证这个顾问不是自己（不然自己的线索不能转化）
        if ($pData['type'] === 'inversion') {
            array_push($where, ['!=', 'salesman_id', $user->id]);
        }

        // 存在别人的没有战败的线索，也不能转化或者激活这个线索
        $otherClue = ClueValidate::validateExists($where);

        // 如果clue为空 则不是意向客户 否则是意向客户
        if ($clue || $otherClue) {
            \Yii::$app->params['code'] = 4005;
            // 判断是否为自己的客户
            if ($clue && $clue->salesman_id == $user->id) {
                $message = '该客户已经是您的客户,';
            } else {
                $message = '该客户正在被其他顾问跟进,';
            }

            // 判断处理类型
            if ($pData['type'] == 'active') {
                // 战败激活
                \Yii::$app->params['message'] = $message . '无法战败激活';
            } else {
                // 线索转化意向
                \Yii::$app->params['message'] = $message . '请将该客户置为无效线索。';
            }
        } else {
            \Yii::$app->params['code'] = 200;
            \Yii::$app->params['message'] = '';
        }

        //返回信息
        return [];
    }
}
