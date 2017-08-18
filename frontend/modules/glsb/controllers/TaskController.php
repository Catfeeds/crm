<?php
/**
 * 任务列表逻辑
 * 作    者：lzx
 * 功    能：任务列表逻辑层
 * 修改日期：2017-3-14
 */
namespace frontend\modules\glsb\controllers;

use common\models\Customer;
//use frontend\modules\glsb\models\Clue;
use common\models\Clue;
use Yii;
//use frontend\modules\glsb\models\Task;
use common\models\Task;
use common\logic\DataDictionary;

/**
 * TaskController implements the CRUD actions for Task model.
 */
class TaskController extends AuthController
{
    /**
     * 获取未完成任务
     * @return array
     */
    public function actionUndoList()
    {
        $shop_id = $this->getShopId();
//        $user = Yii::$app->getUser()->identity;
        //接收参数
        $r = json_decode(Yii::$app->request->post('r'),true);
        $p = json_decode(Yii::$app->request->post('p'),true);

        if(empty($p['date'])){
            $this->echoData(400,'参数不全');
        }

        //获取该用户所属shop
//        $shop_id = (int)$r['shop_id'];
//        $this->checkshop($user,$shop_id);

        $date = $p['date'];

        //获取未完成任务列表  线索id、客户id、店员id、任务日期、预约时间、任务开始时间、任务结束时间
        $task_model = new Task();
        $task_list = $task_model->find()->select('id,clue_id,customer_id,salesman_id')
            ->where(['=','shop_id',$shop_id])->andWhere(['=','task_date',$date])->andWhere(['=','is_finish',1])->andWhere(['=','task_type',3])->asArray()->all();


        //获取线索详情  客户来源、建卡时间、客户姓名、客户手机号、意向车型、意向等级
        $clue_id_arr = array_column($task_list,'clue_id');
        $clue_model = new Clue();
        $clue_list  = $clue_model->find()->select('id as clue_id,clue_source,create_card_time,customer_name,customer_phone,intention_des,intention_level_des,salesman_name,status')->where(['in','id',$clue_id_arr])->asArray()->all();

        //获取客户来源数据字典
        $obj = new DataDictionary();
//        $source_list = $obj->getDictionaryData('source');
//
//        $source_arr = null;
//        foreach ($source_list as $item){
//            $source_arr[$item['id']] = $item;
//        }
//        foreach ($clue_list as $k_clue => $v_clue){
//            $clue_list[$k_clue]['clue_source_name'] = $source_arr[$v_clue['clue_source']]['name'];
//        }


        //拼接数组
        $clue_arr = null;
        foreach ($clue_list as $clue){
            $clue_arr[$clue['clue_id']] = $clue;
        }

        //获取客户信息 地址、性别、年龄
        $customer_id_arr = array_column($task_list,'customer_id');
        $customer_model = new Customer();
        $customer_list = $customer_model->find()->select('id as customer_id,address,sex,birthday')->where(['in','id',$customer_id_arr])->asArray()->all();

        //添加年龄
        $year_now = date('Y');
        foreach ($customer_list as $c_key=>$customer){
            //变量初始化
            $year_birthday = null;
            $year_birthday = explode('-',$customer['birthday'])[0];
            $age = $year_now - $year_birthday;
            $customer_list[$c_key]['age'] = $age;
        }

        //拼接数组
        $customer_arr = null;
        foreach ($customer_list as $customer){
            $customer_arr[$customer['customer_id']] = $customer;
        }

        $task_arr = array();
        foreach ($task_list as $task){
            if(!empty($clue_arr[$task['clue_id']]) && !empty($customer_arr[$task['customer_id']])){
                $task = array_merge($task,$clue_arr[$task['clue_id']],$customer_arr[$task['customer_id']]);
                $task_arr[] = $task;
            }
        }

        //返回数据
        if($task_arr){
            foreach ($task_arr as $d_key=>$d_item){
                unset($task_arr[$d_key]['clue_id']);
                unset($task_arr[$d_key]['customer_id']);
                unset($task_arr[$d_key]['salesman_id']);
                unset($task_arr[$d_key]['birthday']);
                $task_arr[$d_key]['id'] = (int)$d_item['id'];

                $task_arr[$d_key]['clue_source'] = (int)$d_item['clue_source'];
                $task_arr[$d_key]['create_card_time'] = (int)$d_item['create_card_time'];
                $task_arr[$d_key]['customer_name'] = (string)$d_item['customer_name'];
                $task_arr[$d_key]['customer_phone'] = (string)$d_item['customer_phone'];
                $task_arr[$d_key]['intention_des'] = (string)$d_item['intention_des'];
                $task_arr[$d_key]['intention_level_des'] = (string)$d_item['intention_level_des'];
                $task_arr[$d_key]['salesman_name'] = (string)$d_item['salesman_name'];

                if($d_item['status'] == 0){
                    $status = '线索客户';
                }elseif ($d_item['status'] == 1){
                    $status = '意向客户';
                }elseif ($d_item['status'] == 2){
                    $status = '订车客户';
                }elseif ($d_item['status'] == 3){
                    $status = '成交客户';
                }else{
                    $status = '未知';
                }
                $task_arr[$d_key]['status'] = $status;
//                $task_arr[$d_key]['clue_source_name'] = (string)$d_item['clue_source_name'];
                $task_arr[$d_key]['clue_source_name'] = $obj->getSourceName($task_arr[$d_key]['clue_source']);
                $task_arr[$d_key]['address'] = (string)$d_item['address'];
                $task_arr[$d_key]['sex'] = (int)$d_item['sex'];
                $task_arr[$d_key]['age'] = (int)$d_item['age'];
            }

            $data['models'] = $task_arr;
            $msg = '获取成功';
            $count = count($task_arr);

        }else{
            $data['models'] = array();
            $msg = '数据为空';
            $count = 0;

        }
        $data['pages'] = [
            'totalCount' => $count,
            'pageCount' => 1,
            'currentPage' => 1,
            'perPage' => $count,
        ];
        $this->echoData(200,$msg,$data);
    }
}
