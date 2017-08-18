<?php
namespace common\logic;
use common\models\TjDingcheNum;
use common\models\OrganizationalStructure;
use common\models\TjThisMonthIntention;
use common\models\TjLastMonthIntention;
use common\logic\TongJiLogic;
class DealTrendLogic
{
    public function getCountData($year,$info_owner_id,$input_type_id, $arrOrgIds, $level)
    {
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

        //查询当前用户可以看到的各月份数据
        $info_list = TjDingcheNum::find()->select('sum(num) as sum_all,year_and_month')->where(['>','year_and_month',$year])
            ->andWhere($input_type_str)->andWhere($field_str)
            ->groupBy('year_and_month')->asArray()->all();

        foreach ($info_list as $item){
            $info_list_new[$item['year_and_month']] = $item['sum_all'];
        }

        $month_info_list = array();
        for ($i = 1;$i <= 9;$i++){
            if(empty($info_list_new[$year.'-0'.$i])){
//                $info['month'] = $year.'-0'.$i;
                $info['month'] = $i.'月';
                $info['value'] = '0';
            }else{
//                $info['month'] = $year.'-0'.$i;
                $info['month'] = $i.'月';
                $info['value'] = $info_list_new[$year.'-0'.$i];
            }

            $month_info_list[] = $info;
        }

        for ($i = 10;$i <= 12;$i++){
            if(empty($info_list_new[$year.'-'.$i])){
//                $info['month'] = $year.'-'.$i;
                $info['month'] = $i.'月';
                $info['value'] = '0';
            }else{
//                $info['month'] = $year.'-'.$i;
                $info['month'] = $i.'月';
                $info['value'] = $info_list_new[$year.'-'.$i];
            }
            $month_info_list[] = $info;
        }

        return $month_info_list;
    }


    public function getRateData($year,$info_owner_id,$input_type_id, $arrOrgIds, $level){

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

        //查询当年订车数据列表 没有数据用0补齐

        $deal_info_list = TjDingcheNum::find()->select('sum(num) as sum_all,year_and_month')->where(['>','year_and_month',$year])
            ->andWhere($input_type_str)->andWhere($field_str)
            ->groupBy('year_and_month')->asArray()->all();

        foreach ($deal_info_list as $item){
            $deal_info_list_new[$item['year_and_month']] = $item['sum_all'];
        }


        //查询当年各月份新增意向客户
        $new_intention_list = TjThisMonthIntention::find()->select('sum(num) as sum_all,year_and_month')->where(['>','year_and_month',$year])->andWhere($field_str)
            ->groupBy('year_and_month')->asArray()->all();
        foreach ($new_intention_list as $item){
            $new_intention_list_new[$item['year_and_month']] = $item['sum_all'];
        }

        //查询当年各月结余意向数
        $last_intention_list = TjLastMonthIntention::find()->select('sum(num) as sum_all,year_and_month')->where(['>','year_and_month',$year])->andWhere($field_str)
            ->groupBy('year_and_month')->asArray()->all();
        foreach ($last_intention_list as $item){
            $last_intention_list_new[$item['year_and_month']] = $item['sum_all'];
        }


        for ($i = 1;$i <= 9;$i++){
            if(empty($deal_info_list_new[$year.'-0'.$i])){
                $info['month'] = $i.'月';
                $info['value'] = '0';
//                $rate_list_month[$year.'-0'.$i] = '0';
            }else{
                $last_intention = empty($last_intention_list_new[$year.'-0'.($i-1)]) ? 0 : $last_intention_list_new[$year.'-0'.($i-1)];
                $new_intention = empty($new_intention_list_new[$year.'-0'.$i]) ? 0 : $new_intention_list_new[$year.'-0'.$i];
                $sum = $last_intention + $new_intention;

//                $rate_list_month[$year.'-0'.$i] = strval(@round($deal_info_list_new[$year.'-0'.$i]*100/$sum));

                $info['month'] = $i.'月';
                $info['value'] = strval(@round($deal_info_list_new[$year.'-0'.$i]*100/$sum));
            }

            $month_info_list[] = $info;
        }

        for ($i = 10;$i <= 12;$i++){
            if(empty($info_list_new[$year.'-'.$i])){
                $info['month'] = $i.'月';
                $info['value'] = '0';
            }else{
                $last_intention = empty($last_intention_list_new[$year.'-'.($i-1)]) ? 0 : $last_intention_list_new[$year.'-'.($i-1)];
                $new_intention = empty($new_intention_list_new[$year.'-'.$i]) ? 0 : $new_intention_list_new[$year.'-'.$i];
                $sum = $last_intention + $new_intention;
                $info['month'] = $i.'月';
                $info['value'] = strval(@round($deal_info_list_new[$year.'-'.$i]*100/$sum));
            }

            $month_info_list[] = $info;
        }
        return $month_info_list;
    }







}
?>