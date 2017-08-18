<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/3/7
 * Time: 17:07
 */

namespace frontend\modules\sales\logic;


use common\models\InputType;
use common\models\Order;
use frontend\modules\sales\models\Clue;
use common\models\Customer;
use frontend\modules\sales\models\Talk;
use frontend\modules\sales\models\Task;
use common\models\User;
use yii\data\Pagination;
use Yii;
use yii\db\Exception;

/**
 * 任务相关逻辑
 * Class TaskLogic
 * @package frontend\modules\v1\logic
 */
class TaskLogic extends BaseLogic
{
    /**
     * 添加任务
     *
     * @param string $taskDate
     * @param int $clueId int
     * @param int $taskType 1 电话任务 2 到店任务 3 上门任务
     * @param array $params 其他参数
     * @return boolean
     * @throws Exception
     */
    public function add($taskDate, $clueId, $taskType = 1, $params = [])
    {
        $user = \Yii::$app->user->identity;
        $task = new Task();
        $clue = Clue::findOne($clueId);
        if (empty($clue) || empty($clue->shop_id) || empty($clue->customer_id) || empty($user->getId())) {
            $this->errorCode = 400;
            $this->setError('未找到线索');
            return false;
        }
        $task->shop_id = $clue->shop_id;
        $task->salesman_id = $user->getId();
        $task->customer_id = $clue->customer_id;
        $task->clue_id = $clueId;
        $task->task_type = $taskType;
        $task->is_cancel = 0;
        $task->task_time = $_SERVER['REQUEST_TIME'];
        $task->is_finish = 1;
        $task->task_from = '手动添加';
        $task->task_des = date('m月d日').'商谈时添加';// 任务描述
        $task->task_date = $taskDate;

        // edited by liujx 2017-07-28 添加是否需要回访信息 start :
        if (!empty($params['visit_time']) && $params['visit_time'] > 0) {
            // 传递了回访时间，表示需要回访
            $task->visit_time = (int)$params['visit_time'];
            $task->is_visit = Task::IS_VISIT_YES;
            $task->next_handle_time = $task->visit_time; // 处理时间为下次回访时间
        }
        // end

        if ($task->save()) {
            return true;
        } else {
            throw new Exception('添加任务失败', $task->errors);
        }
    }


    /**
     * 到店上门添加已完成任务
     *
     * @param Clue $clue int
     * @param $taskType 1 电话任务 2 到店任务 3 上门任务
     * @param User $user
     * @param Talk $talk
     * @return boolean
     * @throws Exception
     */
    public function addFinish($clue, $taskType, $user, $talk)
    {
        $task = new Task();
        if (empty($clue)) {
            $this->errorCode = 400;
            $this->setError('未找到线索');
            return false;
        }
        $task->shop_id = $user->shop_id;
        $task->salesman_id = $user->getId();
        $task->customer_id = $clue->customer_id;
        $task->clue_id = $clue->id;
        $task->task_type = $taskType;
        $task->is_cancel = 0;
        $task->task_time = $_SERVER['REQUEST_TIME'];
        $task->start_time = $talk->start_time ? : $_SERVER['REQUEST_TIME'];
        $task->end_time = $talk->end_time ? : $_SERVER['REQUEST_TIME'];
        $task->is_finish = 2;
        $task->task_from = '自建';
        $task->task_date = date('Y-m-d');
        if ($task->save()) {
            return true;
        } else {
            throw new Exception('添加任务失败', $task->errors);
        }
    }

    /**
     * 根据任务ID 获取任务信息
     * @param $taskId
     * @return object
     */
    public function getTask($taskId)
    {
        return Task::findOne($taskId);
    }

    /**
     * 任务
     *
     * @param $data string
     * @param User $user 用户对象
     * @return array | boolean
     */
    public function task($data, $user)
    {
        $taskType = isset($data['task_type']) ? $data['task_type'] : null;
        $taskDate = isset($data['task_date']) ? $data['task_date'] : null;
        if (!$taskType || !$taskDate) {
            $this->errorCode = 400;
            $this->setError('缺少必填字段');
            return false;
        }

        $arrWhere = [
            'and',
            ['=', 'salesman_id', $user->id],
	        ['=', 'shop_id', $user->shop_id ],
            ['=', 'task_type', $taskType],
            ['=', 'task_date', $taskDate],
        ];

        // 电话任务只显示未完成的
        if ($taskType == 1) {
            $arrWhere[] = ['=', 'is_finish', 1];
            $arrWhere[] = ['=', 'is_cancel', 0];
            // edited by liujx 2017-08-10 电话任务查询问题bug修改 start :
            $query = Task::find()
                ->from(Task::tableName() . ' t')
                ->innerJoin(Clue::tableName() . ' AS c', 't.clue_id = c.id')
                ->where([
                    'and',
                    ['=', 't.salesman_id', $user->id],
                    ['=', 't.shop_id', $user->shop_id],
                    ['=', 't.task_type', $taskType],
                    ['=', 't.task_date', $taskDate],
                    ['=', 't.is_finish', 1],
                    ['=', 't.is_cancel', 0],
                ]);
            // end
        } else {
            $query = Task::find()->where($arrWhere);
        }

        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $totalCount]);
        $page = isset($data['page']) ? $data['page'] : 1;
        $pagination->setPage($page -1);
        $task = $query->limit(
            $pagination->getLimit()
        )->offset($pagination->getPage() * $pagination->getLimit())->all();

        if ($totalCount > 0) {
            switch ($taskType) {
                case 2:
                    $data = $this->taskToShop($task);
                    break;
                case 3:
                    $data = $this->taskToHome($task);
                    break;
                case 1:
                    $data = $this->taskPhone($task);
            }
            return [
                'models' => $data,
                'pages' => $this->pageFix($pagination),
            ];
        }
        return [];
    }

    /**
     * 到店任务
     *
     * @param $models object 任务
     * @return array
     */
    public function taskToShop($models)
    {

        $data = [];
        foreach ($models as $k => $model) {
            $customer = Customer::findOne($model->customer_id);
            $clue     = Clue::findOne($model->clue_id);
            //未找到客户和线索跳过
            if (empty($customer) || empty($clue)) {
                continue;
            }
            $taskFrom = null;
            if ($clue->clue_input_type > 0)
                $taskFrom = InputType::findOne($clue->clue_input_type)->name;

            if ($model->is_cancel == 1){//未到店
                $data['not_finish'][] = [
                    'task_id' => $model->id,
                    'clue_id' => $model->clue_id,
                    'customer_id' => $model->customer_id,
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone,
                    'task_from' => $taskFrom,
                    'clue_source'         => intval($clue->clue_source),
                    'intention_level_id' => intval($clue->intention_level_id) ,
                    'intention_id'       => intval($clue->intention_id) ,
                    'last_view_time' => intval($clue->last_view_time),
                    'intention_level_des' => strval($clue->intention_level_des),
                    'intention_des' => strval($clue->intention_des),
                    'task_type' => intval($model->task_type),
                    'start_time' => strval($model->start_time),
                    'end_time' => strval($model->end_time),
                    'status' => intval($clue->status),
                    'address' => intval($customer->address),
                ];

            } else if ($model->is_finish == 2) {//已到店

                $data['is_finish'][] = [
                    'task_id' => $model->id,
                    'clue_id' => $model->clue_id,
                    'customer_id' => $model->customer_id,
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone,
                    'task_from' => $taskFrom,
                    'clue_source'         => intval($clue->clue_source),
                    'intention_level_id' => intval($clue->intention_level_id) ,
                    'intention_id'       => intval($clue->intention_id) ,
                    'last_view_time' => intval($clue->last_view_time),
                    'intention_level_des' => strval($clue->intention_level_des),
                    'intention_des' => strval($clue->intention_des),
                    'task_type' => intval($model->task_type),
                    'start_time' => strval($model->start_time),
                    'end_time' => strval($model->end_time),
                    'status' => intval($clue->status),
                    'address' => intval($customer->address),

                ];


            } else if ($model->is_finish == 1) {//预约
                $order_id = '';
                if ($clue->status == 2){
                    $order_id = Order::find()->select('order_id')->where("clue_id={$model->clue_id}")->asArray()->one()['order_id'];
                }
                $data['appointment'][] = [
                    'task_id' => $model->id,
                    'clue_id' => $model->clue_id,
                    'customer_id' => $model->customer_id,
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone,
                    'task_from' => $taskFrom,
                    'clue_source'         => intval($clue->clue_source),
                    'intention_level_id' => intval($clue->intention_level_id) ,
                    'intention_id'       => intval($clue->intention_id) ,
                    'last_view_time' => intval($clue->last_view_time),
                    'intention_level_des' => strval($clue->intention_level_des),
                    'intention_des' => strval($clue->intention_des),
                    'task_type' => intval($model->task_type),
                    'start_time' => strval($model->start_time),
                    'end_time' => strval($model->end_time),
                    'status' => intval($clue->status),
                    'address' => intval($customer->address),
                    'order_id' => $order_id,
                ];

            }
        }
        return $data;
    }

    /**
     * 上门任务
     *
     * @param $models object 任务对象
     * @return array
     */
    public function taskToHome($models)
    {
        $data = [];
        foreach ($models as $k => $model) {
            $customer = Customer::findOne($model->customer_id);
            $clue     = Clue::findOne($model->clue_id);
            //未找到客户和线索跳过
            if (empty($customer) || empty($clue)) {
                continue;
            }
            if ($model->is_cancel == 1) {//未上门
                $data['not_finish'][] = [
                    'task_id' => $model->id,
                    'clue_id' => $model->clue_id,
                    'customer_id' => $model->customer_id,
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone,
                    'clue_source'         => intval($clue->clue_source),
                    'intention_level_id' => intval($clue->intention_level_id) ,
                    'intention_id'       => intval($clue->intention_id) ,
                    'last_view_time' => intval($clue->last_view_time),
                    'intention_level_des' => strval($clue->intention_level_des),
                    'intention_des' => strval($clue->intention_des),
                    'task_type' => intval($model->task_type),
                    'start_time' => strval($model->start_time),
                    'end_time' => strval($model->end_time),
                    'customer_address' => strval($customer->address),
                    'status' => intval($clue->status),
                    'address' => intval($customer->address),
                ];
            }
            else if ($model->is_finish == 2) {//已上门
                $data['is_finish'][] = [
                    'task_id' => $model->id,
                    'clue_id' => $model->clue_id,
                    'customer_id' => $model->customer_id,
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone,
                    'clue_source'         => intval($clue->clue_source),
                    'intention_level_id' => intval($clue->intention_level_id) ,
                    'intention_id'       => intval($clue->intention_id) ,
                    'last_view_time' => intval($clue->last_view_time),
                    'intention_level_des' => strval($clue->intention_level_des),
                    'intention_des' => strval($clue->intention_des),
                    'task_type' => intval($model->task_type),
                    'start_time' => strval($model->start_time),
                    'end_time' => strval($model->end_time),
                    'customer_address' => strval($customer->address),
                    'status' => intval($clue->status),
                    'address' => intval($customer->address),
                ];
            } else if ($model->is_finish == 1){

                $order_id = '';
                if ($clue->status == 2){
                    $order_id = Order::find()->select('order_id')->where("clue_id={$model->clue_id}")->asArray()->one()['order_id'];
                }
                $data['appointment'][] = [
                    'task_id' => $model->id,
                    'clue_id' => $model->clue_id,
                    'customer_id' => $model->customer_id,
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone,
                    'clue_source'         => intval($clue->clue_source),
                    'intention_level_id' => intval($clue->intention_level_id) ,
                    'intention_id'       => intval($clue->intention_id) ,
                    'last_view_time' => intval($clue->last_view_time),
                    'intention_level_des' => strval($clue->intention_level_des),
                    'intention_des' => strval($clue->intention_des),
                    'task_type' => intval($model->task_type),
                    'start_time' => strval($model->start_time),
                    'end_time' => strval($model->end_time),
                    'customer_address' => strval($customer->address),
                    'status' => intval($clue->status),
                    'address' => intval($customer->address),
                    'order_id' => $order_id,
                ];

            }
        }
        return $data;
    }

    /**
     * 电话任务
     *
     * @param $models object 任务
     * @return array
     */
    public function taskPhone($models)
    {
        if (empty($models)) {
            return [];
        }
        //当天日期
        $date = date('Y-m-d');
        $data = [];
        foreach ($models as $k => $model) {
            /* @var $model \common\models\Task */
            $customer = Customer::findOne($model->customer_id);
            $clue     = Clue::findOne($model->clue_id);
            //未找到客户和线索跳过
            if (empty($customer) || empty($clue)) {
                continue;
            }

            //只显示未完成的数据  已完成和已取消的数据不展示
            if ($model->is_finish != 1 || $model->is_cancel == 1) {
                continue;
            }

            $clue_input_type_name = InputType::findOne($clue->clue_input_type);
            if (empty($clue_input_type_name->name)){
                $taskFrom = '';
            }else{
                $taskFrom = $clue_input_type_name->name;
            }

            //如果查询当天及以后日期数据 页面展示为待完成$is_wait = 1  如果查询昨天及以前数据  页面展示为未完成数据$is_wait = 0
            if($model->task_date >= $date){
                $is_wait = 1;
            }else{
                $is_wait = 0;
            }

            $data[] = [
                'task_id'             => $model->id,
                'clue_id'             =>$model->clue_id,
                'customer_id'         => $model->customer_id,
                'customer_name'       => $customer->name,
                'customer_phone'      => $customer->phone,
                'task_from'           => $taskFrom,
                'last_view_time'      => intval($clue->last_view_time),
                'intention_level_des' => strval($clue->intention_level_des) ,
                'clue_source'         => intval($clue->clue_source),
                'intention_level_id' => intval($clue->intention_level_id) ,
                'intention_id'       => intval($clue->intention_id) ,
                'intention_des'       => strval($clue->intention_des) ,
                'clue_input_type'     => intval($clue->clue_input_type),
                'task_type'           => intval($model->task_type),
                'phone_view_times'    => intval($clue->phone_view_times),
                'to_home_view_times'  => intval($clue->to_home_view_times),
                'to_shop_view_times'  => intval($clue->to_shop_view_times),
                'status' => intval($clue->status),
                'address' => intval($customer->address),
                'task_des'           => $model->task_des,
                'task_des_color'     => intval($model->task_des_color),
                'is_wait'     => $is_wait,
                // edited by liujx 2017-07-28 通过是否回访判断是否线索回访标签 start :
                'visit_time' => $model->is_visit == Task::IS_VISIT_YES ? date('H:i', $model->visit_time).'回访' : '',
                // end;
            ];
        }

        return $data;
    }

    /**
     * 任务记录
     * @param $clue_id 客户id
     * @return array
     */
    public function getInfo($clue_id)
    {
        $models = Task::find()->select(['task_from', 'task_time', 'is_cancel', 'cancel_reason', 'is_finish', 'task_date','task_des'])
            ->where("clue_id={$clue_id}")->andWhere([
                'task_type' => 1
            ])->orderby('task_date asc')
            ->asArray()
            ->all();


        $data = [];
        foreach ($models as $k => $model) {

            $data[$k]['task_from'] = strval(!empty($model['task_des']) ? $model['task_des'] :$model['task_from']);
            $data[$k]['task_time'] = intval(strtotime($model['task_date']));
            $data[$k]['is_cancel'] = intval($model['is_cancel']);
            $data[$k]['is_finish'] = intval($model['is_finish']);
            $data[$k]['cancel_reason'] = strval($model['cancel_reason']);//add by 王雕， 增加取消原因的返回
        }

        $des = Clue::findOne($clue_id);
        return [
            'models' => $data,
            'intention_level_des' => $des->intention_level_des,
        ];


    }
}