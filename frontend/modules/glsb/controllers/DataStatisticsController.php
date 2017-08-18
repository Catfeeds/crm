<?php
namespace frontend\modules\glsb\controllers;

use common\common\PublicMethod;
use common\logic\DataDictionary;
use common\logic\TaskLogic;
use common\models\Clue;
use common\models\ClueWuxiao;
use common\models\Customer;
use common\models\InputType;
use common\models\Order;
use common\models\OrganizationalStructure;
use common\models\PutTheCar;
use common\models\Talk;
use common\models\TjClueGenjinzhong;
use common\models\TjFailIntentionTagCount;
use common\models\TjIntentionGenjinzhong;
use common\models\TjJichushuju;
use common\models\TjWeijiaoche;
use common\models\User;
use common\logic\CompanyUserCenter;
use frontend\modules\sales\models\Task;
use Jasny\ValidationResult;
use Yii;
//use yii\db\Expression;
use common\logic\TongJiLogic;
use frontend\modules\sales\logic\TalkLogic;

/**
 * Class 管理速报数据统计控制器
 * @package frontend\modules\glsb\controllers
 */
class DataStatisticsController extends AuthController
{
    private function getSelectData($date){

        //根据日期计算当月第一天
        $start_date=date('Y-m-01', strtotime($date));
        $end_date =  date('Y-m-d', strtotime("$start_date +1 month -1 day"));

        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        return $data;

    }
    
    //管理速报 统计 - 首页
    public function actionOverview(){
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);
        if(empty($p['date']) || empty($p['type'])){
            $this->echoData(400,'参数不全');
        }
        $type = $p['type'];// 日报 月报
        $date = $p['date'];// 日期
        $intStartTime = strtotime($date);

        //判断日报或月报  查询数据
        if($type == 'month')
        {
            $select_date = $this->getSelectData($date);
            $start_date = $select_date['start_date'];
            $end_date = $select_date['end_date'];
            $str = "create_date >= '{$start_date}' and create_date <= '{$end_date}'";
            $intEndTime = strtotime(date('Y-m-01', $intStartTime) . ' +1 month');
        }else{
            $str = "create_date = '{$date}'";
            $intEndTime = $intStartTime + 86400;
        }

        //获取查询条件
        $objTongJiLogic = new TongJiLogic();
        if($this->userinfo['role_level'] == 30)
        {
            $thisShopId = $this->getShopId();//默认店铺id，店长登录进来的时候查看数据查看本店的
            $arrWhereAndGroup = $objTongJiLogic->getSelectFieldByLevelAndOrgId($thisShopId, $this->userinfo['user_role_info']);
        }
        else
        {
            $arrWhereAndGroup = $objTongJiLogic->getStrFieldByLevel($this->userinfo['role_level'], $this->userinfo['user_role_info']);
        }
        
        if(empty($arrWhereAndGroup))
        {
            $this->echoData(400,'数据异常');
        }
        $field_str = $arrWhereAndGroup['where'];
        
        //新增线索、电话任务、新增意向、商谈、已交车
        $info = TjJichushuju::find()->select('sum(new_clue_num) as new_clue_num,sum(phone_task_num) as phone_task_num,sum(new_intention_num) as new_intention_num,sum(talk_num) as talk_num,sum(chengjiao_num) as chengjiao_num')
            ->where($field_str)
            ->andWhere($str)->asArray()->one();

        //跟进中线索
        $following = TjClueGenjinzhong::find()->select('sum(num) as sum_num')
            ->where($field_str)
            ->asArray()->one();
        //跟进中意向客户
        $int_following = TjIntentionGenjinzhong::find()->select('sum(num) as sum_num')
            ->where($field_str)
            ->asArray()->one();

        //未交车
        $undeliver = TjWeijiaoche::find()->select('sum(num) as sum_num')
            ->where($field_str)
            ->asArray()->one();

        //战败数
        $fail = TjFailIntentionTagCount::find()->select('sum(num) as sum_num')
            ->where($field_str)
            ->andWhere(['in','fail_type',[2,3]])
            ->andWhere($str)
            ->asArray()->one();

        // 查询提车任务
        $intMentionCarTask = PutTheCar::getMentionCount($this->userinfo['user_role_info'], $intStartTime, $intEndTime);
        $data['new_clue'] = (int)$info['new_clue_num'];
        $data['wait_follow'] = (int)$following['sum_num'];
        $data['phone_task'] = (int)$info['phone_task_num'];
        $data['intention_customer_new'] = (int)$info['new_intention_num'];
        $data['intention_customer_follow'] = (int)$int_following['sum_num'];
        $data['talk_record'] = (int)$info['talk_num'];
        $data['undelivered_car'] = (int)$undeliver['sum_num'];
        $data['delivered_car'] = (int)$info['chengjiao_num'];
        $data['fail_customer'] = (int)$fail['sum_num'];
        $data['mention_car_task'] = $intMentionCarTask;

        $data['update_time'] = PublicMethod::data_update_time(2);

        $this->echoData(200, '请求成功', $data);

    }

    //统计 - 线索
    public function actionClueList(){
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);
        
        //添加type字段！！！ 判断日报月报
        if(empty($p['date']) || empty($p['type'])){
            $this->echoData(400,'参数不全');
        }

        $date = $p['date'];
        $type = $p['type'];

        //判断日报或月报  查询数据
        if($type == 'month'){
            $select_date = $this->getSelectData($date);
            $start_date = $select_date['start_date'];
            $end_date = $select_date['end_date'];
            $str = "create_date >= '{$start_date}' and create_date <= '{$end_date}'";
        }else{
            $str = "create_date = '{$date}'";
        }

        $info_owner_id = intval($p['info_owner_id']);
        if(empty($info_owner_id))
        {
            $thisShopId = $this->getShopId();//默认店铺id，店长登录进来的时候查看数据查看本店的
            if($thisShopId > 0)
            {
                $info_owner_id = $thisShopId;
            }
        }

        $arrOrgIds = $this->userinfo['user_role_info'];
        $objTongJiLogic = new TongJiLogic();
        if(empty($info_owner_id))//没有传某个组织过来的时候 以登录时的角色信息为准
        {
            $level = $this->userinfo['role_level'];
            $arrWhereAndGourp = $objTongJiLogic->getStrFieldByLevel($level, $arrOrgIds);
        }
        else//以选中的组织的层级为准
        {
            $arrWhereAndGourp = $objTongJiLogic->getSelectFieldByLevelAndOrgId($info_owner_id, $arrOrgIds);
            $level = OrganizationalStructure::findOne($info_owner_id)->level;
        }        
        if(empty($arrWhereAndGourp))
        {
            $this->echoData(400,'数据异常');
        }
        $field_str = $arrWhereAndGourp['where'];
        $strGroupBy = $arrWhereAndGourp['groupby'];
        $clue_info_new = TjJichushuju::find()->select('sum(new_clue_num) as new_clue_num')
                            ->where($field_str)
                            ->andWhere($str)
                            ->asArray()->one();
        $clue_info_following = TjClueGenjinzhong::find()->select('sum(num) as sum_num')->where($field_str)
            ->asArray()->one();

        $topData = [
            'num1'=>intval($clue_info_new['new_clue_num']),
            'num2'=>intval($clue_info_following['sum_num']),
            'info_owner_name'=>$arrWhereAndGourp['org_level_name'],
        ];

        //查询并处理下一级新增线索
        $clue_info_child = TjJichushuju::find()
                                    ->select('sum(new_clue_num) as new_clue_num,'.$strGroupBy.' as info_owner_id')
                                    ->where($field_str)
                                    ->andWhere($str)
                                    ->groupBy($strGroupBy)
                                    ->asArray()->all();
        $clueTmp = [];
        foreach($clue_info_child as $val)
        {
            $clueTmp[$val['info_owner_id']] = $val['new_clue_num'];
        }
        //查询并处理下一级跟进线索
        $clue_info_following_child = TjClueGenjinzhong::find()
                                        ->select('sum(num) as sum_num,'.$strGroupBy.' as info_owner_id')
                                        ->where($field_str)
                                        ->groupBy($strGroupBy)
                                        ->asArray()->all();
        $followingTmp = [];
        foreach($clue_info_following_child as $val)
        {
            $followingTmp[$val['info_owner_id']] = $val['sum_num'];
        }
        $arrList = [];
        foreach($arrWhereAndGourp['nextList'] as $val)
        {
            
            $info = [
                'num1' => (isset($clueTmp[$val['id']]) ? intval($clueTmp[$val['id']]) : 0),
                'num2' => (isset($followingTmp[$val['id']]) ? intval($followingTmp[$val['id']]) : 0),
                'pid' => intval($val['pid']),
                'info_owner_id' => ($val['id'] == 0 ? -10000 : intval($val['id'])),//0特殊处理
                'info_owner_name' => strval($val['name']),
            ];
            if($val['id'] == 0 && $info['num1'] == 0 && $info['num2'] == 0)
            {
                continue;//无顾问的行 都为0的时候不显示
            }
            $arrList[] = $info;
        }
        
        //组织返回数据
        $data['organizational_structure_level'] = intval($level);
        $is_last_level = 0;
        if(empty($info_owner_id))
        {
            $is_last_level = 0;
        }
        else if($level == 30)
        {
            $is_last_level = 1;
        }
        $data['is_last_level'] = $is_last_level;

        $data['topData'] = empty($topData) ? (object)array() : $topData;
        $data['models'] = $arrList;
        $data['pages'] = [
            'totalCount' => count($arrList),
            'pageCount' => 1,
            'currentPage' => 1,
            'perPage' => count($arrList),
        ];
        $this->echoData(200,'获取成功',$data);
    }

    /**
     *2、各顾问线索列表
     */
    public function actionClueListOfSalesman(){
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);

        //添加type字段！！！ 判断日报月报
        if(empty($p['date']) || empty($p['type']) || empty($p['info_owner_id'])){
            $this->echoData(400,'参数不全');
        }

        $date = $p['date'];
        $type = $p['type'];
        //根据信息来源id查询该层级信息及下一层级列表
        $info_owner_id = intval($p['info_owner_id']);
        //如果info_owner_id参数为-10000 则表示查询的为未分配线索
        $shop_id = empty($p['pid']) ? 0 : $p['pid'];
        if($info_owner_id == '-10000' || empty($info_owner_id)){
            $info_owner_name = '未分配';
            //查询未分配线索需用到shop_id 老版本没有此参数 默认门店id为0 查询数据为空
            $own_str = "shop_id = {$shop_id} and is_assign = 0";
        }else{
            $info_owner_name = User::findOne($info_owner_id)->name;
            $own_str = "shop_id = {$shop_id} and salesman_id = {$info_owner_id}";
        }

        //判断日报或月报  查询数据
        if($type == 'month'){
            $select_date = $this->getSelectData($date);
            $start_date = $select_date['start_date'];
            $end_date = $select_date['end_date'];
            $start_time = strtotime($start_date);
            $end_time = strtotime($end_date)+(3600*24);
            $str = "(create_time >= {$start_time} and create_time < {$end_time})";

            //客户端传输数据可能
            if(date('Y-m',strtotime($date)) == date('Y-m')){
                //等于当月 添加跟进中
                $str .= "or (status = 0 and is_fail = 0)";
            }
        }else{
            $start_time = strtotime($date);
            $end_time = strtotime($date)+(3600*24);
            $str = "(create_time >= {$start_time} and create_time < {$end_time})";
            //客户端传输数据可能
            if(date('Y-m-d',strtotime($date)) == date('Y-m-d')){
                //等于当月 添加跟进中
                $str .= "or (status = 0 and is_fail = 0)";
            }
        }

        //如果当天 或当月添加跟进中线索  条件 salesman_id 时间小于starttime status 等于 0  未战败 没有时间条件
        $clue_info_query = Clue::find()->select('id,customer_id,customer_name,clue_input_type,create_time,status,is_fail,salesman_id,is_assign')
            ->where($own_str)
            ->andWhere($str);

        $clue_info_fail = ClueWuxiao::find()->select('id,customer_id,customer_name,clue_input_type,create_time,status,is_fail,salesman_id,is_assign')
            ->where($own_str)
            ->andWhere($str);

        $clue_info = $clue_info_query->union($clue_info_fail)->orderBy('create_card_time DESC')->asArray()->all();
        //可能会出现被禁用的渠道来源
        $input_type = InputType::find()->select('id,name')->asArray()->all();
        foreach ($input_type as $item){
            $input_type_list[$item['id']] = $item;
        }

        $clue_info_list = array();
        foreach ($clue_info as $key=>$value){

            $info = array();
            $info['clue_id'] = intval($value['id']);
            $info['customer_id'] = intval($value['customer_id']);
            $info['customer_name'] = $value['customer_name'];
            $info['num1'] = $input_type_list[$value['clue_input_type']]['name'];
            $info['num2'] = empty($value['create_time']) ? '--' : date('Y-m-d',$value['create_time']);

            if($value['is_assign'] == 0){
                $num3 = '未分配';
                $show_status = 2;
            }elseif($value['status'] > 0){
                $num3 = '已转化';
                $show_status = 4;
            }elseif($value['is_fail'] == 1){
                $num3 = '无效';
                $show_status = 1;
            }else{
                $num3 = '跟进中';
                $show_status = 3;
            }

            $info['num3'] = $num3;
            $info['show_status'] = intval($show_status);
            $clue_info_list[] = $info;
        }

        $data['info_owner_name'] = $info_owner_name;
        $data['num1'] = count($clue_info_list);
        $data['models'] = $clue_info_list;
        $data['pages'] = [
            'totalCount' => count($clue_info_list),
            'pageCount' => 1,
            'currentPage' => 1,
            'perPage' => count($clue_info_list),
        ];

        $this->echoData(200,'获取成功',$data);
    }

    /**
     * 线索详情
     */
    public function actionClueDetail()
    {
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);
        if(empty($p['clue_id'])){
            $this->echoData(400,'参数不全');
        }

        $clue_id = $p['clue_id'];

        //通过线索id查询客户信息
        $clue_info = Clue::find()
            ->select('customer_name,customer_phone,clue_source,intention_des,des,is_fail,fail_reason,status,create_time,clue_input_type,last_fail_time,is_assign')
            ->where(['=','id',$clue_id])
            ->asArray()
            ->one();

        if(!$clue_info){
            $clue_info = ClueWuxiao::find()
                ->select('customer_name,customer_phone,clue_source,intention_des,des,is_fail,fail_reason,status,create_time,clue_input_type,last_fail_time,is_assign')
                ->where(['=','id',$clue_id])
                ->asArray()
                ->one();
        }

        //查询商谈表记录
        $talk_list = Talk::find()
            ->select('start_time,voices_times,voices')
            ->where(['=','clue_id',$clue_id])
            ->andWhere(['in','talk_type',[2,3]])
            ->asArray()
            ->all();

        //处理商谈详情
        foreach ($talk_list as $key=>$value){
            $talk_list[$key]['start_time'] = intval($value['start_time']);
            $talk_list[$key]['voices_times'] = intval($value['voices_times']);
            $talk_list[$key]['voices'] = strval($value['voices']);
        }

        //查询渠道来源名称
        $input_type = InputType::findOne($clue_info['clue_input_type']);

        $obj = new DataDictionary();
        $clue_source_name = (string)$obj->getSourceName($clue_info['clue_source']);
        $customer_info['customer_name'] = strval($clue_info['customer_name']);
        $customer_info['customer_phone'] = strval($clue_info['customer_phone']);
        $customer_info['clue_input_type_name'] = empty($input_type->name) ? '--' : $input_type->name;
        $customer_info['talk_count'] = count($talk_list);
        $customer_info['create_time'] = intval($clue_info['create_time']);
        $customer_info['clue_source_name'] = $clue_source_name;
        $customer_info['intention_des'] = strval($clue_info['intention_des']);
        $customer_info['des'] = strval($clue_info['des']);
        $customer_info['is_fail'] = intval($clue_info['is_fail']);
        $customer_info['last_fail_time'] = intval($clue_info['last_fail_time']);
        $customer_info['fail_reason'] = strval($clue_info['fail_reason']);
        $customer_info['status'] = intval($clue_info['status']);

        if($clue_info['is_fail'] == 1){
            $customer_info['clue_type'] = '无效';
        }else if($clue_info['is_assign'] == 0){
            $customer_info['clue_type'] = '未分配';
        }elseif ($clue_info['status'] == 0){
            $customer_info['clue_type'] = '跟进中';
        }else{
            $customer_info['clue_type'] = '已转化';
        }

        //返回数据
        $data['customer_info'] = $customer_info;
//        $data['talk_list'] = $talk_list;
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
     * 3、任务列表
     */
    public function actionPhoneTaskList(){
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);

        //添加type字段！！！ 判断日报月报
        if(empty($p['date']) || empty($p['type'])){
            $this->echoData(400,'参数不全');
        }

        $date = $p['date'];
        $type = $p['type'];
        
        //判断日报或月报  查询数据
        if($type == 'month'){
            $select_date = $this->getSelectData($date);
            $start_date = $select_date['start_date'];
            $end_date = $select_date['end_date'];
            $str = "create_date >= '{$start_date}' and create_date <= '{$end_date}'";
        }else{
            $str = "create_date = '{$date}'";
        }
        //根据信息来源id查询该层级信息及下一层级列表
        $info_owner_id = intval($p['info_owner_id']);
        if(empty($info_owner_id))
        {
            $thisShopId = $this->getShopId();//默认店铺id，店长登录进来的时候查看数据查看本店的
            if($thisShopId > 0)
            {
                $info_owner_id = $thisShopId;
            }
        }
        $objTongJiLogic = new TongJiLogic();
        $arrOrgIds = $this->userinfo['user_role_info'];
        if(empty($info_owner_id))//没有传某个组织过来的时候 以登录时的角色信息为准
        {
            $level = $this->userinfo['role_level'];
            $arrWhereAndGroup = $objTongJiLogic->getStrFieldByLevel($level, $arrOrgIds);
        }
        else//以选中的组织的层级为准
        {
            $arrWhereAndGroup = $objTongJiLogic->getSelectFieldByLevelAndOrgId($info_owner_id, $arrOrgIds);
            $level = OrganizationalStructure::findOne($info_owner_id)->level;
        }        
        if(empty($arrWhereAndGroup))
        {
            $this->echoData(400,'数据异常');
        }        
        $field_str = $arrWhereAndGroup['where'];
        $strGroupBy = $arrWhereAndGroup['groupby'];
        
        //根据层级查询线索信息
        //1.查询当前层级新增线索 跟进中线索
        $phone_task = TjJichushuju::find()->select('sum(phone_task_num) as phone_task_num,sum(finish_phone_task_num) as finish_phone_task_num,sum(cancel_phone_task_num) as cancel_phone_task_num')
            ->where($field_str)
            ->andWhere($str)
            ->asArray()->one();

        //管理速报完成数为统计表中完成数量和取消数量之和
        $finish_phone_task_num = intval($phone_task['finish_phone_task_num']);
        $cancel_phone_task_num = intval($phone_task['cancel_phone_task_num']);
        $sum_all = $finish_phone_task_num + $cancel_phone_task_num;

        $topData = [
            'num1'=>intval($phone_task['phone_task_num']),
            'num2'=>$sum_all,
            'num3'=>@round($sum_all*100/$phone_task['phone_task_num']).'%',
            'info_owner_name'=>$arrWhereAndGroup['org_level_name'],
        ];

        //查询并处理下一级新增线索
        $phone_task_child = TjJichushuju::find()->select('sum(phone_task_num) as phone_task_num,sum(finish_phone_task_num) as finish_phone_task_num,sum(cancel_phone_task_num) as cancel_phone_task_num,'.$strGroupBy.' as info_owner_id')
            ->where($field_str)
            ->andWhere($str)
            ->groupBy($strGroupBy)
            ->asArray()->all();
        $phone_task_child_new = [];
        foreach ($phone_task_child as $value_new){
            $phone_task_child_new[$value_new['info_owner_id']] = $value_new;
        }
        $arrList = [];
        foreach($arrWhereAndGroup['nextList'] as $val)
        {
            $info['info_owner_id'] = intval($val['id']);
            $info['info_owner_name'] = strval($val['name']);
            $info['pid'] = intval($info_owner_id);
            if(!isset($phone_task_child_new[$val['id']]))
            {
                $info['num1'] = 0;
                $info['num2'] = 0;
                $info['num3'] = '0%';
                $info['pid'] = intval($val['pid']);
            }
            else
            {
                $thisVar = $phone_task_child_new[$val['id']];
                $info['num1'] = intval($thisVar['phone_task_num']);
                //管理速报完成数为统计表中完成数量和取消数量之和
                $finish_phone_task_num = intval($thisVar['finish_phone_task_num']);
                $cancel_phone_task_num = intval($thisVar['cancel_phone_task_num']);
                $sum = $finish_phone_task_num + $cancel_phone_task_num;
                $info['num2'] = $sum;
                $info['num3'] = @round($sum*100/intval($thisVar['phone_task_num'])).'%';
                $info['pid'] = intval($val['pid']);
            }
            $arrList[] = $info;
        }

        //组织返回数据
        $data['organizational_structure_level'] = intval($level);
        $is_last_level = 0;
        if($level == 30){
            $is_last_level = 1;
        }
        $data['is_last_level'] = $is_last_level;

        $data['topData'] = empty($topData) ? (object)array() : $topData;
        $data['models'] = $arrList;
        $data['pages'] = [
            'totalCount' => count($arrList),
            'pageCount' => 1,
            'currentPage' => 1,
            'perPage' => count($arrList),
        ];

        $this->echoData(200,'获取成功',$data);
    }

    /**
     *4、各顾问任务列表
     */
    public function actionPhoneTaskListOfSalesman(){
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);

        //添加type字段！！！ 判断日报月报
        if(empty($p['date']) || empty($p['type'])){
            $this->echoData(400,'参数不全');
        }

        $date = $p['date'];
        $type = $p['type'];
        //根据信息来源id查询该层级信息及下一层级列表
        $info_owner_id = intval($p['info_owner_id']);
        if($info_owner_id){
            $info_owner_name = User::findOne($info_owner_id)->name;
        }
        else{
            $info_owner_name = '无顾问';
        }
        $shop_id = isset($p['pid']) ? intval($p['pid']) : 0;

        //判断日报或月报  查询数据
        if($type == 'month'){
            $select_date = $this->getSelectData($date);
            $start_date = $select_date['start_date'];
            $end_date = $select_date['end_date'];   //确认
            $str = "task_date >= '{$start_date}' and task_date <= '{$end_date}'";
        }else{
            $str = "task_date = '{$date}'";
        }

        $arrWhere = [
            'and',
            ['=','salesman_id',$info_owner_id],
            ['=','task_type',1],
        ];
        $shop_id && $arrWhere[] = ['=', 'shop_id', $shop_id];
        $task_info = \common\models\Task::find()->select('id,clue_id,customer_id,task_from,is_finish,is_cancel,end_time')
            ->where($arrWhere)
            ->andWhere($str)
            ->asArray()->all();

        $clue_id_list = array_column($task_info,'clue_id');

        $customer_info_list = Clue::find()->select('id,customer_name,intention_level_des,status')->where(['in','id',$clue_id_list])->asArray()->all();

        $customer_info_list_new = array();
        foreach ($customer_info_list as $item){
            $customer_info_list_new[$item['id']] = $item;
        }


        $clue_info_list = array();
        foreach ($task_info as $value){
            $info = array();
            $info['clue_id'] = intval($value['clue_id']);
            $info['customer_id'] = intval($value['customer_id']);
            $info['customer_name'] = $customer_info_list_new[$value['clue_id']]['customer_name'];
            $info['num1'] = $customer_info_list_new[$value['clue_id']]['intention_level_des'];
            $info['num2'] = $value['task_from'];

            if($value['is_finish'] == 2){
                $num3 = empty($value['end_time']) ? '--' : date('Y-m-d',$value['end_time']);
            }elseif ($value['is_cancel'] == 1){
                $num3 = '已取消';
            }else{
                $num3 = '跟进中';
            }
            $info['num3'] = $num3;


            $clue_info_list[] = $info;
        }

        $data['info_owner_name'] = $info_owner_name;
        $data['num1'] = count($clue_info_list);
        $data['models'] = $clue_info_list;
        $data['pages'] = [
            'totalCount' => count($clue_info_list),
            'pageCount' => 1,
            'currentPage' => 1,
            'perPage' => count($clue_info_list),
        ];

        $this->echoData(200,'获取成功',$data);
    }

    /**
     * 5、意向客户列表
     */
    public function actionIntentionCustomerList(){
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);

        //添加type字段！！！ 判断日报月报
        if(empty($p['date']) || empty($p['type'])){
            $this->echoData(400,'参数不全');
        }

        $date = $p['date'];
        $type = $p['type'];
        //判断日报或月报  查询数据
        if($type == 'month'){
            $select_date = $this->getSelectData($date);
            $start_date = $select_date['start_date'];
            $end_date = $select_date['end_date'];
            $str = "create_date >= '{$start_date}' and create_date <= '{$end_date}'";
        }else{
            $str = "create_date = '{$date}'";
        }
        
        $info_owner_id = intval($p['info_owner_id']);
        if(empty($info_owner_id))
        {
            $thisShopId = $this->getShopId();//默认店铺id，店长登录进来的时候查看数据查看本店的
            if($thisShopId > 0)
            {
                $info_owner_id = $thisShopId;
            }
        }

        $objTongJiLogic = new TongJiLogic();
        $arrOrgIds = $this->userinfo['user_role_info'];
        if(empty($info_owner_id))//没有传某个组织过来的时候 以登录时的角色信息为准
        {
            $level = $this->userinfo['role_level'];
            $arrWhereAndGroup = $objTongJiLogic->getStrFieldByLevel($level, $arrOrgIds);
        }
        else//以选中的组织的层级为准
        {
            $arrWhereAndGroup = $objTongJiLogic->getSelectFieldByLevelAndOrgId($info_owner_id, $arrOrgIds);
            $level = OrganizationalStructure::findOne($info_owner_id)->level;
        }        
        if(empty($arrWhereAndGroup))
        {
            $this->echoData(400,'数据异常');
        }        
        $field_str = $arrWhereAndGroup['where'];
        $strGroupBy = $arrWhereAndGroup['groupby'];

        //根据层级查询线索信息
        //1.查询当前层级新增线索 跟进中线索
        $intention_info_new = TjJichushuju::find()->select('sum(new_intention_num) as new_intention_num')->where($field_str)
            ->andWhere($str)
            ->asArray()->one();
        $intention_info_following = TjIntentionGenjinzhong::find()->select('sum(num) as sum_num')->where($field_str)
            ->asArray()->one();

        $topData = [
            'num1'=>intval($intention_info_new['new_intention_num']),
            'num2'=>intval($intention_info_following['sum_num']),
            'info_owner_name'=>$arrWhereAndGroup['org_level_name'],
        ];

        //查询并处理下一级新增线索
        $intention_info_child = TjJichushuju::find()
                                ->select('sum(new_intention_num) as new_intention_num,'.$strGroupBy.' as info_owner_id')
                                ->where($field_str)
                                ->andWhere($str)
                                ->groupBy($strGroupBy)
                                ->asArray()->all();
        foreach ($intention_info_child as $value_new){
            $intention_info_child_new[$value_new['info_owner_id']] = $value_new['new_intention_num'];
        }

        //查询并处理下一级跟进线索
        $intention_info_following_child = TjIntentionGenjinzhong::find()
                                            ->select('sum(num) as sum_num,'.$strGroupBy.' as info_owner_id')
                                            ->where($field_str)
                                            ->groupBy($strGroupBy)
                                            ->asArray()->all();
        //处理下一层级数据
        foreach ($intention_info_following_child as $value){
            $intention_info_following_child_new[$value['info_owner_id']] = $value['sum_num'];
        }

        $arrList = [];
        foreach($arrWhereAndGroup['nextList'] as $val)
        {
            $info = array();
            $info['pid'] = intval($val['pid']);
            $info['info_owner_id'] = ($val['id'] == 0 ? -10000 : intval($val['id'])); //0特殊处理;
            $info['info_owner_name'] = strval($val['name']);
            $info['num1'] = (isset($intention_info_child_new[$val['id']]) ? intval($intention_info_child_new[$val['id']]) : 0);
            $info['num2'] = (isset($intention_info_following_child_new[$val['id']]) ? intval($intention_info_following_child_new[$val['id']]) : 0);
            
            if($val['id'] == 0 && $info['num1'] == 0 && $info['num2'] == 0)
            {
                continue;//无顾问的行 都为0的时候不显示
            }
            $arrList[] = $info;
        }

        //组织返回数据
        $data['organizational_structure_level'] = intval($level);
        $is_last_level = 0;
        if($level == 30){
            $is_last_level = 1;
        }
        $data['is_last_level'] = $is_last_level;

        $data['topData'] = empty($topData) ? (object)array() : $topData;
        $data['models'] = $arrList;
        $data['pages'] = [
            'totalCount' => count($arrList),
            'pageCount' => 1,
            'currentPage' => 1,
            'perPage' => count($arrList),
        ];

        $this->echoData(200,'获取成功',$data);
    }

    /**
     * 6、各顾问意向客户列表
     */
    public function actionIntentionCustomerListOfSalesman(){
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);

        //添加type字段！！！ 判断日报月报
        if(empty($p['date']) || empty($p['type'])){
            $this->echoData(400,'参数不全');
        }

        //根据信息来源id查询该层级信息及下一层级列表
        $info_owner_id = intval($p['info_owner_id']);
        //如果info_owner_id参数为-10000 则表示查询的为已删除顾问的意向客户
        $shop_id = empty($p['pid']) ? 0 : $p['pid'];
        if($info_owner_id == '-10000' || empty($info_owner_id))
        {
            $info_owner_id = 0;
            $info_owner_name = '顾问已删除';
            $own_str = "shop_id = {$shop_id} and salesman_id = 0";
        }else{
            $info_owner_name = User::findOne($info_owner_id)->name;
            $own_str = " salesman_id = {$info_owner_id} ";
            !empty($shop_id) && $own_str .= " and shop_id = {$shop_id} ";
        }

        $clue_info = Clue::find()->select('id,customer_id,customer_name,clue_input_type,intention_level_des,intention_des')
            ->where($own_str)
            ->andWhere(['=','is_fail',0])
            ->andWhere(['=','status',1])
            ->asArray()->all();

        $obj = new DataDictionary();
        $input_type = $obj->getDictionaryData('input_type');
        foreach ($input_type as $item){
            $input_type_list[$item['id']] = $item;
        }


        $clue_info_list = array();
        foreach ($clue_info as $value){
            $info = array();
            $info['clue_id'] = intval($value['id']);
            $info['customer_id'] = intval($value['customer_id']);
            $info['customer_name'] = $value['customer_name'];
            $info['num1'] = $value['intention_level_des'];
            $info['num2'] = $input_type_list[$value['clue_input_type']]['name'];
            $info['num3'] = $value['intention_des'];
            $clue_info_list[] = $info;
        }

        $data['info_owner_name'] = $info_owner_name;
        $data['num1'] = count($clue_info_list);
        $data['models'] = $clue_info_list;
        $data['pages'] = [
            'totalCount' => count($clue_info_list),
            'pageCount' => 1,
            'currentPage' => 1,
            'perPage' => count($clue_info_list),
        ];

        $this->echoData(200,'获取成功',$data);
    }

    /**
     * 7、商谈列表
     */
    public function actionTalkList(){
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);

        //添加type字段！！！ 判断日报月报
        if(empty($p['date']) || empty($p['type'])){
            $this->echoData(400,'参数不全');
        }

        $date = $p['date'];
        $type = $p['type'];
        //判断日报或月报  查询数据
        if($type == 'month'){
            $select_date = $this->getSelectData($date);
            $start_date = $select_date['start_date'];
            $end_date = $select_date['end_date'];
            $str = "create_date >= '{$start_date}' and create_date <= '{$end_date}'";
        }else{
            $str = "create_date = '{$date}'";
        }
        
        $info_owner_id = intval($p['info_owner_id']);
        if(empty($info_owner_id))
        {
            $thisShopId = $this->getShopId();//默认店铺id，店长登录进来的时候查看数据查看本店的
            if($thisShopId > 0)
            {
                $info_owner_id = $thisShopId;
            }
        }
        $objTongJiLogic = new TongJiLogic();
        $arrOrgIds = $this->userinfo['user_role_info'];
        if(empty($info_owner_id))//没有传某个组织过来的时候 以登录时的角色信息为准
        {
            $level = $this->userinfo['role_level'];
            $arrWhereAndGroup = $objTongJiLogic->getStrFieldByLevel($level, $arrOrgIds);
        }
        else//以选中的组织的层级为准
        {
            $arrWhereAndGroup = $objTongJiLogic->getSelectFieldByLevelAndOrgId($info_owner_id, $arrOrgIds);
            $level = OrganizationalStructure::findOne($info_owner_id)->level;
        }        
        if(empty($arrWhereAndGroup))
        {
            $this->echoData(400,'数据异常');
        }        
        $field_str = $arrWhereAndGroup['where'];
        $strGroupBy = $arrWhereAndGroup['groupby'];

        //根据层级查询线索信息
        //1.查询当前层级新增线索 跟进中线索
        $talk_info = TjJichushuju::find()
                ->select('sum(lai_dian_num) as lai_dian_num,sum(qu_dian_num) as qu_dian_num,sum(to_shop_num) as to_shop_num,sum(to_home_num) as to_home_num')
                ->where($field_str)
                ->andWhere($str)
                ->asArray()->one();

        $topData = [
            'num1'=>intval($talk_info['lai_dian_num']),
            'num2'=>intval($talk_info['qu_dian_num']),
            'num3'=>intval($talk_info['to_shop_num']),
            'num4'=>intval($talk_info['to_home_num']),
            'info_owner_name'=>$arrWhereAndGroup['org_level_name'],
        ];

        //查询并处理下一级新增线索
        $talk_info_child = TjJichushuju::find()
                ->select('sum(lai_dian_num) as lai_dian_num,sum(qu_dian_num) as qu_dian_num,sum(to_shop_num) as to_shop_num,sum(to_home_num) as to_home_num,'. $strGroupBy .' as info_owner_id')
                ->where($field_str)
                ->andWhere($str)
                ->groupBy($strGroupBy)
                ->asArray()->all();

        foreach ($talk_info_child as $value_new){
            $talk_info_child_new[$value_new['info_owner_id']] = $value_new;
        }
        
        $arrList = [];
        foreach($arrWhereAndGroup['nextList'] as $val)
        {
            $info = array();
            $info['pid'] = intval($val['pid']);
            $info['info_owner_id'] = ($val['id'] == 0 ? -10000 : intval($val['id'])); //0特殊处理;
            $info['info_owner_name'] = strval($val['name']);
            if(isset($talk_info_child_new[$val['id']]))
            {
                $thisVal = $talk_info_child_new[$val['id']];
                $info['num1'] = intval($thisVal['lai_dian_num']);
                $info['num2'] = intval($thisVal['qu_dian_num']);
                $info['num3'] = intval($thisVal['to_shop_num']);
                $info['num4'] = intval($thisVal['to_home_num']);
                $info['pid'] = intval($val['pid']);
            }
            else
            {
                $info['num1'] = 0;
                $info['num2'] = 0;
                $info['num3'] = 0;
                $info['num4'] = 0;
                $info['pid'] = intval($val['pid']);
            }
            
            if($val['id'] == 0 )//&& $info['num1'] == 0 && $info['num2'] == 0 && $info['num3'] == 0 && $info['num4'] == 0 
            {
                continue;//无顾问的行 都为0的时候不显示
            }
            $arrList[] = $info;
        }
        //组织返回数据
        $data['organizational_structure_level'] = intval($level);
        $is_last_level = 0;
        if($level == 30){
            $is_last_level = 1;
        }
        $data['is_last_level'] = $is_last_level;

        $data['topData'] = empty($topData) ? (object)array() : $topData;
        $data['models'] = $arrList;
        $data['pages'] = [
            'totalCount' => count($arrList),
            'pageCount' => 1,
            'currentPage' => 1,
            'perPage' => count($arrList),
        ];

        $this->echoData(200,'获取成功',$data);
    }

    /**
     *8、商谈详情
     */
    public function actionTalkListOfSalesman(){
        $p = json_decode(Yii::$app->request->post('p'),true);

        if(empty($p['date'])){
            $this->echoData(400,'参数不全');
        }
        $talk_date = $p['date'];
        $type = $p['type'];

        //判断日报或月报  查询数据
        if($type == 'month'){
            $select_date = $this->getSelectData($talk_date);
            $start_date = $select_date['start_date'];
            $end_date = $select_date['end_date'];
            $str = "talk_date >= '{$start_date}' and talk_date <= '{$end_date}'";
        }else{
            $str = "talk_date = '{$talk_date}'";
        }
        $info_owner_id = intval($p['info_owner_id']);
        $shopId = (isset($p['pid']) ? intval($p['pid']) : 0);

        if($info_owner_id > 0){
            $info_owner_name = User::findOne($info_owner_id)->name;
        }else{
            $this->echoData(400,'顾问id错误');
        }

        $arrWhere = [
            'and',
            ['in','talk_type',[2,3,5,6,7,8,9,10]],
            ['=', 'salesman_id', $info_owner_id]
        ];
        $shopId && $arrWhere[] = ['=', 'shop_id', $shopId];
        
        $models = Talk::find()->select(['talk_date', 'start_time', 'end_time', 'talk_type',
            'select_tags', 'content', 'imgs', 'voices', 'vedios', 'add_infomation', 'create_time' ,'castomer_id','voices_times'])
            ->where($arrWhere)->andWhere($str)
            ->orderBy('id desc')
            ->asArray()
            ->all();

        $castomer_id_list = array_column($models,'castomer_id');
        $castomer_info_list = Customer::find()->select('id,phone as customer_phone,name as customer_name')->where(['in','id',$castomer_id_list])->asArray()->all();

        foreach ($castomer_info_list as $item){
            $castomer_info_list_new[$item['id']] = $item;
        }

        $list = $this->getTalkList($models);

        foreach ($list as $key=>$value){
            $list[$key]['customer_phone'] = @$castomer_info_list_new[$value['castomer_id']]['customer_phone'];
            $list[$key]['customer_name'] = @$castomer_info_list_new[$value['castomer_id']]['customer_name'];
            $list[$key]['create_time_format'] = date('m-d H:i',$value['create_time']);
        }

        $data['num1'] = count($list);
        $data['info_owner_name'] = $info_owner_name;
        $data['models'] = $list;
        $data['pages'] = [
            'totalCount' => count($list),
            'pageCount' => 1,
            'currentPage' => 1,
            'perPage' => count($list),
        ];

        $this->echoData(200,'获取成功',$data);
    }


    /**
     * 9、未交车列表
     */
    public function actionUndeliverCarList(){
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);
        $info_owner_id = intval($p['info_owner_id']);
        if(empty($info_owner_id))
        {
            $thisShopId = $this->getShopId();//默认店铺id，店长登录进来的时候查看数据查看本店的
            if($thisShopId > 0)
            {
                $info_owner_id = $thisShopId;
            }
        }
        $objTongJiLogic = new TongJiLogic();
        $arrOrgIds = $this->userinfo['user_role_info'];
        if(empty($info_owner_id))//没有传某个组织过来的时候 以登录时的角色信息为准
        {
            $level = $this->userinfo['role_level'];
            $arrWhereAndGroup = $objTongJiLogic->getStrFieldByLevel($level, $arrOrgIds);
        }
        else//以选中的组织的层级为准
        {
            $arrWhereAndGroup = $objTongJiLogic->getSelectFieldByLevelAndOrgId($info_owner_id, $arrOrgIds);
            $level = OrganizationalStructure::findOne($info_owner_id)->level;
        }        
        if(empty($arrWhereAndGroup))
        {
            $this->echoData(400,'数据异常');
        }        
        $field_str = $arrWhereAndGroup['where'];
        $strGroupBy = $arrWhereAndGroup['groupby'];

        //判断日报或月报  查询数据


        //根据层级查询线索信息
        //1.查询当前层级新增线索 跟进中线索
        $undeliver_info = TjWeijiaoche::find()->select('sum(num) as sum_num')
            ->where($field_str)
            ->asArray()->one();

        $topData = [
            'num1'=>intval($undeliver_info['sum_num']),
            'info_owner_name'=>$arrWhereAndGroup['org_level_name'],
        ];

        //查询并处理下一级新增线索
        $undeliver_info_child = TjWeijiaoche::find()->select('sum(num) as sum_num,'.$strGroupBy.' as info_owner_id')
            ->where($field_str)
            ->groupBy($strGroupBy)
            ->asArray()->all();

        foreach ($undeliver_info_child as $value_new){
            $undeliver_info_child_new[$value_new['info_owner_id']] = $value_new['sum_num'];
        }

        $arrList = [];
        foreach($arrWhereAndGroup['nextList'] as $val)
        {
            $info = array();
            $info['pid'] = intval($val['pid']);
            $info['info_owner_id'] = ($val['id'] == 0 ? -10000 : intval($val['id'])); //0特殊处理;
            $info['info_owner_name'] = strval($val['name']);
            $info['num1'] = (isset($undeliver_info_child_new[$val['id']]) ? intval($undeliver_info_child_new[$val['id']]) : 0);
            if($val['id'] == 0 )//&& $info['num1'] == 0 && $info['num2'] == 0 && $info['num3'] == 0 && $info['num4'] == 0 
            {
                continue;//无顾问的行 都为0的时候不显示
            }
            $arrList[] = $info;
        }
        
        //组织返回数据
        $data['organizational_structure_level'] = intval($level);
        $is_last_level = 0;
        if($level == 20){
            $is_last_level = 1;
        }
        $data['is_last_level'] = $is_last_level;

        $data['topData'] = empty($topData) ? (object)array() : $topData;
        $data['models'] = $arrList;
        $data['pages'] = [
            'totalCount' => count($arrList),
            'pageCount' => 1,
            'currentPage' => 1,
            'perPage' => count($arrList),
        ];

        $this->echoData(200,'获取成功',$data);
    }

    /**
     * 10、门店未交车列表
     */
    public function actionUndeliverCarListOfShop(){
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);

        //根据信息来源id查询该层级信息及下一层级列表
        $info_owner_id = intval($p['info_owner_id']);
        if(empty($info_owner_id))
        {
            $thisShopId = $this->getShopId();//默认店铺id，店长登录进来的时候查看数据查看本店的
            if($thisShopId > 0)
            {
                $info_owner_id = $thisShopId;
            }
        }
        $info_owner_name = OrganizationalStructure::findOne($info_owner_id)->name;

        $order_info = Order::find()->select('clue_id,customer_id,car_type_name,salesman_id,status,predict_car_delivery_time')
            ->where(['=','shop_id',$info_owner_id])
            ->andWhere(['=','status',3])
            ->asArray()->all();

        //取出所有顾客id
        $customer_id_list = array_column($order_info,'customer_id');
        $customer_info_list = Customer::find()->select('id as customer_id,name as customer_name,')->where(['in','id',$customer_id_list])->asArray()->all();

        $customer_info_list_new = array();
        foreach ($customer_info_list as $item){
            $customer_info_list_new[$item['customer_id']] = $item;
        }

        //取出所有顾问id
        $salesman_id_list = array_column($order_info,'salesman_id');
        $salesman_info_list = User::find()->select('id as salesman_id,name as salesman_name')->where(['in','id',$salesman_id_list])->asArray()->all();
        $salesman_info_list_new = array();
        foreach ($salesman_info_list as $item){
            $salesman_info_list_new[$item['salesman_id']] = $item;
        }

        $order_info_list = array();
        foreach ($order_info as $value){
            $info = array();
            $info['clue_id'] = intval($value['clue_id']);
            $info['customer_id'] = intval($value['customer_id']);
            $info['customer_name'] = $customer_info_list_new[$value['customer_id']]['customer_name'];
            $info['num1'] = strval($value['car_type_name']);

            $info['num2'] = $salesman_info_list_new[$value['salesman_id']]['salesman_name'];
            $info['num3'] = empty($value['predict_car_delivery_time']) ? '--' : date('Y-m-d',$value['predict_car_delivery_time']);
            $order_info_list[] = $info;
        }

        $data['num1'] = count($order_info_list);
        $data['info_owner_name'] = $info_owner_name;
        $data['models'] = $order_info_list;
        $data['pages'] = [
            'totalCount' => count($order_info_list),
            'pageCount' => 1,
            'currentPage' => 1,
            'perPage' => count($order_info_list),
        ];

        $this->echoData(200,'获取成功',$data);
    }

    /**
     * 11、交车列表
     */
    public function actionDeliverCarList(){
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);

        //添加type字段！！！ 判断日报月报
        if(empty($p['date']) || empty($p['type'])){
            $this->echoData(400,'参数不全');
        }

        $date = $p['date'];
        $type = $p['type'];
        //根据信息来源id查询该层级信息及下一层级列表
        $info_owner_id = intval($p['info_owner_id']);
        $objTongJiLogic = new TongJiLogic();
        $arrOrgIds = $this->userinfo['user_role_info'];
        if(empty($info_owner_id))//没有传某个组织过来的时候 以登录时的角色信息为准
        {
            $level = $this->userinfo['role_level'];
            $arrWhereAndGroup = $objTongJiLogic->getStrFieldByLevel($level, $arrOrgIds);
        }
        else//以选中的组织的层级为准
        {
            $arrWhereAndGroup = $objTongJiLogic->getSelectFieldByLevelAndOrgId($info_owner_id, $arrOrgIds);
            $level = OrganizationalStructure::findOne($info_owner_id)->level;
        }        
        if(empty($arrWhereAndGroup))
        {
            $this->echoData(400,'数据异常');
        }        
        $field_str = $arrWhereAndGroup['where'];
        $strGroupBy = $arrWhereAndGroup['groupby'];

        //判断日报或月报  查询数据
        if($type == 'month'){
            $select_date = $this->getSelectData($date);
            $start_date = $select_date['start_date'];
            $end_date = $select_date['end_date'];
            $str = "create_date >= '{$start_date}' and create_date <= '{$end_date}'";
        }else{
            $str = "create_date = '{$date}'";
        }

        //根据层级查询线索信息
        //1.查询当前层级新增线索 跟进中线索
        $deliver_info = TjJichushuju::find()->select('sum(chengjiao_num) as chengjiao_num')
            ->where($field_str)
            ->andWhere($str)
            ->asArray()->one();



        $topData = [
            'num1'=>intval($deliver_info['chengjiao_num']),
            'info_owner_name'=>$arrWhereAndGroup['org_level_name'],
        ];

        //查询并处理下一级新增线索
        $deliver_info_child = TjJichushuju::find()->select('sum(chengjiao_num) as chengjiao_num,'.$strGroupBy.' as info_owner_id')
            ->where($field_str)
            ->andWhere($str)
            ->groupBy($strGroupBy)
            ->asArray()->all();

        foreach ($deliver_info_child as $value_new){
            $deliver_info_child_new[$value_new['info_owner_id']] = $value_new['chengjiao_num'];
        }

        $arrList = [];
        foreach($arrWhereAndGroup['nextList'] as $val)
        {
            $info = array();
            $info['pid'] = intval($val['pid']);
            $info['info_owner_id'] = ($val['id'] == 0 ? -10000 : intval($val['id'])); //0特殊处理;
            $info['info_owner_name'] = strval($val['name']);
            $info['num1'] = (isset($deliver_info_child_new[$val['id']]) ? intval($deliver_info_child_new[$val['id']]) : 0);
            if($val['id'] == 0 )//&& $info['num1'] == 0 && $info['num2'] == 0 && $info['num3'] == 0 && $info['num4'] == 0 
            {
                continue;//无顾问的行 都为0的时候不显示
            }
            $arrList[] = $info;
        }

        //组织返回数据
        $data['organizational_structure_level'] = intval($level);
        $is_last_level = 0;
        if($level == 20){//只到大区层级
            $is_last_level = 1;
        }
        $data['is_last_level'] = $is_last_level;

        $data['topData'] = empty($topData) ? (object)array() : $topData;
        $data['models'] = $arrList;
        $data['pages'] = [
            'totalCount' => count($arrList),
            'pageCount' => 1,
            'currentPage' => 1,
            'perPage' => count($arrList),
        ];

        $this->echoData(200,'获取成功',$data);
    }
    
    /**
     * 12、单店交车列表
     */
    public function actionDeliverCarListOfShop(){
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);

        //添加type字段！！！ 判断日报月报
        if(empty($p['date']) || empty($p['type'])){
            $this->echoData(400,'参数不全');
        }

        $date = $p['date'];
        $type = $p['type'];
        //根据信息来源id查询该层级信息及下一层级列表
        $info_owner_id = intval($p['info_owner_id']);
        if(empty($info_owner_id))
        {
            $thisShopId = $this->getShopId();//默认店铺id，店长登录进来的时候查看数据查看本店的
            if($thisShopId > 0)
            {
                $info_owner_id = $thisShopId;
            }
        }
        $info_owner_name = OrganizationalStructure::findOne($info_owner_id)->name;

        //判断日报或月报  查询数据
        if($type == 'month'){
            $select_date = $this->getSelectData($date);
            $start_date = $select_date['start_date'];
            $end_date = $select_date['end_date'];
            $start_time = strtotime($start_date);
            $end_time = strtotime($end_date)+(3600*24);
            $str = "car_delivery_time >= {$start_time} and car_delivery_time < {$end_time}";
        }else{
            $start_time = strtotime($date);
            $end_time = strtotime($date)+(3600*24);
            $str = "car_delivery_time >= {$start_time} and car_delivery_time < {$end_time}";
        }

        //查询交车数据
        $order_info = Order::find()->select('clue_id,customer_id,car_type_name,salesman_id,status')
            ->where(['=','shop_id',$info_owner_id])
            ->andWhere(['=','status',6])
            ->andWhere($str)
            ->asArray()->all();

        //取出所有顾客id
        $customer_id_list = array_column($order_info,'customer_id');
        $customer_info_list = Customer::find()->select('id as customer_id,name as customer_name,')->where(['in','id',$customer_id_list])->asArray()->all();

        $customer_info_list_new = array();
        foreach ($customer_info_list as $item){
            $customer_info_list_new[$item['customer_id']] = $item;
        }

        //取出所有顾问id
        $salesman_id_list = array_column($order_info,'salesman_id');
        $salesman_info_list = User::find()->select('id as salesman_id,name as salesman_name')->where(['in','id',$salesman_id_list])->asArray()->all();
        $salesman_info_list_new = array();
        foreach ($salesman_info_list as $item){
            $salesman_info_list_new[$item['salesman_id']] = $item;
        }

        //拼接顾问姓名客户姓名
        $order_info_list = array();
        foreach ($order_info as $value){
            $info = array();
            $info['clue_id'] = intval($value['clue_id']);
            $info['customer_id'] = intval($value['customer_id']);
            $info['customer_name'] = empty($customer_info_list_new[$value['customer_id']]['customer_name']) ? '' : $customer_info_list_new[$value['customer_id']]['customer_name'];
            $info['num1'] = strval($value['car_type_name']);
            $info['num2'] = empty($salesman_info_list_new[$value['salesman_id']]['salesman_name']) ? '' : $salesman_info_list_new[$value['salesman_id']]['salesman_name'];
            $order_info_list[] = $info;
        }

        $data['num1'] = count($order_info_list);
        $data['info_owner_name'] = $info_owner_name;
        $data['models'] = $order_info_list;
        $data['pages'] = [
            'totalCount' => count($order_info_list),
            'pageCount' => 1,
            'currentPage' => 1,
            'perPage' => count($order_info_list),
        ];

        $this->echoData(200,'获取成功',$data);
    }

    /**
     * 13、战败客户列表
     */
    public function actionFailClueList(){
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);

        //添加type字段！！！ 判断日报月报
        if(empty($p['date']) || empty($p['type'])){
            $this->echoData(400,'参数不全');
        }

        $date = $p['date'];
        $type = $p['type'];

        $info_owner_id = intval($p['info_owner_id']);
        if(empty($info_owner_id))
        {
            $thisShopId = $this->getShopId();//默认店铺id，店长登录进来的时候查看数据查看本店的
            if($thisShopId > 0)
            {
                $info_owner_id = $thisShopId;
            }
        }
        $objTongJiLogic = new TongJiLogic();
        $arrOrgIds = $this->userinfo['user_role_info'];
        if(empty($info_owner_id))//没有传某个组织过来的时候 以登录时的角色信息为准
        {
            $level = $this->userinfo['role_level'];
            $arrWhereAndGroup = $objTongJiLogic->getStrFieldByLevel($level, $arrOrgIds);
        }
        else//以选中的组织的层级为准
        {
            $arrWhereAndGroup = $objTongJiLogic->getSelectFieldByLevelAndOrgId($info_owner_id, $arrOrgIds);
            $level = OrganizationalStructure::findOne($info_owner_id)->level;
        }        
        if(empty($arrWhereAndGroup))
        {
            $this->echoData(400,'数据异常');
        }        
        $field_str = $arrWhereAndGroup['where'];
        $strGroupBy = $arrWhereAndGroup['groupby'];

        //判断日报或月报  查询数据
        if($type == 'month'){
            $select_date = $this->getSelectData($date);
            $start_date = $select_date['start_date'];
            $end_date = $select_date['end_date'];
            $str = "create_date >= '{$start_date}' and create_date <= '{$end_date}'";
        }else{
            $str = "create_date = '{$date}'";
        }

        //根据层级查询线索信息
        //1.查询当前层级新增线索 跟进中线索
        $fail_info = TjFailIntentionTagCount::find()->select('fail_type,sum(num) as sum_num')->where($field_str)
                ->andWhere(['in','fail_type',[2,3]])
            ->andWhere($str)
            ->groupBy('fail_type')
            ->asArray()->all();

        $fail_info_new = array();
        foreach ($fail_info as $value){
            $fail_info_new[$value['fail_type']] = $value;
        }

        $num1 = empty($fail_info_new[2]['sum_num']) ? 0 : intval($fail_info_new[2]['sum_num']);
        $num2 = empty($fail_info_new[3]['sum_num']) ? 0 : intval($fail_info_new[3]['sum_num']);
        $num3 = $num1 + $num2;

        $topData = [
            'num1'=>$num1,
            'num2'=>$num2,
            'num3' => $num3,
            'info_owner_name'=>$arrWhereAndGroup['org_level_name'],
        ];

        //查询并处理下一级新增线索
        $fail_info_child = TjFailIntentionTagCount::find()->select('fail_type,sum(num) as sum_num,'.$strGroupBy.' as info_owner_id')->where($field_str)
            ->andWhere($str)
            ->andWhere(['in','fail_type',[2,3]])
            ->groupBy($strGroupBy.',fail_type')
            ->asArray()->all();

        $fail_info_child_new = [];
        foreach ($fail_info_child as $value_new){
            $fail_info_child_new[$value_new['info_owner_id']][$value_new['fail_type']] = $value_new;
        }

        $arrList = [];
        foreach($arrWhereAndGroup['nextList'] as $val)
        {
            $info = array();
            $info['pid'] = intval($val['pid']);
            $info['info_owner_id'] = ($val['id'] == 0 ? -10000 : intval($val['id'])); //0特殊处理;
            $info['info_owner_name'] = strval($val['name']);
            if(isset($fail_info_child_new[$val['id']]))
            {
                $thisVal = $fail_info_child_new[$val['id']];
                $info['num1'] = ( empty($thisVal[2]) ? 0 : intval(intval($thisVal[2]['sum_num'])) );
                $info['num2'] = ( empty($thisVal[3]) ? 0 : intval(intval($thisVal[3]['sum_num'])) );
                $info['num3'] = $info['num1'] + $info['num2'];
            }
            else
            {
                $info['num1'] = $info['num2'] = $info['num3'] = 0;
            }
            if($val['id'] == 0 )//&& $info['num1'] == 0 && $info['num2'] == 0 && $info['num3'] == 0 && $info['num4'] == 0 
            {
                continue;//无顾问的行 都为0的时候不显示
            }
            $arrList[] = $info;
        }

        //组织返回数据
        $data['organizational_structure_level'] = intval($level);
        $is_last_level = 0;
        if($level == 20){
            $is_last_level = 1;
        }
        $data['is_last_level'] = $is_last_level;

        $data['topData'] = empty($topData) ? (object)array() : $topData;
        $data['models'] = $arrList;
        $data['pages'] = [
            'totalCount' => count($arrList),
            'pageCount' => 1,
            'currentPage' => 1,
            'perPage' => count($arrList),
        ];

        $this->echoData(200,'获取成功',$data);
    }

    /**
     * 14、门店战败客户列表
     */
    public function actionFailClueListOfShop(){
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);

        //添加type字段！！！ 判断日报月报
        if(empty($p['date']) || empty($p['type'])){
            $this->echoData(400,'参数不全');
        }

        $date = $p['date'];
        $type = $p['type'];
        //根据信息来源id查询该层级信息及下一层级列表
        $info_owner_id = intval($p['info_owner_id']);
        if(empty($info_owner_id))
        {
            $thisShopId = $this->getShopId();//默认店铺id，店长登录进来的时候查看数据查看本店的
            if($thisShopId > 0)
            {
                $info_owner_id = $thisShopId;
            }
        }
        $info_owner_name = OrganizationalStructure::findOne($info_owner_id)->name;
        //判断日报或月报  查询数据
        if($type == 'month'){
            $select_date = $this->getSelectData($date);
            $start_date = $select_date['start_date'];
            $end_date = $select_date['end_date'];
            $start_time = strtotime($start_date);
            $end_time = strtotime($end_date)+(3600*24);
            $str = "last_fail_time >= {$start_time} and last_fail_time < {$end_time}";
        }else{
            $start_time = strtotime($date);
            $end_time = strtotime($date)+(3600*24);
            $str = "last_fail_time >= {$start_time} and last_fail_time < {$end_time}";
        }


        $clue_info = Clue::find()->select('id,customer_id,customer_name,fail_reason,salesman_name,status')
            ->where(['=','shop_id',$info_owner_id])
            ->andWhere(['=','is_fail',1])
            ->andWhere(['in','status',[1,2]])
            ->andWhere($str)
            ->asArray()->all();


        $clue_info_list = array();
        foreach ($clue_info as $value){
            $info = array();
            $info['clue_id'] = intval($value['id']);
            $info['customer_id'] = intval($value['customer_id']);
            $info['customer_name'] = $value['customer_name'];
            if($value['status'] == 1){
                $num1 = '意向战败';
            }elseif($value['status'] == 2){
                $num1 = '订车战败';
            }
            $info['num1'] = $num1;
            $info['num2'] = strval($value['salesman_name']);
            $info['num3'] = strval($value['fail_reason']);
            $clue_info_list[] = $info;
        }
        $data['info_owner_name'] = $info_owner_name;
        $data['num1'] = count($clue_info_list);
        $data['models'] = $clue_info_list;
        $data['pages'] = [
            'totalCount' => count($clue_info_list),
            'pageCount' => 1,
            'currentPage' => 1,
            'perPage' => count($clue_info_list),
        ];

        $this->echoData(200,'获取成功',$data);
    }
    
    /**
     * 客户详情接口
     */
    public function actionCustomerDetail(){
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);
        if(empty($p['clue_id'])){
            $this->echoData(400,'参数不全');
        }
        $clue_id = $p['clue_id'];
        //查询客户信息
        $info_clue = Clue::find()->select('customer_id,customer_name,des,salesman_name,status,is_fail,clue_source')->where(['=','id',$clue_id])->asArray()->one();
        $customer_id = $info_clue['customer_id'];
        $info_customer = Customer::find()->select('phone,spare_phone,weixin,sex,birthday,profession,area,address,age_group_level_id')->where(['=','id',$customer_id])->asArray()->one();
        $customer_info = array_merge($info_clue,$info_customer);
        $status = $info_clue['status'];
        switch (intval($status))
        {
            case 1:  //意向客户
                $info_name = 'intention_info';
                $info = Clue::find()->select('intention_level_des,intention_des,spare_intention_id,buy_type,planned_purchase_time_id,quoted_price,sales_promotion_content,contrast_intention_id,')->where(['=','id',$clue_id])->asArray()->one();
            break;
            case 2:  //订车客户
                $info_name = 'order_info';
                $info = Order::find()->select('create_time,predict_car_delivery_time,car_type_name,color_configure,buy_type,loan_period,deposit,delivery_price,discount_price,is_insurance,is_add,add_content,give')->where(['=','clue_id',$clue_id])->asArray()->one();
                break;
            case 3:  //成交客户客户
                $info_name = 'deal_info';
                $info = Order::find()->select('create_time,car_type_name,car_number,engine_code,frame_number,color_configure,buy_type,loan_period,deposit,delivery_price,discount_price,is_insurance,insurance_time,is_add,add_content,give,
                car_owner_name,car_owner_phone')->where(['=','clue_id',$clue_id])->asArray()->one();
                
                $customer_info['car_owner_name'] = $info['car_owner_name'];
                $customer_info['car_owner_phone'] = $info['car_owner_phone'];
                $customer_info['deliverman_name'] = $customer_info['salesman_name'];
                break;
            case 4:  //战败客户客户
                $info_name = 'fail_info';
                $info = Clue::find()->select('intention_level_des,intention_des,spare_intention_id,buy_type,planned_purchase_time_id,quoted_price,sales_promotion_content,contrast_intention_id')->where(['=','id',$clue_id])->asArray()->one();
                break;
            default:
                return false;
        }

        $obj = new DataDictionary();
        $customer_info['clue_source_name'] = $obj->getSourceName($customer_info['clue_source']);
        $customer_info['area_name'] = $obj->areaCodeToName($customer_info['area']);
        $customer_info['age'] = $obj->getAgeGroupName($customer_info['age_group_level_id']);
        $customer_info['profession'] = $obj->getProfessionName($customer_info['profession']);
        $info['buy_type_name'] = $obj->getBuyTypeName($info['buy_type']);

        foreach ($customer_info as $key=>$value){
            $customer_info[$key] = strval($value);
        }
        foreach ($info as $key2=>$value2){
            $info[$key2] = strval($value2);
        }
        if(isset($info['spare_intention_id'])){
            $info['spare_intention_name'] = $obj->getCarName($info['spare_intention_id']);
        }
        if(isset($info['planned_purchase_time_id'])){
            $info['planned_purchase_time_name'] = $obj->getPlannedPurchaseTime($info['planned_purchase_time_id']);
        }

        $customer_info['status'] = intval($customer_info['status']);
        $customer_info['is_fail'] = intval($customer_info['is_fail']);
        $customer_info['sex'] = intval($customer_info['sex']);

        if(isset($info['create_time'])){
            $info['create_time'] = intval($info['create_time']);
        }
        if(isset($info['is_insurance'])){
            $info['is_insurance'] = intval($info['is_insurance']);
        }
        if(isset($info['predict_car_delivery_time'])){
            $info['predict_car_delivery_time'] = intval($info['predict_car_delivery_time']);
        }
        if(isset($info['insurance_time'])){
            $info['insurance_time'] = intval($info['insurance_time']);
        }

        $data['customer_info'] = $customer_info;
        $data[$info_name] = $info;

        $this->echoData(200,'获取成功',$data);
    }


    /**
     * 线索商谈记录
     */
    public function actionTalkRecord(){

        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);

        if(empty($p['clue_id'])){
            $this->echoData(400,'参数不全');
        }
        $clue_id = $p['clue_id'];


        $data = TalkLogic::instance()->getTalk($clue_id);
        $list = $data['list'];
        $list_new = array();
        foreach ($list as $item){
            $list_new[$item['talk_type']] = $item;
        }
        if(!empty($list_new[2])){
            $list_new2[] = $list_new[2];
        }else{
            $list_new2[] = ['talk_type'=>'2','count'=>'0'];
        }

        if(!empty($list_new[3])){
            $list_new2[] = $list_new[3];
        }else{
            $list_new2[] = ['talk_type'=>'3','count'=>'0'];
        }
        if(!empty($list_new[4])){
            $list_new2[] = $list_new[4];
        }else{
            $list_new2[] = ['talk_type'=>'4','count'=>'0'];
        }

        $count5_1 = empty($list_new[5]) ? 0 :$list_new[5]['count'];
        $count5_2 = empty($list_new[6]) ? 0 :$list_new[6]['count'];
        $count5_3 = empty($list_new[7]) ? 0 :$list_new[7]['count'];
        $count5 = $count5_1 + $count5_2 + $count5_3;

        $list_new2[] = ['talk_type'=>'5','count'=>strval($count5)];

        $count6_1 = empty($list_new[8]) ? 0 :$list_new[8]['count'];
        $count6_2 = empty($list_new[9]) ? 0 :$list_new[9]['count'];
        $count6_3 = empty($list_new[10]) ? 0 :$list_new[10]['count'];
        $count6 = $count6_1 + $count6_2 + $count6_3;

        $list_new2[] = ['talk_type'=>'6','count'=>strval($count6)];

        if(!empty($list_new[19])){
            $list_new2[] = $list_new[19];
        }else{
            $list_new2[] = ['talk_type'=>'19','count'=>'0'];
        }
        $data['list'] = $list_new2;

        $this->echoData(200,'获取成功',$data);
    }

    /**
     * 任务记录
     */
    public function actionTaskRecord(){

        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);

        if(empty($p['clue_id'])){
            $this->echoData(400,'参数不全');
        }
        $clue_id = $p['clue_id'];

        $models = Task::find()->select(['task_from', 'task_time', 'is_cancel', 'is_finish', 'task_date' ,'cancel_reason'])
            ->where("clue_id={$clue_id}")->andWhere([
                'task_type' => 1
            ])->orderBy('task_date asc')
            ->asArray()
            ->all();


        $data = [];
        foreach ($models as $k => $model) {
            $data[$k]['task_from'] = strval($model['task_from']);
            $data[$k]['cancel_reason'] = strval($model['cancel_reason']);
            $data[$k]['task_time'] = intval(strtotime($model['task_date']));
            $data[$k]['is_cancel'] = intval($model['is_cancel']);
            $data[$k]['is_finish'] = intval($model['is_finish']);
        }

        if(Clue::findOne($clue_id)){
            $des = Clue::findOne($clue_id);
        }else{
            $this->echoData(400,'该线索不存在');
        }

        //获取意向等级字典数据
        $obj = new DataDictionary();
        $intention = $obj->getDictionaryData('intention');
        $info = [
            'models' => $data,
            'intention_level_des' => $des->intention_level_des,
            'list' => $intention,
        ];
        $this->echoData(200,'获取成功',$info);
    }


    /**
     * 交谈记录详情
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
            {
                $data[$k]['content'] = "商谈内容：" . $model['content'];
            }
            else
            {
                $data[$k]['content'] = '';
            }

            $data[$k]['tag'] = [];

            if (!empty($model['select_tags']))
            {
                $data[$k]['tag'] = $model['select_tags'];
            }

            if (!empty($data[$k]['tag'])) {
                $data[$k]['tag'] = explode(',', $data[$k]['tag']);
                $objDataDic = new DataDictionary();//数据字典操作
                $data[$k]['tag_name'] = $objDataDic->getTagNamebyIds($data[$k]['tag']);
            }
            $data[$k]['create_time'] = $model['create_time'];
            $data[$k]['castomer_id'] = $model['castomer_id'];
            $data[$k]['voices_times'] = intval($model['voices_times']);

        }
        return $data;
    }

    /**
     * 查询提车任务列表
     */
    public function actionMentionCarList()
    {
        // 请求参信息
        $params = json_decode($this->mixRequest, true);
        $info_owner_id = empty($params['info_owner_id']) ? 0 : intval($params['info_owner_id']);
        $intStartTime = empty($params['date']) ? strtotime(date('Y-m-d')) : strtotime($params['date']);
        if (empty($params['type'])) {
            $params['type'] = 'day';
        }

        // 判断日报或月报  查询数据
        if ($params['type'] == 'month') {
            $intEndTime = strtotime(date('Y-m-01', $intStartTime) . ' +1 month');
        } else {
            $intEndTime = $intStartTime + 86400;
        }

        // 处理默认一个组织架构
        if (empty($info_owner_id)) {
            // 默认店铺id，店长登录进来的时候查看数据查看本店的
            $thisShopId = $this->getShopId();
            if($thisShopId > 0) {
                $info_owner_id = $thisShopId;
            }
        }

        $objTongJiLogic = new TongJiLogic();
        $arrOrgIds = $this->userinfo['user_role_info'];

        // 判断组织架构是否传递
        if (empty($info_owner_id)) {
            // 没有传某个组织过来的时候 以登录时的角色信息为准
            $level = $this->userinfo['role_level'];
            $arrWhereAndGroup = $objTongJiLogic->getStrFieldByLevel($level, $arrOrgIds);
        } else {
            // 以选中的组织的层级为准
            $arrWhereAndGroup = $objTongJiLogic->getSelectFieldByLevelAndOrgId($info_owner_id, $arrOrgIds);
            $structure = OrganizationalStructure::findOne($info_owner_id);
            $level = $structure ? $structure->level : 0;
        }

        // 不存在数据
        if (empty($arrWhereAndGroup)) {
            $this->echoData(400,'数据异常');
        }

        // 汇总的数据
        $topData = [
            'num1' => 0,
            'info_owner_name' => $arrWhereAndGroup['org_level_name'],
        ];

        $arrReturn = [];
        $objCompany = new CompanyUserCenter();
        foreach($arrWhereAndGroup['nextList'] as $val) {
            if ($val['id'] != 0) {
                if ($level >= OrganizationalStructure::LEVEL_STORE ) {
                    // 查询数据
                    $intNum = (int)PutTheCar::find()->where([
                        'and',
                        ['new_shop_id' => (int)$val['pid']],
                        ['=', 'new_salesman_id', (int)$val['id']],
                        ['!=', 'status', PutTheCar::STATUS_DELETE],
                        ['between', 'claim_time', $intStartTime, $intEndTime]
                    ])->count();
                } else {
                    if ($level == OrganizationalStructure::LEVEL_REGION) {
                        $arrIds = $val['id'];
                    } else {
                        $arrIds = $objCompany->getShopIdsByOrgIds($val['id'], $arrOrgIds);
                    }

                    // 查询数据
                    $intNum = PutTheCar::getMentionCount($arrIds, $intStartTime, $intEndTime);
                }

                $topData['num1'] += $intNum;

                $arrReturn[] = [
                    'pid' => (int)$val['pid'],
                    'info_owner_id' => $val['id'] == 0 ? -10000 : intval($val['id']),
                    'info_owner_name' => $val['name'],
                    'num1' => $intNum,
                ];
            }
        }

        // 返回数据
        $data = [
            'organizational_structure_level' => (int)$level,
            'is_last_level' => $level == OrganizationalStructure::LEVEL_STORE ? 1 : 0,
            'topData' => empty($topData) ? (object)array() : $topData,
            'models' => $arrReturn,
            'pages' => [
                'totalCount' => count($arrReturn),
                'pageCount' => 1,
                'currentPage' => 1,
                'perPage' => count($arrReturn),
            ]
        ];

        // 记录日志返回
        $this->echoData(200,'获取成功', $data);
    }

    /**
     * 提车任务 - 顾问信息列表
     */
    public function actionMentionCarListSalesman()
    {
        // 请求参信息
        $params = json_decode($this->mixRequest, true);
        $info_owner_id = empty($params['info_owner_id']) ? 0 : intval($params['info_owner_id']);
        $intStartTime = empty($params['date']) ? strtotime(date('Y-m-d')) : strtotime($params['date']);
        if (empty($params['type'])) {
            $params['type'] = 'day';
        }

        // 判断日报或月报  查询数据
        if ($params['type'] == 'month') {
            $intEndTime = strtotime(date('Y-m-01', $intStartTime) . ' +1 month');
        } else {
            $intEndTime = $intStartTime + 86400;
        }

        // 处理顾问信息
        if ($info_owner_id > 0) {
            $user = User::findOne($info_owner_id);
            if ($user) {
                $info_owner_name = $user->name;
                // 默认查询条件
                $where = [
                    'and',
                    ['=', 'new_salesman_id', $info_owner_id],
                    ['!=', 'status', PutTheCar::STATUS_DELETE],
                    ['between', 'claim_time', $intStartTime, $intEndTime]
                ];
                if (!empty($params['pid'])) {
                    array_push($where, ['new_shop_id' => $params['pid']]);
                }

                $arrReturn = PutTheCar::find()
                    ->select(['clue_id', 'customer_name', 'customer_phone', 'yu_ding_che_xing','new_salesman_name'])
                    ->where($where)->orderBy(['claim_time' => SORT_DESC])->asArray()->all();
                if ($arrReturn) {
                    foreach ($arrReturn as &$value) {
                        $value['num1'] = $value['yu_ding_che_xing'];
                        $value['num2'] = $value['new_salesman_name'];
                        $value['clue_id'] = (int)$value['clue_id'];
                        unset($value['new_salesman_name'], $value['yu_ding_che_xing']);
                    }

                    unset($value);
                }

                // 返回数据
                $data = [
                    'info_owner_name' => $info_owner_name,
                    'num1' => count($arrReturn),
                    'models' => $arrReturn,
                    'pages' => [
                        'totalCount' => count($arrReturn),
                        'pageCount' => 1,
                        'currentPage' => 1,
                        'perPage' => count($arrReturn),
                    ]
                ];

                // 记录日志返回
                $this->echoData(200,'获取成功', $data);
            }
        }

        $this->echoData(400, '顾问信息有问题');
    }

}
?>