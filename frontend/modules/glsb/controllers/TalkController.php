<?php

namespace frontend\modules\glsb\controllers;

use common\models\Task;
use Yii;
//use frontend\modules\glsb\models\Talk;
use common\models\Talk;
//use frontend\modules\glsb\models\Clue;
use common\models\Clue;
use common\models\Customer;
use common\logic\DataDictionary;

/**
 * TalkController implements the CRUD actions for Talk model.
 */
class TalkController extends AuthController
{
    /**
     * 获取已完成上门商谈记录
     * @return array
     */
    public function actionDoneList()
    {
        $shop_id = $this->getShopId();
//        $user = Yii::$app->getUser()->identity;
        //接收参数
//        $r = json_decode(Yii::$app->request->post('r'),true);
        $p = json_decode(Yii::$app->request->post('p'),true);

        if(empty($p['date'])){
            $this->echoData(400,'参数不全');
        }


        $date = $p['date'];

        //1、查询已完成商谈记录
        //获取未完成任务列表  线索id、客户id、店员id、任务日期、预约时间、任务开始时间、任务结束时间
        $talk_model = new Talk();
        $talk_list = $talk_model->find()
            ->select('id,clue_id,castomer_id,salesman_id,start_time,end_time,imgs,content,talk_type,create_time,select_tags')
            ->where(['=','shop_id',$shop_id])
            ->andWhere(['=','talk_date',$date])
            ->andWhere(['in','talk_type',[8,9,10]])
            ->asArray()
            ->all();

        //获取线索详情  客户来源、建卡时间、客户姓名、客户手机号、意向车型、意向等级
        $clue_id_arr = array_column($talk_list,'clue_id');
        $clue_model = new Clue();
        $clue_list  = $clue_model->find()
            ->select('id as clue_id,clue_source,create_card_time,customer_name,customer_phone,intention_des,intention_level_des,salesman_name,status')
            ->where(['in','id',$clue_id_arr])
            ->asArray()
            ->all();

        //获取客户来源数据字典
        $obj = new DataDictionary();

        //拼接数组
        $clue_arr = null;
        foreach ($clue_list as $clue){
            $clue_arr[$clue['clue_id']] = $clue;
        }

        //获取客户信息 地址、性别、年龄
        $customer_id_arr = array_column($talk_list,'castomer_id');
        $customer_model = new Customer();
        $customer_list = $customer_model->find()->select('id as castomer_id,address,sex,birthday')
            ->where(['in','id',$customer_id_arr])
            ->asArray()
            ->all();

        //添加年龄
//        foreach ($customer_list as $c_key=>$customer){
//            $customer_list[$c_key]['age'] = date('Y-m-d') - $customer['birthday'];
//        }

        //拼接数组
        $customer_arr = null;
        foreach ($customer_list as $customer){
            $customer_arr[$customer['castomer_id']] = $customer;
        }

        //变量初始化
        $talk_arr = array();
        foreach ($talk_list as $talk){

            if(!empty($clue_arr[$talk['clue_id']]) && !empty($customer_arr[$talk['castomer_id']])){
                $talk_new['clue_info'] = array_merge($talk,$clue_arr[$talk['clue_id']],$customer_arr[$talk['castomer_id']]);
                $talk_new['talk_info'] = $this->getTalkList([$talk])[0];
                $talk_arr[] = $talk_new;
            }
        }

        foreach ($talk_arr as $d_key=>$d_item){

            $talk_arr[$d_key]['clue_info']['id'] = (int)$d_item['clue_info']['id'];

//            $talk_arr[$d_key]['clue_info']['start_time'] = (int)$d_item['clue_info']['start_time'];
//            $talk_arr[$d_key]['clue_info']['end_time'] = (int)$d_item['clue_info']['end_time'];
//            $talk_arr[$d_key]['clue_info']['imgs'] = ($d_item['clue_info']['imgs'] != "" ? explode(',',$d_item['clue_info']['imgs']) : array());
            $talk_arr[$d_key]['clue_info']['content'] = (string)$d_item['clue_info']['content'];
            $talk_arr[$d_key]['clue_info']['clue_source'] = (int)$d_item['clue_info']['clue_source'];
            $talk_arr[$d_key]['clue_info']['create_card_time'] = (int)$d_item['clue_info']['create_card_time'];
            $talk_arr[$d_key]['clue_info']['customer_name'] = (string)$d_item['clue_info']['customer_name'];
            $talk_arr[$d_key]['clue_info']['customer_phone'] = (string)$d_item['clue_info']['customer_phone'];
            $talk_arr[$d_key]['clue_info']['intention_des'] = (string)$d_item['clue_info']['intention_des'];
            $talk_arr[$d_key]['clue_info']['intention_level_des'] = (string)$d_item['clue_info']['intention_level_des'];
            $talk_arr[$d_key]['clue_info']['salesman_name'] = (string)$d_item['clue_info']['salesman_name'];

            if($d_item['clue_info']['status'] == 0){
                $status = '线索客户';
            }elseif ($d_item['clue_info']['status'] == 1){
                $status = '意向客户';
            }elseif ($d_item['clue_info']['status'] == 2){
                $status = '订车客户';
            }elseif ($d_item['clue_info']['status'] == 3){
                $status = '成交客户';
            }else{
                $status = '未知';
            }
            $talk_arr[$d_key]['clue_info']['status'] = $status;
//                $talk_arr[$d_key]['clue_source_name'] = (string)$d_item['clue_source_name'];
//            $talk_arr[$d_key]['clue_info']['clue_source_name'] = $obj->getSourceName($talk_arr[$d_key]['clue_info']['clue_source']);
            $talk_arr[$d_key]['clue_info']['address'] = (string)$d_item['clue_info']['address'];
            $talk_arr[$d_key]['clue_info']['sex'] = (int)$d_item['clue_info']['sex'];
//            $talk_arr[$d_key]['clue_info']['age'] = (int)$d_item['clue_info']['age'];

            unset($talk_arr[$d_key]['clue_info']['castomer_id']);
            unset($talk_arr[$d_key]['clue_info']['salesman_id']);
            unset($talk_arr[$d_key]['clue_info']['birthday']);
            unset($talk_arr[$d_key]['clue_info']['clue_id']);
            unset($talk_arr[$d_key]['clue_info']['start_time']);
            unset($talk_arr[$d_key]['clue_info']['end_time']);
            unset($talk_arr[$d_key]['clue_info']['imgs']);
            unset($talk_arr[$d_key]['clue_info']['sex']);
            unset($talk_arr[$d_key]['clue_info']['select_tags']);
            unset($talk_arr[$d_key]['clue_info']['clue_source']);
            unset($talk_arr[$d_key]['clue_info']['content']);
            unset($talk_arr[$d_key]['clue_info']['create_time']);
            unset($talk_arr[$d_key]['clue_info']['talk_type']);
            unset($talk_arr[$d_key]['clue_info']['clue_source_name']);
        }


        //2、查询已取消上门任务
        //获取未完成任务列表  线索id、客户id、店员id、任务日期、预约时间、任务开始时间、任务结束时间
        $task_model = new Task();
        $task_list = $task_model->find()->select('id,clue_id,customer_id,salesman_id')
            ->where(['=','shop_id',$shop_id])
            ->andWhere(['=','task_date',$date])
            ->andWhere(['=','is_cancel',1])
            ->andWhere(['=','task_type',3])
            ->asArray()
            ->all();


        //获取线索详情  客户来源、建卡时间、客户姓名、客户手机号、意向车型、意向等级
        $clue_id_arr = array_column($task_list,'clue_id');
        $clue_model = new Clue();
        $clue_list  = $clue_model->find()
            ->select('id as clue_id,clue_source,create_card_time,customer_name,customer_phone,intention_des,intention_level_des,salesman_name,status')
            ->where(['in','id',$clue_id_arr])
            ->asArray()
            ->all();

        //拼接数组
        $clue_arr = null;
        foreach ($clue_list as $clue){
            $clue_arr[$clue['clue_id']] = $clue;
        }

        //获取客户信息 地址、性别、年龄
        $customer_id_arr = array_column($task_list,'customer_id');
        $customer_model = new Customer();
        $customer_list = $customer_model->find()->select('id as customer_id,address,sex,birthday')
            ->where(['in','id',$customer_id_arr])
            ->asArray()
            ->all();

        //添加年龄
//        $year_now = date('Y');
//        foreach ($customer_list as $c_key=>$customer){
//            //变量初始化
//            $year_birthday = null;
//            $year_birthday = explode('-',$customer['birthday'])[0];
//            $age = $year_now - $year_birthday;
//            $customer_list[$c_key]['age'] = $age;
//        }

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

        foreach ($task_arr as $d_key=>$d_item){

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
//            $task_arr[$d_key]['clue_source_name'] = $obj->getSourceName($task_arr[$d_key]['clue_source']);
            $task_arr[$d_key]['address'] = (string)$d_item['address'];
            $task_arr[$d_key]['sex'] = (int)$d_item['sex'];
//            $task_arr[$d_key]['age'] = (int)$d_item['age'];
            unset($task_arr[$d_key]['clue_id']);
            unset($task_arr[$d_key]['customer_id']);
            unset($task_arr[$d_key]['salesman_id']);
            unset($task_arr[$d_key]['birthday']);
            unset($task_arr[$d_key]['sex']);
            unset($task_arr[$d_key]['clue_source']);
        }

        $task_arr_new = [];
        foreach ($task_arr as $item){
            $info = [];
            $info['is_cancel'] = 1;
            $info['clue_info'] = $item;
            $task_arr_new[] = $info;
        }

        $data_models = array_merge($talk_arr,$task_arr_new);

        //返回数据
        if($data_models){
            $data['models'] = $data_models;
            $msg = '获取成功';
            $count = count($data_models);
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



    /**
     * 个人商谈记录列表
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionList(){

        //获取用户信息对象
        $get = Yii::$app->request->get();
        if(!empty($get['userid'])){
            $salesman_id = $get['userid'];
        }else{
            //返回错误 中止程序
            return [0];
        }
        $model = new Talk();
        $list = $model->find()->where(['=','salesman_id',$salesman_id])->asArray()->all();
        return $list;
    }


    /**
     * 交谈记录详情   与datastatistics控制器内方法一致
     *
     * @param array $models
     * @return array $data
     */
    public function getTalkList($models)
    {
        $data = [];
        foreach ($models as $k => $model) {
            switch ($model['talk_type']) {
                case 1;
                    $data[$k]['title'] = '修改客户信息';
                    break;
                case 2;
                    $data[$k]['title'] = '来电';
                    break;
                case 3;
                    $data[$k]['title'] = '去电';
                    break;
                case 4;
                    $data[$k]['title'] = '给改客户发短信';
                    break;
                case 5;
                    $data[$k]['title'] = '到店';
                    break;
                case 6;
                    $data[$k]['title'] = '到店';
                    break;
                case 7;
                    $data[$k]['title'] = '到店';
                    break;
                case 8;
                    $data[$k]['title'] = '上门';
                    break;
                case 9;
                    $data[$k]['title'] = '上门';
                    break;
                case 10;
                    $data[$k]['title'] = '上门';
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
            }

            $data[$k]['img'] = [];
            if (!empty($model['imgs'])) {//验证图片

                $img = explode(',', $model['imgs']);
                $data[$k]['img'] = $img;
            }

            $data[$k]['voices'] = "";
            if (!empty($model['voices'])) {//验证音频

                $data[$k]['voices'] = $model['voices'];
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
            $data[$k]['castomer_id'] = $model['castomer_id'];

        }
        return $data;
    }
}
