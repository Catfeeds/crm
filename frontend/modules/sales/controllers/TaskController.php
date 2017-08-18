<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/3/6
 * Time: 16:35
 */

namespace frontend\modules\sales\controllers;


use frontend\modules\sales\models\Task;
use frontend\modules\sales\logic\TaskLogic;
use Yii;


/**
 * 任务控制器
 * Class TaskController
 * @package frontend\modules\v1\controllers
 */
class TaskController extends AuthController
{
    /**
     * 任务列表
     * task_type 1 - 电话任务 2 - 到店任务 3 - 上门任务
     * @return array | boolean
     */
    public function actionIndex()
    {
        $data = $this->getPData();

        $user = Yii::$app->user->identity;
        $task =  TaskLogic::instance()->task($data, $user);
        if(!$task) {
            Yii::$app->params['code']    = TaskLogic::instance()->getErrorCode();
            Yii::$app->params['message'] = TaskLogic::instance()->getError();
            return false;
        }
        return $task;
    }

    /**
     * 新增到店和上门和电话任务
     *
     * @return array
     */
    public function actionAdd()
    {
        $data = $this->getPData();
        if (!isset($data['clue_id']) && !$data['clue_id']) {
            return $this->paramError();
        }
        $taskType = isset($data['task_type']) ? $data['task_type'] : null;;
        $taskDate = isset($data['task_date']) ? $data['task_date'] : null;
        if (!in_array($taskType, [1, 2, 3]) || !$taskDate) {
            return $this->paramError();
        }
        $task = TaskLogic::instance()->add($taskDate, $data['clue_id'], $taskType);
        if (!$task) {
            //返回添加失败信息
            Yii::$app->params['code']    = TaskLogic::instance()->getErrorCode();
            Yii::$app->params['message'] = TaskLogic::instance()->getError();
            return [];
        }
        return [];
    }

    /*
     * 取消任务
     */
    public function actionCancel()
    {
        $pData = $this->getPData();
        if (empty($pData['task_id']) || empty($pData['fail_reason'])) {
            $this->paramError();return false;
        }
        $user = Yii::$app->user->identity;
        $task = Task::findOne($pData['task_id']);
        if ($user->getId() != $task->salesman_id) {
            Yii::$app->params['code']    = 400;
            Yii::$app->params['message'] = '无权限操作';
            return false;
        }
        if ($task->is_cancel == 1) {
            Yii::$app->params['code']    = 400;
            Yii::$app->params['message'] = '已经取消';
            return false;
        }
        $task->is_cancel = 1;
        $task->cancel_reason = $pData['fail_reason'];
        $task->is_finish = 1;
        if ($task->save()) {
            return true;
        }
        return false;
    }

    /**
     * 客户未到店
     */
    public function actionCancelTask() {
        $pData = $this->getPData();
        if (empty($pData["task_id"])) {
            $this->paramError();
        }
        $task = Task::findOne($pData['task_id']);
        $task->is_cancel = 1;
        if ($task->save()) {
            return true;
        }
        return false;

    }

    /**
     * 查看未完成任务
     *
     */
    public function actionView()
    {
        $data = $this->getPData();
        $task = Task::findOne($data['task_id']);
        if (!$task) {
            Yii::$app->params['code']    = 400;
            Yii::$app->params['message'] = '未找到相关任务';
            return [];
        }
        return $task;
    }

    /**
     * 任务列表
     */
    public function actionGetInfo()
    {

        $data    = $this->getPData();
        $clue_id = isset($data['clue_id']) ? $data['clue_id'] : null;

        if (empty($clue_id)) {
            return $this->paramError();
        }

        $task = new TaskLogic();
        return $task->getInfo($clue_id);
    }

    /**
     * 手动完成任务
     *
     * @return bool
     */
    public function actionUpdatePhoneTask()
    {
        $pData = $this->getPData();
        if (!isset($pData['task_id']) || !isset($pData['start_time']) || !isset($pData['end_time'])) {
            $this->paramError();
        }
        $user = Yii::$app->user->identity;
        $task = Task::findOne($pData['task_id']);
        if ($user->getId() != $task->salesman_id) {
            Yii::$app->params['code']    = 400;
            Yii::$app->params['message'] = '无权限操作';
            return false;
        }
        if ($task->is_cancel == 1) {
            Yii::$app->params['code']    = 400;
            Yii::$app->params['message'] = '已经取消';
            return false;
        }
        $task->start_time = $pData['start_time'] ? : $_SERVER['REQUEST_TIME'];
        $task->end_time   = $pData['end_time'] ? : $_SERVER['REQUEST_TIME'];
        $task->is_finish  = 2;
        if ($task->save()) {
            return true;
        }
        return false;
    }


    /**
     * 2017-07-04
     * 删除未完成的电话任务 更新最新的意向等级电话 只调用一次
     */
    public function actionUpdateTasks() {
        set_time_limit(0);//无超时
        ini_set('memory_limit','-1');//设置内存
        $sql = "select id,intention_level_id from crm_clue where intention_level_id>0 and salesman_id>0 and is_fail=0 and `status` not in(0,2,3)";
        $list = Yii::$app->db->createCommand($sql)->queryAll();
        $task = new \common\logic\TaskLogic();
        foreach ($list as $v) {
            $task->cancelNoFinishIntentionTask($v['id']);
            $task->newIntentionAddTask($v['id'],$v['intention_level_id']);
        }
    }
}