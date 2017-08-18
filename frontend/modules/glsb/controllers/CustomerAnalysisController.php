<?php
namespace frontend\modules\glsb\controllers;

use common\logic\DataDictionary;
use common\models\TjFailIntentionTagCount;
use common\models\TjThisMonthIntention;
use common\models\TjThisMonthNewClue;
use Yii;
use common\logic\CustomerAnalysisLogic;
use common\models\OrganizationalStructure;
use common\models\InputType;
use common\logic\TongJiLogic;

class CustomerAnalysisController extends AuthController
{
    //转化漏斗接口
    public function actionConversionFunnel()
    {
        $p = json_decode(Yii::$app->request->post('p'),true);
        //接收参数 包括渠道信息、区域门店信息
        if(!empty($p['input_type_id'])){
            $input_type_id = $p['input_type_id'];
        }else{
            $input_type_id = 'all';
        }

        if(!empty($p['month'])){
            $month = $p['month'];
        }else{
            $month = date('Y-m');
        }

        $arrOrgIds = $this->userinfo['user_role_info'];
        $level = $this->userinfo['role_level'];
        $arrInfoOwnerIdTmp = explode(',', $p['info_owner_id']);
        $info_owner_id = array_pop($arrInfoOwnerIdTmp);//逗号分隔的  取最后一个
        if(empty($info_owner_id))
        {
            $thisShopId = $this->getShopId();//默认店铺id，店长登录进来的时候查看数据查看本店的
            if($thisShopId > 0)
            {
                $info_owner_id = $thisShopId;
            }
        }

        //保存默认信息
        $data_common['input_type_id'] = $input_type_id;
        $data_common['month'] = $month;
        $data_common['info_owner_id'] = $info_owner_id;


        //根据日期计算当月第一天
        $start_date=date('Y-m-01', strtotime($month));
        $end_date =  date('Y-m-d', strtotime("$start_date +1 month -1 day"));

        //查询信息
        $logic = new CustomerAnalysisLogic();
        $data = $logic->getConversionFunnel($input_type_id,$info_owner_id,$start_date,$end_date, $arrOrgIds, $level);

        //处理真实数据信息
        $real_data = array();
        foreach ($data['funnel_real_data_rate'] as $key=>$value){
            $info = array();

            if($key == 'new_clue_num'){
                $item = '新增线索';
            }elseif ($key == 'new_intention_num'){
                $item = '新增意向';
            }elseif ($key == 'to_shop_num'){
                $item = '来店数量';
            }elseif ($key == 'dingche_num'){
                $item = '订车数量';
            }else{
                continue;
            }

            $info['name'] = $item;
            $info['value'] = $value;
            $real_data[] = $info;
        }

        //处理期望数据信息
        $expect_data = array();
        foreach ($data['funnel_expect_data'] as $key=>$value){
            $info = array();

            if($key == 'new_clue_num'){
                $item = '新增线索';
            }elseif ($key == 'new_intention_num'){
                $item = '新增意向';
            }elseif ($key == 'to_shop_num'){
                $item = '来店数量';
            }elseif ($key == 'dingche_num'){
                $item = '订车数量';
            }else{
                continue;
            }

            $info['name'] = $item;

            $info['value'] = $value;
            $expect_data[] = $info;
        }

        //返回数据
        $data_funnel['real_data'] = $real_data;
        $data_funnel['expect_data'] = $expect_data;
        $data_funnel['data_common'] = $data_common;

        echo json_encode($data_funnel);
    }
    /**
     * 意向战败客户
     */
    public function actionGetIntentionFailCustomer()
    {
        //接收参数  包含渠道来源 区域门店 开始结束时间
        $p = json_decode(Yii::$app->request->post('p'),true);

        if(!empty($p['input_type_id'])){
            $input_type_id = $p['input_type_id'];
        }else{
            $input_type_id = 'all';
        }

        if(!empty($p['month'])){
            $month = $p['month'];
        }else{
            $month = date('Y-m');
        }

        $arrOrgIds = $this->userinfo['user_role_info'];
        $level = $this->userinfo['role_level'];
        $arrInfoOwnerIdTmp = explode(',', $p['info_owner_id']);
        $info_owner_id = array_pop($arrInfoOwnerIdTmp);//逗号分隔的  取最后一个
        if(empty($info_owner_id))
        {
            $thisShopId = $this->getShopId();//默认店铺id，店长登录进来的时候查看数据查看本店的
            if($thisShopId > 0)
            {
                $info_owner_id = $thisShopId;
            }
        }


        $data_common['input_type_id'] = $input_type_id;
        $data_common['month'] = $month;
        $data_common['info_owner_id'] = $info_owner_id;


        //根据日期计算当月第一天
        $start_date=date('Y-m-01', strtotime($month));
        $end_date =  date('Y-m-d', strtotime("$start_date +1 month -1 day"));

        //查询数据
        $logic = new CustomerAnalysisLogic();
        $data = $logic->getIntentionFailCustomer($info_owner_id,$input_type_id,$start_date,$end_date, $arrOrgIds, $level);

        $num = 0;
        $data_1 = $data['fail_info'];
        foreach ($data_1 as $value1){
            $num += empty($value1['sum_num']) ? 0 : $value1['sum_num'];
        }

        $data_2 = array();
        foreach ($data_1 as $key=>$value2){
            $info['name'] = $value2['tag_name'];
            $info['value'] = $value2['sum_num'];

            $data_2[] = $info;

            $data_1[$key]['proportion'] = @round($value2['sum_num']*100/$num);
            unset($data_1[$key]['tag_id']);
        }

        //返回数据
        $data_new['chart'] = $data_2;
        $data_new['list'] = $data_1;
        $data_new['data_common'] = $data_common;

        echo json_encode($data_new);
    }


    //获取订车战败客户数据
    public function actionGetOrderFailCustomer()
    {
        //获取参数  包含开始日期 结束日期 当前页码  每页数量
        $p = json_decode(Yii::$app->request->post('p'),true);

        if(!empty($p['input_type_id'])){
            $input_type_id = $p['input_type_id'];
            $input_type_str = "input_type_id = {$input_type_id}";
        }else{
            $input_type_id = 'all';
            $input_type_str = '1=1';
        }

        if(!empty($p['month'])){
            $month = $p['month'];
        }else{
            $month = date('Y-m');
        }

        $arrOrgIds = $this->userinfo['user_role_info'];
        $level = $this->userinfo['role_level'];
        $arrInfoOwnerIdTmp = explode(',', $p['info_owner_id']);
        $info_owner_id = array_pop($arrInfoOwnerIdTmp);//逗号分隔的  取最后一个
        if(empty($info_owner_id))
        {
            $thisShopId = $this->getShopId();//默认店铺id，店长登录进来的时候查看数据查看本店的
            if($thisShopId > 0)
            {
                $info_owner_id = $thisShopId;
            }
        }

        //保存默认信息
        $data_common['input_type_id'] = $input_type_id;
        $data_common['month'] = $month;
        $data_common['info_owner_id'] = $info_owner_id;

        //根据日期计算当月第一天
        $start_date=date('Y-m-01', strtotime($month));
        $end_date =  date('Y-m-d', strtotime("$start_date +1 month -1 day"));

        //根据info_owner_id查询所属层级 判断查询条件
        $objTongJiLogic = new TongJiLogic();
        if(empty($info_owner_id))//没有传某个组织过来的时候 以登录时的角色信息为准
        {
            $arrWhereAndGourp = $objTongJiLogic->getStrFieldByLevel($level, $arrOrgIds);
        }
        else//以选中的组织的层级为准
        {
            $arrWhereAndGourp = $objTongJiLogic->getSelectFieldByLevelAndOrgId($info_owner_id, $arrOrgIds);
        }
        $field_str = $arrWhereAndGourp['where'];

        //查询当前层级战败原因数据
        $fail_info = TjFailIntentionTagCount::find()->select('sum(num) as sum_num,tag_id')->where($field_str)
            ->andWhere(['>=','create_date',$start_date])->andWhere(['<=','create_date',$end_date])
            ->andWhere(['=','fail_type',3])->andWhere($input_type_str)
            ->groupBy('tag_id')->asArray()->all();

        //根据tag_id查询失败原因
        //获取失败原因词典
        $data_dictionary = new DataDictionary();

        $order_fail_tag = $data_dictionary->getDictionaryData('fail_tags')['order_fail'];

        //处理返回标签数据
        $order_fail_tag_new = array();
        foreach ($order_fail_tag as $value){
            $order_fail_tag_new = array_merge($order_fail_tag_new,$value);
        }

        //拼接意向等级数据为id作为键值的数组
        foreach ($order_fail_tag_new as $item){
            $fail_tags_new[$item['id']] = $item;
        }

        //为数据列表添加失败标签名称
        foreach ($fail_info as $key_in=>$item_in){
            if(isset($fail_tags_new[$item_in['tag_id']]))
            {
                $fail_info[$key_in]['tag_name'] = $fail_tags_new[$item_in['tag_id']]['name'];
            }
            else
            {
                $fail_info[$key_in]['tag_name'] = '未知';
            }
        }

        //求出总数量
        $num = 0;
        foreach ($fail_info as $value1){
            $num += empty($value1['sum_num']) ? 0 : $value1['sum_num'];
        }

        //给定默认值
        if(empty($fail_info)){
            $fail_info[] = [
                'sum_num' => '0',
                'tag_name' => '无数据',
                'proportion' => 0
            ];
        }

        //初始化chart数组
        $data_2 = array();
        foreach ($fail_info as $key=>$value2){
            $info['name'] = $value2['tag_name'];
            $info['value'] = $value2['sum_num'];

            $data_2[] = $info;

            //计算百分比
            $fail_info[$key]['proportion'] = @round($value2['sum_num']*100/$num);
            unset($fail_info[$key]['tag_id']);
        }

        //返回数据
        $data_new['chart'] = $data_2;
        $data_new['list'] = $fail_info;
        $data_new['data_common'] = $data_common;

        echo json_encode($data_new);die;
    }


    /**
     * H5意向等级分析接口
     */
    public function actionIntentionLevel()
    {
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);

        if(!empty($p['input_type_id'])){
            $input_type_id = $p['input_type_id'];
        }else{
            $input_type_id = 'all';
        }

        if(!empty($p['month'])){
            $month = $p['month'];
        }else{
            $month = date('Y-m');
        }

        $arrOrgIds = $this->userinfo['user_role_info'];
        $level = $this->userinfo['role_level'];
        $arrInfoOwnerIdTmp = explode(',', $p['info_owner_id']);
        $info_owner_id = array_pop($arrInfoOwnerIdTmp);//逗号分隔的  取最后一个
        if(empty($info_owner_id))
        {
            $thisShopId = $this->getShopId();//默认店铺id，店长登录进来的时候查看数据查看本店的
            if($thisShopId > 0)
            {
                $info_owner_id = $thisShopId;
            }
        }

        $data_common['input_type_id'] = $input_type_id;
        $data_common['month'] = $month;
        $data_common['info_owner_id'] = $info_owner_id;

        //查询数据
        $logic = new CustomerAnalysisLogic();
        $data = $logic->actionIntentionLevel($info_owner_id,$input_type_id, $arrOrgIds, $level);

        $data_1 = $data['intention_info'];

        $num = 0;
        foreach ($data_1 as $value1){
            $num += empty($value1['sum_num']) ? 0 : $value1['sum_num'];
        }

        $data_2 = array();
        foreach ($data_1 as $key=>$value){

            $data_1[$key]['proportion'] = @round($value['sum_num']*100/$num);

            if($value['intention_level_name'] == '无数据'){
                $info['name'] = $value['intention_level_name'];
                $data_1[$key]['intention_level_name'] = $value['intention_level_name'];
            }else{
                $info['name'] = $value['intention_level_name'].'级';
                $data_1[$key]['intention_level_name'] = $value['intention_level_name'].'级';
            }
            unset($data_1[$key]['intention_level_id']);

            $info['value'] = $value['sum_num'];

            $data_2[] = $info;

        }

        //返回数据
        $data_new['chart'] = $data_2;
        $data_new['list'] = $data_1;
        $data_new['data_common'] = $data_common;

        echo json_encode($data_new);
    }


    //线索渠道来源分析
    public function actionInputType()
    {
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);

        if(!empty($p['month'])){
            $month = $p['month'];
        }else{
            $month = date('Y-m');
        }

        $arrOrgIds = $this->userinfo['user_role_info'];
        $level = $this->userinfo['role_level'];
        $arrInfoOwnerIdTmp = explode(',', $p['info_owner_id']);
        $info_owner_id = array_pop($arrInfoOwnerIdTmp);//逗号分隔的  取最后一个
        if(empty($info_owner_id))
        {
            $thisShopId = $this->getShopId();//默认店铺id，店长登录进来的时候查看数据查看本店的
            if($thisShopId > 0)
            {
                $info_owner_id = $thisShopId;
            }
        }

        //保存默认信息
        $data_common['month'] = $month;
        $data_common['info_owner_id'] = $info_owner_id;

        //根据info_owner_id查询所属层级 判断查询条件
        $objTongJiLogic = new TongJiLogic();
        if(empty($info_owner_id))//没有传某个组织过来的时候 以登录时的角色信息为准
        {
            $arrWhereAndGourp = $objTongJiLogic->getStrFieldByLevel($level, $arrOrgIds);
        }
        else//以选中的组织的层级为准
        {
            $arrWhereAndGourp = $objTongJiLogic->getSelectFieldByLevelAndOrgId($info_owner_id, $arrOrgIds);
        }
        $field_str = $arrWhereAndGourp['where'];
//        $groupBy = $arrWhereAndGourp['groupby'];
        
        //查询渠道id名称对应关系
        $input_type = InputType::find()->asArray()->all();
        foreach ($input_type as $value){
            $input_type_new[$value['id']] = $value;
        }

        //处理线索信息
        $clue_input_type_info = TjThisMonthNewClue::find()->select('sum(num) as sum_num,input_type_id')
            ->where($field_str)
            ->andWhere(['=','year_and_month',$month])
            ->groupBy('input_type_id')
            ->asArray()->all();

        $clue_num = 0;
        foreach ($clue_input_type_info as $value1){
            //input_type_id = 0 一般不会出现
            if($value1['input_type_id'] == 0){
                continue;
            }
            $clue_num += empty($value1['sum_num']) ? 0 : $value1['sum_num'];
        }


        $clue_data = array();
        $clue_list = array();
        foreach ($clue_input_type_info as $key=>$value){

            //input_type_id = 0 一般不会出现
            if($value['input_type_id'] == 0){
                unset($clue_input_type_info[$key]);
                continue;
            }

            $value['input_type_name'] = $input_type_new[$value['input_type_id']]['name'];
            $value['proportion'] = @round($value['sum_num']*100/$clue_num);
            unset($value['input_type_id']);
            $clue_list[] = $value;


            $info['name'] = $value['input_type_name'];
            $info['value'] = $value['sum_num'];
            $clue_data[] = $info;
        }

        //给定默认值
        if(empty($clue_data)){
            $clue_data[] = [
                'name' => '无数据',
                'value' => '0'
            ];
        }
        //给定默认值
        if(empty($clue_list)){
            $clue_list[] = [
                'sum_num' => '0',
                'input_type_name' => '无数据',
                'proportion' => 0
            ];
        }

        $data_new['clue']['chart'] = $clue_data;
        $data_new['clue']['list'] = $clue_list;


        //处理意向客户信息
        //处理线索信息
        $intention_input_type_info = TjThisMonthIntention::find()->select('sum(num) as sum_num,input_type_id')
            ->where($field_str)
            ->andWhere(['=','year_and_month',$month])
            ->groupBy('input_type_id')
            ->asArray()->all();


        $intention_num = 0;
        foreach ($intention_input_type_info as $value2){
            //input_type_id = 0 一般不会出现
            if($value2['input_type_id'] == 0){
                continue;
            }
            $intention_num += empty($value2['sum_num']) ? 0 : $value2['sum_num'];
        }


        $intention_data = array();
        $intentionf_list = array();
        foreach ($intention_input_type_info as $key=>$value){

            //input_type_id = 0 一般不会出现
            if($value['input_type_id'] == 0){
                unset($intention_input_type_info[$key]);
                continue;
            }

            $value['input_type_name'] = $input_type_new[$value['input_type_id']]['name'];
            $value['proportion'] = @round($value['sum_num']*100/$intention_num);
            unset($value['input_type_id']);
            $intentionf_list[] = $value;

            $info['name'] = $value['input_type_name'];
            $info['value'] = $value['sum_num'];
            $intention_data[] = $info;
        }

        //返回数据
        //给定默认值
        if(empty($intention_data)){
            $intention_data[] = [
                'name' => '无数据',
                'value' => '0'
            ];
        }
        //给定默认值
        if(empty($intentionf_list)){
            $intentionf_list[] = [
                'sum_num' => '0',
                'input_type_name' => '无数据',
                'proportion' => 0
            ];
        }
        $data_new['intention']['chart'] = $intention_data;
        $data_new['intention']['list'] = $intentionf_list;
        $data_new['data_common'] = $data_common;

        echo json_encode($data_new);die;
    }



}
?>