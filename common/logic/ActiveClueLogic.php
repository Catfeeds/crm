<?php

namespace common\logic;


use common\models\Clue;
use common\models\Talk;
use common\models\Task;
use common\models\User;
use Yii;

class ActiveClueLogic
{
    /**
     * 根据门店、时间获取该时间段未联系客户
     * @param $shop_id
     * @param $start_day
     * @param $end_day
     * @return mixed
     */
    public function unconnectList($shop_id,$start_day,$end_day)
    {

        //根据日期计算时间
        $start_time = strtotime(date('Y-m-d')) - 3600*24*$start_day;
        $end_time = strtotime(date('Y-m-d')) - 3600*24*$end_day;

        //查询线索数据
        $model = new Clue();
        $list = $model->find()->select('id,salesman_id,customer_name,intention_des,salesman_name')
            ->where(['OR',['between','last_view_time',$start_time,$end_time],['last_view_time'=>null]])
            ->andWhere(['=','is_fail',0])
            ->andWhere(['=','status',1])
            ->andWhere(['=','shop_id',$shop_id])
            ->asArray()
            ->all();

        $list_id = array_column($list,'id');

        //查询有未完成任务的线索
        $task_model = new Task();
        $diff_id_list = $task_model->find()->select('clue_id')
            ->where(['in','clue_id',$list_id])
            ->andWhere(['>=','task_date',date("Y-m-d")])
            ->andWhere(['=','is_cancel',0])
            ->asArray()
            ->all();

        $diff_id_arr = array_column($diff_id_list,'clue_id');

        //去除有未完成任务线索、处理数据
        foreach ($list as $k_list => $v_list){
            if(in_array($v_list['id'],$diff_id_arr)){
                unset($list[$k_list]);
            }else{
                $list[$k_list]['id'] = (int)$v_list['id'];
                $list[$k_list]['salesman_id'] = (int)$v_list['salesman_id'];
                $list[$k_list]['customer_name'] = (string)$v_list['customer_name'];
                $list[$k_list]['intention_des'] = (string)$v_list['intention_des'];
                $list[$k_list]['salesman_name'] = (string)$v_list['salesman_name'];
            }
        }

        //返回数据
        if($list){
            $list = array_merge($list,array());
            $data['models'] = $list;
        }else{
            $data['models'] = array();
        }
        $data['pages'] = [
            'totalCount' => count($list),
            'pageCount' => 1,
            'currentPage' => 1,
            'perPage' => count($list),
        ];
        return $data;
    }

    public function activeClue($shop_id,$clue_id,$salesman_id,$who_assign_id,$type = ''){

        $salesman = User::findOne($salesman_id);
        $salesman_name = $salesman->name;

        $who_assign = User::findOne($who_assign_id);
        $who_assign_name = $who_assign->name;
        if(!$salesman){
            return false;
        }
        $model = new Clue();
        $clue = $model->find()->where(['=','id',$clue_id])->andWhere(['=','shop_id',$shop_id])->one();

        if($clue){
            $clue->salesman_id = $salesman_id;
            $clue->who_assign_id = $who_assign_id;
            $clue->salesman_name = $salesman_name;
            $clue->who_assign_name = $who_assign_name;
        }else{
//            $this->echoData(400,'数据不存在');
        }

        //获取新任务所需数据
        $customer_id = $clue->customer_id;
        $date = date("Y-m-d");
        $time = strtotime($date);

        //保存数据
        $clue->save();
        //取消原店员与该线索线索任务（可以激活到原店员）
        Yii::$app->db->createCommand("update crm_task set is_cancel = 1,cancel_reason = '重新激活线索' 
                                          where shop_id = {$shop_id} and clue_id = {$clue_id} and is_finish = 1")->execute();

        //判断当前是否是重新分配以及当前线索是否是线索状态  如果是重新分配且为线索状态 不添加电话任务
        if($clue->status == 0 && $type == 'reassign'){
            return true;
        }

	if(empty($clue->shop_id) || empty($clue->customer_id) || empty($clue->salesman_id)){
		return true;//无顾问 无门店 无客户的 不生成电话任务
	}

        //新建任务
        $task = new Task();
        $task->shop_id = $shop_id;
        $task->clue_id = $clue_id;
        $task->customer_id = $customer_id;
        $task->salesman_id = $salesman_id;
        $task->task_date = $date;
        $task->task_time = $time;
        $task->task_from = '店长主动分配';
        $task->task_type = 1;
        $task->is_cancel = 0;
        $task->is_finish = 1;
        $task->task_des = date('m月d日').$who_assign_name.'分配';
        if($task->save()){
            //推送通知   推送电话任务
//            $noticeTemplet = new NoticeTemplet();
//            $noticeTemplet->telephoneTaskNotice($who_assign_id, $salesman_id ,1);
//            $noticeTemplet->reassignReminderNotice($who_assign_id, $salesman_id ,1,$clue_id);
            return true;
        }else{
            return false;
        }
    }
}
?>