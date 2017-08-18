<?php
namespace frontend\modules\glsb\controllers;

use Yii;
use common\logic\DealTrendLogic;
/**
 * Class 管理速报数据统计控制器
 * @package frontend\modules\glsb\controllers
 */
class DealTrendController extends AuthController
{
    /**
     * 根据accesstoken判断层级 展示第一级数据列表
     * 如果组织架构高于等于区长展示下一级列表 如果为店长 取出shopid 展示店员信息列表
     *
     */

    public function actionGetCountData()
    {
        //接收参数  包含 渠道来源 区域门店 年份
        $p = json_decode(Yii::$app->request->post('p'),true);

        //接收参数
        if(!empty($p['input_type_id'])){
            $input_type_id = $p['input_type_id'];
        }else{
            $input_type_id = 'all';
        }

        if(!empty($p['year'])){
            $year = $p['year'];
        }else{
            $year = date('Y');
        }

        $arrOrgIds = $this->userinfo['user_role_info'];
        $level = $this->userinfo['role_level'];
        $info_owner_id = $p['info_owner_id'];
        if(empty($info_owner_id))
        {
            $thisShopId = $this->getShopId();//默认店铺id，店长登录进来的时候查看数据查看本店的
            if($thisShopId > 0)
            {
                $info_owner_id = $thisShopId;
            }
        }


        $data_common['input_type_id'] = $input_type_id;
        $data_common['year'] = $year;
        $data_common['info_owner_id'] = $info_owner_id;

        $logic = new DealTrendLogic();
        $data = $logic->getCountData($year,$info_owner_id,$input_type_id, $arrOrgIds, $level);

        $data_new['chart']['data'][] = array_column($data,'value');
        $data_new['chart']['legend'] = ['成交台数'];
        $data_new['chart']['x'] = array_column($data,'month');

        $data_new['list'] = $data;
        $data_new['data_common'] = $data_common;

        $this->echoData(200,'获取成功',$data_new);

    }


    public function actionGetRateData()
    {

        $p = json_decode(Yii::$app->request->post('p'),true);

        //接收参数
        if(!empty($p['input_type_id'])){
            $input_type_id = $p['input_type_id'];
        }else{
            $input_type_id = 'all';
        }

        if(!empty($p['year'])){
            $year = $p['year'];
        }else{
            $year = date('Y');
        }

        $arrOrgIds = $this->userinfo['user_role_info'];
        $level = $this->userinfo['role_level'];
        $info_owner_id = $p['info_owner_id'];
        if(empty($info_owner_id))
        {
            $thisShopId = $this->getShopId();//默认店铺id，店长登录进来的时候查看数据查看本店的
            if($thisShopId > 0)
            {
                $info_owner_id = $thisShopId;
            }
        }


        $data_common['input_type_id'] = $input_type_id;
        $data_common['year'] = $year;
        $data_common['info_owner_id'] = $info_owner_id;

        $logic = new DealTrendLogic();
        $data = $logic->getRateData($year,$info_owner_id,$input_type_id, $arrOrgIds, $level);

        $data_new['chart']['data'][] = array_column($data,'value');
        $data_new['chart']['legend'] = ['成交率'];
        $data_new['chart']['x'] = array_column($data,'month');

        $data_new['list'] = $data;
        $data_new['data_common'] = $data_common;

        $this->echoData(200,'获取成功',$data_new);
    }


}
?>