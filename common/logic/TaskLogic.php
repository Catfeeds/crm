<?php
/**
 * 任务逻辑层 - 主要是添加任务功能
 * 作    者：王雕
 * 功    能：任务逻辑层 - 主要是添加任务功能
 * 修改日期：2017-3-12
 */
namespace common\logic;
use common\models\Task;
use \common\models\Intention;
use \common\models\Clue;
use yii;
class TaskLogic
{
    /**
     * 功    能：添加意向等级的时候分配任务
     * 参    数：$clueId        int     线索id
     *         ：$intentionId   int     意向等级id
     * 返    回：$rtn           int     添加的电话任务条数
     * 作    者：王雕
     * 修改日期：2017-3-22
     */
    public function newIntentionAddTask($clueId, $intentionId)
    {
        $rtn = 0;
        $intentionModel = Intention::findOne(['id' => $intentionId]);
        if($intentionModel)//意向等级存在
        {
            //根据线索id获取信息
            $clueModel = Clue::findOne(['id' => $clueId]);
            if($clueModel && $clueModel->salesman_id > 0 && $clueModel->shop_id > 0 && $clueModel->customer_id > 0)
            {
                $arrTask = [];
                $arrPublic = [
                    'shop_id' => $clueModel->shop_id,
                    'clue_id' => $clueId,
                    'customer_id' => $clueModel->customer_id,
                    'salesman_id' => $clueModel->salesman_id,
                    'is_finish' => 1,//1 - 未完成  2 - 已完成
                    'task_from' => $intentionModel->name . '级客户自动分配',
                    'is_cancel' => 0,
                    'task_type' => 1,
                ];
                //是否有当天的电话任务
                if($intentionModel->has_today_task)
                {
                    $arrTask[] = [
                        'task_date' => date('Y-m-d'),
                        'task_time' => time(),
                        'task_des' => $intentionModel->name . '级当天电话任务'
                    ];
                }
                for($i = 1; $i <= $intentionModel->total_times; $i++)
                {
                    $nextDate = $i * $intentionModel->frequency_day;//按照频率计算下次任务的时间
                    $arrTask[] = [
                        'task_date' => date('Y-m-d', strtotime("+ $nextDate day")),
                        'task_time' => strtotime("+{$nextDate} day"),
                        'task_des' => $intentionModel->name . '级第' . $i .'次电话任务'
                    ];
                }
                //批量插入
                $arrItems = [];//要插入的字段信息
                foreach($arrTask as &$val)
                {
                    $val = array_merge($val, $arrPublic);
                    empty($arrItems) && $arrItems = array_keys($val);
                }
                $rtn = Yii::$app->db->createCommand()->batchInsert(Task::tableName(), $arrItems, $arrTask)->execute();
            }
        }
        return $rtn;
    }
    
    /**
     * 功    能：意向等级变化的时候电话任务更新
     * 参    数：$clueId            int     线索id
     *         ：$oldIntentionId    int     原来的意向等级
     *         ：$newIntentionId    int     新的意向等级
     * 返    回：$rtn               int     添加的电话任务条数
     * 作    者：王雕
     * 修改日期：2017-3-22
     */
    public function exchangeIntentionUpdateTask($clueId, $oldIntentionId, $newIntentionId)
    {
        $rtn = 0;
        if($oldIntentionId != $newIntentionId)
        {
            $clueModel = Clue::findOne(['id' => $clueId]);
            $intentionModel = new Intention();
            $namesTmp = $intentionModel->find()->select('id, name')->where(['in','id', [$oldIntentionId, $newIntentionId]])->asArray()->all();
            foreach($namesTmp as $val)
            {
                $names[$val['id']] = $val['name'];
            }
            //数据有效，且意向等级真的变化了
            if($clueModel && isset($names[$oldIntentionId]) && isset($names[$newIntentionId]))
            {
                $cancelReason = "意向等级变换({$names[$oldIntentionId]} => {$names[$newIntentionId]})";
                $this->cancelNoFinishIntentionTask($clueId, $cancelReason);
                $rtn = $this->newIntentionAddTask($clueId, $newIntentionId);
            }
        }
        return $rtn;
    }
    
    /**
     * 功    能：取消意向等级对应的未完成的任务 - 删除掉
     * 参    数：$clueId        int     线索id
     *         ：$cancelReason  string  取消的原因 - 文案描述
     * 返    回：$rtn           int     取消的电话任务的条数
     * 作    者：王雕
     * 修改日期：2017-3-22
     */
    public function cancelNoFinishIntentionTask($clueId, $cancelReason=null)
    {
        //删除所有时间为今天以及之后的未完成的电话任务，不仅仅只删除原来的意向等级电话任务
        $arrWhere = [
            'and',
            ['=', 'clue_id', $clueId],
            ['<>', 'is_finish', 2],//未完成的（2 为已完成）
            ['<>', 'is_cancel', 1],//被取消的电话也不删除
            ['=', 'task_type', 1],//电话任务
            ['>=', 'task_date', date('Y-m-d')],//今天以及今天之后的任务才删除，今天的不删除
        ];
        $rtn = Yii::$app->db->createCommand()->delete(Task::tableName(), $arrWhere)->execute();
        return $rtn;
    }
    
    /**
     * 功    能：创建一条任务
     * 参    数：$data          array   创建的任务的内容
     * 返    回：无
     * 作    者：王雕
     * 修改日期：2017-3-22
     */
    public function addTask($data)
    {
        if(isset($data['shop_id']) && $data['shop_id'] &&
            isset($data['clue_id']) && $data['clue_id'] &&
            isset($data['customer_id']) && $data['customer_id'] && 
            isset($data['salesman_id']) && $data['salesman_id'])
        {
            $task = new Task();
            $task->setAttributes($data);
            return $task->save();
        }
        return false;
    }
}