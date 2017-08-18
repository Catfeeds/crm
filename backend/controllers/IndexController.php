<?php
/**
 * 网站首页控制器以及门店选择功能
 */

namespace backend\controllers;

use common\models\PutTheCar;
use Yii;
use common\logic\CompanyUserCenter;
use common\models\TjSalesTarget;
use common\logic\JsSelectDataLogic;
use common\logic\TongJiLogic;
use common\models\OrganizationalStructure;

class IndexController extends BaseController
{
    public $enableCsrfValidation = false;

    /**
     * 网站的欢迎页面，但用户没有首页权限的时候跳到欢迎页面来
     */
    public function actionWelcome()
    {
        return $this->render('welcome', []);
    }
    
    /**
     * 网站首页
     */
    public function actionIndex()
    {
        //权限控制 - 所有
        $this->checkPermission('/index/index');

        $session = Yii::$app->getSession();
        
        $month           = date('Y-n');//当前月
        $data['time']    = 0;
        $data['addtime'] = null;

        $content  = null;//公用sql查询
        $sqlLevel = null;//不同角色sql拼接

        if (!empty($_POST['time']) || !empty($_POST['addtime'])) {

            if (!empty($_POST['time'])) {

                $time = $_POST['time'];

                if ($time == 1) {//今天

                    $start   = date('Y-m-d');
                    $content = "create_date = '{$start}'";
                    $data['addtime'] = $start.' - ' .$start;

                } else if ($time == 2) {//昨天

                    $content = "(TO_DAYS(NOW())-TO_DAYS(create_date)) = 1";
                    $data['addtime'] = date("Y-m-d",strtotime("-1 day")).' - '.date("Y-m-d",strtotime("-1 day"));

                } else if ($time == 3) {//本月

                    $start   = date('Y-m-01 00:00:00');
                    $end     = date('Y-m-d H:i:s');
                    $content = "create_date >= '{$start}' and create_date <='{$end}'";
                    $data['addtime'] =   date('Y-m') . '-01'." - ".date('Y-m-d');
                }
                $data['time'] = $time;
            } else {

                list($startDate, $endDate) = explode(' - ', trim($_POST['addtime']));
                $content .= " create_date >= '{$startDate}'";
                $content .= " and create_date <= '{$endDate}'";
                $data['addtime'] = $_POST['addtime'];
            }
        } else {
            $create_date        = date('Y-m-d');
            $content      = "create_date = '{$create_date}'";
            $data['time'] = 1;
            $data['addtime'] = $create_date.' - ' .$create_date;
        }
        
        $arrOrgIds = $session['userinfo']['permisson_org_ids'];
        $intLevel = $session['userinfo']['role_level'];
        $objTongjiLogic = new TongJiLogic();
        if($intLevel == 30)
        {
            $shopId = $this->getDefaultShopId();
            $arrWhereAndGroup = $objTongjiLogic->getSelectFieldByLevelAndOrgId($shopId, $arrOrgIds);
        }
        else
        {
            $arrWhereAndGroup = $objTongjiLogic->getStrFieldByLevel($intLevel, $arrOrgIds);
        }
        $sqlLevel = ' and ' . $arrWhereAndGroup['where'];
        $data['name'] = $arrWhereAndGroup['org_level_name'];

        //未交车数据数
        $sql                = "select IFNULL(sum(num),0) num from crm_tj_weijiaoche where 1=1 $sqlLevel";
        $data['weijiaoche'] = Yii::$app->db->createCommand($sql)->queryOne();
        
        //本月指标完成度
        $sql = "select sum(target_num)target_num,sum(finish_num)finish_num from crm_tj_sales_target 
                    where  year_and_month = '{$month}' $sqlLevel";

        $month = Yii::$app->db->createCommand($sql)->queryOne();

        if (!empty($month['target_num'])) {
            $data['month']      = @round($month['finish_num'] / $month['target_num'], 2);
            $data['month_list'] = $month;
        } else {
            $data['month'] = 0;
        }

        $sql = "select 
                IFNULL(sum(chengjiao_num),0)chengjiao_num,
                IFNULL(sum(fail_num),0)fail_num,
                IFNULL(sum(new_clue_num),0)new_clue_num,
                IFNULL(sum(phone_task_num),0)phone_task_num,
               IFNULL(sum(finish_phone_task_num),0)finish_phone_task_num,
               IFNULL(sum(new_intention_num),0)new_intention_num,
               IFNULL(sum(talk_num),0)talk_num,
               IFNULL(sum(lai_dian_num),0)lai_dian_num,
               IFNULL(sum(qu_dian_num),0)qu_dian_num,
               IFNULL(sum(to_shop_num),0)to_shop_num,
               IFNULL(sum(to_home_num),0)to_home_num
               from crm_tj_jichushuju where  $content $sqlLevel
            ";

        $data['list'] = Yii::$app->db->createCommand($sql)->queryOne();

        //跟进中的意向数
        $sql                    = "select IFNULL(sum(num),0)num from crm_tj_intention_genjinzhong where 1=1 $sqlLevel";
        $data['yixiang_genjin'] = Yii::$app->db->createCommand($sql)->queryOne();

        //跟进中的线索数
        $sql                    = "select IFNULL(sum(num),0)num from crm_tj_clue_genjinzhong where 1=1 $sqlLevel";
        $data['xiansuo_genjin'] = Yii::$app->db->createCommand($sql)->queryOne();

        // 交车任务
        $arrTime = explode(' - ', $data['addtime']);
        $start = strtotime($arrTime[0]);
        $end = empty($arrTime[1]) ? time() : strtotime($arrTime[1]) + 86400;
        $intMentionCarTask = PutTheCar::getMentionCount($arrOrgIds, $start, $end);
        $data['mention_task_num'] = $intMentionCarTask;

        return $this->render('index', ['data' => $data, 'level' => $intLevel]);
    }

    /**
     * 销售指标
     */
    public function actionTarget()
    {

        $session = Yii::$app->getSession();

        $company = new CompanyUserCenter();

        $id = null;

        //大区 获取所属区域下门店数据
        $objSelectDataLogic = new \common\logic\JsSelectDataLogic();
        $arrSelectorgList = $objSelectDataLogic->getSelectOrgNew($session['userinfo']['permisson_org_ids'], $session['userinfo']['role_level'], true);

        
        if($session['userinfo']['role_level'] == 20) 
        {
            $year = empty($_POST['year']) ? '2017' : $_POST['year'];
            
            $arrOrgIds = $session['userinfo']['permisson_org_ids'];
            //用户有权限的门店
            $arrOrgWhere = [
                'and',
                ['=', 'is_delete', 0],
                ['in', 'id', $arrOrgIds],
                ['=', 'level', 30]
            ];
            $arrShopList = OrganizationalStructure::find()->where($arrOrgWhere)->asArray()->all();
            $arrShopIds = array_column($arrShopList, 'id');
            $strShopIds = implode(',', $arrShopIds);
            //查找门店指标
            $sql = "select sum(target_num) target_num,sum(finish_num)finish_num,shop_id from crm_tj_sales_target 
                    where shop_id in({$strShopIds}) and substring_index(year_and_month,'-',1) = '{$year}'
                    GROUP by shop_id";

            $listTmp = Yii::$app->db->createCommand($sql)->queryAll();
            $list = [];
            foreach($listTmp as $val)
            {
                $list[$val['shop_id']] = $val;
            }            
            //合并数据
            $dataList = [];
            foreach ($arrShopList as $k => $v) {
                $thisShopData = (isset($list[$v['id']]) ? $list[$v['id']] : []);
                $percentage = empty($thisShopData) ? '0%' : @round($thisShopData['finish_num'] / $thisShopData['target_num'] * 100, 2) . '%';
                $dataList[] = [
                    'id' => $v['id'],
                    'name' => $v['name'],
                    'target_num' => (isset( $thisShopData['target_num']) ? $thisShopData['target_num'] : 0),
                    'finish_num' => (isset( $thisShopData['finish_num']) ? $thisShopData['finish_num'] : 0),
                    'percentage' => $percentage,
                ];
            }
            return $this->render('areaIndex', ['dataList' => $dataList, 'year' => $year]);
        }
        else if ($session['userinfo']['role_level'] == 15)
        {//公司获取所有区域数据
            $session = Yii::$app->getSession();
            echo '销售指标先放下';exit;
//            $company = new CompanyUserCenter();
//            $area    = $company->getCanSelectAreaOrShopListByPhoneOrEmail($session['userinfo']['email']);
//            $areas   = $area['children'];//所有区域
            $title = '公司';
            $level = 1;

            return $this->render('bossIndex', [
//                    'areas' => $areas,
                    'title'=>$title,
                    'selectOrgJson' => json_encode($arrSelectorgList),
//                    'level'=>$level
                ]);

        }else if ($session['userinfo']['role_level'] == 10) {//总部获取所有区域数据
            echo '销售指标先放下';exit;
            $session = Yii::$app->getSession();

            $company = new CompanyUserCenter();
            $area    = $company->getCanSelectAreaOrShopListByPhoneOrEmail($session['userinfo']['email']);

            $areas   = $area['children'];//所有区域
            $title = '总部';
            $level = 0;
            return $this->render('bossIndex', [
                    'areas' => $areas,
                    'title'=>$title,
                    'selectOrgJson' => json_encode($arrSelectorgList),
                    'level'=>$level
                ]);
        }

    }

    /**
     * 操作列表
     */
    public function actionEdit()
    {
        $get = Yii::$app->request->get();
        //获取门店销售指标
        $sql = "select target_num,finish_num,substring_index(year_and_month,'-',-1)months,year_and_month from  crm_tj_sales_target";
        $sql .= " where shop_id = {$get['shop_id']} and  substring_index(year_and_month,'-',1) = '{$get['year']}'";
        $list = Yii::$app->db->createCommand($sql)->queryAll();
        return $this->renderPartial('edit', ['list' => $list, 'get' => $get]);
    }

    /**
     * 保存
     */
    public function actionSave()
    {

        $session = Yii::$app->getSession();
        $post    = $_POST;

        $db          = Yii::$app->db;
        $transaction = $db->beginTransaction();

        $objOrgShop = OrganizationalStructure::findOne($post['shop_id']);
        $objOrgArea = OrganizationalStructure::findOne($objOrgShop->pid);
        
        for ($i = 1; $i <= 12; $i++) {
            //1.检测当前信息是否存在
            $target = TjSalesTarget::find()
                ->select('id')
                ->where("
                area_id = {$objOrgShop->pid} 
                and shop_id = {$post['shop_id']}
                and substring_index(year_and_month,'-',1) = {$post['year']}
                and substring_index(year_and_month,'-',-1) = {$i}
                "
                )->one();

            if (empty($target)) {

                //新增指标
                $target                   = new TjSalesTarget();
                $target->company_id       = $objOrgArea->pid;//假数据
                $target->area_id          = $objOrgShop->pid;
                $target->shop_id          = $post['shop_id'];
                $target->year_and_month   = $post['year'] . '-' . $i;
                $target->target_num       = $post[$i];
                $target->create_time      = date('Y-m-d H:i:s');
                $target->create_person    = $session['userinfo']['name'];
                $target->create_person_id = $session['userinfo']['id'];

                if (!$target->save()) {
                    $this->res(300, '信息增加失败！');
                    $transaction->rollBack();
                }
            } else {
                //修改指标

                $target->target_num = $post[$i];

                if (!$target->save()) {
                    $this->res(300, '信息修改失败！');
                    $transaction->rollBack();
                }
            }
        }
        $transaction->commit();
        $this->res();

    }

    /**
     * 大区ajax
     */
    public function actionAjaxres()
    {
        $session = Yii::$app->getSession();
        $company = new CompanyUserCenter();
        $area    = $company->getCanSelectAreaOrShopListByPhoneOrEmail($session['userinfo']['email']);
        $area_id = $area['id'];

        $id = null;

        //所有门店信息
        $children = $area['children'];


        //查找门店指标
        $sql = "select shop_id,target_num,finish_num from crm_tj_sales_target 
                    where area_id ={$area_id} and year_and_month = '{$_POST['yearandmonth']}'";

        $list = Yii::$app->db->createCommand($sql)->queryAll();

        $target_nums = 0;
        $finish_nums = 0;

        $html = "<table class='table table-hover table-bordered table-list-check'>
             <thead>
                <tr>
                  <th width='60'>序号</th>
                  <th>名称</th>
                  <th>目标台数</th>
                  <th>实际完成台数</th>
                  <th>完成率</th>
                </tr>
              </thead>
              <tbody>
             ";

        $i = 1;
        //合并数据
        foreach ($children as $k => $v) {

            if (!empty($list)) {
                foreach ($list as $val) {
                    if ($v['id'] == $val['shop_id']) {
                        $children[$k]['list']               = $val;
                        $children[$k]['list']['percentage'] = @round(($val['finish_num'] / $val['target_num']) * 100, 2) . '%';
                        $target_nums += $children[$k]['list']['target_num'];
                        $finish_nums += $children[$k]['list']['finish_num'];
                        break;
                    } else {
                        $children[$k]['list']['target_num'] = 0;
                        $children[$k]['list']['finish_num'] = 0;
                        $children[$k]['list']['percentage'] = '0%';
                        $target_nums += $children[$k]['list']['target_num'];
                        $finish_nums += $children[$k]['list']['finish_num'];
                    }
                }
            } else {
                $children[$k]['list']['target_num'] = 0;
                $children[$k]['list']['finish_num'] = 0;
                $children[$k]['list']['percentage'] = '0%';
                $target_nums += 0;
                $finish_nums += 0;
            }
            //增加@ 屏蔽division by zero php警告  0不能做除数
            $bfb = @round(($children[$k]['list']['finish_num'] / $children[$k]['list']['target_num']) * 100, 2) . '%';
            $html .= "<tr>
                      <td>{$i}</td>
                      <td>{$v['name']}</td>
                      <td>{$children[$k]['list']['target_num']}</td>
                      <td>{$children[$k]['list']['finish_num']}</td>
                      <td>{$bfb}</td>
                    </tr>";
            $i++;
        }
        $bfb = @round(($finish_nums / $target_nums) * 100, 2) . '%';
        $html .= "<tr>
                      <td></td>
                      <td>总计</td>
                      <td>{$target_nums}</td>
                      <td>{$finish_nums}</td>
                      <td>{$bfb}</td>";
        $html .= "</tbody></table>";

        $children['counts'] = count($children);
        $children['html']   = $html;

        echo json_encode($children);
    }


    /**
     * 总部下面的公司
     */
    public function actionAjaxarea()
    {

        $session = Yii::$app->getSession();

        $company  = new CompanyUserCenter();
        $area     = $company->getCanSelectAreaOrShopListByPhoneOrEmail($session['userinfo']['email']);
        $children = $area['children'];//所有公司

        $yearandmonth = empty($_POST['yearandmonth']) ? date('Y-n') : $_POST['yearandmonth'];

        //查找区域指标
        $sql = "select sum(target_num)target_num,sum(finish_num)finish_num,company_id from crm_tj_sales_target 
                    where year_and_month = '{$yearandmonth}'
                    GROUP by company_id";

        $list = Yii::$app->db->createCommand($sql)->queryAll();


        $html = "<table class='table table-hover table-bordered table-list-check'>
             <thead>
                <tr>
                  <th width='60'>序号</th>
                  <th>名称</th>
                  <th>目标台数</th>
                  <th>实际完成台数</th>
                  <th>完成率</th>
                </tr>
              </thead>
              <tbody>
             ";

        $i = 1;
        //合并数据
        foreach ($children as $k => $v) {

            $children[$k]['list']['target_num'] = 0;
            $children[$k]['list']['finish_num'] = 0;
            $children[$k]['list']['percentage'] = '0%';

            if (!empty($list)) {
                foreach ($list as $val) {

                    if ($v['id'] == $val['company_id']) {

                        $children[$k]['list']               = $val;
                        $children[$k]['list']['percentage'] = @round(($val['finish_num'] / $val['target_num']) * 100, 2) . '%';

                        break;
                    }
                }
            }
            //增加@ 屏蔽division by zero php警告  0不能做除数
            $bfb = @round(($children[$k]['list']['finish_num'] / $children[$k]['list']['target_num']) * 100, 2) . '%';
            $html .= "<tr>
                      <td>{$i}</td>
                      <td>{$v['name']}</td>
                      <td>{$children[$k]['list']['target_num']}</td>
                      <td>{$children[$k]['list']['finish_num']}</td>
                      <td>{$bfb}</td>
                    </tr>";
            $i++;
        }


        $html .= "</tbody></table>";

        $children['counts'] = count($children);
        $children['html']   = $html;

        echo json_encode($children);

    }

    /**
     * 公司下面的区域
     */
    public function actionAjaxarea1()
    {

        $yearandmonth = empty($_POST['yearandmonth']) ? date('Y-n') : $_POST['yearandmonth'];

        //查找区域指标
        $sql = "select sum(target_num)target_num,sum(finish_num)finish_num,area_id from crm_tj_sales_target 
                    where company_id ={$_POST['id']} and year_and_month = '{$yearandmonth}'
                    GROUP by area_id";

        $list = Yii::$app->db->createCommand($sql)->queryAll();


        $html = "<table class='table table-hover table-bordered table-list-check'>
             <thead>
                <tr>
                  <th width='60'>序号</th>
                  <th>名称</th>
                  <th>目标台数</th>
                  <th>实际完成台数</th>
                  <th>完成率</th>
                </tr>
              </thead>
              <tbody>
             ";

        $i      = 1;


        //查找区域下的门店
        $objJsSelectData = new JsSelectDataLogic();
        $res = $objJsSelectData->getSelectOrg($_POST['id']);

        $newArr = $res['id_0'];

        //2.查找指定区域下的门店
        foreach ($newArr as $k => $v) {
            $newArr[$k]['list']['target_num'] = 0;
            $newArr[$k]['list']['finish_num'] = 0;
            $newArr[$k]['list']['percentage'] = '0%';

            if (!empty($list)) {
                foreach ($list as $val) {
                    if ($v['id'] == $val['area_id']) {
                        $newArr[$k]['list']               = $val;
                        $newArr[$k]['list']['percentage'] = @round(($val['finish_num'] / $val['target_num']) * 100, 2) . '%';
                        break;
                    }
                }
            }
            //增加@ 屏蔽division by zero php警告  0不能做除数
            $bfb = @round(($newArr[$k]['list']['finish_num'] / $newArr[$k]['list']['target_num']) * 100, 2) . '%';
            $html .= "<tr>
                      <td>{$i}</td>
                      <td>{$v['name']}</td>
                      <td>{$newArr[$k]['list']['target_num']}</td>
                      <td>{$newArr[$k]['list']['finish_num']}</td>
                      <td>{$bfb}</td>
                    </tr>";
            $i++;
        }

        $html .= "</tbody></table>";

        $newArr['counts'] = count($newArr);
        $newArr['html']   = $html;

        echo json_encode($newArr);
    }
    /**
     *获取区域下的门店信息
     */
    public function actionAjaxarea2()
    {


        $yearandmonth = empty($_POST['yearandmonth']) ? date('Y-n') : $_POST['yearandmonth'];

        //查找区域指标
        $sql = "select sum(target_num)target_num,sum(finish_num)finish_num,shop_id from crm_tj_sales_target 
                    where area_id ={$_POST['id']} and year_and_month = '{$yearandmonth}'
                    GROUP by shop_id";

        $list = Yii::$app->db->createCommand($sql)->queryAll();


        $html = "<table class='table table-hover table-bordered table-list-check'>
             <thead>
                <tr>
                  <th width='60'>序号</th>
                  <th>名称</th>
                  <th>目标台数</th>
                  <th>实际完成台数</th>
                  <th>完成率</th>
                </tr>
              </thead>
              <tbody>
             ";

        $i      = 1;


        //查找区域下的门店
        $objJsSelectData = new JsSelectDataLogic();
        $res = $objJsSelectData->getSelectOrg($_POST['id']);
        $newArr = null;
        if (!empty($res['id_0'])){
            $newArr = $res['id_0'];
            //2.查找指定区域下的门店
            foreach ($newArr as $k => $v) {
                $newArr[$k]['list']['target_num'] = 0;
                $newArr[$k]['list']['finish_num'] = 0;
                $newArr[$k]['list']['percentage'] = '0%';

                if (!empty($list)) {
                    foreach ($list as $val) {
                        if ($v['id'] == $val['shop_id']) {
                            $newArr[$k]['list']               = $val;
                            $newArr[$k]['list']['percentage'] = @round(($val['finish_num'] / $val['target_num']) * 100, 2) . '%';
                            break;
                        }
                    }
                }
                //增加@ 屏蔽division by zero php警告  0不能做除数
                $bfb = @round(($newArr[$k]['list']['finish_num'] / $newArr[$k]['list']['target_num']) * 100, 2) . '%';
                $html .= "<tr>
                          <td>{$i}</td>
                          <td>{$v['name']}</td>
                          <td>{$newArr[$k]['list']['target_num']}</td>
                          <td>{$newArr[$k]['list']['finish_num']}</td>
                          <td>{$bfb}</td>
                        </tr>";
                $i++;
            }

            $html .= "</tbody></table>";

            $newArr['counts'] = count($newArr);
            $newArr['html']   = $html;
        }else{
            $newArr['html'] = null;
            $newArr['counts'] = 0;
        }

        echo json_encode($newArr);


    }

}
