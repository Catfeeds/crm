<?php
/**
 * 排行控制器
 * 于凯
 */
namespace backend\controllers;

use common\common\PublicMethod;
use Yii;
use common\logic\CompanyUserCenter;
use common\models\User;


class RanKingController extends BaseController
{
    public $enableCsrfValidation = false;

    /**
     *列表
     */
    public function actionIndex()
    {
        //权限控制 - 所有
        $this->checkPermission('/ran-king/index');
        
        $data_common['data_update_time'] = PublicMethod::data_update_time(1);  //17-04-24 lzx
        return $this->render('index',['data_common' => $data_common]);

    }

    /**
     * 订车
     */
    public function actionDingCheAjax() {

        $post = $_POST;

        $time = !empty($post['time']) ? $post['time'] : date('Y-m');

        $where = "year_and_month = '{$time}'";

        try{
            if ($post['level'] == 1)
            {//按区域查询订单排行

                $company = new CompanyUserCenter();

                //区域信息
                $area = $company->getLocalOrganizationalStructure(20);

                //订单排行榜
                $sql = " select sum(num)num,area_id from  crm_tj_dingche_num where $where GROUP by area_id order by num desc";

                $list = Yii::$app->db->createCommand($sql)->queryAll();

                $sum_count = 0;
                //获取总和
                foreach ($list as $v) {
                    $sum_count += $v['num'];
                }

                $html = '';
                if (!empty($list)) {
                    $html .= '<table class="table table-condensed custom-table">
                            <tr>
                                <th width="120">名称</th>
                                <th>订车</th>
                                <th class="t-l">占比</th>
                            </tr><tbody>';

                    foreach ($list as $k => $v) {

                        if ($k < 10) {
                            $v['area_name'] = null;
                            //百分比
                            $v['bfv'] = @round((($v['num'] / $sum_count) * 100),2).'%';
                            foreach ($area as $val) {

                                if ($v['area_id'] == $val['id']) {
                                    $v['area_name'] = $val['name'];
                                }
                            }
                           $html .= "
                                <tr>
                                    <td>{$v['area_name']}</td>
                                    <td>{$v['num']}</td>
                                    <td class='t-l'>
                                        <span class='bfb'>{$v['bfv']}</span>
                                        <div class='progress progress-xs'>
                                            <div class='progress-bar progress-bar-danger' style='width: {$v['bfv']}'></div>
                                        </div>
                                    </td>
                                </tr>
                               ";
                        }
                    }
                    $html .= ' </tbody>
                        </table>';
                }
                echo  $html;
            }else if ($post['level'] == 2) 
            {//按门店
                $company = new CompanyUserCenter();

                //门店信息
                $area = $company->getLocalOrganizationalStructure(30);

                //订单排行榜
                $sql = " select sum(num)num,shop_id from  crm_tj_dingche_num where $where GROUP by shop_id order by num desc";
                $list = Yii::$app->db->createCommand($sql)->queryAll();

                $sum_count = 0;
                //获取总和
                foreach ($list as $v) {
                    $sum_count += $v['num'];
                }

                $html = '';
                if (!empty($list)) {
                    $html .= '<table class="table table-condensed custom-table">
                            <tr>
                                <th width="120">名称</th>
                                <th>订车</th>
                                <th class="t-l">占比</th>
    
                            </tr><tbody>';

                    foreach ($list as $k => $v) {
                        if ($k < 10) {
                            $v['shop_name'] = null;
                            //百分比
                            $v['bfv'] = @round((($v['num'] / $sum_count) * 100),2).'%';
                            foreach ($area as $val) {

                                if ($v['shop_id'] == $val['id']) {
                                    $v['shop_name'] = $val['name'];
                                    break;
                                }
                            }
                            $html .= "
                                <tr>
                                    <td>{$v['shop_name']}</td>
                                    <td>{$v['num']}</td>
                                    <td class='t-l'>
                                        <span class='bfb'>{$v['bfv']}</span>
                                        <div class='progress progress-xs'>
                                            <div class='progress-bar progress-bar-danger' style='width: {$v['bfv']}'></div>
                                        </div>
                                    </td>
                                </tr>
                               ";
                        }
                    }
                    $html .= ' </tbody>
                        </table>';
                }
                echo  $html;
            }else if ($post['level'] == 3) 
            {//按顾问

                $user = User::find()->select('id,name')->asArray()->all();
                //订单排行榜
                $sql = " select sum(num)num,salesman_id from  crm_tj_dingche_num where $where GROUP by salesman_id order by num desc";
                $list = Yii::$app->db->createCommand($sql)->queryAll();

                $sum_count = 0;
                //获取总和
                foreach ($list as $v) {
                    $sum_count += $v['num'];
                }

                $html = '';
                if (!empty($list)) {
                    $html .= '<table class="table table-condensed custom-table">
                            <tr>
                                <th width="120">名称</th>
                                <th>订车</th>
                                <th class="t-l">占比</th>
    
                            </tr><tbody>';

                    foreach ($list as $k => $v) {
                        if ($k < 10) {
                            $v['shop_name'] = null;
                            //百分比
                            $v['bfv'] = @round((($v['num'] / $sum_count) * 100),2).'%';
                            foreach ($user as $val) {

                                if ($v['salesman_id'] == $val['id']) {
                                    $v['shop_name'] = $val['name'];
                                    break;
                                }
                            }
                            $html .= "
                                <tr>
                                    <td>{$v['shop_name']}</td>
                                    <td>{$v['num']}</td>
                                    <td class='t-l'>
                                        <span class='bfb'>{$v['bfv']}</span>
                                        <div class='progress progress-xs'>
                                            <div class='progress-bar progress-bar-danger' style='width: {$v['bfv']}'></div>
                                        </div>
                                    </td>
                                </tr>
                               ";
                        }
                    }
                    $html .= ' </tbody>
                        </table>';
                }
                echo  $html;
            }
        }catch (\Exception $e) {
            $this->dump($e->getMessage());

        }
    }

    //获取上月日期：
    function getlastMonthDays($date){
        $timestamp=strtotime($date);
        $firstday=date('Y-m-01',strtotime(date('Y',$timestamp).'-'.(date('m',$timestamp)-1).'-01'));
        $lastday=date('Y-m-d',strtotime("$firstday +1 month -1 day"));
        return array($firstday,$lastday);
    }
    /**
     * 成交
     */
    public function actionChengJiaoAjax() {
        $post = $_POST;

        $time = !empty($post['time']) ? $post['time'] : date('Y-m');

        $sy = $this->getlastMonthDays($time);//上个月

        $sy_time =  date('Y-m',strtotime($sy[0]));

        $where = "year_and_month = '{$time}'";

        try{

            if ($post['level'] == 1){//按区域查询成交排行

                $company = new CompanyUserCenter();

                //区域信息
                $area = $company->getLocalOrganizationalStructure(20);

                //订单排行榜
                $sql = " select sum(num)num,area_id from  crm_tj_dingche_num where $where GROUP by area_id order by num desc";
                $list = Yii::$app->db->createCommand($sql)->queryAll();

                //新增意向客户
                $sql = "select sum(num)num,area_id from crm_tj_this_month_intention where $where GROUP by area_id";
                $new = Yii::$app->db->createCommand($sql)->queryAll();

                // 上月结余意向
                $sql = "select sum(num)num,area_id from crm_tj_last_month_intention where year_and_month = '{$sy_time}' GROUP by area_id";
                $last = Yii::$app->db->createCommand($sql)->queryAll();

                $newArr = [];

                if (!empty($new)) {

                    foreach ($new as $n_val) {

                        if (!empty($last)) {

                            foreach ($last as $l_val) {

                                if ($n_val['area_id'] == $l_val['area_id']) {
                                    $newArr[$n_val['area_id']] = $n_val['num'] + $l_val['num'];
                                }else{
                                    $newArr[$n_val['area_id']] = $n_val['num'] ;
                                    $newArr[$l_val['area_id']] =$l_val['num'] ;
                                }
                            }
                        }else {
                            $newArr[$n_val['area_id']] = $n_val['num'];
                        }

                    }

                }else if (!empty($last)){
                    foreach ($last as $l_val) {

                        $newArr[$l_val['area_id']] = $l_val['num'] ;
                    }

                }

                $sum_count = 0;
                //获取总和
                foreach ($list as $v) {
                    $sum_count += $v['num'];
                }

                $html = '';
                if (!empty($list)) {

                    $html .= ' <table class="table table-condensed custom-table">
                            <tr>
                                <th width="120">名称</th>
                                <th >成交率</th>
                            </tr><tbody>
                           ';

                    foreach ($list as $k => $v) {
                        $list[$k]['area_name'] = null;

                        //百分比
                        $list[$k]['bfv'] = '0%';

                        //区域名
                        foreach ($area as $val) {

                            if ($v['area_id'] == $val['id']) {
                                $list[$k]['area_name'] = $val['name'];
                            }
                        }

                        if (!empty($newArr)) {
                                                   //成交率
                            foreach ($newArr as $key => $val) {
                                if ($key == $v['area_id']) {
                                    $list[$k]['bfv'] = @(round($v['num'] / $val,2) * 100) .'%';
                                }
                            }

                        }

                    }

                    //按照百分比排序
                    $sort = array_column($list, 'bfv');
                    array_multisort($sort, SORT_DESC, $list);

                    foreach ($list as $k => $v) {
                        if ($k < 10) {
                            $html .= "
                                    <tr>
                                        <td>{$v['area_name']}</td>
                                        <td><span class='badge bg-red'>{$v['bfv']}</span></td>
                                    </tr>
                                   ";
                        }
                    }
                    $html .= '</tbody></table>';
                }
                echo  $html;
            }else if ($post['level'] == 2){
                $company = new CompanyUserCenter();

                //门店信息
                $area = $company->getLocalOrganizationalStructure(30);

                //订单排行榜
                $sql = " select sum(num)num,shop_id from  crm_tj_dingche_num where $where GROUP by shop_id ";
                $list = Yii::$app->db->createCommand($sql)->queryAll();

                //新增意向客户
                $sql = "select sum(num)num,shop_id from crm_tj_this_month_intention where $where GROUP by shop_id";
                $new = Yii::$app->db->createCommand($sql)->queryAll();

                // 上月结余意向
                $sql = "select sum(num)num,shop_id from crm_tj_last_month_intention where year_and_month = '{$sy_time}' GROUP by shop_id";
                $last = Yii::$app->db->createCommand($sql)->queryAll();

                $newArr = [];

                if (!empty($new)) {

                    foreach ($new as $n_val) {

                        if (!empty($last)) {

                            foreach ($last as $l_val) {

                                if ($n_val['shop_id'] == $l_val['shop_id']) {
                                    $newArr[$n_val['shop_id']] = $n_val['num'] + $l_val['num'];
                                }else{
                                    $newArr[$n_val['shop_id']] = $n_val['num'] ;
                                    $newArr[$l_val['shop_id']] =$l_val['num'] ;
                                }
                            }
                        }else {
                            $newArr[$n_val['shop_id']] = $n_val['num'];
                        }

                    }

                }else if (!empty($last)){
                    foreach ($last as $l_val) {

                        $newArr[$l_val['shop_id']] = $l_val['num'] ;
                    }

                }

                $sum_count = 0;
                //获取总和
                foreach ($list as $v) {
                    $sum_count += $v['num'];
                }

                $html = '';
                if (!empty($list)) {

                    $html .= ' <table class="table table-condensed custom-table">
                            <tr>
                                <th width="120">名称</th>
                                <th >成交率</th>
                            </tr><tbody>
                           ';

                    foreach ($list as $k => $v) {
                        $list[$k]['area_name'] = null;

                        //百分比
                        $list[$k]['bfv'] = '0%';

                        //门店名
                        foreach ($area as $val) {

                            if ($v['shop_id'] == $val['id']) {
                                $list[$k]['area_name'] = $val['name'];
                            }
                        }

                        if (!empty($newArr)) {
                            //成交率
                            foreach ($newArr as $key => $val) {
                                if ($key == $v['shop_id']) {
                                    $list[$k]['bfv'] = @(round($v['num'] / $val,2) * 100);
                                }
                            }

                        }
                    }

                    //按照百分比排序
                    $sort = array_column($list, 'bfv');
                    array_multisort($sort, SORT_DESC, $list);

                    foreach ($list as $k => $v) {
                        if ($k < 10) {//获取排名前10的信息
                            $html .= "
                                <tr>
                                    <td>{$v['area_name']}</td>
                                    <td><span class='badge bg-red'>{$v['bfv']}%</span></td>
                                </tr>
                               ";
                        }
                    }
                    $html .= '</tbody></table>';
                }
                echo  $html;

            }else if ($post['level'] == 3) {
                $company = new CompanyUserCenter();


                $user = User::find()->select('id,name')->asArray()->all();

                //订单排行榜
                $sql = " select sum(num)num,salesman_id from  crm_tj_dingche_num where $where GROUP by salesman_id ";
                $list = Yii::$app->db->createCommand($sql)->queryAll();

                //新增意向客户
                $sql = "select sum(num)num,salesman_id from crm_tj_this_month_intention where $where GROUP by salesman_id";
                $new = Yii::$app->db->createCommand($sql)->queryAll();

                // 上月结余意向
                $sql = "select sum(num)num,salesman_id from crm_tj_last_month_intention where year_and_month = '{$sy_time}' GROUP by salesman_id";
                $last = Yii::$app->db->createCommand($sql)->queryAll();

                $newArr = [];

                if (!empty($new)) {

                    foreach ($new as $n_val) {

                        if (!empty($last)) {

                            foreach ($last as $l_val) {

                                if ($n_val['salesman_id'] == $l_val['salesman_id']) {
                                    $newArr[$n_val['salesman_id']] = $n_val['num'] + $l_val['num'];
                                }else{
                                    $newArr[$n_val['salesman_id']] = $n_val['num'] ;
                                    $newArr[$l_val['salesman_id']] =$l_val['num'] ;
                                }
                            }
                        }else {
                            $newArr[$n_val['salesman_id']] = $n_val['num'];
                        }

                    }

                }else if (!empty($last)){
                    foreach ($last as $l_val) {

                        $newArr[$l_val['salesman_id']] = $l_val['num'] ;
                    }

                }

                $sum_count = 0;
                //获取总和
                foreach ($list as $v) {
                    $sum_count += $v['num'];
                }

                $html = '';
                if (!empty($list)) {

                    $html .= ' <table class="table table-condensed custom-table">
                            <tr>
                                <th width="120">名称</th>
                                <th >成交率</th>
                            </tr><tbody>
                           ';

                    foreach ($list as $k => $v) {
                        $list[$k]['name'] = null;
                        //百分比
                        $list[$k]['bfv'] = '0%';

                        //顾问名
                        foreach ($user as $val) {

                            if ($v['salesman_id'] == $val['id']) {
                                $list[$k]['name'] = $val['name'];
                                break;
                            }
                        }

                        if (!empty($newArr)){
                            //成交率
                            foreach ($newArr as $key => $val) {
                                if ($key == $v['salesman_id']) {
                                    $list[$k]['bfv'] = @(round($v['num'] / $val,2) * 100);
                                }
                            }

                        }

                    }

                    //按照百分比排序
                    $sort = array_column($list, 'bfv');
                    array_multisort($sort, SORT_DESC, $list);


                    foreach ($list as $k => $v) {
                        if ($k < 10) {
                            $html .= "
                                    <tr>
                                        <td>{$v['name']}</td>
                                        <td><span class='badge bg-red'>{$v['bfv']}%</span></td>
                                    </tr>
                                   ";
                        }
                    }
                    $html .= '</tbody></table>';
                }
                echo  $html;
            }
        }catch (\Exception $e) {
            $this->dump($e->getMessage());

        }
    }

}
