<?php
namespace common\logic;

use common\models\Clue;
use common\models\InputType;
use common\models\PutTheCar;
use common\models\Task;
use common\models\User;

class AssignClueLogic
{
    /**
     * 根据门店id查询未分配线索
     * @param $shop_id
     * @return mixed
     */
    public function unassignList($shop_id)
    {
        //获取需分配线索
        $model = new Clue();

        $list = $model->find()->select('id,customer_name,customer_phone,intention_des,clue_source,clue_input_type')->where(['=','shop_id',$shop_id])->andWhere(['=','is_assign',0])->asArray()->all();

        //获取渠道列表 并处理数组
        $input_type = InputType::find()->where(['=','status',1])->asArray()->all();
        $input_type_new = array();
        foreach ($input_type as $value){
            $input_type_new[$value['id']] = $value;
        }

        //获取客户来源数据字典
        $obj = new DataDictionary();
        //处理数据
        if($list){
            foreach ($list as $k_list=>$v_list){
                $list[$k_list]['id'] = (int)$v_list['id'];
                $list[$k_list]['customer_name'] = (string)$v_list['customer_name'];
                $list[$k_list]['customer_phone'] = (string)$v_list['customer_phone'];
                $list[$k_list]['intention_des'] = (string)$v_list['intention_des'];
                $list[$k_list]['clue_source'] = (int)$v_list['clue_source'];
                $list[$k_list]['clue_source_name'] =  (string)$obj->getSourceName($list[$k_list]['clue_source']);;
                $list[$k_list]['clue_input_type_name'] =  isset($input_type_new[$v_list['clue_input_type']]['name']) ? $input_type_new[$v_list['clue_input_type']]['name'] : '--';
                unset($list[$k_list]['clue_input_type']);
            }
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

        //返回数据
        return $data;
    }

    /**
     * 重新分配线索   暂时和激活线索代码一致
     * @param $shop_id
     * @param $clue_id
     * @param $salesman_id
     * @param $who_assign_id
     * @return bool
     */
    public function reassignClue($shop_id,$clue_id,$salesman_id,$who_assign_id,$source = ''){

        $clue_id = intval($clue_id);
        $salesman_id = intval($salesman_id);


//        $salesman = User::find()->select('name,phone')->where(['=','id',$salesman_id])->andWhere(['=','is_delete',0])->asArray()->one();
//        if(!$salesman){
//            return false;
//        }
//        $salesman_name = $salesman['name'];

        $salesman = User::findOne($salesman_id);
        $salesman_name = $salesman->name;

        /* @var $clue \common\models\Clue */
        $clue = Clue::find()->where(['=','id',$clue_id])->one();

        $customer_id = $clue->customer_id;
        $date = date("Y-m-d");
        $time = strtotime($date);

        $assignUser = User::findOne($who_assign_id);
        $clue->salesman_id = $salesman_id;
        $clue->salesman_name = $salesman_name;
        $clue->who_assign_id = $who_assign_id;
        $clue->who_assign_name = $assignUser->name; //分配人姓名
        $clue->assign_time = time();
        $clue->is_assign = 1;

        // edited by liujx 2017-08-03 重新分配后，提车任务也要更新顾问信息 start :
        if ($clue->save()) {
            PutTheCar::handleOriginalConsultantLeft($clue->id, $clue->salesman_id, $clue->salesman_name);
        }

        // end

        //取消原店员与该线索线索任务（可以激活到原店员）
        \Yii::$app->db->createCommand("update crm_task set is_cancel = 1,cancel_reason = '重新分配线索' 
                                          where  clue_id = {$clue_id} and is_finish = 1")->execute();

        //判断当前线索是否是线索状态 如果是线索状态不添加电话任务
        //无人跟进客户 重新分配不添加电话任务 17-06-06
        if($clue->status == 0 || $source == 'nofollow'){
            return true;
        }
        
	if(empty($clue->shop_id) || empty($clue->customer_id) || empty($clue->salesman_id)){
		return true;//无顾问 无门店 无客户的 不生成电话任务
	}

        //新建任务
        $task = new Task();
        $task->shop_id = $clue->shop_id;
        $task->clue_id = $clue_id;
        $task->customer_id = $customer_id;
        $task->salesman_id = $salesman_id;
        $task->task_date = $date;
        $task->task_time = $time;
        $task->task_from = '店长主动分配';
        $task->task_type = 1;
        $task->is_cancel = 0;
        $task->is_finish = 1;
        $task->task_des = date('m月d日').$assignUser->name.'分配';
        if($task->save()){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 分配线索
     * @param $shop_id
     * @param $clue_id
     * @param $salesman_id
     * @param $who_assign_id
     * @return bool
     */
    public function assignClue($shop_id,$clue_id,$salesman_id,$who_assign_id){

        $clue_id = intval($clue_id);
        $salesman_id = intval($salesman_id);


        $salesman = User::find()->select('name,phone')->where(['=','id',$salesman_id])->andWhere(['=','is_delete',0])->asArray()->one();

        if(!$salesman){
            return false;
        }

        $salesman_name = $salesman['name'];

        $clue = Clue::find()->where(['=','id',$clue_id])->andWhere(['=','shop_id',$shop_id])->one();
        $assignUser = User::findOne($who_assign_id);
        $clue->salesman_id = $salesman_id;
        $clue->salesman_name = $salesman_name;
        $clue->who_assign_id = $who_assign_id;
        $clue->who_assign_name = $assignUser->name; //分配人姓名
        $clue->assign_time = time();
        $clue->is_assign = 1;

        $clue->save();

        return true;
    }
}

?>