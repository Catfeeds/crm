<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/3/7
 * Time: 17:07
 */

namespace frontend\modules\sales\logic;


use common\logic\CarBrandAndType;
use common\logic\CompanyUserCenter;
use common\models\ClueWuxiao;
use common\models\Yuqi;
use frontend\modules\sales\models\Clue;
use common\models\Customer;
use common\models\Intention;
use common\models\Order;
use common\models\User;
use yii\data\Pagination;
use yii\db\Exception;
use Yii;
use common\models\UpdateXlsxLog;

/**
 * 任务相关逻辑
 * Class TaskLogic
 * @package frontend\modules\v1\logic
 */
class ClueLogic extends BaseLogic
{
    /**
     * 用户字段
     * @var array
     */
    public $arrCustomer = [
        'name',
        'phone',
        'spare_phone',
        'weixin',
        'sex',
        'profession',
        'area',
        'address',
        'birthday',
        'age_group_level_id'
    ];
    /**
     * 线索字段
     * @var array
     */
    public $arrClue = [
        'intention_id',
        'intention_des',
        'customer_id',
        'customer_phone',
        'buy_type',
        'des',
        'planned_purchase_time_id',
        'quoted_price',
        'sales_promotion_content',
        'customer_name',
        'is_star',
        'spare_intention_id',
        'contrast_intention_id',
        'intention_level_id',
        'quoted_price',
        'status',
        'clue_source'
    ];
    public $clueArr = [
        'clue' => ['clue_source'],
        'customer' => [
            'clue_source', 'intention_id', 'intention_level_id'
        ],
        'all' => [
            'intention_id', 'buy_type', 'planned_purchase_time_id', 'quoted_price', 'sales_promotion_content', 'spare_intention_id',
            'contrast_intention_id', 'customer_phone', 'intention_level_id', 'des', 'fail_tags', 'customer_name', 'fail_reason',
            'clue_source', 'is_star', 'clue_input_type'
        ]
    ];

    /**
     * 新增线索
     *
     * @param  array $data
     * @param  int $status
     * @param  object $user
     * @return bool|Clue
     * @throws Exception
     */
    public function add($data, $status = 0, $user = null)
    {

        if (empty($data)) {
            $this->setError('请求数据不能为空');
            $this->setErrorCode(400);
            return false;
        }
        if (!isset($data['customer_name'])) {
            $data['name'] = '未知';
        } else {
            $data['name'] = $data['customer_name'];
        }
        if (isset($data['customer_phone']) && $data['customer_phone']) {
            $data['phone'] = $data['customer_phone'];
        } else {
            $this->setError('缺少必填字段');
            $this->setErrorCode(400);
            return false;
        }
        if (!$this->checkPhone($data['customer_phone'])) {
            return false;
        }
        $user        = $user ?: \Yii::$app->user->identity;
        $db          = \Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            $type     = ($status == 0) ? 'clue' : 'customer';
            $customer = CustomerLogic::instance()->customerAdd($data, $type);
            $clue     = $this->clueAdd($data, $customer, $user, $type);
            $transaction->commit();
            return $clue;
        } catch (Exception $exception) {
            $transaction->rollBack();
            $this->errorCode = 4000;
            $this->setError($exception->errorInfo);
            return false;
        }


    }

    /**
     * 检查手机号
     * @param  string $phone
     * @return bool
     */
    public function checkPhone($phone)
    {
        // 不限制号段，只限制11位数字
        if (!preg_match("/^\d{11}$/", $phone)) {
            $this->setError('手机号格式错误');
            $this->setErrorCode(400);
            return false;
        }
//        $customer = Customer::findOne(['phone' => $phone]);
//        if (empty($customer)) {
//            return true;
//        }
//        $user = \Yii::$app->user->identity;
//        $clue = Clue::find()->where([
//            'shop_id' => $user->shop_id,
//            'customer_id' => $customer->id
//        ])->andWhere([
//            'in', 'status', [0, 1, 2]
//        ])->one();
//        if ($clue) {
//            if ($clue->salesman_id == $user->getId()) {
//                $this->errorCode = 4001;
//                $this->setError('您创建客户已存在，销售顾问为您本人，无法创建为新的客户');
//                return false;
//            } else {
//                $salesman        = User::findOne($clue->salesman_id);
//                $this->errorCode = 4003;
//                if (!empty($salesman)) {
//                    $this->setError('您创建客户已存在，销售顾问为：' . $salesman->name . ',无法创建为新的客户');
//                } else {
//                    $this->setError('您创建客户已存在,无法创建为新的客户');
//                }
//
//                return false;
//            }
//        }
        return true;
    }

    /**
     * @param $data
     * @param Customer $customer
     * @param User $user
     * @param $type  customer | clue
     * @return bool|Clue
     * @throws Exception
     */
    public function clueAdd($data, $customer, $user, $type)
    {
        $check = $this->checkRequire($data, $this->clueArr[$type]);
        if (!$check) {
            $this->errorCode = 400;
            $this->setError('缺少必填参数');
            return false;
        }

        $attributeData = $this->getAttributeData($data, $this->clueArr['all']);

        // edited by liujx 2017-06-22 需求变更 start :
        $arrReturn = $this->clueCheckPhoneInfo($data);
        if ($arrReturn === null) {
            $this->errorCode = Yii::$app->params['code'];
            $this->setError(Yii::$app->params['message']);
            return false;
        }

        // end;

        $obj   = new CompanyUserCenter();
        $model = new Clue();

        $time = $_SERVER['REQUEST_TIME'];
        $date = date('Y-m-d H:i:s', $time);
        // 获取当前最大的线索id
        $id  = Clue::find()->max('id');
        $id  = empty($id) ? 0 : $id;
        $log = new UpdateXlsxLog();

        if ($model->isNewRecord) {
            $model->customer_id        = $customer->id;
            $model->shop_id            = $user->shop_id;
            $model->shop_name          = $obj->getStructureNameByIds($user->shop_id);
            $model->salesman_name      = $user->name;
            $model->salesman_id        = $user->id;
            $model->is_assign          = 1;
            $model->assign_time        = $time;
            $model->who_assign_name    = $user->name;
            $model->who_assign_id      = $user->id;
            $model->create_type        = 0; //自己手动创建
            $model->create_person_name = $user->name;
            $model->create_time        = $time;
            $model->is_fail            = 0;

            if ($type == 'clue') {
                $model->status = 0;
            } else {
                $model->status           = 1;
                $model->create_card_time = $time;
            }
        }


        $model->setAttributes($attributeData);
        if (!$model->clue_input_type) {
            //自建标签  见: common\models\InputType
            $model->clue_input_type = 8;
        }
        //冗余字段处理
        if ($model->intention_id) {
            $obj                  = new CarBrandAndType();
            $model->intention_des = $obj->getCarTypeNameByTypeId($model->intention_id);
        } else {
            $model->intention_id = 0; //如果没有传改参数 默认为0
        }
        if ($model->intention_level_id) {
            $intention = Intention::findOne($model->intention_level_id);
            $model->intention_level_des     = $intention ? $intention->name : '';
            $model->initial_intention_level = $model->intention_level_des;
        }
        if ($model->is_star) {
            $model->star_time = $time;
        }
        if (!$model->save()) {
            throw new Exception('Clue保存失败', $model->errors);
        }
        if ($type == 'clue') {
            $log->insertYuQi($date, $time, $id);//线索增加逾期线索 新增客户不增加
        }
        return $model;
    }

    /**
     * 增加新客户
     *
     * @param $phone
     * @return bool|Customer
     */
    public function checkCustomer($phone)
    {
        $customer = Customer::findOne(['phone' => $phone]);
        if (!empty($customer)) {
            return $customer;
        }
        return false;
    }

    /**
     * 获取任务列表
     *
     * @param array $pData
     * @return array|null
     */
    public function getClueList($pData)
    {
        $user       = \Yii::$app->user->identity;
        $query      = Clue::find()->select([
            'id as clue_id', 'customer_name', 'customer_phone', 'create_time', 'intention_id',
            'clue_source', 'intention_des', 'clue_input_type', 'view_times', 'intention_level_des',
            'status', 'des', 'who_assign_name', 'assign_time','view_times'
        ])->where([
            'salesman_id' => $user->getId(),
            'shop_id' => $user->shop_id,
            'status' => 0,
            'is_fail' => 0
        ]);
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount]);

        $page = isset($pData['page']) ? $pData['page'] : 1;
        if (isset($pData['sort']) && $pData['sort']) {
            $orderBy = $pData['sort'] . ' DESC';
        } else {
            $orderBy = 'create_time DESC';
        }
        $pagination->setPage($page - 1);
        $models  = $query->limit(
            $pagination->getLimit()
        )->offset($pagination->getPage() * $pagination->pageSize)->orderBy($orderBy)->asArray()->all();
        $data    = [];
        $clue_id = null;
        foreach ($models as $v) {
            $clue_id .= $v['clue_id'] . ',';
        }

        //查找逾期数据
        if (!empty($clue_id)) {
            $clue_id = rtrim($clue_id, ',');
            $yuqi    = Yuqi::find()->select('clue_id,end_time,is_lianxi')->where("clue_id in ({$clue_id})")->asArray()->all();
            //$this->dump($yuqi);
        }
        foreach ($models as $k => $model) {
            $data[$k]['yq_end_time'] = '0';
            $data[$k]['rl_end_time'] = 0;
            $data[$k]['is_lianxi']   = false;
            foreach ($yuqi as $v) {
                if ($v['clue_id'] == $model['clue_id']) {
                    $data[$k]['yq_end_time'] = strval(strtotime($v['end_time']));
                    $data[$k]['is_lianxi']   = $v['is_lianxi'] == 1 ? true : false;
                    break;
                }
            }
            $data[$k]['clue_id']             = intval($model['clue_id']);
            $data[$k]['customer_name']       = strval($model['customer_name']);
            $data[$k]['customer_phone']      = strval($model['customer_phone']);
            $data[$k]['create_time']         = intval($model['create_time']);
            $data[$k]['view_times']          = intval($model['view_times']) ?: 0;
            $data[$k]['clue_source']         = intval($model['clue_source']);
            $data[$k]['clue_input_type']     = intval($model['clue_input_type']);
            $data[$k]['status']              = intval($model['status']);
            $data[$k]['des']                 = strval($model['des']);
            $data[$k]['intention_id']        = strval($model['intention_id']);
            $data[$k]['intention_des']       = strval($model['intention_des']);
            $data[$k]['intention_level_des'] = strval($model['intention_level_des']);
            $data[$k]['end_time']            = 0;
            if ($model['who_assign_name'] == '个人认领' && $model['view_times'] == 0) {
                $data[$k]['rl_end_time'] = $model['assign_time'] + (30*60);//剩余认领时间
            }

        }
        if ($totalCount > 0) {
            return [
                'models' => $data,
                'pages' => $this->pageFix($pagination),
            ];
        } else {
            return null;
        }
    }

    /**
     * 获取线索相关信息
     *
     * @param $attribute
     * @param $type
     * @param User $user
     * @return array|bool
     */
    public function getClueById($attribute, $user, $type = 'clue_id')
    {
        if ($type == 'phone') {
            $clue = Clue::find()->where([
                'customer_phone' => $attribute,
                'salesman_id' => $user->id,
                'shop_id' => $user->shop_id,//一个销售可能有多个店的业务
            ])->andWhere(['in', 'status', [0, 1, 2]])->one();
        } else {
            $clue = Clue::findOne($attribute);
        }
        if (empty($clue)) {
            $this->errorCode = 400;
            $this->setError('未找到该线索');
            return false;
        }
        $customer = Customer::findOne($clue->customer_id);
        if (empty($customer)) {
            $this->errorCode = 400;
            $this->setError('未找到该线索关联的客户');
            return false;
        }
        if ($clue->is_fail == 1) {
            $status = 4;
        } else {
            $status = $clue->status;
        }
        $intentionDes = $spareIntentionDes = null;
        if ($clue->intention_id) {
            $obj          = new CarBrandAndType();
            $intentionDes = $obj->getCarTypeNameByTypeId($clue->intention_id);
        }
        if ($clue->spare_intention_id) {
            $obj               = new CarBrandAndType();
            $spareIntentionDes = $obj->getCarTypeNameByTypeId($clue->spare_intention_id);
        }
        if ($clue->status == 0) {
            $data = [
                'clue_id' => $clue->id,
                'customer_name' => $clue->customer_name, //客户姓名
                'customer_phone' => $clue->customer_phone, //手机号
                'spare_phone' => strval($customer->spare_phone), //备用手机号
                'weixin' => strval($customer->weixin), //微信
                'sex' => $customer->sex ?: 1, //性别
                'profession' => intval($customer->profession), //职业
                'clue_source' => intval($clue->clue_source), //客户来源
                'area' => intval($customer->area),//地区
                'des' => strval($clue->des), //说明
                'intention_id' => $clue->intention_id, //意向车型
                'intention_des' => strval($intentionDes), //意向车型
                'buy_type' => intval($clue->buy_type), //购买类型
                'planned_purchase_time_id' => intval($clue->planned_purchase_time_id), //拟购时间
                'quoted_price' => strval($clue->quoted_price), //报价信息
                'sales_promotion_content' => strval($clue->sales_promotion_content), //促销内容
                'status' => $status,
                'shopName'=>$clue->shop_name,
            ];
            //如果是分配的则显示分配信息
            if ($clue->who_assign_id != $user->getId()) {
                if ($clue->who_assign_name) {
                    $data['who_assign_name'] = $clue->who_assign_name;
                } else {
                    $data['who_assign_name'] = User::findOne($clue->who_assign_id)->name;
                }
                $data['assign_time'] = $clue->assign_time;
            }
            return $data;
        } elseif ($clue->status == 1) {
            return [
                'clue_id' => $clue->id,
                'customer_name' => $clue->customer_name, //客户姓名
                'customer_phone' => $clue->customer_phone, //手机号
                'spare_phone' => strval($customer->spare_phone), //备用手机号
                'weixin' => strval($customer->weixin), //微信
                'sex' => $customer->sex ?: 1, //性别
                'birthday' => $customer->birthday,//生日
                'profession' => intval($customer->profession), //职业
                'clue_source' => intval($clue->clue_source), //客户来源
                'area' => intval($customer->area),//地区
                'address' => strval($customer->address), //地址
                'age_group_level_id' => intval($customer->age_group_level_id), //年龄段
                'intention_level_id' => intval($clue->intention_level_id), //意向等级
                'spare_intention_id' => intval($clue->spare_intention_id),//备用车型
                'spare_intention_desc' => strval($spareIntentionDes),//备用车型
                'des' => strval($clue->des), //说明
                'intention_id' => $clue->intention_id, //意向车型
                'intention_des' => strval($intentionDes), //意向车型
                'buy_type' => intval($clue->buy_type), //购买类型
                'planned_purchase_time_id' => intval($clue->planned_purchase_time_id), //拟购时间
                'quoted_price' => strval($clue->quoted_price), //报价信息
                'sales_promotion_content' => strval($clue->sales_promotion_content), //促销内容
                'contrast_intention_id' => strval($clue->contrast_intention_id), //对比车型,string
                'is_star' => intval($clue->is_star) ? true : false,
                'status' => $status,
                'fail_tag' => $status == 4 ? '意向战败' : '',
                'shopName'=>$clue->shop_name,

            ];
        } elseif ($clue->status == 2) {
            $order = Order::find()->where([
                'clue_id' => $clue->id
            ])->andWhere([
                '!=', 'status', 6
            ])->one();
            return [
                'clue_id' => $clue->id,
                'customer_name' => $clue->customer_name, //客户姓名
                'customer_phone' => $clue->customer_phone, //手机号
                'spare_phone' => strval($customer->spare_phone), //备用手机号
                'weixin' => strval($customer->weixin), //微信
                'sex' => $customer->sex ?: 1, //性别
                'birthday' => $customer->birthday,//生日
                'profession' => intval($customer->profession), //职业
                'clue_source' => intval($clue->clue_source), //客户来源
                'area' => intval($customer->area),//地区
                'address' => strval($customer->address), //地址
                'salesman_name' => strval($clue->salesman_name), //归属顾问
                'age_group_level_id' => intval($customer->age_group_level_id), //年龄段
                'des' => strval($clue->des), //说明

                'intention_level_id' => intval($clue->intention_level_id), //意向等级
                'intention_id' => $clue->intention_id, //意向车型
                'intention_des' => strval($intentionDes), //意向车型

                'order_id' => $order->order_id, //订单ID
                'ordain_date' => $order->create_time, //订车日期
                'car_type_id' => $order->car_type_id, //车型
                'car_type_name' => strval($order->car_type_name), //车型
                'color_configure' => strval($order->color_configure),//颜色配置
                'loan_period' => $order->loan_period,//贷款期限
                'predict_car_delivery_time' => intval($order->predict_car_delivery_time), //预计交车时间
                'deposit' => $order->deposit, //定金
                'delivery_price' => $order->delivery_price,//成交价格
                'discount_price' => $order->discount_price ?: '0.00',//优惠金额
                'is_insurance' => $order->is_insurance, //是否本店投保
                'is_add' => $order->is_add,//是否加装
                'give' => strval($order->give), //赠送
                'add_content' => $order->add_content, //金品装饰
                'status' => $status,
                'fail_tag' => $status == 4 ? '订车战败' : '',
                'shopName'=>$clue->shop_name,
            ];
        } elseif ($clue->status == 3) {
            $order = Order::findOne(['clue_id' => $clue->id]);
            return [
                'clue_id' => $clue->id,
                'customer_name' => $clue->customer_name, //客户姓名
                'customer_phone' => $clue->customer_phone, //手机号
                'spare_phone' => strval($customer->spare_phone), //备用手机号
                'weixin' => strval($customer->weixin), //微信
                'sex' => $customer->sex ?: 1, //性别
                'birthday' => $customer->birthday,//生日
                'profession' => intval($customer->profession), //职业
                'clue_source' => intval($clue->clue_source), //客户来源
                'area' => intval($customer->area),//地区
                'address' => strval($customer->address), //地址
                'salesman_name' => strval($clue->salesman_name), //归属顾问
                'age_group_level_id' => intval($customer->age_group_level_id), //年龄段
                'des' => strval($clue->des), //说明

                'intention_level_id' => intval($clue->intention_level_id), //意向等级
                'intention_id' => $clue->intention_id, //意向车型
                'intention_des' => strval($intentionDes), //意向车型

                'order_id' => $order->order_id, //订单ID
                'buy_date' => $order->create_time, //购车日期
                'car_type_id' => $order->car_type_id, //车型
                'car_type_name' => strval($order->car_type_name), //车型
                'color_configure' => $order->color_configure,//颜色配置
                'loan_period' => $order->loan_period,//贷款期限
                'predict_car_delivery_time' => $order->predict_car_delivery_time, //预计交车时间
                'deposit' => $order->deposit, //定金
                'delivery_price' => $order->delivery_price,//成交价格
                'discount_price' => $order->discount_price,//优惠金额
                'is_insurance' => $order->is_insurance, //是否本店投保
                'is_add' => $order->is_add,//是否加装
                'give' => $order->give, //赠送
                'add_content' => $order->add_content, //金品装饰
                'status' => $status,
                'engine_code' => $order->engine_code, //发动机编号
                'car_number' => $order->car_number,//车牌号
                'frame_number' => $order->frame_number,//车架号
                'give_person' => strval($clue->salesman_name), //交车顾问
                'car_owner_name' => $order->car_owner_name, //车主姓名
                'car_owner_phone' => $order->car_owner_phone, //车主电话
                'buy_type' => intval($order->buy_type),
                'shopName'=>$clue->shop_name,
            ];
        } else {
            $this->setError('未找到信息');
            $this->errorCode = 400;
            return false;
        }
    }

    /**
     * 线索客户转换为意向客户,没有交谈记录
     *
     * @param $data
     * @param User $user
     * @return  boolean
     */
    public function toIntent($data, $user)
    {
        $customer = isset($data['customer']) ? $data['customer'] : null;
        if (!isset($customer['clue_id'])) {
            $this->errorCode = 400;
            $this->setError('没有线索ID');
            return false;
        }
        $clue = Clue::findOne($customer['clue_id']);
        if (empty($clue)) {
            $this->errorCode = 400;
            $this->setError('未找到线索');
            return false;
        }
        if (!$clue->customer_name && !isset($customer['customer_name'])) {
            $this->errorCode = 400;
            $this->setError('缺少必填参数');
            return false;
        }
        if (!$clue->intention_id && !isset($customer['intention_id'])) {
            $this->errorCode = 400;
            $this->setError('缺少必填参数');
            return false;
        }
        if (!$clue->intention_level_id && !isset($customer['intention_level_id'])) {
            $this->errorCode = 400;
            $this->setError('缺少必填参数');
            return false;
        }
        $customer['status'] = 1;
        return $this->update($customer);
    }

    /**
     * 更新意向客戶
     *
     * @param $data
     * @return bool
     * @throws Exception
     */
    public function update($data)
    {
        $clue = Clue::findOne($data['clue_id']);
        if (empty($clue)) {
            $this->errorCode = 4000;
            $this->setError("未找到线索");
            return false;
        }

        //允许修改手机号，前提是该手机号没有被占用过
        if (isset($data['phone']) && $data['phone'] != $clue->customer_phone) {
            //用户输入的手机号和原来的手机号不一致，为修改手机号行为
            //判断手否新手机号被占用
            if ($objCustomer = Customer::findOne(['phone' => $data['phone']])) {
                $this->errorCode = 4000;
                $this->setError("修改的手机号已经被占用，无法修改");
                return false;
            }
            $data['customer_phone'] = $data['phone'];//修改手机号
        }
        if (!isset($data['customer_name'])) {
            $data['name'] = '未知';
        } else {
            $data['name'] = $data['customer_name'];
        }
        $CrmCustomer = []; //客户表修改字段
        foreach ($this->arrCustomer as $v) {
            if (isset($data[$v])) {
                $CrmCustomer[$v] = $data[$v];
            }
        }
        if (isset($data['is_star']) && $data['is_star']) {
            $data['is_star'] = 1;
        } else {
            $data['is_star'] = 0;
        }
        $db = \Yii::$app->db;
        //开启事务
        $transaction = $db->beginTransaction();
        try {
            //开启记录交谈中修改信息
            if ($clue->status > 0)
                Yii::$app->cache->set('UPDATE_CLUE' . Yii::$app->user->getId(), true);

            $member = new MemberLogic();
            $member->addMember($clue->customer_id);
            $db->createCommand()->update('crm_customer', $CrmCustomer, ['id' => $clue->customer_id])->execute();
            $this->clueUpdate($data);
            $transaction->commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * 更新线索
     *
     * @param $data
     * @return bool|Clue
     * @throws Exception
     */
    public function clueUpdate($data)
    {
        $model = Clue::findOne($data['clue_id']);

        //建卡时间
        if ($model->status == 0 && $data['status'] == 1) {
            $model->create_card_time = $_SERVER['REQUEST_TIME'];
            $model->status           = 1;
        }

        //有战败标签，即战败
//        if ($model->fail_tags) {
//            $model->is_fail = 1;
//        }
        //冗余字段,意向车型
        if ($data['intention_id'] && $data['intention_id'] != $model->intention_id) {
            $obj                  = new CarBrandAndType();
            $model->intention_des = $obj->getCarTypeNameByTypeId($data['intention_id']);
        }
        //意向等级
        if ($data['intention_level_id'] && $data['intention_level_id'] != $model->intention_level_id) {
            $model->intention_level_des     = Intention::findOne($data['intention_level_id'])->name;
            $model->initial_intention_level = $model->intention_level_des;
        }
        if ($data['is_star'] != $model->is_star) {
            $model->star_time = $_SERVER['REQUEST_TIME'];
        }

        $attributeData = $this->getAttributeData($data, $this->clueArr['all']);
        $model->setAttributes($attributeData);

        if (!$model->save()) {
            throw new Exception('Clue修改失败', $model->errors);
        }
        return $model;
    }

    /**
     * 详情
     * @param $id
     * @return array
     */
    public function getInfo($id)
    {

        //客户信息
        $customer = Customer::find()->select([
            'phone', 'name', 'spare_phone', 'weixin', 'sex', 'birthday', 'profession', 'area', 'address', 'age_group_level_id'
        ])
            ->where('id=' . $id)
            ->one();

        $data = [];


        $data['customer_phone']     = intval($customer['phone']);
        $data['customer_name']      = strval($customer['name']);
        $data['spare_phone']        = intval($customer['spare_phone']);
        $data['weixin']             = strval($customer['weixin']);
        $data['sex']                = intval($customer['sex']);
        $data['birthday']           = strval($customer['birthday']);
        $data['profession']         = intval($customer['profession']);
        $data['area']               = strval($customer['area']);
        $data['address']            = strval($customer['address']);
        $data['age_group_level_id'] = intval($customer['age_group_level_id']);


        //客户信息详情
        $clue = Clue::find()->select([
            'intention_level_id', 'intention_id', 'spare_intention_id', 'buy_type', 'planned_purchase_time_id', 'quoted_price', 'sales_promotion_content', 'contrast_intention_id'
        ])
            ->where('customer_id=' . $id)
            ->orderby('id desc')
            ->one();


        $data['intention_level_id']       = intval($clue['intention_level_id']);
        $data['intention_id']             = intval($clue['intention_id']);
        $data['spare_intention_id']       = intval($clue['spare_intention_id']);
        $data['buy_type']                 = intval($clue['buy_type']);
        $data['planned_purchase_time_id'] = intval($clue['planned_purchase_time_id']);
        $data['quoted_price']             = strval($clue['quoted_price']);
        $data['sales_promotion_content']  = strval($clue['sales_promotion_content']);
        $data['contrast_intention_id']    = intval($clue['contrast_intention_id']);


        return $data;

    }

    /**
     * 根据时间查找线索列表
     *
     * @param $data
     * @param User $user
     * @return array
     */
    public function getClueListByTime($data, $user)
    {
        if ($data['type'] == 'month') {
            $clue_sql        = Clue::find()->where([
                'salesman_id' => $user->id,
            ])->andWhere([
                'like', 'FROM_UNIXTIME(create_time,"%Y-%m")', $data['date_time']
            ]);
            $clue_wuxiao_sql = ClueWuxiao::find()->where([
                'salesman_id' => $user->id,
            ])->andWhere([
                'like', 'FROM_UNIXTIME(create_time,"%Y-%m")', $data['date_time']
            ]);
            $clue            = $clue_sql->union($clue_wuxiao_sql);
            if ($data['date_time'] == date('Y-m')) {
                $model_sql        = Clue::find()->where([
                    'salesman_id' => $user->id,
                    'status' => 0,
                    'is_fail' => 0
                ]);
                $model_wuxiao_sql = ClueWuxiao::find()->where([
                    'salesman_id' => $user->id,
                    'status' => 0,
                    'is_fail' => 0
                ]);
                $model            = $model_sql->union($model_wuxiao_sql);
            }
        }


        if ($data['type'] == 'day') {
            $clue_sql        = Clue::find()->where([
                'salesman_id' => $user->id,
            ])->andWhere([
                'FROM_UNIXTIME(create_time,"%Y-%m-%d")' => $data['date_time']
            ]);
            $clue_wuxiao_sql = ClueWuxiao::find()->where([
                'salesman_id' => $user->id,
            ])->andWhere([
                'FROM_UNIXTIME(create_time,"%Y-%m-%d")' => $data['date_time']
            ]);
            $clue            = $clue_sql->union($clue_wuxiao_sql);
            if ($data['date_time'] == date('Y-m-d')) {
                $model_sql        = Clue::find()->where([
                    'salesman_id' => $user->id,
                    'status' => 0,
                    'is_fail' => 0
                ]);
                $model_wuxiao_sql = ClueWuxiao::find()->where([
                    'salesman_id' => $user->id,
                    'status' => 0,
                    'is_fail' => 0
                ]);
                $model            = $model_sql->union($model_wuxiao_sql);
            }
        }
        $return = [];
        if (!empty($clue)) {
            foreach ($clue->all() as $val) {
                $status = ($val->is_fail == 1) ? 4 : $val->status;
                if (!$val->customer_name) {
                    $customer       = Customer::findOne($val->customer_id);
                    $customer_name  = $customer->name;
                    $customer_phone = $customer->phone;
                } else {
                    $customer_name  = $val->customer_name;
                    $customer_phone = $val->customer_phone;
                }
                //状态描述
                if ($val->status >= 1) {
                    $des = '已转化';
                } elseif ($val->is_fail == 1) {
                    $des = '无效线索';
                } elseif ($val->is_assign == 1) {
                    $des = '跟进中';
                } else {
                    $des = '未分配';
                }


                $return[$val->id] = [
                    'customer_name' => $customer_name,
                    'customer_phone' => $customer_phone,
                    'view_times' => $val->view_times,
                    'status' => $val->status,
                    'is_fail' => $val->is_fail,
                    'show_status' => $des,//客户描述
                ];
            }
        }
        if (!empty($model)) {
            foreach ($model->all() as $k => $v) {
                $status = ($v->is_fail == 1) ? 4 : $v->status;
                if (isset($return[$v->id]) && $return[$v->id]) continue;
                if (!$v->customer_name) {
                    $customer       = Customer::findOne($v->customer_id);
                    $customer_name  = $customer->name;
                    $customer_phone = $customer->phone;
                } else {
                    $customer_name  = $v->customer_name;
                    $customer_phone = $v->customer_phone;
                }

                //状态描述
                if ($v->status >= 1) {
                    $des = '已转化';
                } elseif ($v->is_fail == 1) {
                    $des = '无效线索';
                } elseif ($v->is_assign == 1) {
                    $des = '跟进中';
                } else {
                    $des = '未分配';
                }
                $return[$v->id] = [
                    'customer_name' => $customer_name,
                    'customer_phone' => $customer_phone,
                    'view_times' => $v->view_times,
                    'status' => $status,
                    'is_fail' => $v->is_fail,
                    'show_status' => $des,//客户描述
                ];
            }
        }

        sort($return);
        return $return;
    }

    /**
     * 逾期线索状态更新
     */
    public function updateYuQi($clue_id)
    {
        $yuqi = Yuqi::find()->where("clue_id = {$clue_id}")->one();
        if (!empty($yuqi)) {
            $yuqi->is_lianxi   = 1;
            $yuqi->lianxi_time = empty($yuqi->lianxi_time) ? date('Y-m-d H:i:s') : $yuqi->lianxi_time;
            $yuqi->save();
        }
    }

    /**
     * 新增线索或者客户的时候 通过手机号检测用户信息
     *
     * @desc 现在有两个地方使用这个函数 添加线索&新增客户
     * @param array $pData 请求的参数
     * @return array|null
     */
    public function clueCheckPhoneInfo($pData)
    {
        /**
         * edited by liujx 2017-06-28 修改验证规则 start :
         *
         * 客户存在没有战败的线索，并且线索状态为 线索、意向、订车 不允许新增线索
         *
         * 2017-7-25 liujx update 需求变更，现在是天猫的订单可以多次下单（必须为天猫订单）
         */
        $clue = Clue::find()->where([
            'customer_phone' => $pData['phone'],
            'is_fail' => 0,
            'status' => [0, 1, 2]
        ])->orderBy('id desc')->one();
        if ($clue) {
            $user = \Yii::$app->user->identity;

            // 当这个订车客户是自己的客户，需要查询这个订单是否为天猫订单
            /* @var $clue \common\models\Clue */
            if ($clue->status == Clue::STATUS_BOOK && $clue->salesman_id == $user->getId()) {
                // 查询这个订单是否为天猫下单
                $isHave = Order::findOne(['clue_id' => $clue->id, 'order_type' => Order::ORDER_TYPE_TIANMAO]);
                if ($isHave) {
                    return [];
                }
            }

            \Yii::$app->params['code'] = 4001;
            if ($clue->salesman_id == $user->getId()) {
                \Yii::$app->params['message'] = '该客户已经是您的客户,请勿重复添加';
            } else {
                \Yii::$app->params['message'] = '该客户已经被其他顾问跟进,无法添加';
            }

            return null;
        } else {
            return [];
        }
    }

    /**
     * 工作台 添加到店接待\上门  通过手机号检测用户信息
     * @param $pData
     * @return array
     */
    public function clueCheckPhoneInfo1($pData)
    {
        $user = \Yii::$app->user->identity;
        $clue = Clue::find()->where([
            'customer_phone' => $pData['phone'],
        ])->orderBy('id desc')->one();
        if ($clue) {
            if ($user->shop_id == $clue->shop_id){//当前门店存在客户

                if ($clue->salesman_id == $user->getId()) {//自己的客户
                    if ($clue->is_fail == 1) {
                        return [];

                    }else if($clue->status == 3) {
                        return [];

                    } else if ($clue->status == 0 ) {
                        \Yii::$app->params['code'] = 4001;
                        \Yii::$app->params['message'] = '该客户目前是线索状态,请将该客户转化后再进行操作';
                    }else if ($clue->status == 1 || $clue->status == 2) {
                        return $this->getClueInfo($clue);
                    }

                } else {//同门店其他顾问的客户
                    if ($clue->is_fail == 1) {
                       return [];

                    }else if($clue->status == 3) {
                        return [];

                    } else if ($clue->status == 0) {
                        \Yii::$app->params['code'] = 4002;
                        \Yii::$app->params['message'] = "该客户已经是顾问：{$clue->salesman_name}的线索客户,无法接待";
                        return ;
                    }else if ($clue->status == 1 || $clue->status == 2) {
                        \Yii::$app->params['code'] = 4003;
                        \Yii::$app->params['message'] = "该客户是顾问：{$clue->salesman_name}客户,您可以替他接待";
                        return $this->getClueInfo($clue);
                    }
                }
            }else{
                \Yii::$app->params['code'] = 4001;
                \Yii::$app->params['message'] = "该客户是顾问：{$clue->salesman_name}的客户,无法添加";
            }

        } else {
            return [];
        }
    }

    public function getClueInfo($clue) {
        $salesman = User::findOne($clue->salesman_id);
        return ClueLogic::instance()->getClueById($clue->id, $salesman);
    }


}
