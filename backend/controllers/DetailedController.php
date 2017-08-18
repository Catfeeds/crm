<?php
/**
 * 明细控制器
 */
namespace backend\controllers;

use common\models\PutTheCar;
use Yii;
use common\logic\CompanyUserCenter;
use common\models\Clue;
use common\models\User;
use common\models\Order;
use common\models\Tags;
use common\models\Task;
use yii\data\Pagination;
use common\logic\DataDictionary;
use common\logic\Excel;
use common\logic\JsSelectDataLogic;
use common\common\PublicMethod;
use common\logic\TongJiLogic;
use common\models\OrganizationalStructure;

class DetailedController extends BaseController
{
    public $enableCsrfValidation = false;

    /**
     *明细列表
     */
    public function actionIndex()
    {
        $session     = Yii::$app->getSession();
        $where       = null;//查询条件
        $get         = Yii::$app->request->get();
        $get['area'] = empty($get['area']) ? '' : $get['area'];
        $get['shop'] = empty($get['shop']) ? '' : $get['shop'];
        if (!empty($get['check'])) {
            $get['addtime'] = '';
        } else {
            $get['addtime'] = !isset($get['addtime']) ? date('Y-m-d') . ' - ' . date('Y-m-d') : $get['addtime'];
        }
        $arrOrgIds = $session['userinfo']['permisson_org_ids'];
        
        $level =  $session['userinfo']['role_level'];
        $objTongjiLogic = new TongJiLogic();
        if($level == 30 && (!isset($get['shop_id'])))
        {
            $get['shop_id'] = $this->getDefaultShopId();
        }
            
        if (isset($get['shop_id']) && !empty($get['shop_id'])) {
            $arrGetShopIds = explode(',', $get['shop_id']);
//            if($orgId == -1)
//            {
//                $orgId = array_pop($arrGetShopIds);
//            }
            $orgId = array_pop($arrGetShopIds);
            if ($orgId == -1) {
                $orgId = array_pop($arrGetShopIds);
            }

            $level = @OrganizationalStructure::findOne($orgId)->level;
            $arrWhereAndGroup = $objTongjiLogic->getSelectFieldByLevelAndOrgId($orgId, $arrOrgIds);
        } else {
            $get['id'] = 0;
            $orgId = 0;
            $arrWhereAndGroup = $objTongjiLogic->getStrFieldByLevel($level, $arrOrgIds);
        }
        $where .=  ' and ' . $arrWhereAndGroup['where'];
        $groupBy = $arrWhereAndGroup['groupby'];
        $areas = $arrWhereAndGroup['nextList'];
        
        if (!empty($get['addtime'])) {
            list($startDate, $endDate) = explode(' - ', trim($get['addtime']));
            $where .= " and create_date >= '{$startDate}'";
            $where .= " and create_date <= '{$endDate}'";
        }
        $clumTongji = "
                IFNULL(sum(chengjiao_num),0)chengjiao_num,
                IFNULL(sum(new_clue_num),0)new_clue_num,
                IFNULL(sum(phone_task_num),0)phone_task_num,
                IFNULL(sum(finish_phone_task_num),0)finish_phone_task_num,
                IFNULL(sum(cancel_phone_task_num),0)cancel_phone_task_num,
                IFNULL(sum(new_intention_num),0)new_intention_num,
                IFNULL(sum(talk_num),0)talk_num,
                IFNULL(sum(to_shop_num),0)to_shop_num,
                IFNULL(sum(to_home_num),0)to_home_num,
                IFNULL(sum(ding_che_num),0)ding_che_num,
                IFNULL(sum(fail_num),0)fail_num";
        if ($level == 10) {//总部人员
            $areas = $this->items($areas, $clumTongji, $where, $groupBy, 0, $get);
        } else if ($level == 15) {//公司人员
            $areas = $this->items($areas, $clumTongji, $where, $groupBy, 0, $get);
        } else if ($level == 20) {//大区
            $areas = $this->items($areas, $clumTongji, $where, $groupBy, 0, $get);
        } else if ($level == 30) {//门店店长
            $areas = $this->items($areas, $clumTongji, $where, $groupBy, $orgId, $get);
        }
        $area['upTime'] = PublicMethod::data_update_time(1);//最新修改时间

        $objSelectDataLogic = new JsSelectDataLogic();
        $arrSelectorgList = $objSelectDataLogic->getSelectOrgNew($arrOrgIds, $session['userinfo']['role_level'], true);
        $arrRtn = [
                'data' => $areas, 
                'area' => $area, 
                'level' => $level, 
                'get' => $get, 
                'groupBy' => $groupBy, 
                'selectOrgJson' => json_encode($arrSelectorgList)];
        return $this->render('index', $arrRtn);
    }

    /**
     * 统计结果处理
     * @param $level  等级
     * @param $areas  组织结构
     * @param $clumTongji  查询统计的字段
     * @param $where 筛选条件
     * @param $x_id 某某id  ($level==0 id=company_id)  ($level==1 id=area_id) ($level==2 id=shop_id)
     * $shop_id 门店下面的销售人员列表中跟进中的数据需要指定门店有条件
     * @return array
     */
    public function items($areas, $clumTongji, $where, $x_id, $shop_id = 0, $get = null)
    {
        //当前时间前30分钟
        $time = time() - (30*60);
        $wheres = "
        status=0 
        and (
            salesman_id=0 
            or (assign_time < {$time} 
                and last_view_time=0 
                and who_assign_name = '个人认领')
        )";
        $issued = [];
        
        switch($x_id)
        {
            case 'company_id':
                $level = 10;//父层级
                break;
            case 'area_id':
                $level = 15;//父层级
                break;
            case 'shop_id':
                $level = 20;//父层级
                break;
            case 'salesman_id':
                $level = 30;//父层级
                break;                
        }
        $objCompany = new CompanyUserCenter();
        $session     = Yii::$app->getSession();
        $arrOrgIds = $session['userinfo']['permisson_org_ids'];

        $date = empty($get['addtime']) ? [date('Y-m-d'), date('Y-m-d')] : explode(' - ', $get['addtime']);
        $intStartTime = strtotime($date[0]);
        $intEndTime = empty($date[1]) ? time() : strtotime($date[1]);
        $intEndTime += 86400;

        if ($level == 10) {//总部 获取各个公司的门店id
            foreach($areas as $val)
            {
                $companyId = $val['id'];
                $arrShopIds = $objCompany->getShopIdsByOrgIds($companyId,$arrOrgIds);
                if(empty($arrShopIds)){
                    continue;
                }
                //公司下面的店铺id
                $shopids = implode(',', $arrShopIds);
                $weixiafa = Clue::find()->where($wheres)->andWhere("shop_id in ({$shopids})")->count();
                $issued[$companyId]['not_issued_num'] = $weixiafa;

                // 查询交车任务
                $intMentionTask = PutTheCar::getMentionCount($arrShopIds, $intStartTime, $intEndTime);

                // 未跟进交车任务
                $intNoMentionTask = PutTheCar::getNotMentionCount($arrShopIds, $intStartTime, $intEndTime);

                $issued[$companyId]['mention_task'] = (int)$intMentionTask;
                $issued[$companyId]['not_mention_task'] = (int)$intNoMentionTask;
            }
        }else if($level == 15) {//获取公司下面各大区的id
            foreach ($areas as $k => $val) {
                $areaId = $val['id'];
                $arrShopIds = $objCompany->getShopIdsByOrgIds($areaId, $arrOrgIds);
                if(empty($arrShopIds)){
                    continue;
                }
                //公司下面的店铺id
                $shopids = implode(',', $arrShopIds);                
                $weixiafa = Clue::find()->where($wheres)->andWhere("shop_id in ({$shopids})")->count();
                $issued[$areaId]['not_issued_num'] = $weixiafa;

                // 查询交车任务
                $intMentionTask = PutTheCar::getMentionCount($arrShopIds, $intStartTime, $intEndTime);

                // 未跟进交车任务
                $intNoMentionTask = PutTheCar::getNotMentionCount($arrShopIds, $intStartTime, $intEndTime);

                $issued[$areaId]['mention_task'] = (int)$intMentionTask;
                $issued[$areaId]['not_mention_task'] = (int)$intNoMentionTask;
            }
        }else if($level == 20) {//获取区域下面各门店的id
            foreach ($areas as $k => $v) {
                $weixiafa = Clue::find()->where($wheres)->andWhere("shop_id in ({$v['id']})")->count();
                $issued[$v['id']]['not_issued_num'] = $weixiafa;
                // 查询交车任务
                $intMentionTask = PutTheCar::getMentionCount($v['id'], $intStartTime, $intEndTime);

                // 未跟进交车任务
                $intNoMentionTask = PutTheCar::getNotMentionCount($v['id'], $intStartTime, $intEndTime);
                $issued[$v['id']]['mention_task'] = (int)$intMentionTask;
                $issued[$v['id']]['not_mention_task'] = (int)$intNoMentionTask;
            }

        // $level 门店顾问
        } elseif ($level == OrganizationalStructure::LEVEL_STORE) {
            foreach ($areas as $k => $v) {
                // 查询交车任务
                $intMentionTask = PutTheCar::find()->where([
                    'and',
                    ['in', 'new_shop_id', $v['pid']],
                    ['=', 'new_salesman_id', $v['id']],
                    ['!=', 'status', PutTheCar::STATUS_DELETE],
                    ['between', 'claim_time', $intStartTime, $intEndTime]
                ])->count();

                $issued[$v['id']]['not_issued_num'] = 0;
                $issued[$v['id']]['mention_task'] = (int)$intMentionTask;
                $issued[$v['id']]['not_mention_task'] = 0;
            }
        }


        //查询数据
        $sql  = "select {$x_id},{$clumTongji} from crm_tj_jichushuju where 1=1 $where GROUP by {$x_id}";
        $list = Yii::$app->db->createCommand($sql)->queryAll();

        //跟进中的意向数
        $strShopWhere = (empty($shop_id) ? ' 1 ' : " shop_id = {$shop_id} ");
        $sql            = "select {$x_id}, IFNULL(sum(num),0)num from crm_tj_intention_genjinzhong where {$strShopWhere} GROUP by {$x_id}";
        $yixiang_genjin = Yii::$app->db->createCommand($sql)->queryAll();

        //跟进中的线索数
        $sql            = "select {$x_id},IFNULL(sum(num),0)num from crm_tj_clue_genjinzhong where {$strShopWhere} GROUP by {$x_id}";
        $xiansuo_genjin = Yii::$app->db->createCommand($sql)->queryAll();
        $NewArr         = [];
        foreach ($areas as $k => $v) {//各公司
            $NewArr[$k]['list']['this_level']            = $level;
            $NewArr[$k]['list']['chengjiao_num']         = 0;
            $NewArr[$k]['list']['new_clue_num']          = 0;
            $NewArr[$k]['list']['phone_task_num']        = 0;
            $NewArr[$k]['list']['finish_phone_task_num'] = 0;
            $NewArr[$k]['list']['cancel_phone_task_num'] = 0;
            $NewArr[$k]['list']['new_intention_num']     = 0;
            $NewArr[$k]['list']['talk_num']              = 0;
            $NewArr[$k]['list']['to_shop_num']           = 0;
            $NewArr[$k]['list']['to_home_num']           = 0;
            $NewArr[$k]['list']['ding_che_num']          = 0;
            $NewArr[$k]['list']['fail_num']              = 0;
            $NewArr[$k]['list']['name']                  = $v['name'];
            $NewArr[$k]['list'][$x_id]                   = $v['id'];
            $NewArr[$k]['yixiang_genjin']['num']         = 0;
            $NewArr[$k]['xiansuo_genjin']['num']         = 0;
            $NewArr[$k]['shop_id']                       = 0;

            if (!empty($list)) {
                foreach ($list as $val) {
                    if ($v['id'] == $val[$x_id]) {
                        $NewArr[$k]['list']         = $val;
                        $NewArr[$k]['list']['this_level'] = $level;//$val 中没有level数据需补上
                        $NewArr[$k]['list']['name'] = $v['name'];
                        break;
                    }
                }
            }
            //未下发
            $NewArr[$k]['list']['not_issued_num']        = 0;
            $NewArr[$k]['list']['mention_task'] = 0;
            $NewArr[$k]['list']['not_mention_task'] = 0;
            if (!empty($issued)){
                foreach ($issued as $key => $val) {
                    if ($v['id'] == $key) {
                        $NewArr[$k]['list']['not_issued_num'] = $val['not_issued_num'];
                        $NewArr[$k]['list']['mention_task'] = $val['mention_task'];
                        $NewArr[$k]['list']['not_mention_task'] = $val['not_mention_task'];
                        break;
                    }
                }
            }
            if (!empty($yixiang_genjin)) {
                //各大区跟进中意向数分组
                foreach ($yixiang_genjin as $val) {
                    if ($v['id'] == $val[$x_id]) {
                        $NewArr[$k]['yixiang_genjin'] = $val;
                        break;
                    }
                }
            }
            if (!empty($xiansuo_genjin)) {
                //各大区跟进中的线索数分组
                foreach ($xiansuo_genjin as $val) {
                    if ($v['id'] == $val[$x_id]) {
                        $NewArr[$k]['xiansuo_genjin'] = $val;
                        break;
                    }
                }
            }
        }

        return $NewArr;
    }

    /**
     * 线索、跟进中线索、新增意向客户、跟进中意向客户列表
     */
    public function actionNewXianSuo()
    {
        $get   = Yii::$app->request->get();
        $where = null;//查询条件
        $title = '新增线索';
        if ($get['level'] == 10) { //公司下面的所有大区
            $res   = $this->getRoles($get['id']);
            $where = "shop_id in ($res)";

        } else if ($get['level'] == 15) { //选择的区域下所有门店
            $res   = $this->getRole($get['id']);
            $where = "shop_id in ($res)";

        } else if ($get['level'] == 20) {//当前门店
            $where = "shop_id = {$get['id']}";

        } else if (!empty($get['shop_id']) && $get['id'] == 0) {//未分配的客户
            $arrSelectedIds = explode(',', $get['shop_id']);
            $intShopId = intval(array_pop($arrSelectedIds));
            $where = "shop_id = {$intShopId} and (salesman_id = 0 or isnull(salesman_id))";

        } else if ($get['level'] == 30) {//所属人员
            $where = "salesman_id = {$get['id']}";
        }
        $date = date('Y-m-d');
        //$get['type'] 0=新增线索 1=跟进中线索 2=新增意向客户 3跟进中意向客户
        if ($get['type'] == 0) {
            if (!empty($get['addtime'])) {
                list($startDate, $endDate) = explode(' - ', trim($get['addtime']));
                $where .= " and FROM_UNIXTIME(create_time, '%Y-%m-%d')  >= '{$startDate}'";
                $where .= " and FROM_UNIXTIME(create_time, '%Y-%m-%d') <=  '{$endDate}'";
            } else {
                $date = date('Y-m-d');
                $where .= " and FROM_UNIXTIME(create_time, '%Y-%m-%d') <=  '{$date}'";
            }
        } else if ($get['type'] == 1) {
            //跟进中线索  (无时间概念)1、线索状态 status = 0 2、被分配过 is_assign  = 1
            $where .= " and status = 0  and is_assign = 1";
            $title = '跟进中线索';

        } else if ($get['type'] == 2) {
            //新增意向客户  更新条件：建卡时间在选择时间区间
            $title = '新增意向客户';
            if (!empty($get['addtime'])) {
                list($startDate, $endDate) = explode(' - ', trim($get['addtime']));
                $where .= " and FROM_UNIXTIME(create_card_time, '%Y-%m-%d')  >= '{$startDate}'";
                $where .= " and FROM_UNIXTIME(create_card_time, '%Y-%m-%d') <=  '{$endDate}'";
            } else {
                $where .= " and FROM_UNIXTIME(create_card_time, '%Y-%m-%d') <=  '{$date}'";
            }
        } else if ($get['type'] == 3) {
            //跟进中意向客户  20175.22 更新条件：无时间概念 status=1 意向没有被战败 is_fail=0
            $where .= " and status = 1 and is_fail=0";
            $title = '跟进中意向客户';
        }else if ($get['type'] == 4) {
            //未下发线索
            //下发时间前30分钟
            $time = time() - (30*60);
            $where .= "  and status=0 and (
            salesman_id=0 
            or (assign_time < {$time} 
                and last_view_time=0 
                and who_assign_name = '个人认领')
            )";
            $title = '未认领线索';
        }
        //搜索
        if (!empty($get['keyword'])) {
            $keyword = $get['keyword'];
            if ($get['type'] <= 1) {//线索有创建人查询
                $where .= " and (create_person_name like '%{$keyword}%'";
                $where .= " or customer_name like '%{$keyword}%'";
                $where .= " or customer_phone like '%{$keyword}%'";
                $where .= " or salesman_name like '%{$keyword}%')";
            } else {
                $where .= " and (customer_name like '%{$keyword}%'";
                $where .= " or customer_phone like '%{$keyword}%'";
                $where .= " or salesman_name like '%{$keyword}%')";
            }

        } else {
            $get['keyword'] = null;
        }

        $clum = "id,
                create_time,
                assign_time,
                is_fail,
                create_person_name,
                customer_name,
                customer_phone,
                clue_input_type,
                intention_des,
                des,
                last_view_time,
                status,
                salesman_name,
                create_card_time,
                intention_level_des,
                shop_id,
                shop_name";

        $sql = "select {$clum} from crm_clue where $where";

        if ($get['type'] == 0) {//新增线索 查询无效线索表
            $sql2 = "select {$clum} from crm_clue_wuxiao where $where";
            $sql3 = $sql;
            $sql = " select {$clum} from ( {$sql3} union {$sql2}) as tmp";

            $query1 = "select count(*)count from crm_clue_wuxiao where {$where}";
            $query2 = "select count(*)count from crm_clue where {$where}";
            $query = " SELECT sum(count)count from ( {$query1} union {$query2}) tmp ";
        }else{
            $query = "select count(*)count  from crm_clue where $where ";
        }
        $sql .= " order by id desc";

        if (empty($get['ischeck'])) {//列表
            $get['ischeck'] = 0;


            $queryList = Yii::$app->db->createCommand($query)->queryOne();
            $count = $queryList['count'];

            //分页
            $pagination = new Pagination([
                'defaultPageSize' => 20,
                'totalCount' => $count,
            ]);
            $sql .= " limit $pagination->offset,$pagination->limit ";
        }

        $list    = Yii::$app->db->createCommand($sql)->queryAll();

        $arrDict = new DataDictionary();
        $input_type  = $arrDict->getDictionaryData('input_type');

        $company     = new CompanyUserCenter();
        $org = $company->getLocalOrganizationalStructure();

        foreach ($list as $k => $v) {
            $list[$k]['input_type_name'] = '--';
            $list[$k]['area_name'] = '--';
            //拼接渠道来源
            foreach ($input_type as $val) {
                if ($val['id'] == $v['clue_input_type']) {
                    $list[$k]['input_type_name'] = $val['name'];
                    break;
                }
            }
            $pid = null;
            //获取区域id
            foreach ($org as $val) {
                if ($v['shop_id'] == $val['id']) {
                    $pid = $val['pid'];
                    break;
                }
            }
            //获取区域名
            foreach ($org as $val) {
                if ($pid == $val['id']) {
                    $list[$k]['area_name'] = $val['name'].'-'.$v['shop_name'];
                    break;
                }
            }
            $list[$k]['create_time']      = empty($v['create_time']) ? '' : date('Y-m-d H:i', $v['create_time']);
            $list[$k]['assign_time']      = empty($v['assign_time']) ? '' : date('Y-m-d H:i', $v['assign_time']);
            $list[$k]['last_view_time']   = empty($v['last_view_time']) ? '' : date('Y-m-d H:i', $v['last_view_time']);
            $list[$k]['create_card_time'] = empty($v['create_card_time']) ? '' : date('Y-m-d H:i', $v['create_card_time']);

            if ($v['status'] == 0 && $v['is_fail'] == 0) $list[$k]['statusDes'] = '跟进中';
            else if ($v['status'] == 1 && $v['is_fail'] == 0) $list[$k]['statusDes'] = '意向';
            else if ($v['status'] == 2 && $v['is_fail'] == 0) $list[$k]['statusDes'] = '订车';
            else if ($v['status'] == 3 && $v['is_fail'] == 0) $list[$k]['statusDes'] = '成交';
            else if ($v['status'] == 0 && $v['is_fail'] == 1) $list[$k]['statusDes'] = '无效线索';
            else if ($v['status'] == 1 && $v['is_fail'] == 1) $list[$k]['statusDes'] = '意向战败';
            else if ($v['status'] == 2 && $v['is_fail'] == 1) $list[$k]['statusDes'] = '订车战败';
            else if ($v['status'] == 3 && $v['is_fail'] == 1) $list[$k]['statusDes'] = '成交战败';
        }
        if (empty($get['ischeck'])) {//列表

            if ($get['type'] == 0 || $get['type'] == 1 || $get['type'] == 4)
                return $this->renderPartial('newXianSuoIndex', ['list' => $list, 'pagination' => $pagination, 'title' => $title, 'get' => $get]);

            else if ($get['type'] == 2 || $get['type'] == 3)
                return $this->renderPartial('newYiXiangIndex', ['list' => $list, 'pagination' => $pagination, 'title' => $title, 'get' => $get]);

        } else {//导出excel

            if ($get['type'] <= 1 || $get['type'] == 4) {//新增线索/跟进中线索

                // edited by liujx 2017-06-21 新增线索和未跟进线索导出添加区域门店 start:
                $arrColumns = ['创建时间', '创建人', '姓名', '手机号码', '意向车型', '说明', '最近联系', '状态', '顾问', '渠道来源', '区域门店'];
//                if ($get['type'] == 4){
//                    array_push($arrColumns,'区域门店');
//                }

                // end;

                if ($get['type'] == 0 || $get['type'] == 4){
                    $xx_time = 'create_time';
                }else{
                    $xx_time = 'assign_time';
                }
                foreach ($list as $k => $v) {
                    $arrModels[$k] = [
                        $v[$xx_time],
                        $v['create_person_name'],
                        $v['customer_name'],
                        $v['customer_phone'],
                        $v['intention_des'],
                        $v['des'],
                        $v['last_view_time'],
                        $v['statusDes'],
                        $v['salesman_name'],
                        $v['input_type_name'],
                        // edited by liujx 2017-06-21 新增线索和未跟进线索导出添加区域门店 start:
                        $v['area_name']
                        // end;
                    ];


//                    if ($get['type'] == 4){
//                        array_push($arrModels[$k],$v['area_name']);
//                    }
                }
            } else if ($get['type'] >= 2) {//新增意向客户/跟进中意向客户

                $arrColumns = ['建卡时间', '姓名', '手机号码', '意向等级', '意向车型', '最近联系', '顾问', '渠道来源'];
                foreach ($list as $k => $v) {
                    $arrModels[$k] = [
                        $v['create_card_time'],
                        $v['customer_name'],
                        $v['customer_phone'],
                        $v['intention_level_des'],
                        $v['intention_des'],
                        $v['last_view_time'],
                        $v['salesman_name'],
                        $v['input_type_name'],
                    ];
                }
            }

            $this->outPutExcel($title, $arrColumns, $arrModels);
        }
    }

    /**
     * 电话任务
     */
    public function actionTask()
    {
        $get   = Yii::$app->request->get();

        $where = ' t.task_type = 1';//查询条件
        $title = '电话任务';
        if ($get['level'] == 10) {//公司下面的所有大区
            $res = $this->getRoles($get['id']);
            $where .= " and t.shop_id in ($res)";

        } else if ($get['level'] == 15) { //选择的区域下所有门店
            $res = $this->getRole($get['id']);
            $where .= " and t.shop_id in ($res)";

        } else if ($get['level'] == 20) {//当前门店
            $where .= " and t.shop_id = {$get['id']}";

        } else if (!empty($get['shop_id']) && $get['id'] == 0) {//未分配的客户
            $arrSelectedIds = explode(',', $get['shop_id']);
            $intShopId = intval(array_pop($arrSelectedIds));
            $where = "t.shop_id = {$intShopId} and (t.salesman_id = 0 or isnull(t.salesman_id))";

        } else if ($get['level'] == 30) { //所属人员
            $where .= " and t.salesman_id = {$get['id']}";
        }

        if ($get['type'] == 1) {//任务完成
            $where .= " and t.is_finish = 2";
            $title = '电话任务完成';

        } else if ($get['type'] == 2) {//任务取消
            $where .= " and t.is_cancel = 1";
            $title = '任务取消';
        }
        if (!empty($get['addtime'])) {
            list($startDate, $endDate) = explode(' - ', trim($get['addtime']));
            $where .= " and t.task_date  >= '{$startDate}'";
            $where .= " and t.task_date <=  '{$endDate}'";
        } else {
            $date = date('Y-m-d');
            $where .= " and t.task_date  <= '{$date}'";
        }
        //搜索
        if (!empty($get['keyword'])) {
            $keyword = $get['keyword'];
            $where .= " and (c.customer_name like '%{$keyword}%'";
            $where .= " or c.customer_phone like '%{$keyword}%'";
            $where .= " or u.name like '%{$keyword}%')";
        } else {
            $get['keyword'] = null;
        }
        $query = Task::find()->select(
            't.task_date,
            t.start_time,
            t.task_from,
            t.task_type,
            t.end_time,
            t.is_finish,
            t.is_cancel,
            t.task_des,
            t.salesman_id,
            c.customer_name,
            c.customer_phone,
            t.cancel_reason,
            u.name salesman_name
            ')
            ->from('crm_task t')
            ->join('left join', 'crm_clue c', 't.clue_id=c.id')
            ->join('join', 'crm_user u', 't.salesman_id=u.id')
            ->where($where);
        //echo $query->createCommand()->getRawSql();
        if (empty($get['ischeck'])) {//列表
            $get['ischeck'] = 0;
            $count          = $query->count();
            //分页
            $pagination = new Pagination([
                'defaultPageSize' => 20,
                'totalCount' => $count,
            ]);
            $list       = $query->offset($pagination->offset)
                ->limit($pagination->limit);
        }
        $list = $query->orderBy('t.id desc')
            ->asArray()
            ->all();

        if (empty($get['ischeck'])) {//列表
            return $this->renderPartial('taskIndex', ['list' => $list, 'pagination' => $pagination, 'title' => $title, 'get' => $get]);
        } else {//导出excel
            // edited by liujx 2017-06-21 导出添加 姓名和电话 start:
            $arrColumns = ['任务日期', '来源', '姓名', '电话', '任务类型', '完成时间', '状态', '备注', '顾问'];
            // end;
            foreach ($list as $k => $v) {

                if ($v['task_type'] == 1) $list[$k]['task_type'] = '电话任务';
                else if ($v['task_type'] == 2) $list[$k]['task_type'] = '到店任务';
                else if ($v['task_type'] == 3) $list[$k]['task_type'] = '上门任务';

                if ($v['is_finish'] == 2) {
                    $list[$k]['is_finish'] = '已完成';
                } else if ($v['is_cancel'] == 1) {
                    $list[$k]['is_finish'] = '已取消';
                } else {
                    $list[$k]['is_finish'] = '未完成';
                }
                $list[$k]['end_time'] = empty($v['end_time']) ? '' : date('Y-m-d H:i', $v['end_time']);
            }
            $arrModels = [];
            foreach ($list as $k => $v) {
                $arrModels[$k] = [
                    $v['task_date'],
                    $v['task_from'],
                    // edited by liujx 2017-06-21 导出添加 姓名和电话 start:
                    $v['customer_name'],
                    $v['customer_phone'],
                    // end;
                    $v['task_des'],
                    $v['end_time'],
                    $v['is_finish'],
                    $v['cancel_reason'],
                    $v['salesman_name']
                ];
            }
            $this->outPutExcel($title, $arrColumns, $arrModels);
        }
    }

    /**
     * 订车/交车
     */
    public function actionCar()
    {
        $get   = Yii::$app->request->get();

        $where = null;//查询条件
        $title = '订车客户';
        if ($get['level'] == 10) {//公司下面的所有大区
            $res   = $this->getRoles($get['id']);
            $where = "o.shop_id in ($res)";

        } else if ($get['level'] == 15) { //选择的区域下所有门店
            $res   = $this->getRole($get['id']);
            $where = "o.shop_id in ($res)";

        } else if ($get['level'] == 20) { //当前门店
            $where = "o.shop_id = {$get['id']}";

        } else if (!empty($get['shop_id']) && $get['id'] == 0) {//未分配的客户
            $arrSelectedIds = explode(',', $get['shop_id']);
            $intShopId = intval(array_pop($arrSelectedIds));
            $where = "o.shop_id = {$intShopId} and (o.salesman_id = 0 or isnull(o.salesman_id))";

        } else if ($get['level'] == 30) {//所属人员
            $where = "o.salesman_id = {$get['id']}";
        }
        $date = date('Y-m-d');
        if ($get['type'] == 0) {//订车
            $title = '订车客户';
            if (!empty($get['addtime'])) {
                list($startDate, $endDate) = explode(' - ', trim($get['addtime']));
                $where .= " and  FROM_UNIXTIME(o.cai_wu_dao_zhang_time, '%Y-%m-%d') >= '{$startDate}'";
                $where .= " and FROM_UNIXTIME(o.cai_wu_dao_zhang_time, '%Y-%m-%d') <=  '{$endDate}'";
            } else {
                $where .= " and FROM_UNIXTIME(o.cai_wu_dao_zhang_time, '%Y-%m-%d') <=  '{$date}'";
            }

        } else if ($get['type'] == 1) { //交车
            $where .= " and o.status = 6";
            $title = '交车客户';
            if (!empty($get['addtime'])) {
                list($startDate, $endDate) = explode(' - ', trim($get['addtime']));
                $where .= " and  FROM_UNIXTIME(o.car_delivery_time, '%Y-%m-%d') >= '{$startDate}'";
                $where .= " and FROM_UNIXTIME(o.car_delivery_time, '%Y-%m-%d') <=  '{$endDate}'";
            } else {
                $where .= " and FROM_UNIXTIME(o.car_delivery_time, '%Y-%m-%d') <=  '{$date}'";
            }
        }
        //搜索
        if (!empty($get['keyword'])) {
            $keyword = $get['keyword'];
            $where .= " and (c.customer_name like '%{$keyword}%'";
            $where .= " or c.customer_phone like '%{$keyword}%'";
            $where .= " or u.name like '%{$keyword}%')";
        } else {
            $get['keyword'] = null;
        }

        $sql = "select 
            o.create_time,
            o.car_owner_name,
            o.car_owner_phone,
            o.car_type_name,
            o.status,
            o.salesman_id,
            o.car_delivery_time,
            o.cai_wu_dao_zhang_time,
            c.create_card_time,
            c.last_view_time,
            c.clue_source,
            c.customer_name,
            c.customer_phone,
            u.name salesman_name
        from crm_order o 
        left join crm_clue c on o.clue_id = c.id 
        left join crm_user u on u.id = o.salesman_id
        where $where order by o.id desc";

        if (empty($get['ischeck'])) {//列表
            $get['ischeck'] = 0;

            $query = "select count(o.id)count from crm_order o 
                        left join crm_clue c on o.clue_id = c.id 
                        left join crm_user u on u.id = o.salesman_id
                        where $where";
            $queryList = Yii::$app->db->createCommand($query)->queryOne();
            $count = $queryList['count'];
            //分页
            $pagination = new Pagination([
                'defaultPageSize' => 20,
                'totalCount' => $count,
            ]);
            $sql .= " limit $pagination->offset,$pagination->limit ";
        }
        $list = Yii::$app->db->createCommand($sql)->queryAll();

        $arrDict = new DataDictionary();
        $source  = $arrDict->getDictionaryData('source');
        foreach ($list as $k => $v) {
            $list[$k]['source_name'] = '--';
            //拼接信息来源
            foreach ($source as $val) {
                if ($val['id'] == $v['clue_source']) {
                    $list[$k]['source_name'] = $val['name'];
                    break;
                }
            }
            $list[$k]['create_time']       = empty($v['create_time']) ? '--' : date('Y-m-d H:i', $v['create_time']);
            $list[$k]['create_card_time']  = empty($v['create_card_time']) ? '--' : date('Y-m-d H:i', $v['create_card_time']);
            $list[$k]['car_delivery_time'] = empty($v['car_delivery_time']) ? '--' : date('Y-m-d H:i', $v['car_delivery_time']);
            $list[$k]['last_view_time']    = empty($v['last_view_time']) ? '--' : date('Y-m-d H:i', $v['last_view_time']);
            $list[$k]['cai_wu_dao_zhang_time'] = empty($v['cai_wu_dao_zhang_time']) ? '--' : date('Y-m-d H:i', $v['cai_wu_dao_zhang_time']);

            if ($v['status'] == 1) $list[$k]['statusDes'] = '处理中';
            elseif ($v['status'] == 2) $list[$k]['statusDes'] = '客户未支付';
            elseif ($v['status'] == 3) $list[$k]['statusDes'] = '财务到账';
            elseif ($v['status'] == 4) $list[$k]['statusDes'] = '战败';
            elseif ($v['status'] == 5) $list[$k]['statusDes'] = '客户已支付';
            elseif ($v['status'] == 6) $list[$k]['statusDes'] = '已交车';

        }
        if (empty($get['ischeck'])) {//列表
            return $this->renderPartial('carIndex', ['list' => $list, 'pagination' => $pagination, 'title' => $title, 'get' => $get]);

        } else {//导出EXCEL
            $arrModels = [];
            if ($get['type'] == 0) {
                $arrColumns = ['订车时间（财务到账）', '姓名', '手机号码', '信息来源', '订车车型', '建卡时间', '最近联系', '状态', '顾问'];
                foreach ($list as $k => $v) {
                    $arrModels[$k] = [
                        $v['cai_wu_dao_zhang_time'],
                        $v['customer_name'],
                        $v['customer_phone'],
                        $v['source_name'],
                        $v['car_type_name'],
                        $v['create_card_time'],
                        $v['last_view_time'],
                        $v['status'],
                        $v['salesman_name'],
                    ];
                }
            } else {
                $arrColumns = ['购车时间', '姓名', '手机号码', '信息来源', '订车车型', '建卡时间', '最近联系', '顾问'];
                foreach ($list as $k => $v) {
                    $arrModels[$k] = [
                        $v['car_delivery_time'],
                        $v['customer_name'],
                        $v['customer_phone'],
                        $v['source_name'],
                        $v['car_type_name'],
                        $v['create_card_time'],
                        $v['last_view_time'],
                        $v['salesman_name'],
                    ];
                }
            }
            $this->outPutExcel($title, $arrColumns, $arrModels);
        }

    }

    /**
     *商谈/到点/上门
     */
    public function actionTalk()
    {
        $get = Yii::$app->request->get();
        $where = null;//查询条件
        $title = '商谈';
        if ($get['level'] == 10) {//公司下面的所有大区
            $res   = $this->getRoles($get['id']);
            $where = "t.shop_id in ($res)";

        } else if ($get['level'] == 15) {//选择的区域下所有门店
            $res = $this->getRole($get['id']);
            $where = "t.shop_id in ($res)";

        } else if ($get['level'] == 20) {//当前门店
            $where = "t.shop_id = {$get['id']}";

        } else if ($get['level'] == 30) {//所属人员
            $where = "t.salesman_id = {$get['id']}";
        }
        if ($get['type'] == 0) {//商谈数
            $where .= " and t.talk_type in(2, 3, 5, 6, 7, 8, 9, 10)";
            $title = '商谈';

        } else if ($get['type'] == 1) {//到店
            $where .= " and t.talk_type in(5, 6, 7)";
            $title = '到店';

        } else if ($get['type'] == 2) {//上门
            $where .= " and t.talk_type in(8, 9, 10)";
            $title = '上门';
        }
        if (!empty($get['addtime'])) {
            list($startDate, $endDate) = explode(' - ', trim($get['addtime']));
            $where .= " and  t.talk_date >= '{$startDate}'";
            $where .= " and  t.talk_date <=  '{$endDate}'";
        }

        //搜索
        if (!empty($get['keyword'])) {
            $keyword = $get['keyword'];
            $where .= " and (c.customer_name like '%{$keyword}%'";
            $where .= " or c.customer_phone like '%{$keyword}%'";
            $where .= " or u.name like '%{$keyword}%')";
        } else {
            $get['keyword'] = null;
        }
        $sql   = "select t.talk_date,
        t.start_time,
       c.name as customer_name,
	   c.phone as customer_phone,
        t.talk_type,
        t.select_tags,
        t.content,
        t.imgs,
        t.voices,
        t.salesman_id,
        t.is_type,
        u.name salesman_name
        from crm_talk t 
        left join crm_customer c on t.castomer_id = c.id
        left join crm_user u on u.id = t.salesman_id 
        where $where 
        order by t.id desc ";

        if (empty($get['ischeck'])) {//列表
            $get['ischeck'] = 0;
            $query = "select count(t.id)count  from crm_talk t 
            left join crm_customer c on t.castomer_id = c.id
            left join crm_user u on u.id = t.salesman_id 
            where $where ";
            $queryList = Yii::$app->db->createCommand($query)->queryOne();
            $count = $queryList['count'];
            //分页
            $pagination = new Pagination([
                'defaultPageSize' => 20,
                'totalCount' => $count,
            ]);
            $sql .= " limit $pagination->offset,$pagination->limit ";
        }

        $list = Yii::$app->db->createCommand($sql)->queryAll();
        $tags = Tags::find()->select('id,name')->where('status=1')->asArray()->all();

        foreach ($list as $k => $v) {
            $list[$k]['select_tags_name'] = '--';
            $list[$k]['talk_time'] = empty($v['start_time']) ? '--' : date('Y-m-d H:i',$v['start_time']);
            if (!empty($v['select_tags'])) {

                $select_tags = explode(",", $v['select_tags']);
                //拼接标签
                foreach ($tags as $val) {
                    if (in_array($val['id'], $select_tags)) {
                        $list[$k]['select_tags_name'] .= $val['name'] . ',';
                    }
                }
            }
            if ($v['talk_type'] == 2) $list[$k]['talk_typeDes'] = '来电';
            else if ($v['talk_type'] == 3) {
                $list[$k]['talk_typeDes'] = '去电';

                // edited by liujx 2017-06-21 显示问题 start:
                switch ($v['is_type']) {
                    case 1:
                        $list[$k]['talk_typeDes'] .= '-手动';
                        break;
                    case 2:
                        $list[$k]['talk_typeDes'] .= '-电话';
                        break;
                    default:
                        // 目前不做处理

                }

                // end;
            }
            else if ($v['talk_type'] == 5) $list[$k]['talk_typeDes'] = '到店-商谈';
            else if ($v['talk_type'] == 6) $list[$k]['talk_typeDes'] = '到店-订车';
            else if ($v['talk_type'] == 7) $list[$k]['talk_typeDes'] = '到店-交车';
            else if ($v['talk_type'] == 8) $list[$k]['talk_typeDes'] = '上门-商谈';
            else if ($v['talk_type'] == 9) $list[$k]['talk_typeDes'] = '上门-订车';
            else if ($v['talk_type'] == 10) $list[$k]['talk_typeDes'] = '上门-交车';
        }
        if (empty($get['ischeck'])) {//列表
            return $this->renderPartial('talkIndex', ['list' => $list, 'pagination' => $pagination, 'title' => $title, 'get' => $get]);

        }else{//导出EXCEL
            $arrModels  = [];
            $arrColumns = ['联系时间', '姓名', '手机号码', '类型', '标签', '商谈内容', '图片', '录音', '顾问'];
            foreach ($list as $k => $v) {
                $arrModels[$k] = [
//                    $v['talk_date'],
                    $v['talk_time'],
                    $v['customer_name'],
                    $v['customer_phone'],
                    $v['talk_type'],
                    $v['select_tags_name'],
                    $v['content'],
                    $v['imgs'],
                    $v['voices'],
                    $v['salesman_name']
                ];

            }

            $this->outPutExcel($title, $arrColumns, $arrModels);
        }
    }

    /**
     * 战败
     */
    public function actionFail()
    {
        $get = Yii::$app->request->get();
        $where = null;//查询条件
        $title = '战败';
        if ($get['level'] == 10) {//公司下面的所有大区
            $res   = $this->getRoles($get['id']);
            $where = "shop_id in ($res)";

        } else if ($get['level'] == 15) {//选择的区域下所有门店
            $res = $this->getRole($get['id']);
            $where = "shop_id in ($res)";

        } else if ($get['level'] == 20) {//当前门店
            $where = "shop_id = {$get['id']}";

        } else if (!empty($get['shop_id']) && $get['id'] == 0) {//未分配的客户
            $arrSelectedIds = explode(',', $get['shop_id']);
            $intShopId = intval(array_pop($arrSelectedIds));
            $where = "shop_id = {$intShopId} and (salesman_id = 0 or isnull(salesman_id))";

        } else if ($get['level'] == 30) {//所属人员
            $where = "salesman_id = {$get['id']}";
        }
        $date = date('Y-m-d');
        if (!empty($get['addtime'])) {
            list($startDate, $endDate) = explode(' - ', trim($get['addtime']));
            $where .= " and FROM_UNIXTIME(last_fail_time, '%Y-%m-%d')  >= '{$startDate}'";
            $where .= " and FROM_UNIXTIME(last_fail_time, '%Y-%m-%d') <=  '{$endDate}'";
        } else {
            $date = date('Y-m-d');
            $where .= " and FROM_UNIXTIME(last_fail_time, '%Y-%m-%d') <=  '{$date}'";
        }
        //搜索
        if (!empty($get['keyword'])) {
            $keyword = $get['keyword'];
            $where .= " and (customer_name like '%{$keyword}%'";
            $where .= " or customer_phone like '%{$keyword}%'";
            $where .= " or salesman_name like '%{$keyword}%')";
        } else {
            $get['keyword'] = null;
        }
        $clum  = "last_fail_time,customer_name,customer_phone,clue_input_type,intention_id,intention_des,status,fail_reason,salesman_name";
        $query = Clue::find()->select($clum)->where($where);
        if (empty($get['ischeck'])) {//列表
            $get['ischeck'] = 0;
            $count = $query->count();

            //分页
            $pagination = new Pagination([
                'defaultPageSize' => 20,
                'totalCount' => $count,
            ]);
            $query = $query->offset($pagination->offset)
                ->limit($pagination->limit);
        }

        $list = $query->orderBy('last_fail_time desc')
            ->asArray()
            ->all();

        $arrDict = new DataDictionary();
        $car     = new \common\logic\CarBrandAndType();
        //渠道来源
        $input_type = $arrDict->getDictionaryData('input_type');
        foreach ($list as $k => $v) {

            $list[$k]['last_fail_time'] = empty($list[$k]['last_fail_time']) ? '--' : date('Y-m-d H:i', $list[$k]['last_fail_time']);
            $list[$k]['statusDes']      = '--';
            $list[$k]['clue_input_type_name'] = '--';

            //获取车系名字
            $list[$k]['intention_des'] = $car->getCarTypeNameByTypeId($v['intention_id']);
            if ($v['status'] == 1) {
                $list[$k]['statusDes'] = '意向战败';
            } else if ($v['status'] == 2) {
                $list[$k]['statusDes'] = '订车战败';
            }
            //拼接渠道来源
            foreach ($input_type as $val) {
                if ($val['id'] == $v['clue_input_type']) {
                    $list[$k]['clue_input_type_name'] = $val['name'];
                    break;
                }
            }
        }
        if (empty($get['ischeck'])) {//列表
            return $this->renderPartial('failIndex', ['list' => $list, 'pagination' => $pagination, 'title' => $title, 'get' => $get]);
        }else{
            $arrModels  = [];
            $arrColumns = ['战败时间', '姓名', '手机号码', '渠道来源', '意向车型', '战败类型', '战败原因', '顾问'];
            foreach ($list as $k => $v) {
                $arrModels[$k] = [
                    $v['last_fail_time'],
                    $v['customer_name'],
                    $v['customer_phone'],
                    $v['clue_input_type_name'],
                    $v['intention_des'],
                    $v['statusDes'],
                    $v['fail_reason'],
                    $v['salesman_name']
                ];
            }
            $this->outPutExcel($title, $arrColumns, $arrModels);
        }
    }

    /**
     * 未交车
     */
    public function actionNoCar()
    {

        $session = Yii::$app->getSession();
        $company = new CompanyUserCenter();
        $get     = Yii::$app->request->get();

        $keyword = null;
        $where   = null;
        if (!empty($get['keyword'])) {
            $keyword = $get['keyword'];
            $where .= " and (o.car_owner_name like '%{$keyword}%' || 
            o.car_owner_phone like '%{$keyword}%' || 
            o.car_type_name = '{$keyword}' ||
            o.color_configure = '{$keyword}')";
        }
        $role = $session['userinfo']['role_level'];
        $orgIds = $session['userinfo']['permisson_org_ids'];
        $strOrgIds = implode(',', $orgIds);
        //查找区域下的门店
        if ($role == 15) {//公司人员
            $where .= " and o.shop_id in($strOrgIds)";
        } else if ($role == 20) {//大区
            $where .= " and o.shop_id in($strOrgIds)";
        } else if ($role == 30) {//门店店长
            $where .= " and o.shop_id in ({$strOrgIds})";
        }
        $arrDict = new DataDictionary();
        //购买方式
        $buy_type = $arrDict->getDictionaryData('buy_type');
        $query = "select count(o.id)count from  crm_order o left join crm_clue c on o.clue_id = c.id
        where o.status != 6 and o.status != 4 and o.cai_wu_dao_zhang_time > 0 $where";
        $queryList = Yii::$app->db->createCommand($query)->queryOne();
        $count = $queryList['count'];

        //分页
        $pagination = new Pagination([
            'defaultPageSize' => 20,
            'totalCount' => $count,
        ]);

        $sql = "select
        c.id,
        c.customer_name,
        c.customer_phone,
        o.car_owner_name,
        o.car_owner_phone,
        c.create_card_time,
        o.create_time,
        o.predict_car_delivery_time,
        o.car_type_name,
        o.color_configure,
        o.deposit,
        o.buy_type,
        o.is_insurance,
        o.status from  crm_order o  left join crm_clue c on o.clue_id = c.id
        where o.status != 6 and o.status != 4 and o.cai_wu_dao_zhang_time > 0 $where order by o.id desc
        limit $pagination->offset,$pagination->limit
        ";
        $list = Yii::$app->db->createCommand($sql)->queryAll();
        foreach ($list as $k => $v) {
            $list[$k]['buy_name'] = '--';
            //拼接购买方式
            foreach ($buy_type as $val) {
                if ($v['buy_type'] == $val['id']) {
                    $list[$k]['buy_name'] = $val['name'];
                    break;
                }
            }
        }
        return $this->render('noCarIndex', ['list' => $list, 'pagination' => $pagination, 'keyword' => $keyword]);
    }

    public function actionMentionTask()
    {
        $get = Yii::$app->request->get();
        $where = ['and']; // 查询条件
        $title = '交车任务';
        if (in_array($get['level'], [
            OrganizationalStructure::LEVEL_ALL,         // 公司下面的所有大区
            OrganizationalStructure::LEVEL_COMPANY,     // 选择的区域下所有门店
        ])) {
            $objCompany = new CompanyUserCenter();
            $session     = Yii::$app->getSession();
            $arrOrgIds = $session['userinfo']['permisson_org_ids'];
            $arrShopIds = $objCompany->getShopIdsByOrgIds($get['id'], $arrOrgIds);
            if ($arrShopIds) {
                $arrShopIds = array_map(function($value) {
                    return (int)$value;
                }, $arrShopIds);
            }
            $where[] = ['in', 'new_shop_id', $arrShopIds];
        // 门店
        } elseif ($get['level'] == OrganizationalStructure::LEVEL_REGION) {
            $where[] = ['new_shop_id' => (int)$get['id']];
        } elseif ($get['level'] == OrganizationalStructure::LEVEL_STORE) {
            $where[] = ['=', 'new_salesman_id', (int)$get['id']];
            $arrShopIds = explode(',', $get['shop_id']);
            $where[] = ['new_shop_id' => (int)array_pop($arrShopIds)];
        }

        // 查询类型
        $type = empty($get['type']) ? 'mention-task' : $get['type'];
        if ($type === 'mention-task') {
            $where[] = ['!=', 'status', PutTheCar::STATUS_DELETE];
            if ($get['level'] !== OrganizationalStructure::LEVEL_STORE) {
                $where[] = ['>', 'new_salesman_id', 0];
            }
            $field = 'claim_time';
        } else {
            $where[] = ['=', 'status', PutTheCar::STATUS_UNDONE];
            $field = 'confirm_time';
            $where[] = ['=', 'new_salesman_id', 0];
            $title = '未跟进'.$title;
        }

        // 查询时间
        $date = empty($get['addtime']) ? [date('Y-m-d'), date('Y-m-d')] : explode(' - ', $get['addtime']);
        $start = strtotime($date[0]);
        $end = empty($date[1]) ? time() : strtotime($date[1]) + 86400;
        $where[] = ['between', $field, $start, $end];

        // 搜索条件
        if (!empty($get['keyword'])) {
            $where[] = [
                'or',
                ['like', 'customer_name', $get['keyword']],
                ['like', 'old_salesman_name', $get['keyword']],
                ['like', 'customer_phone', $get['keyword']],
                ['like', 'new_salesman_name', $get['keyword']],
            ];
        } else {
            $get['keyword'] = null;
        }

        $query = PutTheCar::find()->where($where);
        $pagination = null;
        if (empty($get['ischeck'])) { // 列表
            $get['ischeck'] = 0;
            $count = $query->count();
            // 分页
            $pagination = new Pagination([
                'defaultPageSize' => 20,
                'totalCount' => $count,
            ]);

            $query = $query->offset($pagination->offset)
                ->limit($pagination->limit);
        }

        $list = $query->orderBy(['create_time' => SORT_DESC])
            ->asArray()
            ->all();

        // 显示列表还是导出
        if (empty($get['ischeck'])) {
            return $this->renderPartial('mention-task', [
                'list' => $list,
                'pagination' => $pagination,
                'title' => $title,
                'get' => $get
            ]);
        } else {
            $arrModels  = [];
            $arrColumns = ['购车时间', '姓名', '手机号码', '订购车型', '交车门店', '交车顾问', '订车门店', '订车顾问'];
            foreach ($list as $k => $v) {
                $arrModels[$k] = [
                    date('Y-m-d H:i:s', $v['the_car_time']),
                    $v['customer_name'],
                    $v['customer_phone'],
                    $v['yu_ding_che_xing'],
                    $v['new_shop_name'],
                    $v['new_salesman_name'],
                    $v['old_shop_name'],
                    $v['old_salesman_name']
                ];
            }

            $this->outPutExcel($title, $arrColumns, $arrModels);
        }
    }
}
