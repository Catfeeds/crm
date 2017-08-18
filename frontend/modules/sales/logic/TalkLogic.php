<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/3/15
 * Time: 13:58
 */

namespace frontend\modules\sales\logic;


use common\helpers\Helper;
use common\logic\CarBrandAndType;
use common\models\FailTags;
use common\models\Intention;
use common\models\Order;
use common\models\OrganizationalStructure;
use common\models\PutTheCar;
use frontend\modules\sales\models\Talk;
use frontend\modules\sales\models\Task;
use common\models\User;
use frontend\modules\sales\models\Clue;
use Yii;
use yii\data\Pagination;
use yii\db\Exception;
use common\logic\DataDictionary;
use yii\helpers\ArrayHelper;
use common\models\GongHaiLog;
use common\models\GongHai;
use common\logic\GongHaiLogic;

/**
 * 交谈记录
 *
 * Class TalkLogic
 * @package frontend\modules\sales\logic
 */
class TalkLogic extends BaseLogic
{
    /**
     * 表单字段
     * @var array
     */
    public $_attribute_data = [
        'start_time', 'end_time', 'talk_type', 'select_tags', 'content', 'imgs', 'voices', 'vedios', 'order_id', 'is_intention_change','add_infomation'
    ];

    /**
     * 新增新的交谈记录
     *
     * @param $data
     * @param Clue $clue
     * @param User $user
     * @return Talk
     * @throws Exception
     */

    public function addNewTalk($data, $clue, $user, $taskId = null,$theCarOrder = null)
    {
        //上传文件
        if ($_FILES) {
            foreach ($_FILES as $k => $file) {
                $aacToMp3 = ($k === 'voices');
                $files = $this->saveFiles($file, $k, $aacToMp3);
                $data[$k] = $files;
            }
        }
        
        //保存标签信息，逗号分隔
        if (isset($data['select_tags'])&& is_array($data['select_tags'])) {
            $data['select_tags'] = implode(',', $data['select_tags']);
        }
        //记录订车信息
        if(!empty($theCarOrder)) {
            if (Yii::$app->cache->get('addTalk'.Yii::$app->user->getId())) {
                $addInfo = json_decode(Yii::$app->cache->get('talk_change_' . Yii::$app->user->getId()), true) ?: [];
                $intention_des = Yii::$app->cache->get('intention_des'.$clue->id);
                $addInfo['意向等级'] = $intention_des . ' --> ' . Intention::findOne(6)->name;
                Yii::$app->cache->delete('intention_des'.$clue->id);
                $addInfo['定金'] = !empty($theCarOrder['deposit']) ? $theCarOrder['deposit'] :'--';
                $addInfo['预计交车时间'] = '--';
                Yii::$app->cache->set('talk_change_' . Yii::$app->user->getId(), json_encode($addInfo));
            }
        }
        $talk = new Talk();
        if (isset($data['is_type'])){
            $talk->is_type = $data['is_type'];
        }
        if ($talk->isNewRecord) {
            $talk->shop_id = $user->shop_id;
            $talk->salesman_id = $clue->salesman_id;
            $talk->clue_id = $clue->id;
            $talk->talk_date = date('Y-m-d');
            $talk->castomer_id = $clue->customer_id;
            $talk->create_time = $_SERVER['REQUEST_TIME'];
        }
        if(!isset($data['start_time']) || $data['start_time'] == -1 || $data['start_time'] == 0) {
            $data['start_time'] = time();
        }
        if(!isset($data['end_time']) || $data['end_time'] == -1 || $data['end_time']) {
            $data['end_time'] = time();
        }
        $attributeData = $this->getAttributeData($data, $this->_attribute_data);
        $talk->setAttributes($attributeData);

        //增加电话录音时间
        if(isset($data['voices'])) {
            $talk->voices_times = $data['voices_times'] ;
        }

        if(isset($data['task_to_phone']) && $data['task_to_phone']) {
            //添加下次电话任务
            TaskLogic::instance()->add($data['task_to_phone'], $clue->id, 1, $data);
        } else {
            if (isset($data['visit_time']) && $data['visit_time'] > 0) {
                $date = date('Y-m-d', $data['visit_time']);
                TaskLogic::instance()->add($date, $clue->id, 1, $data);
            }
        }

        if(isset($data['task_to_shop']) && $data['task_to_shop']) {
            //添加到店任务
            TaskLogic::instance()->add($data['task_to_shop'], $clue->id, 2);
        }
        if(isset($data['task_to_home']) && $data['task_to_home']) {
            //添加上门任务
            TaskLogic::instance()->add($data['task_to_home'], $clue->id, 3);
        }
        //有订单号 订车商谈
        if(!empty($theCarOrder['order_id'])) {
            $talk->order_id = $theCarOrder['order_id'];
        }
        if (!$talk->save()) {
            throw new Exception('线索更新失败', $talk->errors);
        }
        if(!$taskId && $talk && in_array($talk->talk_type, [5,6,7,8,9,10])) {
            if (in_array($talk->talk_type, [5, 6, 7]))
                $taskType = 2;
            else
                $taskType = 3;

            TaskLogic::instance()->addFinish($clue, $taskType, $user, $talk);
        }
        if($data['talk_type'] == 3) {
            $clue->view_times = $clue->view_times + 1;
            $clue->phone_view_times = $clue->phone_view_times + 1;
            $clue->last_view_time = $_SERVER['REQUEST_TIME'];
            $clue->save();
        }
        if(in_array($data['talk_type'], [5,6,7])) {
            $clue->view_times = $clue->view_times + 1;
            $clue->to_shop_view_times = $clue->to_shop_view_times + 1;
            $clue->last_view_time = $_SERVER['REQUEST_TIME'];
            $clue->save();
        }
        if(in_array($data['talk_type'], [8,9,10])) {
            $clue->view_times = $clue->view_times + 1;
            $clue->last_view_time = $_SERVER['REQUEST_TIME'];
            $clue->to_home_view_times = $clue->to_home_view_times + 1;
            $clue->save();
        }
        return $talk;
    }

    /**
     * 增加交谈记录
     *
     * @param array $customer
     * @param array $talkData 交谈信息
     * @param User $user
     * @param array $order 订单信息
     * @param null $taskId
     * @param null|array $theCarOrder
     * @return bool
     * @throws Exception
     */
    public function talkAdd($customer, $talkData, $user, $order, $taskId = null, $theCarOrder = null)
    {
        if(isset($talkData['task_to_intention']) && $talkData['task_to_intention']) {
            $customer['intention_level_id'] = $talkData['task_to_intention'];
        }
        $clue = Clue::findOne($customer['clue_id']);
        if (empty($clue)) {
            $this->errorCode = 400;
            $this->setError('未找到线索');
            return false;
        }

        if ($clue->status == 0) {
            $this->errorCode = 400;
            $this->setError('线索不能添加商谈记录');
            return false;
        }

        $customer['name'] = isset($customer['customer_name']) ? $customer['customer_name'] : $clue->customer_name;

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            //开启记录交谈中修改信息
            Yii::$app->cache->set('addTalk'.Yii::$app->user->getId(), true);

            //冗余字段,意向车型
            if ($customer['intention_level_id'] && $customer['intention_id'] != $clue->intention_id) {
                $obj = new CarBrandAndType();
                $clue->intention_des = $obj->getCarTypeNameByTypeId($customer['intention_id']);
            }
            //意向等级
            if ($customer['intention_level_id'] && $customer['intention_level_id'] != $clue->intention_level_id) {
                $clue->intention_level_des = Intention::findOne($customer['intention_level_id'])->name;
                $talkData['is_intention_change'] = 1;
            }else{
                $talkData['is_intention_change'] = 0;
            }
            if ($customer['is_star'] != $clue->is_star) {
                $clue->star_time = $_SERVER['REQUEST_TIME'];
            }
            //有战败标签，即战败
            if ($customer['fail_tags']) {
                $customer['fail_reason'] = FailTags::findOne($customer['fail_tags'])->name;
                $customer['fail_tags'] = strval($customer['fail_tags']);
                //战败后改变意向等级
                $clue->is_fail = 1;
                $clue->last_fail_time = $_SERVER['REQUEST_TIME'];

                if ($clue->status == 1) {
                    $talkData['talk_type'] = 13;
                }
                if ($clue->status == 2) {
                    $talkData['talk_type'] = 16;
                }
                //如果是订车客户战败，订车信息状态变为4
                if($clue->status == 2) {
                    $cancelOrder = Order::find()->where([
                        'clue_id' => $clue->id
                    ])->andWhere([
                        'NOT IN', 'status', [4,6]
                    ])->one();
                    if($cancelOrder){
                        $cancelOrder->status = 4;
                        $cancelOrder->save();
                    }
                }

                //战败更新提车任务表
                PutTheCar::updateTheCar($customer['clue_id'],3);

                /**
                 * edited by liujx 2017-07-31 战败投入公海 start :
                 *
                 * 代码分开
                 */
                GongHaiLogic::failIntoSeas($customer['fail_tags'], $customer['fail_reason'], $clue);
                // end;
            }

            $attribute = $this->getAttributeData($customer, ClueLogic::instance()->clueArr['all']);
            $clue->setAttributes($attribute);
            $clue->intention_id = $clue->intention_id ? : 0;

            //交车信息
            if (in_array($talkData['talk_type'], [7,10]) && $order) {
                $orderResult = OrderLogic::instance()->orderSave($order);
                if ($orderResult) {
                    $clue->status = 3;
                    $clue->intention_level_id = 8;
                    $clue->intention_level_des =  Intention::findOne(8)->name;
                }
            }

            //订车信息
            if (in_array($talkData['talk_type'], [6,9]) && !empty($theCarOrder)) {
                $check = Yii::$app->cache->get('intention_des'.$clue->id);
                if (empty($check)) {
                    Yii::$app->cache->set('intention_des' . $theCarOrder['clue_id'], $clue['intention_level_des']);
                }
                $orderResult = OrderLogic::instance()->orderTheCar($theCarOrder);
                if ($orderResult) {
                    $clue->status = 2;
                    $clue->intention_level_id = 6;
                    $clue->intention_level_des =  Intention::findOne(6)->name;
                }
            }

            $talk = $this->addNewTalk($talkData, $clue, $user, $taskId,$theCarOrder);
            $task = Task::findOne($taskId);

            if ($taskId && $task  && $task->is_cancel != 1) {//modify by wangdiao
                $task->start_time = $talk->start_time;
                $task->is_finish = 2;
                if (!$task->save()) {
                    throw new Exception('任务更新失败', $task->errors);
                }
            }

            if (!$clue->save()) {
                throw new Exception('线索更新失败', $clue->errors);
            }
            CustomerLogic::instance()->customerAdd($customer, 'customer');
            /**
             * edited by liujx 2017-7-05 start :
             *
             * 战败客户清除所有未完成的电话任务
             */
            // 有战败标签，即战败
            if ($customer['fail_tags']) {
                $date = date('Y-m-d');
                $sql = "DELETE FROM `crm_task` WHERE `is_cancel` <> 0 AND task_date>='{$date}' and `is_finish` <> 2 AND `clue_id` = ".$customer['clue_id'];
                \Yii::$app->db->createCommand($sql)->execute();
            }
            // end;

            $transaction->commit();
            //删除记录交谈中修改信息
            Yii::$app->cache->delete('talk_change_'.Yii::$app->user->getId());
            Yii::$app->cache->delete('addTalk'.Yii::$app->user->getId());
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * 获取商谈记录
     */
    public function getTalk($clue_id)
    {
      //  $intUserId = Yii::$app->user->identity->getId();

        //统计类型数据
        $list = Talk::find()->select('talk_type,count(talk_type)count')
            //->where("salesman_id={$intUserId} and clue_id={$clue_id}")
            ->where("clue_id={$clue_id}")
            ->groupBy('talk_type')
            ->asArray()
            ->all();

        //获取商谈记录列表
        $models = Talk::find()->where("clue_id={$clue_id}")
            ->orderBy('create_time desc')
            ->asArray()
            ->all();

        $data = $this->getTalkList($models);


        return [
            'models' => $data,
            'list' => $list,
        ];

    }

    /**
     * 交谈记录
     *
     * @param $pData
     * @param User $user
     * @return  array
     */
    public function getTalkLog($pData, $user)
    {
        if ($pData['type'] == 'month') {
            $model = Talk::find()->where([
                'salesman_id' => $user->id,
                'shop_id' => $user->shop_id
            ])->andWhere([
                'in', 'talk_type', [2,3,5,6,7,8,9,10]
            ])->andWhere([
                'like', 'DATE_FORMAT(talk_date,"%Y-%m")', $pData['date_time']
            ]);
        } else {
            $model = Talk::find()->where([
                'salesman_id' => $user->id,
                'talk_date' => $pData['date_time']
            ])->andWhere([
                'in', 'talk_type', [2,3,5,6,7,8,9,10]
            ]);
        }
        $_model = clone $model;
        $totalCount = $_model->count();
        $pagination = new Pagination(compact('totalCount'));

        $page = isset($pData['currentPage']) ? $pData['currentPage'] : 1;
        $perPage = isset($pData['perPage']) ? $pData['perPage'] : 20;
        $pagination->setPage($page -1);
        $pagination->setPageSize($perPage, true);

        $result= $model->limit(
            $pagination->getLimit()
        )->offset($pagination->getPage() * $pagination->pageSize)->orderBy([
            'create_time' => SORT_DESC
        ])->asArray()->all();
        $models = $this->getTalkList($result, true);
        $total = $this->total($pData, $user);
        $pages = $this->pageFix($pagination);
        return compact('total', 'models', 'pages');
    }

    /**
     * 看板交谈记录统计
     *
     * @param User $user
     * @return array
     */
    public function total($pData, $user)
    {
        //店铺平均
        $shopArr = ArrayHelper::map(Talk::find()->select(['salesman_id', 'count(id) as total'])->where([
            'shop_id' => $user->shop_id,
        ])->andWhere([
            'like', 'talk_date', $pData['date_time']
        ])->andWhere([
            'in', 'talk_type', [2,3,5,6,7,8,9,10]
        ])->groupBy('salesman_id')->asArray()->all(),'salesman_id','total');

        if (count($shopArr) == 0) {
            $shop = 0;
            $most = 0;
        }else {
            $shop = intval(round(array_sum($shopArr) / count($shopArr)));
            $most = intval(max($shopArr));
        }


        $self = isset($shopArr[$user->id]) ? intval($shopArr[$user->id]) : 0;

        return compact('self', 'shop', 'most');
    }

    /**
     * 交谈记录详情
     *
     * @param array $models
     * @param $ext
     * @return array $data
     */
    public function getTalkList($models, $ext = false)
    {

        $data = [];
        foreach ($models as $k => $model) {
            switch ($model['talk_type']) {
                case 1;
                    $data[$k]['title'] = '修改客户信息';
                    break;
                case 2;
                    $data[$k]['title'] = '客户来电';
                    break;
                case 3;
                    $data[$k]['title'] = '给客户去电';
                    break;
                case 4;
                    $data[$k]['title'] = '给改客户发短信';
                    break;
                case 5;
                    $data[$k]['title'] = '客户到店-商谈';
                    break;
                case 6;
                    $data[$k]['title'] = '客户到店-订车';
                    break;
                case 7;
                    $data[$k]['title'] = '客户到店-交车';
                    break;
                case 8;
                    $data[$k]['title'] = '客户上门-商谈';
                    break;
                case 9;
                    $data[$k]['title'] = '客户上门-订车';
                    break;
                case 10;
                    $data[$k]['title'] = '客户上门-交车';
                    break;
                case 13;
                    $data[$k]['title'] = '意向客户战败';
                    break;
                case 16;
                    $data[$k]['title'] = '订车客户战败';
                    break;
                case 20;
                    $data[$k]['title'] = '战败客户激活';
                    break;
                case 21;
                    $data[$k]['title'] = '休眠客户激活';
                    break;
                case 22;
                    $data[$k]['title'] = '订车客户换车';
                    break;
                case 23;
                    $data[$k]['title'] = '添加备注';
                    break;
                case 24;
                    $data[$k]['title'] = '顾问重新分配';
                    break;
                case 25;
                    $data[$k]['title'] = 'ERP终止合同—客户转为意向级';
                    break;
                case 26;
                    $data[$k]['title'] = 'ERP确认交车';
                    break;
                case 27;
                    $data[$k]['title'] = '客户订车—电商';
                    break;
            }
            if ($ext) {
                if(in_array($model['talk_type'], [2,3,5,6,7,8,9,10])) {
                    $clue = Clue::findOne($model['clue_id']);
                    if (!empty($clue)){
                        if ($clue->status == 1) {
                            $data[$k]['title'] .= '('.$clue->customer_name . ' ' . $clue->intention_des.')';
                        } else {
                            $order = Order::findOne($model['order_id']);
                            if($order) {
                                $data[$k]['title'] .= '('.$clue->customer_name . ' ' . $order->car_type_name.')';
                            } else {
                                $data[$k]['title'] .= '('.$clue->customer_name . ' ' . $clue->intention_des.')';
                            }
                        }
                    }
                }
            }
            $data[$k]['img'] = [];
            if (!empty($model['imgs'])) {//验证图片

                $img = explode(',', $model['imgs']);
                $data[$k]['img'] = $img;
            }

            $data[$k]['voices'] = "";
            if (!empty($model['voices'])) {//验证音频

                $data[$k]['voices'] = $model['voices'];
                $data[$k]['voices_times'] = intval($model['voices_times']);
            }

            if (!empty($model['add_infomation'])) {
                $info = json_decode($model['add_infomation'], true);
                $arr = [];
                foreach ($info as $key => $v) {
                    $arr[] = $key . '：' . $v;
                }
                $data[$k]['partInfo'] = $arr;

            } else {
                $data[$k]['partInfo'] = [];
            }
            if ($model['talk_type'] >= 5 && $model['talk_type'] <= 7) {
                $data[$k]['timeinfo'] = [
                    '进店时间：' . date("Y-m-d H:i", $model['start_time'])
//                    '离店时间：' . date('Y-m-d H:i', $model['end_time'])
                ];
            }else if($model['talk_type'] == 23){
                $data[$k]['timeinfo'] = [];
            } elseif ($model['talk_type'] >= 8 && $model['talk_type'] <= 10) {
                $data[$k]['timeinfo'] = [
                    '上门时间：' . date("Y-m-d H:i", $model['start_time'])
//                    '离开时间：' . date('Y-m-d H:i', $model['end_time'])
                ];

            }

            if (!empty($model['content']))
                $data[$k]['content'] = "商谈内容：" . $model['content'];
            else
                $data[$k]['content'] = '';

            $data[$k]['tag'] = [];

            if (!empty($model['select_tags']))
                $data[$k]['tag'] = $model['select_tags'];

            if (!empty($data[$k]['tag'])) {
                $data[$k]['tag'] = explode(',', $data[$k]['tag']);
                $objDataDic = new DataDictionary();//数据字典操作
                $data[$k]['tag_name'] = $objDataDic->getTagNamebyIds($data[$k]['tag']);
            }
            $data[$k]['create_time'] = $model['create_time'];

        }
        return $data;
    }

    /**
     * 用户
     *
     * @param $data
     * @param User $user
     *
     * @return boolean
     */
    public function addCall($data, $user)
    {
        $clue = Clue::find()->where([
            'salesman_id' => $user->id,
            'customer_phone' => $data['phone']
        ])->orderBy('status asc')->limit(1)->one();
        if(empty($clue)) {
            $this->setError('未找到该用户');
            $this->setErrorCode(4006);
            return false;
        }
        $talk = $this->addNewTalk([
            'talk_type' => 2,
            'start_time' => time(),
            'end_time' => time(),
        ], $clue, $user);
        if($talk){
            return true;
        } else {
            $this->setError('提交失败');
            $this->setErrorCode(4007);
            return false;
        }
    }
}
