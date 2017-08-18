<?php
namespace common\logic;

use common\models\OrganizationalStructure;
use common\models\TjZhuanhualoudou;
use common\models\User;
use common\models\TjFailIntentionTagCount;
use Yii;
use common\models\Clue;
use common\models\Order;
use common\models\TjIntentionLevelCount;
use common\logic\TongJiLogic;
class CustomerAnalysisLogic extends BaseLogic
{

    public function getConversionFunnel($input_type_id,$info_owner_id,$start_date,$end_date, $arrOrgIds, $level)
    {
        //默认查看所有渠道
        if($input_type_id == 'all'){
            $input_type_str = '1=1';
        }else{
            $input_type_str = "input_type_id = {$input_type_id}";
        }

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
        $groupBy = $arrWhereAndGourp['groupby'];


        //查询当前层级信息
        $funnel_sql = TjZhuanhualoudou::find()->select('sum(new_clue_num) as new_clue_num,sum(new_intention_num) as new_intention_num,sum(to_shop_num) as to_shop_num,sum(dingche_num) as dingche_num')
            ->where($input_type_str)
            ->andWhere(['>=','create_date',$start_date])
            ->andWhere(['<=','create_date',$end_date])
            ->andWhere($field_str);
        $funnel_real_data = $funnel_sql->asArray()->one();

        //查询下一层级信息 并按照下一层级分组
        $table_sql = TjZhuanhualoudou::find()->select($groupBy.' as info_owner_id,sum(new_clue_num) as new_clue_num,sum(new_intention_num) as new_intention_num,sum(to_shop_num) as to_shop_num,sum(dingche_num) as dingche_num')
            ->where($field_str)
            ->groupBy($groupBy);

        $table_data = $table_sql->andWhere($input_type_str)
            ->andWhere(['>=','create_date',$start_date])
            ->andWhere(['<=','create_date',$end_date])
            ->asArray()->all();

        //对子级列表以id作为键值
        $child_list_new = array();
        foreach ($arrWhereAndGourp['nextList'] as $item_ch_l) {
            $child_list_new[$item_ch_l['id']] = $item_ch_l;
        }

        //对子级信息按照子级组织架构id进行分组
        foreach ($table_data as $value){
            $table_data_new[$value['info_owner_id']] = $value;
        }

        //按照子级列表处理每个子级的数据  没有数值用0补齐
        $table_data_list = array();
        foreach ($child_list_new as $key_ch_n=>$value_ch_n){

            //没有数值用0补齐
            if(empty($table_data_new[$key_ch_n])){
                $info = [
                    'new_clue_num' =>  '0',
                    'new_intention_num' =>  '0' ,
                    'to_shop_num' =>  '0',
                    'dingche_num' =>  '0'
                ];

            }else{

                $info = $table_data_new[$key_ch_n];
                if(empty($info['new_clue_num'])){
                    $info['new_clue_num'] = '0';
                }
                if(empty($info['new_intention_num'])){
                    $info['new_intention_num'] = '0';
                }
                if(empty($info['to_shop_num'])){
                    $info['to_shop_num'] = '0';
                }
                if(empty($info['dingche_num'])){
                    $info['dingche_num'] = '0';
                }

            }
            $info['info_owner_id'] = $key_ch_n;
            $info['info_owner_name'] = $value_ch_n['name'];

            if($key_ch_n == 0 &&
                $info['new_clue_num'] == 0 &&
                $info['new_intention_num'] == 0 &&
                $info['to_shop_num'] == 0 &&
                $info['dingche_num'] == 0){
                continue;
            }

            $table_data_list[] = $info;
        }
                
        //设定期望数据
        $funnel_expect_data = [
            'new_clue_num' =>  '100' ,
            'new_intention_num' =>  '75',
            'to_shop_num' =>  '50' ,
            'dingche_num' =>  '25'
        ];

        //计算实际数据百分比信息
        $funnel_real_data_rate = array();
        foreach ($funnel_real_data as $key=>$value){
            //总计栏数据  如果为空默认为0
            if(empty($value)){
                $funnel_real_data[$key] = '0';
            }

            //处理百分比数据
            if($key == 'new_clue_num'){
                $funnel_real_data_rate[$key] = '100';
            }else{
                $rate = @round($funnel_real_data[$key]*100/$funnel_real_data['new_clue_num']);
                $funnel_real_data_rate[$key] = strval($rate);
            }
        }

        //返回数据
        $data['funnel_expect_data'] = $funnel_expect_data;
        $data['funnel_real_data_rate'] = $funnel_real_data_rate;
        $data['funnel_real_data'] = $funnel_real_data;
        $data['table_data'] = $table_data_list;

        return $data;
    }

    /**
     * 获取意向战败客户
     * @param $info_owner_id
     * @param $input_type_id
     * @param $start_date
     * @param $end_date
     * @return mixed
     */
    public function getIntentionFailCustomer($info_owner_id,$input_type_id,$start_date,$end_date, $arrOrgIds, $level)
    {
        //默认查看所有渠道
        if($input_type_id == 'all'){
            $input_type_str = '1=1';
        }else{
            $input_type_str = "input_type_id = {$input_type_id}";
        }

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
        $groupBy = $arrWhereAndGourp['groupby'];
        
        //查询当前层级战败原因数据
        $fail_info = TjFailIntentionTagCount::find()->select('sum(num) as sum_num,tag_id')->where($field_str)
            ->andWhere(['>=','create_date',$start_date])->andWhere(['<=','create_date',$end_date])
            ->andWhere(['=','fail_type',2])->andWhere($input_type_str)
            ->groupBy('tag_id')->asArray()->all();

        //根据tag_id查询失败原因
        //获取失败原因词典
        $data_dictionary = new DataDictionary();
        $fail_tags = $data_dictionary->getDictionaryData('fail_tags')['intention_fail'];
        //拼接意向等级数据为id作为键值的数组
        foreach ($fail_tags as $item){
            $fail_tags_new[$item['id']] = $item;
        }

        //按照数值冒泡排序
        $count = count($fail_info);
        for($i=0;$i<$count;$i++){
            for($j=0;$j<$count-$i-1;$j++){
                if( $fail_info[$j]['sum_num'] < $fail_info[$j+1]['sum_num'] ){
                    $temp=$fail_info[$j];
                    $fail_info[$j]=$fail_info[$j+1];
                    $fail_info[$j+1]=$temp;
                }
            }
        }

        //取出前五个数据
        //拼接数据错误原因
        $sum_num = 0;
        foreach ($fail_info as $key_in=>$item_in){

            if($key_in < 5){
                @$fail_info[$key_in]['tag_name'] = $fail_tags_new[$item_in['tag_id']]['name'];
            }else{
                $sum_num += $item_in['sum_num'];
                unset($fail_info[$key_in]);
            }
        }
//        if($sum_num > 0){
        $fail_info[5]['sum_num'] = strval($sum_num);
        $fail_info[5]['tag_id'] = '0';
        $fail_info[5]['tag_name'] = '其他';
//        }

        //取出前五原因tag_id 用来处理子级数据
        $tag_id_arr = array_column($fail_info,'tag_id');

        //查询当前层级下一层级列表
        $intention_info_child = TjFailIntentionTagCount::find()->select('sum(num) as sum_num,tag_id,'.$groupBy.' as info_owner_id')
            ->where($field_str)
            ->andWhere(['>=','create_date',$start_date])->andWhere(['<=','create_date',$end_date])->andWhere(['=','fail_type',2])
            ->andWhere($input_type_str)->groupBy(['tag_id',$groupBy])->asArray()->all();

        //对数据按照用户分组
        $intention_info_child_new = array();
        foreach ($intention_info_child as $item_in_ch){
            $intention_info_child_new[$item_in_ch['info_owner_id']][] = $item_in_ch;
        }

        //对二级数组按照tag_id作为字段


        //对子级以id作为键值
        $child_list_new = array();
        foreach ($arrWhereAndGourp['nextList'] as $item_ch_l) {
            $child_list_new[$item_ch_l['id']] = $item_ch_l;
        }

        //处理数据组合各种失败原因数据


        //处理子级数据 将二维数组组合为一维数组
        foreach ($intention_info_child_new as $key=>$value){
            foreach ($value as $item){
                $intention_info_child_list[$key][$item['tag_id']] = $item['sum_num'];
            }
        }

        //按照用户处理每个子级的数据  没有数值用0补齐
        $child_list_info_list = array();
        foreach ($child_list_new as $key_ch_n=>$value_ch_n){
            $is_all_null = 0;//判断是否所有项目都为0

            if(empty($intention_info_child_list[$key_ch_n])){
                foreach ($tag_id_arr as $tag_id){
                    $info[$tag_id] = '0';
                }
                //其他原因
                $info[0] = '0';
            }else{
                //此处有问题 子级其他原因可能不准！！！！！   已解决
                //其他原因
                $sum = 0;
                //按照失败标签遍历数组  如果在前五中赋值  否则计算其他原因总和
                foreach($fail_tags as $v){

                    if(in_array($v['id'],$tag_id_arr)){

                        if(empty($intention_info_child_list[$key_ch_n][$v['id']])){
                            $info[$v['id']] = '0';
                        }else{
                            $is_all_null = 1;
                            $info[$v['id']] = $intention_info_child_list[$key_ch_n][$v['id']];
                        }
                    }else{
                        if(!empty($intention_info_child_list[$key_ch_n][$v['id']])){
                            $sum += $intention_info_child_list[$key_ch_n][$v['id']];
                        }
                    }
                }
                //其他原因
                $info[0] = strval($sum);
            }
            $info['info_owner_id'] = $key_ch_n;
            $info['info_owner_name'] = $value_ch_n['name'];

            if($key_ch_n == 0 && $is_all_null == 0){
                continue;
            }

            $child_list_info_list[] = $info;
        }

        //组合前五原因数据
        $fail_tag = array();
        foreach ($fail_info as $key=>$item){
            $info_tag['tag_name'] = $item['tag_name'];
            $info_tag['tag_id'] = $item['tag_id'];
            $fail_tag[] = $info_tag;
        }

        //返回数据
        $data['fail_info'] = $fail_info;
        $data['child_info_list'] = $child_list_info_list;
        $data['fail_tags_list'] = $fail_tag;  //???
        
        return $data;
    }


    public function getOrderFailCustomer($start_date,$end_date,$currentPage,$perPage){

        //转化查询日期为时间戳
        $start_time = strtotime($start_date);
        $end_time = strtotime($end_date) + (24*3600);

        //根据参数查询 按照分页信息 查询当前时间段内战败数据  按照战败时间倒序排列
        //查询按照时间数据线索表取出status=2、is_fail=1线索列表

        $sql = Clue::find()->select('id,customer_id,customer_name,last_fail_time,salesman_name,fail_reason')
            ->where(['between','last_fail_time',$start_time,$end_time])
            ->andWhere(['=','status',2])->andWhere(['=','is_fail',1]);

        //查询当前账号所属层级  所查看最高区域
        $session = Yii::$app->getSession();

        if($session['userinfo']['role_level'] == 10){
            $info_sql = $sql;
        }elseif($session['userinfo']['role_level'] == 15){
            $company_id = $session['userinfo']['area_id'];
            $area_list = OrganizationalStructure::find()->select('id,name')->where(['=','pid',$company_id])->asArray()->all();
            $area_id_list = array_column($area_list,'id');
            $shop_list = OrganizationalStructure::find()->select('id,name')->where(['=','pid',$area_id_list])->asArray()->all();
            $shop_id_list = array_column($shop_list,'id');
            $info_sql = $sql->andWhere(['in','shop_id',$shop_id_list]);

        }elseif ($session['userinfo']['role_level'] == 20){

            $area_id = $session['userinfo']['area_id'];
            $shop_list = OrganizationalStructure::find()->select('id,name')->where(['=','pid',$area_id])->asArray()->all();
            $shop_id_list = array_column($shop_list,'id');

            $info_sql = $sql->andWhere(['in','shop_id',$shop_id_list]);

        }elseif ($session['userinfo']['role_level'] == 30){
            $shop_id = $session['userinfo']['shop_id'];
            $info_sql = $sql->andWhere(['=','shop_id',$shop_id]);
        }
//        $info_sql = $sql;//考虑接口
        $pages['totalCount'] = $info_sql->count();
        $pages['perPage'] = $perPage;
        $pages['currentPage'] = $currentPage;
        $pages['pageCount'] = ceil($info_sql->count()/$perPage);

//        die($info_sql->createCommand()->getRawSql());
        $info_list = $info_sql->orderBy('last_fail_time desc')->offset(($currentPage-1)*$perPage)->limit($perPage)->asArray()->all();

        //在order表查询车型信息

        //取出数据线索id
        $clue_id_list = array_column($info_list,'id');
        //查询该线索id下对应车型
        $car_type_name_list = Order::find()->select('id,clue_id,car_type_name')->where(['in','clue_id',$clue_id_list])->asArray()->all();
        //车型数据按照组织为clue_id为键值的字段
        foreach ($car_type_name_list as $item_c_n){
            $car_type_name_list_new[$item_c_n['clue_id']] = $item_c_n;
        }

        $info_list_new = array();
        foreach ($info_list as $value){
//            $info = $value;
            if(empty($car_type_name_list_new[$value['id']])){
                $value['car_type_name'] = '';
            }else{
                $value['car_type_name'] = $car_type_name_list_new[$value['id']]['car_type_name'];
            }
            $value['last_fail_time'] = date('Y-m-d',$value['last_fail_time']);
            foreach ($value as $k=>$v){
                if(!$v){
                    $value[$k] = '--';
                }
            }

            $info_list_new[] = $value;
        }

        $data['info_list'] = $info_list_new;
        $data['pages'] = $pages;

        return $data;
    }
    public function actionIntentionLevel($info_owner_id,$input_type_id, $arrOrgIds, $level)
    {
        //默认查看所有渠道
        if($input_type_id == 'all'){
            $input_type_str = '1=1';
        }else{
            $input_type_str = "input_type_id = {$input_type_id}";
        }
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
        $groupBy = $arrWhereAndGourp['groupby'];

        //根据参数查询当前等级该渠道所有线索意向等级信息
        $intention_info = TjIntentionLevelCount::find()->select('sum(num) as sum_num,intention_level_id')->where($field_str)
            ->andWhere($input_type_str)->groupBy('intention_level_id')->asArray()->all();

        //拼接下一层级数据为id作为键值的数组
        foreach ($arrWhereAndGourp['nextList'] as $item_or){
            $info_owner_name_list[$item_or['id']] = $item_or;
        }

        //根据参数查询下一等级意向等级列表列表数据  没有数据的用0补齐  也可以用！！
        $intention_info_child = TjIntentionLevelCount::find()->select('sum(num) as sum_num,intention_level_id,'.$groupBy.' as info_owner_id')
            ->where($field_str)
            ->andWhere($input_type_str)->groupBy(['intention_level_id',$groupBy])->asArray()->all();

        //获取意向等级词典
        $data_dictionary = new DataDictionary();
        $intention_list = $data_dictionary->getDictionaryData('intention');
        //拼接意向等级数据为id作为键值的数组  除去6/7/8订车客户、战败客户、交车客户
        $unset_arr = [6,7,8];
        $intention_list_new = array();
        foreach ($intention_list as $item_in){
            if(in_array($item_in['id'],$unset_arr)){
                continue;
            }
            $intention_list_new[$item_in['id']] = $item_in;
        }

        //排除订车客户战败客户交车客户登记
        $intention_list_table = array();
        foreach ($intention_list as $item_il){
            if(in_array($item_il['id'],$unset_arr)){
                continue;
            }
            $intention_list_table[$item_il['name']] = $item_il;
        }

        //取出显示意向等级id
        $intention_id_arr = array_column($intention_list_new,'id');

        //处理饼图数据 只显示允许意向等级数据
        foreach ($intention_info as $key_in_info=>$item_in_info){

            if(in_array($item_in_info['intention_level_id'],$intention_id_arr)){
                $intention_info[$key_in_info]['intention_level_name'] = $intention_list_new[$item_in_info['intention_level_id']]['name'];
            }else{
                unset($intention_info[$key_in_info]);
            }
        }
        //处理子级数据  改为三维数组 第一级id 用户 第二级 渠道
        $intention_info_child2 = array();
        foreach ($intention_info_child as $item_in){
            $intention_info_child2[$item_in['info_owner_id']][$item_in['intention_level_id']] = $item_in;
        }

        //循环处理每个子级
        $child_list_new = array();
        foreach ($arrWhereAndGourp['nextList'] as $item_ch){
            $info = array();
            $info['info_owner_name'] = $item_ch['name'];
            $info['info_owner_id'] = $item_ch['id'];

            //每个子级意向客户数
            $sum_all = 0;
            //处理每个渠道来源
            $is_all_null = 0;//判断是否所有渠道信息都为0
            foreach ($intention_list_new as $value){

                if(empty($intention_info_child2[$item_ch['id']][$value['id']])){
                    $info[$value['name']] = '0';
                }else{
                    $is_all_null = 1;
                    $info[$value['name']] = $intention_info_child2[$item_ch['id']][$value['id']]['sum_num'];
                    $sum_all += $intention_info_child2[$item_ch['id']][$value['id']]['sum_num'];
                }

            }
            $info['sum_all'] = $sum_all;
            if($item_ch['id'] == 0 &&  $is_all_null == 0 ){
                continue;
            }
            $child_list_new[] = $info;
        }

        if(empty($intention_info)){
            $intention_info[] = ['sum_num'=>0,'intention_level_name'=>'无数据'];
        }
        //返回数据
        $data['intention_info'] = $intention_info;
        $data['intention_info_child'] = $child_list_new;
        $data['intention_list_table'] = $intention_list_new;
        return $data;
    }
}
?>