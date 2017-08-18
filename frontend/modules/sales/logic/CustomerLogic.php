<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/3/13
 * Time: 14:02
 */

namespace frontend\modules\sales\logic;


use common\logic\CarBrandAndType;
use frontend\modules\sales\models\Clue;
use common\models\Customer;
use common\models\FailTags;
use common\models\Intention;
use common\models\Order;
use common\models\User;
use yii\data\Pagination;
use yii\db\Exception;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * 客户相关逻辑代码
 *
 * Class CustomerLogic
 * @package frontend\modules\sales\logic
 */
class CustomerLogic extends BaseLogic
{
    /**
     * 获取客户信息
     *
     * @param $customerId
     * @return object
     */
    public function getCustomerById($customerId)
    {
        $customer = Customer::findOne($customerId);
        return $customer;
    }

    /**
     * 获取失败用户
     * 线索战败不现实在战败列表
     *
     * @param User $user
     * @return null | array
     */
    public function getFailCustomer($user,$sql,$clueIds,$OldclueIds)
    {
        $query = $sql->OrWhere([
            'salesman_id' => $user->getId(),
            'shop_id' => $user->shop_id,
            'is_fail' => 1
        ])->andWhere([
            '!=', 'status', 0
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
        foreach ($models as $k => $model) {
            $data[$k]['isDistribution'] = 0;
            $data[$k]['distributionDes'] = '';
            //验证分配给我的提车信息
            if (!empty($clueIds)) {
                if (in_array($model->id,$clueIds)) {
                    $data[$k]['isDistribution'] = 1;
                    $data[$k]['distributionDes'] = '提车任务';
                }
            }

            //验证我分配给其他门店提车信息
            if (!empty($OldclueIds)) {
                if (in_array($model->id,$OldclueIds)) {
                    $data[$k]['isDistribution'] = 2;
                    $data[$k]['distributionDes'] = '异地提车';
                }
            }
            $reason = null;
            if (!empty($model->fail_reason) && !empty($model->fail_tags)) {
                $reason = FailTags::findOne($model->fail_tags);
            }

            $data[$k]['clue_id'] = intval($model->id);
            $data[$k]['customer_name'] = strval($model->customer_name);
            $data[$k]['customer_phone'] = strval($model->customer_phone);
            $data[$k]['last_view_time'] = intval($model->last_view_time);
            $data[$k]['fail_tags'] = intval($model->fail_tags);
            $data[$k]['fail_reason'] = !empty($reason) ? $reason->name : $reason;
            $data[$k]['last_fail_time'] = intval($model->last_fail_time);
            $data[$k]['fail_date'] = intval($model->last_fail_time);
            $data[$k]['status'] = 4;
        }
        if ($totalCount > 0) {
            return [
                'models' => $data,
                'pages' => BaseLogic::instance()->pageFix($pagination),
            ];
        }
        return null;
    }

    /**
     * 增加客户
     *
     * @param $pData
     * @param User $user
     * @return bool
     */
    public function addCustomer($pData, $user)
    {
        $talk = (isset($pData['talk'])) ? $pData['talk'] : null; //交谈信息
        $customer = (isset($pData['customer'])) ? $pData['customer'] : null; //客户信息
        if ($talk && $customer) {
            if (!isset($talk['start_time']) || !isset($talk['end_time']) || !isset($talk['talk_type'])) {
                $this->errorCode = 400;
                $this->setError('缺少参数');
                return false;
            }
            return $this->newCustomerTalk($customer, $talk);
        }
        //添加线索
        $clue = ClueLogic::instance()->add($pData['customer'], 1, $user);

        if ($clue) {
            return true;
        } else {
            $this->errorCode = ClueLogic::instance()->errorCode;
            $this->setError(ClueLogic::instance()->getError());
            return false;
        }
    }

    /**
     * 线索转化为意向客户，并包含交谈记录
     *
     * @param $customer array 客户信息
     * @param $talkData array 交谈信息
     * @return boolean
     * @throws Exception
     */
    public function ClueToCustomerTalk($customer, $talkData)
    {
        if (!isset($talkData['talk_type']) || !isset($talkData['start_time']) || !isset($talkData['end_time'])) {
            $this->errorCode = 400;
            $this->setError('缺少必填参数');
            return false;
        }
        $clue = Clue::findOne($customer['clue_id']);
        if (empty($clue)) {
            $this->errorCode = 400;
            $this->setError('未找到线索');
            return false;
        }
        //检查必填字段

        $customer['name'] = isset($customer['customer_name']) ? $customer['customer_name'] : $clue->customer_name;
        $customer['phone'] = isset($customer['customer_phone']) ? $customer['customer_phone'] : $clue->customer_phone;
        if (isset($customer['is_star']) && $customer['is_star'] === true) {
            $customer['is_star'] = 1;
        } else {
            $customer['is_star'] = 0;
        }
        $clue->status = 1;
        $db = \Yii::$app->db;
        $user = \Yii::$app->user->identity;
        $transaction = $db->beginTransaction();
        try {

            //检测是否已经注册会员
            $member = new MemberLogic();
            $member->addMember($clue->customer_id);

            //开启记录交谈中修改信息
            Yii::$app->cache->set('addTalk'.Yii::$app->user->getId(), true);
            CustomerLogic::instance()->customerAdd($customer, 'customer');


            if(isset($customer['intention_id']) && $customer['intention_id']
                && $customer['intention_id'] != $clue->intention_id) {
                $obj = new CarBrandAndType();
                $clue->intention_des = $obj->getCarTypeNameByTypeId($customer['intention_id']);
            }

            if(isset($customer['intention_level_id']) && $customer['intention_level_id']
                && $customer['intention_level_id'] != $clue->intention_level_id)
                $clue->intention_level_des = Intention::findOne($customer['intention_level_id'])->name;

            if ($customer['is_star'] != $clue->is_star) {
                $clue->star_time = $_SERVER['REQUEST_TIME'];
            }

            $attribute = $this->getAttributeData($customer, ClueLogic::instance()->clueArr['all']);
            $clue->setAttributes($attribute);

            if (!$clue->save()) {
                throw new Exception("线索更新失败", $clue->errors);
            }

            TalkLogic::instance()->addNewTalk($talkData, $clue, $user);
            $transaction->commit();
            //删除记录交谈中修改信息
            Yii::$app->cache->delete('talk_change_'.Yii::$app->user->getId());
            Yii::$app->cache->delete('addTalk'.Yii::$app->user->getId());
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * 新增客户，并包含交谈记录
     * @param $customer
     * @param $talkData
     * @return bool
     * @throws Exception
     */
    public function newCustomerTalk($customer, $talkData)
    {
        if (!ClueLogic::instance()->checkPhone($customer['customer_phone'])) {
            $this->errorCode = 400;
            $this->setError('不能重复建线索');
            return false;
        }
        $customer['name'] = $customer['customer_name'];
        $customer['phone'] = $customer['customer_phone'];
        if (isset($customer['is_star']) && $customer['is_star'] === true) {
            $customer['is_star'] = 1;
        } else {
            $customer['is_star'] = 0;
        }
        if (isset($customer['status']) && $customer['status'] == 4) {
            unset($customer['status']);
        }

        $db = \Yii::$app->db;
        $user = \Yii::$app->user->identity;
        $transaction = $db->beginTransaction();
        $isSubmit = false; // 是否提交该订单
        try {
            /**
             * edited by liujx 2017-06-28 start:
             *
             * 修改添加线索返回false情况的处理
             */

            // 用户存在更新用户
            $customerModel = $this->customerAdd($customer, 'customer');
            if ($customerModel) {
                $clue = ClueLogic::instance()->clueAdd($customer, $customerModel,$user, 'customer');
                if ($clue) {
                    @TalkLogic::instance()->addNewTalk($talkData, $clue, $user);
                    $isSubmit = true;
                } else {
                    $this->errorCode = ClueLogic::instance()->errorCode;
                    $this->setError(ClueLogic::instance()->getError());
                }
            }

            // 最终提交
            if ($isSubmit) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }

            // end;

            return $isSubmit;
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * 客户表必填字段，和可填字段
     * @var array
     */
    public $customerArr = [
        'clue' => [
            'phone'
        ],
        'customer' => [
            'phone', 'name'
        ],
        'all' => [
            'phone', 'name', 'spare_phone', 'weixin', 'sex', 'profession', 'area', 'address', 'birthday', 'age_group_level_id'
        ]
    ];

    /**
     * 增加客户
     *
     * @param $data
     * @param string $type
     * @return bool|Customer
     * @throws Exception
     */
    public function customerAdd($data, $type = 'clue')
    {
        //已存在改客户修改信息
        if (isset($data['phone']))
            $model = Customer::findOne(['phone' => $data['phone']]);
        if (empty($model)) {
            $model = new Customer();
            $check = $this->checkRequire($data, $this->customerArr[$type]);
            if (!$check) {
                $this->errorCode = 400;
                $this->setError('手机号必填');
                return false;
            }
        }
        $attributeData = $this->getAttributeData($data, $this->customerArr['all']);
        if ($model->isNewRecord) {
            $model->create_time = $_SERVER['REQUEST_TIME'];
        }


        $model->setAttributes($attributeData);
        if (!$model->save()) {

            throw new Exception('客户信息保存失败', $model->errors);
        }
        if($type == 'customer'){//意向客户增加注册会员  线索不注册
            //检测是否已经注册会员
            $member = new MemberLogic();
            $member->addMember($model->id);
        }

        return $model;
    }

    /**
     * 得到交车和保有客户
     *
     * @param int $status
     * @param User $user
     * @return array | null
     */
    public function getDealCustomer($status, $user,$sql,$clueArrId,$OldclueIds)
    {
        if ($status == 5) {
            $query = $sql->orWhere([
                'salesman_id' => $user->getId(),
                'shop_id' => $user->shop_id,
                'status' => 3,
                'is_fail' => 0
            ])->orderBy([
                "is_star" => SORT_DESC,
                "create_time" => SORT_DESC
            ]);
        } else {
            $query = $sql->orWhere([
                'salesman_id' => $user->getId(),
                'status' => $status,
                'is_fail' => 0
            ])->orderBy([
                "is_star" => SORT_DESC,
                "create_time" => SORT_DESC
            ]);
        }

        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination([
            'totalCount' => $totalCount,
            'defaultPageSize' => 1000,
            'pageSizeLimit' => [1, 1000]
        ]);

        $models = $query->all();
        $clueIds = ArrayHelper::getColumn($models,'id');
        $orders = Order::find()->where([
            'status' => 6
        ])->andWhere([
            'in', 'clue_id', $clueIds
        ])->all();
        $data = [];

        // edited by liujx 2017-07-18 添加购买车型的品牌信息 start:
        if ($status == 3) {
            $arrIntentionIds = ArrayHelper::getColumn($orders, 'car_type_id');
            $arrBrand = (new CarBrandAndType())->getBrandByIntentionIdAll($arrIntentionIds);
        } else {
            $arrBrand = [];
        }
        // end

        foreach ($orders as $k => $order) {
            $data[$k]['isDistribution'] = 0;
            $data[$k]['distributionDes'] = '';
            //验证分配给我的提车信息
            if (!empty($clueArrId)) {
                if (in_array($order->clue_id,$clueArrId)) {
                    $data[$k]['isDistribution'] = 1;
                    $data[$k]['distributionDes'] = '提车任务';
                }
            }

            //验证我分配给其他门店提车信息
            if (!empty($OldclueIds)) {
                if (in_array($order->clue_id,$OldclueIds)) {
                    $data[$k]['isDistribution'] = 2;
                    $data[$k]['distributionDes'] = '异地提车';
                }
            }

            /* @var $order \common\models\Order */
            $data[$k]['clue_id'] = intval($order->clue->id);
            $data[$k]['customer_name'] = strval($order->clue->customer_name);
            $data[$k]['customer_phone'] = strval($order->clue->customer_phone);
            $data[$k]['last_view_time'] = intval($order->clue->last_view_time);
            $data[$k]['intention_id'] = intval($order->clue->intention_id);
            $data[$k]['intention_des'] = strval($order->clue->intention_des); //意向车型
            $data[$k]['des'] = strval($order->clue->des);
            $data[$k]['buy_date'] = $order->create_time; //购车时间
            $data[$k]['buy_models'] = strval($order->car_type_name); //购买车型
            $data[$k]['status'] = intval($status);
            // edited by liujx 2017-08-02 start :
            $data[$k]['order_id'] = $order->order_id;
            // end;

            // edited by liujx 2017-07-18 添加购买车型的品牌信息 start:
            if ($status == 3) {

                $tmpKey = $order->car_type_id;
                if (isset($arrBrand[$tmpKey])) {
                    $data[$k]['buy_models'] = $arrBrand[$tmpKey]['brand_name'].' '.$data[$k]['buy_models'];
                }
            }
        }

        if ($totalCount > 0) {
            return [
                'models' => $data,
                'pages' => BaseLogic::instance()->pageFix($pagination),
            ];
        } else {
            return null;
        }

    }
}