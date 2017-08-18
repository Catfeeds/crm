<?php

namespace frontend\modules\glsb\controllers;

use common\logic\AssignClueLogic;
use common\logic\NoticeTemplet;
use common\logic\PhoneLetter;
use common\models\Customer;
use common\models\InputType;
use common\models\Task;
use common\models\User;
use common\models\Yuqi;
use frontend\modules\sales\logic\TalkLogic;
use Yii;
//use frontend\modules\glsb\models\Clue;
use common\models\Clue;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use common\logic\DataDictionary;
use common\logic\ActiveClueLogic;


/**
 * ClueController implements the CRUD actions for Clue model.
 */
class ClueController extends AuthController
{

    /**
     * 一定时间内未联系客户 - 店长功能
     * Lists all Clue models.
     * @return mixed
     */
    public function actionUnconnectList()
    {
        $shop_id = $this->getShopId();
        $p = json_decode(Yii::$app->request->post('p'),true);
        if(empty($p['time_interval_type'])){
            $this->echoData(400,'参数不全');
        }
        $time_interval_type = $p['time_interval_type'];

        if($time_interval_type == 1){
            $start_day = 15;
            $end_day = 7;
        }elseif($time_interval_type == 2){
            $start_day = 30;
            $end_day = 15;
        }elseif($time_interval_type == 3){
            $start_day = 10000;
            $end_day = 30;
        }

        //获取数据
        $activelogic = new ActiveClueLogic();
        $data = $activelogic->unconnectList($shop_id,$start_day,$end_day);

        if(count($data['models']) == 0){
            $msg = '数据为空';
        }else{
            $msg = '获取成功';
        }
        $this->echoData(200,$msg,$data);
    }

    /**
     * 激活线索
     * @return bool
     * @throws \Exception
     */
    public function actionActive(){

        $shop_id = $this->getShopId();
        $user = Yii::$app->getUser()->identity;
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);
        if(empty($p['active_list'])){
            $this->echoData(400,'参数不全');
        }
        $c_s = $p['active_list'];

        //后续激活线索 重新分配线索  分离
        //取出接收的线索id
        $clue_id_list = [];
        $salesman_id_list = [];
        foreach ($c_s as $val){
            $clue_id_list[] = $val['clue_id'];
            $salesman_id_list[] = $val['salesman_id'];
        }
        //查询数据库该线索salesman_id
        $salesman_list = Clue::find()->select('salesman_id')->where(['in','id',$clue_id_list])->asArray()->all();

        //如果线索对应顾问id  中含有不在接收列表中的数据  则判定为 重新分配
        $type = '';
        foreach ($salesman_list as $v){
            if(!in_array(intval($v['salesman_id']),$salesman_id_list)){
                $type = 'reassign';
                break;
            }
        }

        //获取当前用户id
        $who_assign_id = $user->id;
        //修改该条线索对接员工信息

        $noticeTemplet = new NoticeTemplet();
        foreach ($c_s as $item)
        {
            if(empty($item['clue_id']) || empty($item['salesman_id'])){
                $this->echoData(400,'参数错误');
            }

            $clue_id = (int)$item['clue_id'];
            $salesman_id = (int)$item['salesman_id'];

            $activelogic = new ActiveClueLogic();
            $rtn = $activelogic->activeClue($shop_id,$clue_id,$salesman_id,$who_assign_id,$type);
            if(!$rtn){
                $this->echoData(400,'操作失败');
            }

            //如果激活循环发送电话任务
            //推送电话任务
            if($type == ''){
                $noticeTemplet->telephoneTaskNotice($who_assign_id, $item['salesman_id'] ,1);
            }

        }

        //如果重新分配发送重新分配通知  发送一条电话任务
        if($type == 'reassign'){

            //华为电话任务推送的总数量  如果当前线索状态为0 不添加电话任务
            $task_count = Clue::find()->where(['in','id',$clue_id_list])->andWhere(['!=','status',0])->count();

            $clue_num = count($c_s);
            $clue_id_str = implode(',',$clue_id_list);
            if($task_count != 0){
                $noticeTemplet->telephoneTaskNotice($who_assign_id, $salesman_id ,$task_count);
            }

            $noticeTemplet->reassignReminderNotice($who_assign_id, $salesman_id ,$clue_num,$clue_id_str);
        }

        $this->echoData(200,'操作成功');
    }

    /**
     * 获取基础数据信息  暂时只包含该店长未分配线索数量
     */
    public function actionNotificationCount()
    {
        $shop_id = $this->getShopId();
        $count = Clue::find()->where(['=','shop_id',$shop_id])->andWhere(['=','is_assign',0])->count();
        $data['unassign_count'] = intval($count);
        $this->echoData(200,'获取成功',$data);
    }

    /**
     * 获取需分配线索
     * @return array|bool
     */
    public function actionUnassignList()
    {
        $shop_id = $this->getShopId();

        //获取数据列表及分页信息
        $assignLogic = new AssignClueLogic();
        $data = $assignLogic->unassignList($shop_id);

        if(count($data['models']) == 0){
            $msg = '数据为空';
        }else{
            $msg = '获取成功';
        }

        $this->echoData(200,$msg,$data);
    }

    /**
     * 线索分配
     * @return bool
     */
    public function actionAssign(){

        $shop_id = $this->getShopId();
        $user = Yii::$app->getUser()->identity;
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);
        if(empty($p['assign_list'])){
            $this->echoData(400,'参数不全');
        }

        $c_s = $p['assign_list'];
        $who_assign_id = $user->id;
        $who_assign_name = $user->name;

        foreach ($c_s as $k=>$item){
            if(empty($item['clue_id']) || empty($item['salesman_id'])){
                //删除该元素
                unset($c_s[$k]);
                continue;
            }

            $assignClueLogic = new AssignClueLogic();
            $assignClueLogic->assignClue($shop_id,$item['clue_id'],$item['salesman_id'],$who_assign_id);
        }

        //对数组重新排序  考虑被删除后只剩一条数据的情况
        $c_s = array_values($c_s);
        //推送任务
        $count = count($c_s);

        //如果所有数据被删除
        if($count == 0){
            $this->echoData(400,'操作失败');
        }

        //如果重新分配1条线索 发送短信包含来源信息  如果多条不包含线索来源信息
        if($count > 1) {

            //分配给同一顾问  取第一个顾问id
            $salesman_id = $c_s[0]['salesman_id'];

            //查询顾问信息
            $salesman = User::find()->select('name,phone')->where(['=','id',$salesman_id])->andWhere(['=','is_delete',0])->asArray()->one();
            $salesman_name = $salesman['name'];
            $salesman_phone = $salesman['phone'];

            //发送短信
            $phoneLetter = new PhoneLetter();
            //店长分配线索给销售时发送短信通知 - 多条
            $phoneLetter->shopownerAssignClues($salesman_phone,$salesman_name,$who_assign_name,$count);
            //店长分配线索给顾问语音短信   多条
            $phoneLetter->shopOwnerAssignCluesToSalesman($salesman_phone,$salesman_name,$who_assign_name,$count);
        }else{
            //只有一个元素 去第一个元素信息
            $salesman_id = $c_s[0]['salesman_id'];
            $clue_id = $c_s[0]['clue_id'];

            //查询线索来源信息
            $clue = Clue::find()->where(['=','id',$clue_id])->andWhere(['=','shop_id',$shop_id])->one();
            $clue_input_type = $clue->clue_input_type;

            //查询顾问信息
            $salesman = User::find()->select('name,phone')->where(['=','id',$salesman_id])->andWhere(['=','is_delete',0])->asArray()->one();
            $salesman_name = $salesman['name'];
            $salesman_phone = $salesman['phone'];

            //查询渠道来源名称
            $input_type_info = InputType::findOne($clue_input_type);
            $input_type_name = $input_type_info->name;

            //发送短信
            $phoneLetter = new PhoneLetter();
            //店长分配线索给销售时发送短信通知 - 单条
            $phoneLetter->shopownerAssignClueToSales($salesman_phone,$salesman_name,$who_assign_name,1,$input_type_name);
            //店长分配线索给顾问语音短信  单条
            $phoneLetter->shopOwnerAssignClueToSalesman($salesman_phone,$salesman_name,$who_assign_name,1,$input_type_name);
        }

        $salesman_id = $c_s[0]['salesman_id'];

        $noticeTemplet = new NoticeTemplet();
        //店长分配线索
        $noticeTemplet->assignClueSendNotice($who_assign_id, $salesman_id ,$count);

        $this->echoData(200,'操作成功');
    }

    /**
     * 获取须激活线索详情
     */
    public function actionUnconnectDetail()
    {
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);

        if(empty($p['clue_id'])){
            $this->echoData(400,'参数不全');
        }

        $clue_id = (int)$p['clue_id'];

        //获取线索信息
        $clue_model = new Clue();
        $clue_info = $clue_model->find()->select('id,customer_id,customer_name,customer_phone,salesman_id,salesman_name,clue_source,create_card_time,intention_des,intention_level_des,status')->where(['=','id',$clue_id])->asArray()->one();

        //获取客户来源数据字典
        $obj = new DataDictionary();

        //获取客户信息
        $customer_id = $clue_info['customer_id'];
        $customer_model = new Customer();
        $customer_info = $customer_model->find()->select('birthday,sex,address')->where(['=','id',$customer_id])->asArray()->one();

        //合并数组
        if(empty($clue_info) || empty($customer_info)){
            $data['customer_info'] = (object)array();
            $data['models'] = array();
            $data['pages'] = [
                'totalCount' => 0,
                'pageCount' => 0,
                'currentPage' => 0,
                'perPage' => 0,
            ];
            $this->echoData(200,'数据信息不全',$data);
        }
        $clue_info = array_merge($clue_info,$customer_info);

        if(empty($clue_info['birthday']) || $clue_info['birthday'] == '0000-00-00'){
            $age = '';
        }else{
            $age = date('Y-m-d') - $clue_info['birthday'];
        }

        $clue_info['age'] = $age;

        if($clue_info['status'] == 0){
            $clue_info['status'] = '线索客户';
        }elseif ($clue_info['status'] == 1){
            $clue_info['status'] = '意向客户';
        }elseif ($clue_info['status'] == 2){
            $clue_info['status'] = '订车客户';
        }elseif ($clue_info['status'] == 3){
            $clue_info['status'] = '成交客户';
        }else{
            $clue_info['status'] = '未知';
        }

        $clue_info['id'] = (int)$clue_info['id'];
        $clue_info['customer_name'] = (string)$clue_info['customer_name'];
        $clue_info['customer_phone'] = (string)$clue_info['customer_phone'];
        $clue_info['salesman_name'] = (string)$clue_info['salesman_name'];
        $clue_info['create_card_time'] = (int)$clue_info['create_card_time'];
        $clue_info['intention_des'] = (string)$clue_info['intention_des'];
        $clue_info['intention_level_des'] = (string)$clue_info['intention_level_des'];
        $clue_info['clue_source_name'] = $obj->getSourceName($clue_info['clue_source']);
        $clue_info['sex'] = (int)$clue_info['sex'];
        $clue_info['address'] = (string)$clue_info['address'];
        unset($clue_info['customer_id']);
        unset($clue_info['salesman_id']);
        unset($clue_info['clue_source']);
        unset($clue_info['birthday']);
        //组合数据
        $talk_list = array();
        if(TalkLogic::instance()->getTalk($clue_id)['models']){
            $talk_list = TalkLogic::instance()->getTalk($clue_id)['models'];
        }

        if(empty($clue_info)){
            $clue_info = (object)array();
        }

        $data['customer_info'] = $clue_info;
        $data['models'] = $talk_list;

        $data['pages'] = [
            'totalCount' => count($talk_list),
            'pageCount' => 1,
            'currentPage' => 1,
            'perPage' => count($talk_list),
        ];

        $this->echoData(200,'获取成功',$data);
    }

    /**
     * 获取未分配线索详情
     */
    public function actionUnassignDetail()
    {
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);

        if(empty($p['clue_id'])){
            $this->echoData(400,'参数不全');
        }

        $clue_id = (int)$p['clue_id'];

        //获取线索详情
        $clue_model = new Clue();
        $clue_info = $clue_model->find()->select('id,customer_id,customer_name,customer_phone,clue_source,intention_des,planned_purchase_time_id,buy_type,quoted_price,sales_promotion_content,des')->where(['=','id',$clue_id])->andWhere(['=','is_assign',0])->asArray()->one();
        if(empty($clue_info)){
            $this->echoData(200,'数据为空',(object)array());
        }

        $customer_id = $clue_info['customer_id'];

        $customer_model = new Customer();
        $customer_info = $customer_model->find()->select('sex,profession,weixin,spare_phone,area')->where(['=','id',$customer_id])->asArray()->one();

//        //获取客户来源数据字典
        $obj = new DataDictionary();

        if(empty($clue_info) || empty($customer_info)){
            $this->echoData(200,'数据信息不全',(object)array());
        }
        $clue_info = array_merge($clue_info,$customer_info);

        //返回数据
        if($clue_info){

            $clue_info['id'] = (int)$clue_info['id'];
            $clue_info['customer_name'] = (string)$clue_info['customer_name'];
            $clue_info['customer_phone'] = (string)$clue_info['customer_phone'];
//            $clue_info['clue_source_name'] = (string)$clue_info['clue_source_name'];
            $clue_info['clue_source_name'] = $obj->getSourceName($clue_info['clue_source']);
            $clue_info['intention_des'] = (string)$clue_info['intention_des'];
//            $clue_info['planned_purchase_time'] = '计划购买时间';//通过planned_purchase_time_id查询字典得出
            $clue_info['planned_purchase_time'] = $obj->getPlannedPurchaseTime($clue_info['planned_purchase_time_id']);
//            $clue_info['buy_type_name'] = '购买方式';//通过buy_type查询字典得出
            $clue_info['buy_type_name'] = $obj->getBuyTypeName($clue_info['buy_type']);
            $clue_info['quoted_price'] = (string)$clue_info['quoted_price'];
            $clue_info['sales_promotion_content'] = (string)$clue_info['sales_promotion_content'];
            $clue_info['des'] = (string)$clue_info['des'];
            $clue_info['sex'] = (int)$clue_info['sex'];
//            $clue_info['profession_name'] = '职位';
            $clue_info['profession_name'] = $obj->getProfessionName($clue_info['profession']);
            $clue_info['weixin'] = (string)$clue_info['weixin'];
            $clue_info['spare_phone'] = (string)$clue_info['spare_phone'];
//            $clue_info['area_name'] = '地区';//通过area查询字典得出
            $clue_info['area_name'] = $obj->areaCodeToName($clue_info['area']);
            unset($clue_info['customer_id']);
            unset($clue_info['clue_source']);
            unset($clue_info['area']);
            unset($clue_info['planned_purchase_time_id']);
            unset($clue_info['buy_type']);
            unset($clue_info['profession']);
            $this->echoData(200,'请求成功',$clue_info);
        }else{
            $this->echoData(400,'请求失败');
        }
    }

    /**
     * 逾期线索列表
     */
    public function actionOverdueList()
    {
        //获取用户信息
        $user = Yii::$app->getUser()->identity;

        //取出门店id
        $shop_id = $this->getShopId();

        $p = json_decode(Yii::$app->request->post('p'),true);
        if(empty($p['perPage']) || empty($p['currentPage'])){
            $this->echoData(400,'参数不全');
        }

        $perPage = (int)$p['perPage'];
        $currentPage = (int)$p['currentPage'];


        //查询逾期线索
        $query   = Yuqi::find()->select('clue_id,end_time')
            ->where(['=','shop_id',$shop_id])
            ->andWhere(['=','is_lianxi',0]);

        $totalCount = (int)$query->count();
        $pageCount = ceil($totalCount/$perPage);

        $yuqi_list = $query->offset(($currentPage-1)*$perPage)
            ->limit($perPage)
            ->asArray()
            ->all();

        //处理数据
        $yuqi_list_new = ArrayHelper::index($yuqi_list,'clue_id');

        //取出线索id
        $clue_id_arr = array_column($yuqi_list,'clue_id');

        //查询线索详情
        $clue_list = Clue::find()
            ->select('id as clue_id,customer_id,customer_name,customer_phone,salesman_id,salesman_name,create_time,clue_input_type,clue_source')
            ->where(['in','id',$clue_id_arr])
            ->asArray()
            ->all();

        //查询渠道来源信息来源信息
        $obj = new DataDictionary();
        $input_type = $obj->getDictionaryData('input_type');
        $input_type_new = ArrayHelper::index($input_type,'id');

        $clue_source = $obj->getDictionaryData('source');
        $clue_source_new = ArrayHelper::index($clue_source,'id');

        //初始化数据  处理数据
        $clue_list_new = array();
        foreach ($clue_list as $item){
            $item['clue_input_type_name'] = $input_type_new[$item['clue_input_type']]['name'];
            $item['clue_source_name'] = $clue_source_new[$item['clue_source']]['name'];
            $item['end_time'] = strtotime($yuqi_list_new[$item['clue_id']]['end_time']);

            $item['clue_id'] = intval($item['clue_id']);
            $item['customer_id'] = intval($item['customer_id']);
            $item['salesman_id'] = intval($item['salesman_id']);
            $item['customer_name'] = strval($item['customer_name']);
            $item['customer_phone'] = strval($item['customer_phone']);
            $item['salesman_name'] = strval($item['salesman_name']);
            $item['create_time'] = intval($item['create_time']);

            unset($item['clue_source']);
            unset($item['clue_input_type']);

            $clue_list_new[] = $item;
        }

        //返回数据
        $data['models'] = $clue_list_new;

        $data['pages'] = [
            'totalCount' => $totalCount,
            'pageCount' => $pageCount,
            'currentPage' => $currentPage,
            'perPage' => $perPage,
        ];

        $this->echoData(200,'请求成功',$data);
    }

    /**
     * 逾期线索提醒
     */
    public function actionOverdueRemind()
    {
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);
        if(empty($p['salesman_id'])){
            $this->echoData(400,'参数不全');
        }

        $salesman_id = (int)$p['salesman_id'];
        //查询顾问电话号
        $salesman = User::findOne($salesman_id);
        $salesman_phone = $salesman->phone;
        //语音通知
//        $content = '您有即将逾期的线索待跟进，请及时跟进';
        $phone_logic = new PhoneLetter();
        $phone_rtn = $phone_logic->shopownerRemindOverdueClue($salesman_phone);

        if($phone_rtn){
            $this->echoData(200,'通知成功');
        }else{
            $this->echoData(400,'通知失败');
        }
    }

    /**
     * Finds the Clue model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Clue the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Clue::findOne($id)) !== null) {
            return $model;
        } else {
            $this->echoData(400,'The requested page does not exist.');
//            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
