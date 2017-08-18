<?php
namespace frontend\modules\glsb\controllers;

use common\common\PublicMethod;
use common\models\OrganizationalStructure;
use common\models\TjJichushuju;
use common\models\TjLastMonthIntention;
use Yii;

/**
 * Class 管理速报数据统计控制器
 * @package frontend\modules\glsb\controllers
 */
class DataAnalysisController extends AuthController
{
    /**
     * 分析总览接口
     */
    public function actionOverview(){

        $time = PublicMethod::data_update_time('DataAnalysis');

        $data['update_time'] = $time;

        $this->echoData(200,'请求成功',$data);

    }

    private function getSelectData($date){

        //根据日期计算当月第一天
        $start_date=date('Y-m-01', strtotime($date));
        $end_date =  date('Y-m-d', strtotime("$start_date +1 month -1 day"));

        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        return $data;

    }

    /**
     * 1、
     */
    public function actionCountRanking(){
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);

        if(empty($p['month'])){
            $this->echoData(400,'参数不全');
        }

        $date = $this->getSelectData($p['month']);

        //取出所有门店数据列表 - 订车数
        $list = TjJichushuju::find()->select('sum(ding_che_num) as ding_che_num,shop_id as info_owner_id')
            ->where("create_date >=  '{$date['start_date']}' and create_date <  '{$date['end_date']}'")
            ->groupBy('shop_id')
            ->orderBy('ding_che_num DESC')
            ->asArray()->all();

        //安装门店id处理数据
        $list_new = array();
        foreach ($list as $item_l){
            $list_new[$item_l['info_owner_id']] = $item_l;
        }

        //查询所有门店大区名称
        $org_name_arr = OrganizationalStructure::find()->select('id,name')->where(['in','level',[20, 30]])->asArray()->all();
        foreach ($org_name_arr as $item){
            $org_name_list[$item['id']] = $item;
        }

        //查询所有门店
        $shop_all_list = OrganizationalStructure::find()->select('id,name,pid')->where(['=','level',30])->andWhere(['=','is_delete',0])->asArray()->all();

        //对每个门店分别赋值
        $shop_all_list_new = array();
        foreach ($shop_all_list as $item_sh){

            $info['info_owner_id'] = intval($item_sh['id']);
            $info['info_owner_name'] = $item_sh['name'];
            $info['num1'] = empty($org_name_list[$item_sh['pid']]['name']) ? '' :$org_name_list[$item_sh['pid']]['name'];
            $info['num2'] = empty($list_new[$item_sh['id']]['ding_che_num']) ? 0 : intval($list_new[$item_sh['id']]['ding_che_num']);
            $shop_all_list_new[] = $info;
        }

        //按照数值冒泡排序
        $count = count($shop_all_list_new);
        for($i=0;$i<$count;$i++){
            for($j=0;$j<$count-$i-1;$j++){
                if( $shop_all_list_new[$j]['num2'] < $shop_all_list_new[$j+1]['num2'] ){
                    $temp=$shop_all_list_new[$j];
                    $shop_all_list_new[$j]=$shop_all_list_new[$j+1];
                    $shop_all_list_new[$j+1]=$temp;
                }
            }
        }

        $data['models'] = $shop_all_list_new;
        $data['pages'] = [
            'totalCount' => count($shop_all_list_new),
            'pageCount' => 1,
            'currentPage' => 1,
            'perPage' => count($shop_all_list_new),
        ];

        $this->echoData(200,'获取成功',$data);
    }


    public function actionRateRanking(){
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);

        if(empty($p['month'])){
            $this->echoData(400,'参数不全');
        }

        //根据日期计算当月第一天
        $date = $this->getSelectData($p['month']);

        //取出所有门店数据列表
        $list = TjJichushuju::find()->select('sum(ding_che_num) as ding_che_num,sum(new_intention_num) as new_intention_num,shop_id as info_owner_id')
            ->where("create_date >=  '{$date['start_date']}' and create_date <  '{$date['end_date']}'")
            ->groupBy('shop_id')
            ->orderBy('ding_che_num DESC')
            ->asArray()->all();

        //按照门店id处理数据
        $list_new = array();
        foreach ($list as $item_l){
            $list_new[$item_l['info_owner_id']] = $item_l;
        }


        //查询上月结余意向客户  没有时间维度
        $list_last_month = TjLastMonthIntention::find()->select('sum(num) as num,shop_id as info_owner_id')
            ->groupBy('shop_id')
            ->orderBy('num DESC')
            ->asArray()->all();

        //按照门店id处理数据
        $list_last_month_new = array();
        foreach ($list_last_month as $item_ll){
            $list_last_month_new[$item_ll['info_owner_id']] = $item_ll;
        }

        //查询所有门店大区名称
        $org_name_arr = OrganizationalStructure::find()->select('id,name')->where(['in','level',[20 ,30]])->asArray()->all();
        foreach ($org_name_arr as $item){
            $org_name_list[$item['id']] = $item;
        }

        //查询所有门店
        $shop_all_list = OrganizationalStructure::find()->select('id,name,pid')->where(['=','level',30])->andWhere(['=','is_delete',0])->asArray()->all();

        //对每个门店分别赋值
        $shop_all_list_new = array();
        foreach ($shop_all_list as $item_sh){
            $info['info_owner_id'] = intval($item_sh['id']);
            $info['info_owner_name'] = $item_sh['name'];
            $info['num1'] = empty($org_name_list[$item_sh['pid']]['name']) ? '' :$org_name_list[$item_sh['pid']]['name'];
            //计算成交率
            $num2 = 0;
            if(!empty($list_new[$item_sh['id']]['ding_che_num'])){
                $new_intention_num = empty($list_new[$item_sh['id']]['new_intention_num'])? 0 :$list_new[$item_sh['id']]['new_intention_num'];
                $last_intention_num = empty($list_last_month_new[$item_sh['id']]['num'])? 0 :$list_last_month_new[$item_sh['id']]['num'];
                $num2 = @round($list_new[$item_sh['id']]['ding_che_num']*100/($new_intention_num+$last_intention_num ));
            }
            $info['num2'] = $num2;

            $shop_all_list_new[] = $info;
        }

        //按照数值冒泡排序
        $count = count($shop_all_list_new);
        for($i=0;$i<$count;$i++){
            for($j=0;$j<$count-$i-1;$j++){
                if( $shop_all_list_new[$j]['num2'] < $shop_all_list_new[$j+1]['num2'] ){
                    $temp=$shop_all_list_new[$j];
                    $shop_all_list_new[$j]=$shop_all_list_new[$j+1];
                    $shop_all_list_new[$j+1]=$temp;
                }
            }
        }
        
        $data['models'] = $shop_all_list_new;
        $data['pages'] = [
            'totalCount' => count($shop_all_list_new),
            'pageCount' => 1,
            'currentPage' => 1,
            'perPage' => count($shop_all_list_new),
        ];

        $this->echoData(200,'获取成功',$data);
    }
}
?>
