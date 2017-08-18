<?php
namespace console\logic;

use common\models\Task;
use Yii;

class UnfinishedTelTaskLogic extends BaseLogic
{
    //每日凌晨把当天未完成电话任务转移到今天
    public function index()
    {
        //当天日期
        $date = date('Y-m-d');
        $yesterday = date('Y-m-d',strtotime("- 1 day"));
        $yesterday2 = date('n月j日',strtotime("- 1 day"));

        $task_time = strtotime("+ 9 hours");//当日上午九点时间

        //查询前一天未完成电话任务
        $task_list = Task::find()->select("shop_id,clue_id,customer_id,salesman_id")
            ->where(['and',
                ['=','is_cancel',0],
                ['=','is_finish',1],
                ['=','task_type',1],
                ['=','task_date',$yesterday],
            ])
            ->asArray()
            ->all();

        //添加基础数据
        foreach ($task_list as $key=>$value)
        {
            $task_list[$key]['task_date'] = $date;
            $task_list[$key]['task_time'] = $task_time;
            $task_list[$key]['is_finish'] = 1;
            $task_list[$key]['task_from'] = '昨天未完成任务转接';
            $task_list[$key]['task_des'] = $yesterday2.'未完成任务重新生成';
            $task_list[$key]['is_cancel'] = 0;
            $task_list[$key]['task_type'] = 1;
            $task_list[$key]['task_des_color'] = 1;
        }

        //插入多条新增电话任务
        Yii::$app->db->createCommand()
            ->batchInsert(Task::tableName(),
                ['shop_id','clue_id','customer_id','salesman_id','task_date','task_time','is_finish','task_from','task_des','is_cancel','task_type','task_des_color'],
                $task_list)
            ->execute();

        /*
         * 可能导致主键不连续情况
        $sql = "insert into crm_task(shop_id,clue_id,customer_id,salesman_id,task_date,task_time,is_finish,task_from,is_cancel,task_type,task_des)
                select shop_id,clue_id,customer_id,salesman_id,'{$date}',{$time_task},0,'昨日未完成电话任务',is_cancel,task_type,task_des from crm_task
                where is_cancel = 0 and is_finish = 1 and task_type = 1 and task_time > $time_start and task_time < $time_end";

        Yii::$app->db->createCommand($sql)->execute();
        */
    }
}
?>