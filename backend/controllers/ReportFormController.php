<?php
/**
 * 报表查询
 * 于凯
 */
namespace backend\controllers;

use Yii;
use common\models\Clue;
use yii\data\Pagination;
use common\logic\DataDictionary;
use common\common\PublicMethod;
set_time_limit(0);//无超时
ini_set('memory_limit','-1');//设置内存
class ReportFormController extends BaseController
{
    public $enableCsrfValidation = false;

    /**
     * 列表
     */
    public function actionIndex()
    {
        $this->checkPermission('/report-form/index');
        $get = Yii::$app->request->get();
        if (empty($get)) {
            $list           = null;
            $get['status']  = null;
            $get['addtime'] = null;
            $get['upTime']  = PublicMethod::data_update_time(1);//最新修改时间
            return $this->render('index', ['list' => $list, 'get' => $get]);
        } else {
            $where = '1=1';
            $order = null;
            list($startDate, $endDate) = explode(' - ', trim($get['addtime']));

            if ($get['status'] <= 4) {//意向表
                $tableName = 'crm_clue';
                switch ($get['status']) {

                    case 1 :
                        //$where = ' status = 0';
                        //线索(2017.5.18更新需求 按照创建时间 不需要状态status = 0验证)
                        $where .= " and FROM_UNIXTIME(create_time, '%Y-%m-%d')  >= '{$startDate}'";
                        $where .= " and FROM_UNIXTIME(create_time, '%Y-%m-%d') <=  '{$endDate}'";
                        $order = "cl.create_time ";
                        break;

                    case 2 :
                        $where = ' status = 0 and is_fail = 1';//无效线索
                        $where .= " and FROM_UNIXTIME(cl.create_time, '%Y-%m-%d')  >= '{$startDate}'";
                        $where .= " and FROM_UNIXTIME(cl.create_time, '%Y-%m-%d') <=  '{$endDate}'";
                        $order     = "cl.last_fail_time ";
                        $tableName = 'crm_clue_wuxiao';
                        break;

                    case 3 :
                        //$where = ' status = 1';//意向
                        //意向 (2017.5.18更新需求 按照建卡时间 不需要状态status = 1验证)
                        $where .= " and FROM_UNIXTIME(cl.create_card_time, '%Y-%m-%d')  >= '{$startDate}'";
                        $where .= " and FROM_UNIXTIME(cl.create_card_time, '%Y-%m-%d') <=  '{$endDate}'";
                        $order = "cl.create_card_time ";
                        break;

                    case 4 :
                        $where = ' is_fail = 1';//战败
                        $where .= " and FROM_UNIXTIME(cl.last_fail_time, '%Y-%m-%d')  >= '{$startDate}'";
                        $where .= " and FROM_UNIXTIME(cl.last_fail_time, '%Y-%m-%d') <=  '{$endDate}'";
                        $order = "cl.last_fail_time ";
                        break;
                }

                $sql = "select 
            cl.customer_name,
            cl.customer_phone,
            cl.planned_purchase_time_id,
            cu.sex,
            cl.clue_input_type,
            cl.clue_source,
            cl.intention_id,
            cl.intention_des,
            cl.shop_id,
            cl.create_time,
            cl.create_card_time,
            cl.last_fail_time from {$tableName} cl join crm_customer cu on cl.customer_id = cu.id where $where";

                if ($get['status'] == 1) {
                    $sql1 = "select 
                        customer_id,
                        customer_name,
                        customer_phone,
                        planned_purchase_time_id,
                        clue_input_type,
                        clue_source,
                        intention_id,
                        intention_des,
                        shop_id,
                        create_time,
                        create_card_time,
                        last_fail_time from crm_clue  where $where";

                    $sql2 = "select 
                        customer_id,
                        customer_name,
                        customer_phone,
                        planned_purchase_time_id,
                        clue_input_type,
                        clue_source,
                        intention_id,
                        intention_des,
                        shop_id,
                        create_time,
                        create_card_time,
                        last_fail_time from crm_clue_wuxiao  where $where";

                    $sql = "  select 
                            cl.customer_name,
                            cl.customer_phone,
                            cl.planned_purchase_time_id,
                            cu.sex,
                            cl.clue_input_type,
                            cl.clue_source,
                            cl.intention_id,
                            cl.intention_des,
                            cl.shop_id,
                            cl.create_time,
                            cl.create_card_time,
                            cl.last_fail_time from ( {$sql1} union {$sql2}) as cl
                            join crm_customer cu on cl.customer_id = cu.id ";

                    $where = "  FROM_UNIXTIME(cl.create_time, '%Y-%m-%d')  >= '{$startDate}'";
                    $where .= "  and FROM_UNIXTIME(cl.create_time, '%Y-%m-%d') <=  '{$endDate}'";
                    $query1 = "select count(*)count from crm_clue_wuxiao cl  where {$where}";
                    $query2 = "select count(*)count from crm_clue cl  where {$where}";
                    $query = " SELECT sum(count)count from ( {$query1} union {$query2}) as tmp";
                    $queryList = Yii::$app->db->createCommand($query)->queryOne();
                    $count = $queryList['count'];

                }else{
                    $query = "select count(cl.id)count from {$tableName} cl  join crm_customer cu on cl.customer_id = cu.id where $where";
                    $queryList = Yii::$app->db->createCommand($query)->queryOne();
                    $count = $queryList['count'];
                }

                //分页
                $pagination = new Pagination([
                    'defaultPageSize' => 20,
                    'totalCount' => $count,
                ]);

                $sql .= " order by {$order} desc limit $pagination->offset,$pagination->limit ";

            } else if ($get['status'] == 5) {//商谈表

                $where = ' t.talk_type in(5,6,7)';
                $where .= " and talk_date  >= '{$startDate}'";
                $where .= " and talk_date <=  '{$endDate}'";
                $order = " talk_date ";

                $sql = "select id
            from 
            (SELECT  t.id,
                t.clue_id
                 from crm_clue cl 
                join crm_customer cu on cl.customer_id = cu.id 
                join crm_talk t on t.clue_id = cl.id 
                where $where
            order by t.talk_date desc
            ) as tmp 
            group by clue_id 
            ";

                $count = Yii::$app->db->createCommand($sql)->query()->rowCount;

                //分页
                $pagination = new Pagination([
                    'defaultPageSize' => 20,
                    'totalCount' => $count,
                ]);

                $sql = "
            select   
            create_time, 
            customer_name, 
            customer_phone, 
            planned_purchase_time_id, 
            sex, 
            clue_input_type, 
            clue_source, 
            intention_id, 
            intention_des, 
            shop_id,
            talk_date,
            clue_id  
            from 
            (SELECT  t.create_time,
                cl.customer_name,
                cl.customer_phone,
                cl.planned_purchase_time_id,
                cu.sex,
                cl.clue_input_type,
                cl.clue_source,
                cl.intention_id,
                cl.intention_des,
                t.shop_id,
                t.talk_date,
                t.clue_id
                 from crm_clue cl 
                join crm_customer cu on cl.customer_id = cu.id 
                join crm_talk t on t.clue_id = cl.id 
                where $where
            order by t.talk_date desc
            ) as tmp 
            group by clue_id order by {$order} desc limit $pagination->offset,$pagination->limit
            ";

            } else if ($get['status'] == 6) {//订单表

                $where = 'o.cai_wu_dao_zhang_time > 0';
                $where .= " and FROM_UNIXTIME(o.cai_wu_dao_zhang_time, '%Y-%m-%d')  >= '{$startDate}'";
                $where .= " and FROM_UNIXTIME(o.cai_wu_dao_zhang_time, '%Y-%m-%d') <=  '{$endDate}'";

                $query = "select
            count(*)count
            from crm_clue cl 
            join crm_customer cu on cl.customer_id = cu.id 
            join crm_order o on o.clue_id = cl.id
            where $where";
            $queryList = Yii::$app->db->createCommand($query)->queryOne();
            $count = $queryList['count'];
            //分页
            $pagination = new Pagination([
                'defaultPageSize' => 20,
                'totalCount' => $count,
            ]);

                $sql = "select
            o.create_time,
            cl.customer_name,
            cl.customer_phone,
            cl.planned_purchase_time_id,
            cu.sex,
            cl.clue_input_type,
            cl.clue_source,
            cl.intention_id,
            cl.intention_des,
            cl.shop_id
            from crm_clue cl 
            join crm_customer cu on cl.customer_id = cu.id 
            join crm_order o on o.clue_id = cl.id
            where $where";



                $sql .= " order by cl.id desc limit $pagination->offset,$pagination->limit";
            }

            $list = Yii::$app->db->createCommand($sql)->queryAll();

            if (!empty($list)) {
                $list = $this->resItsm($list);
            }

            //                $this->dump($list);
            return $this->render('index', ['list' => $list, 'get' => $get, 'pagination' => $pagination]);
        }
    }


    /**
     * 返回的数据处理
     */
    public function resItsm($list)
    {
        $arr = [];

        $arrDict = new DataDictionary();

        //渠道来源
        $input_type = $arrDict->getDictionaryData('input_type');

        //信息来源
        $source = $arrDict->getDictionaryData('source');

        //拟购时间
        $planned_purchase_time = $arrDict->getDictionaryData('planned_purchase_time');

        foreach ($list as $k => $v) {

            array_push($arr, $v['intention_id']);
        }

        //车系，品牌，厂商
        $obj = new \common\logic\CarBrandAndType();
        $c   = $obj->getBrandAndFactoryInfoByTypeId($arr);

        // 获取门店信息
        $os      = new \common\logic\CompanyUserCenter();
        $os_list = $os->getLocalOrganizationalStructure();

        foreach ($list as $k => $v) {

            $list[$k]['brand_name']          = '--';//品牌
            $list[$k]['factory_name']        = '--';//厂商
            $list[$k]['car_brand_type_name'] = '--';//车系

            $list[$k]['clue_input_type_name']       = '--';//渠道来源名字
            $list[$k]['source_name']                = '--';//信息来源名字
            $list[$k]['shop_name']                  = '--';//门店名字
            $list[$k]['planned_purchase_time_name'] = '--';//拟购时间名字

            if (!empty($c)) {

                foreach ($c as $key => $val) {

                    if ($key == $v['intention_id']) {

                        $list[$k]['brand_name']          = $val['brand_name'];
                        $list[$k]['factory_name']        = $val['factory_name'];
                        $list[$k]['car_brand_type_name'] = $val['car_brand_type_name'];
                        break;
                    }
                }

            }

            //拼接渠道来源
            foreach ($input_type as $val) {
                if ($val['id'] == $v['clue_input_type']) {
                    $list[$k]['clue_input_type_name'] = $val['name'];
                    break;
                }
            }

            //拼接信息来源
            foreach ($source as $val) {
                if ($val['id'] == $v['clue_source']) {
                    $list[$k]['source_name'] = $val['name'];
                    break;
                }
            }

            //拼接门店
            foreach ($os_list as $val) {
                if ($val['id'] == $v['shop_id']) {
                    $list[$k]['shop_name'] = $val['name'];
                    break;
                }
            }

            //拼接拟购时间
            foreach ($planned_purchase_time as $val) {
                if ($val['id'] == $v['planned_purchase_time_id']) {
                    $list[$k]['planned_purchase_time_name'] = $val['name'];
                    break;
                }
            }

        }

        return $list;

    }

    /**
     * 导出线索excel
     */
    public function actionExcel1()
    {

        $get = Yii::$app->request->get();

        //$where = ' status = 0';
        //线索(2017.5.18更新需求 按照创建时间 不需要状态status = 0验证)
        list($startDate, $endDate) = explode(' - ', trim($get['addtime']));
        $where = "  FROM_UNIXTIME(create_time, '%Y-%m-%d')  >= '{$startDate}'";
        $where .= " and FROM_UNIXTIME(create_time, '%Y-%m-%d') <=  '{$endDate}'";
        $order = "cl.create_time ";


        $sql1 = "select  
                customer_id,
                customer_name,
                customer_phone,
                planned_purchase_time_id,
                clue_input_type,
                clue_source,
                intention_id,
                intention_des,
                shop_id,
                create_time,
                last_fail_time,
                create_person_name,
                des from crm_clue  where $where";

        $sql2 = "select 
                customer_id,
                customer_name,
                customer_phone,
                planned_purchase_time_id,
                clue_input_type,
                clue_source,
                intention_id,
                intention_des,
                shop_id,
                create_time,
                last_fail_time,
                create_person_name,
                des from crm_clue_wuxiao  where $where";

        $sql = "  select 
                    cl.customer_name,
                    cl.customer_phone,
                    cl.planned_purchase_time_id,
                    cu.sex,
                    cl.clue_input_type,
                    cl.clue_source,
                    cl.intention_id,
                    cl.intention_des,
                    cl.shop_id,
                    cl.create_time,
                    cl.last_fail_time,
                    cl.create_person_name,
                    cl.des from ( {$sql1} union {$sql2}) as cl
                    join crm_customer cu on cl.customer_id = cu.id order by {$order} desc";

        $list = Yii::$app->db->createCommand($sql)->queryAll();
        if (!empty($list)) {
            $list = $this->resItsm($list);
        }

        $arrColumns = ['客户姓名', '手机号码', '性别', '渠道来源', '信息来源',
            '品牌', '厂商', '意向车系', '意向车型', '提车门店', '拟购时间', '线索创建时间', '线索创建人', '说明'];
        $arrModels  = [];
        foreach ($list as $k => $v) {

            $v['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            $v['sex']         = $v['sex'] == 1 ? '男' : '女';
            $arrModels[$k]    = [
                $v['customer_name'],
                $v['customer_phone'],
                $v['sex'],
                $v['clue_input_type_name'],
                $v['source_name'],
                $v['brand_name'],
                $v['factory_name'],
                $v['car_brand_type_name'],
                $v['intention_des'],
                $v['shop_name'],
                $v['planned_purchase_time_name'],
                $v['create_time'],
                $v['create_person_name'],
                $v['des']
            ];

        }
        $this->outPutExcel('线索', $arrColumns, $arrModels);

    }


    /**
     * 导出无效线索excel
     */
    public function actionExcel2()
    {
        $get   = Yii::$app->request->get();
        $where = ' status = 0 and is_fail = 1';
        list($startDate, $endDate) = explode(' - ', trim($get['addtime']));
        $where .= " and FROM_UNIXTIME(cl.create_time, '%Y-%m-%d')  >= '{$startDate}'";
        $where .= " and FROM_UNIXTIME(cl.create_time, '%Y-%m-%d') <=  '{$endDate}'";
        $order = "cl.last_fail_time ";

        $sql = "select 
            cl.id,
            cl.customer_name,
            cl.customer_phone,
            cl.planned_purchase_time_id,
            cu.sex,
            cl.clue_input_type,
            cl.clue_source,
            cl.intention_id,
            cl.intention_des,
            cl.shop_id,
            cl.create_time,
            cl.last_fail_time,
            cl.create_person_name,
            cl.last_fail_time,
            cl.salesman_name,
            cl.fail_reason
            from crm_clue_wuxiao cl join crm_customer cu on cl.customer_id = cu.id where $where
            order by {$order} desc 
            ";

        $list = Yii::$app->db->createCommand($sql)->queryAll();

        if (!empty($list)) {

            $list = $this->resItsm($list);
            $id   = null;
            foreach ($list as $v) {
                $id .= $v['id'] . ',';
            }
            $id = rtrim($id, ',');

            //首次跟进时间  跟进次数
            $sql = "select  min(FROM_UNIXTIME(create_time))create_time,count(clue_id)count,clue_id from 
                (SELECT create_time,clue_id from crm_talk where talk_type in(2,3,5,6,7,8,9,10) 
                and clue_id in($id) order by create_time asc
                ) as tmp GROUP BY clue_id;";

            $talkList = Yii::$app->db->createCommand($sql)->queryAll();

            foreach ($list as $k => $v) {
                if (!empty($talkList)) {
                    foreach ($talkList as $val) {
                        if ($v['id'] == $val['clue_id']) {
                            $list[$k]['shoucigenjin'] = $val['create_time'];
                            $list[$k]['genjincishu']  = $val['count'];
                            break;
                        } else {
                            $list[$k]['shoucigenjin'] = '';
                            $list[$k]['genjincishu']  = '';
                        }
                    }
                } else {
                    $list[$k]['shoucigenjin'] = '';
                    $list[$k]['genjincishu']  = '';
                }
            }

        }


        $arrColumns = ['客户姓名', '手机号码', '性别', '渠道来源', '信息来源',
            '品牌', '厂商', '意向车系', '意向车型', '提车门店', '拟购时间', '线索创建时间', '线索创建人',
            '首次跟进时间', '跟进次数', '无效判定时间', '顾问', '无效类型'];

        $arrModels = [];

        foreach ($list as $k => $v) {

            $v['create_time']    = date('Y-m-d H:i:s', $v['create_time']);
            $v['last_fail_time'] = date('Y-m-d H:i:s', $v['last_fail_time']);
            $v['sex']            = $v['sex'] == 1 ? '男' : '女';
            $arrModels[$k]       = [
                $v['customer_name'],
                $v['customer_phone'],
                $v['sex'],
                $v['clue_input_type_name'],
                $v['source_name'],
                $v['brand_name'],
                $v['factory_name'],
                $v['car_brand_type_name'],
                $v['intention_des'],
                $v['shop_name'],
                $v['planned_purchase_time_name'],
                $v['create_time'],
                $v['create_person_name'],
                $v['shoucigenjin'],
                $v['genjincishu'],
                $v['last_fail_time'],
                $v['salesman_name'],
                $v['fail_reason'],

            ];

        }
        //        $this->dump($arrModels);
        $this->outPutExcel('无效线索', $arrColumns, $arrModels);

    }

    /**
     * 导出意向excel
     */
    public function actionExcel3()
    {
        $get = Yii::$app->request->get();

        //$where = ' status = 1';//意向
        //意向 (2017.5.18更新需求 按照建卡时间 不需要状态status = 1验证)
        list($startDate, $endDate) = explode(' - ', trim($get['addtime']));
        $where = "  FROM_UNIXTIME(cl.create_card_time, '%Y-%m-%d')  >= '{$startDate}'";
        $where .= " and FROM_UNIXTIME(cl.create_card_time, '%Y-%m-%d') <=  '{$endDate}'";
        $order = "cl.create_card_time ";

        $sql = "select 
        cl.id,
        cl.customer_name,
        cl.customer_phone,
        cl.planned_purchase_time_id,
        cu.sex,
        cl.clue_input_type,
        cl.clue_source,
        cl.intention_id,
        cl.intention_des,
        cl.shop_id,
        cl.initial_intention_level,
        FROM_UNIXTIME(cl.create_time)create_time,
        cl.last_fail_time,
        cl.create_person_name,
        cl.intention_level_des,
        cl.salesman_name,
        FROM_UNIXTIME(cl.create_card_time)create_card_time
        from crm_clue cl join crm_customer cu on cl.customer_id = cu.id where $where
        order by {$order} desc 
        ";

        $list = Yii::$app->db->createCommand($sql)->queryAll();
        if (!empty($list)) {
            $list = $this->resItsm($list);

            $id = null;
            foreach ($list as $v) {
                $id .= $v['id'] . ',';
            }
            $id = rtrim($id, ',');

            //查询意向改变信息
            $sql = "select a.clue_id,a.salesman_id,FROM_UNIXTIME(a.create_time) gaibianshijan
                    from crm_talk a join (SELECT clue_id,MAX(create_time) create_time FROM crm_talk 
                    where  is_intention_change = 1 and
                     clue_id in($id) group by clue_id)b on a.clue_id=b.clue_id and a.create_time = b.create_time";


            $talkList = Yii::$app->db->createCommand($sql)->queryAll();
            
            $sql      = "select id,name from crm_user";
            $userList = Yii::$app->db->createCommand($sql)->queryAll();

            foreach ($list as $k => $v) {
                $list[$k]['gaibianshijian'] = '';
                $list[$k]['gaibianren']     = '';
                if (!empty($talkList)) {
                    //拼接意向改变
                    foreach ($talkList as $val) {
                        if ($v['id'] == $val['clue_id']) {
                            $list[$k]['gaibianshijian'] = $val['gaibianshijan'];
                            foreach ($userList as $user) {
                                if ($user['id'] == $val['salesman_id']) {
                                    $list[$k]['gaibianren'] = $user['name'];
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }


        $arrColumns = ['客户姓名', '手机号码', '性别', '渠道来源', '信息来源',
            '品牌', '厂商', '意向车系', '意向车型', '提车门店', '拟购时间', '线索创建时间', '线索创建人',
            '意向首次等级', '意向首次评级人', '意向首次评级时间', '意向最后等级', '意向最后评级人', '意向最后评级时间'];

        $arrModels = [];
        foreach ($list as $k => $v) {

            $v['sex']      = $v['sex'] == 1 ? '男' : '女';
            $arrModels[$k] = [
                $v['customer_name'],
                $v['customer_phone'],
                $v['sex'],
                $v['clue_input_type_name'],
                $v['source_name'],
                $v['brand_name'],
                $v['factory_name'],
                $v['car_brand_type_name'],
                $v['intention_des'],
                $v['shop_name'],
                $v['planned_purchase_time_name'],
                $v['create_time'],
                $v['create_person_name'],
                $v['initial_intention_level'],
                $v['salesman_name'],
                $v['create_card_time'],
                $v['intention_level_des'],
                $v['gaibianren'],
                $v['gaibianshijian']
            ];

        }

        $this->outPutExcel('意向', $arrColumns, $arrModels);

    }

    /**
     * 导出战败excel
     */
    public function actionExcel4()
    {

        $get   = Yii::$app->request->get();
        $where = ' is_fail = 1';//战败

        list($startDate, $endDate) = explode(' - ', trim($get['addtime']));
        $where .= " and FROM_UNIXTIME(cl.last_fail_time, '%Y-%m-%d')  >= '{$startDate}'";
        $where .= " and FROM_UNIXTIME(cl.last_fail_time, '%Y-%m-%d') <=  '{$endDate}'";
        $order = "cl.last_fail_time ";

        $sql = "select 
            cl.id,
            cl.customer_name,
            cl.customer_phone,
            cl.planned_purchase_time_id,
            cu.sex,
            cl.clue_input_type,
            cl.clue_source,
            cl.intention_id,
            cl.intention_des,
            cl.shop_id,
            cl.create_time,
            cl.last_fail_time,
            cl.create_person_name,
            cl.last_fail_time,
            cl.salesman_name,
            cl.fail_reason,
            cl.fail_tags
            from crm_clue cl join crm_customer cu on cl.customer_id = cu.id where $where
            order by {$order} desc 
            ";

        $list = Yii::$app->db->createCommand($sql)->queryAll();

        if (!empty($list)) {

            $list = $this->resItsm($list);
            $id   = null;
            foreach ($list as $v) {
                $id .= $v['id'] . ',';
            }
            $id = rtrim($id, ',');


            //首次跟进时间  跟进次数
            $sql = "select  min(FROM_UNIXTIME(create_time))create_time,count(clue_id)count,clue_id from 
                (SELECT create_time,clue_id from crm_talk where talk_type in(2,3,5,6,7,8,9,10) 
                and clue_id in($id) order by create_time asc
                ) as tmp GROUP BY clue_id;";

            $talkList = Yii::$app->db->createCommand($sql)->queryAll();

            //战败标签
            $sql_fail_tags = "select id,name from crm_dd_fail_tags";
            $fail_tags     = Yii::$app->db->createCommand($sql_fail_tags)->queryAll();


            foreach ($list as $k => $v) {

                $list[$k]['fail_tags_name'] = null;

                //拼接跟进时间 次数
                if (!empty($talkList)) {
                    foreach ($talkList as $val) {
                        if ($v['id'] == $val['clue_id']) {
                            $list[$k]['shoucigenjin'] = $val['create_time'];
                            $list[$k]['genjincishu']  = $val['count'];
                            break;
                        } else {
                            $list[$k]['shoucigenjin'] = '';
                            $list[$k]['genjincishu']  = '';
                        }
                    }
                } else {
                    $list[$k]['shoucigenjin'] = '';
                    $list[$k]['genjincishu']  = '';
                }

                //拼接战败标签
                if (!empty($fail_tags)) {

                    if (!empty($v['fail_tags'])) {
                        $tags = explode(',', $v['fail_tags']);

                        foreach ($fail_tags as $val) {
                            if (in_array($val['id'], $tags)) {
                                $list[$k]['fail_tags_name'] .= $val['name'] . ',';
                            }
                        }
                    } else {
                        $list[$k]['fail_tags_name'] = '';
                    }
                } else {
                    $list[$k]['fail_tags_name'] = '';
                }
            }

        }


        $arrColumns = ['客户姓名', '手机号码', '性别', '渠道来源', '信息来源',
            '品牌', '厂商', '意向车系', '意向车型', '提车门店', '拟购时间', '线索创建时间', '线索创建人',
            '首次跟进时间', '跟进次数', '顾问', '战败时间', '战败类型', '战败说明'];

        $arrModels = [];

        foreach ($list as $k => $v) {

            $v['create_time']    = date('Y-m-d H:i:s', $v['create_time']);
            $v['last_fail_time'] = date('Y-m-d H:i:s', $v['last_fail_time']);
            $v['sex']            = $v['sex'] == 1 ? '男' : '女';
            $arrModels[$k]       = [
                $v['customer_name'],
                $v['customer_phone'],
                $v['sex'],
                $v['clue_input_type_name'],
                $v['source_name'],
                $v['brand_name'],
                $v['factory_name'],
                $v['car_brand_type_name'],
                $v['intention_des'],
                $v['shop_name'],
                $v['planned_purchase_time_name'],
                $v['create_time'],
                $v['create_person_name'],
                $v['shoucigenjin'],
                $v['genjincishu'],
                $v['salesman_name'],
                $v['last_fail_time'],
                $v['fail_tags_name'],
                $v['fail_reason']

            ];

        }

        $this->outPutExcel('战败', $arrColumns, $arrModels);


    }

    /**
     * 导出已到店excel
     */
    public function actionExcel5()
    {
        $get   = Yii::$app->request->get();
        $where = 't.talk_type in(5,6,7)';

        list($startDate, $endDate) = explode(' - ', trim($get['addtime']));
        $where .= " and talk_date  >= '{$startDate}'";
        $where .= " and talk_date <=  '{$endDate}'";
        $order = " talk_date ";

        $sql = "
            select   
          create_time,
           shop_id,
           id,
           customer_name,
           customer_phone,
           planned_purchase_time_id,
           sex,
           clue_input_type,
           clue_source,
           intention_id,
           intention_des,
           create_person_name,
           salesman_name,
           intention_level_des,
           clue_id,
           talk_date
            from
            (SELECT  t.create_time,
                    t.shop_id,
                    cl.id,
                    cl.customer_name,
                    cl.customer_phone,
                    cl.planned_purchase_time_id,
                    cu.sex,
                    cl.clue_input_type,
                    cl.clue_source,
                    cl.intention_id,
                    cl.intention_des,
                    cl.create_person_name,
                    cl.salesman_name,
                    cl.intention_level_des,
                    t.clue_id,
                    t.talk_date
                 from crm_clue cl 
                join crm_customer cu on cl.customer_id = cu.id 
                join crm_talk t on t.clue_id = cl.id 
                where $where
            order by t.talk_date desc
            ) as tmp 
            group by  clue_id order by {$order} desc
            ";


        $list = Yii::$app->db->createCommand($sql)->queryAll();

        if (!empty($list)) {

            $list = $this->resItsm($list);
            $id   = null;
            foreach ($list as $v) {
                $id .= $v['id'] . ',';
            }
            $id = rtrim($id, ',');


            //首次跟进时间  跟进次数
            $sql = "select min(FROM_UNIXTIME(create_time))create_time,count(clue_id)count,clue_id from 
                (SELECT create_time,clue_id from crm_talk where talk_type in(2,3,5,6,7,8,9,10) 
                and clue_id in($id) order by create_time asc
                ) as tmp GROUP BY clue_id;";

            $talkShouciList = Yii::$app->db->createCommand($sql)->queryAll();

            //最后跟进时间
            $sql             = "select max(FROM_UNIXTIME(create_time))create_time,clue_id from 
                (SELECT create_time,clue_id from crm_talk where talk_type in(2,3,5,6,7,8,9,10) 
                and clue_id in($id) order by create_time desc
                ) as tmp GROUP BY clue_id;";
            $talkZhuiHouList = Yii::$app->db->createCommand($sql)->queryAll();

            //邀约到店时间
            $sql = "select max(task_date)task_date,clue_id from 
                (SELECT task_date,clue_id from crm_task where clue_id in($id) order by task_date desc
                ) as tmp GROUP BY clue_id;";

            $yaoyue = Yii::$app->db->createCommand($sql)->queryAll();

            foreach ($list as $k => $v) {
                $list[$k]['shoucigenjin'] = '';
                $list[$k]['genjincishu']  = '';
                $list[$k]['zuihougenjin'] = '';
                $list[$k]['yaoyue']       = '';

                //拼接跟进时间 次数
                if (!empty($talkShouciList)) {
                    foreach ($talkShouciList as $val) {
                        if ($v['id'] == $val['clue_id']) {
                            $list[$k]['shoucigenjin'] = $val['create_time'];
                            $list[$k]['genjincishu']  = $val['count'];
                            break;
                        }
                    }
                }

                //拼接最后跟进时间
                if (!empty($talkZhuiHouList)) {
                    foreach ($talkZhuiHouList as $val) {
                        if ($v['id'] == $val['clue_id']) {
                            $list[$k]['zuihougenjin'] = $val['create_time'];
                            break;
                        }
                    }
                }

                //拼接邀约到点时间
                if (!empty($yaoyue)) {
                    foreach ($yaoyue as $val) {
                        if ($v['id'] == $val['clue_id'] && $list[$k]['zuihougenjin'] > $val['task_date']) {
                            $list[$k]['yaoyue'] = $val['task_date'];
                            break;
                        }
                    }
                }

            }
        }

        $arrColumns = ['客户姓名', '手机号码', '性别', '渠道来源', '信息来源', '品牌', '厂商', '意向车系',
            '意向车型', '提车门店', '拟购时间', '线索创建时间', '线索创建人',
            '顾问', '首次跟进时间', '最近一次跟进时间', '意向级别', '跟进次数', '邀约到店时间', '预约店面', '实际到店时间'];
        $arrModels  = [];

        foreach ($list as $k => $v) {

            $v['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            $v['sex']         = $v['sex'] == 1 ? '男' : '女';
            $arrModels[$k]    = [
                $v['customer_name'],
                $v['customer_phone'],
                $v['sex'],
                $v['clue_input_type_name'],
                $v['source_name'],
                $v['brand_name'],
                $v['factory_name'],
                $v['car_brand_type_name'],
                $v['intention_des'],
                $v['shop_name'],
                $v['planned_purchase_time_name'],
                $v['create_time'],
                $v['create_person_name'],
                $v['salesman_name'],
                $v['shoucigenjin'],
                $v['zuihougenjin'],
                $v['intention_level_des'],
                $v['genjincishu'],
                $v['yaoyue'],
                $v['shop_name'],
                $v['create_time']
            ];

        }

        $this->outPutExcel('已到店', $arrColumns, $arrModels);


    }

    /**
     * 导出已签单excel
     */
    public function actionExcel6()
    {
        $get = Yii::$app->request->get();

        $where = 'o.cai_wu_dao_zhang_time > 0';
        list($startDate, $endDate) = explode(' - ', trim($get['addtime']));
        $where .= " and FROM_UNIXTIME(o.cai_wu_dao_zhang_time, '%Y-%m-%d')  >= '{$startDate}'";
        $where .= " and FROM_UNIXTIME(o.cai_wu_dao_zhang_time, '%Y-%m-%d') <=  '{$endDate}'";
        $order = "o.cai_wu_dao_zhang_time ";

        $sql  = "select
            o.create_time as qiandanshijan,
            o.car_type_id,
            o.car_type_name,
            o.delivery_price,
            t.create_time,
            cl.id,
            cl.customer_name,
            cl.customer_phone,
            cl.planned_purchase_time_id,
            cu.sex,
            cl.clue_input_type,
            cl.clue_source,
            cl.intention_id,
            cl.intention_des,
            cl.shop_id,
            cl.create_person_name,
            cl.salesman_name,
            cl.intention_level_des
            from crm_clue cl 
            join crm_customer cu on cl.customer_id = cu.id 
            join crm_order o on o.clue_id = cl.id
            left join crm_talk t on t.clue_id = cl.id
            where $where and not EXISTS(
            select null from crm_talk t1 where t.clue_id = t1.clue_id and t1.create_time>t.create_time)
            order by {$order} desc
            ";
        $list = Yii::$app->db->createCommand($sql)->queryAll();

        if (!empty($list)) {


            $list = $this->resItsm($list);

            $id = null;
            foreach ($list as $v) {
                $id .= $v['id'] . ',';
            }
            $id = rtrim($id, ',');


            //首次跟进时间  跟进次数
            $sql = "select min(FROM_UNIXTIME(create_time))create_time,count(clue_id)count,clue_id from 
                (SELECT create_time,clue_id from crm_talk where talk_type in(2,3,5,6,7,8,9,10) 
                and clue_id in($id) order by create_time asc
                ) as tmp GROUP BY clue_id;";

            $talkShouciList = Yii::$app->db->createCommand($sql)->queryAll();

            //最后跟进时间
            $sql             = "select max(FROM_UNIXTIME(create_time))create_time,clue_id from 
                (SELECT create_time,clue_id from crm_talk where talk_type in(2,3,5,6,7,8,9,10) 
                and clue_id in($id) order by create_time desc
                ) as tmp GROUP BY clue_id;";
            $talkZhuiHouList = Yii::$app->db->createCommand($sql)->queryAll();

            //邀约到店时间
            $sql = "select max(task_date)task_date,clue_id from 
                (SELECT task_date,clue_id from crm_task where clue_id in($id) order by task_date desc
                ) as tmp GROUP BY clue_id;";

            $yaoyue = Yii::$app->db->createCommand($sql)->queryAll();

            //车系，品牌，厂商
            $obj = new \common\logic\CarBrandAndType();

            foreach ($list as $k => $v) {
                $list[$k]['zhidaojia']                 = null;
                $list[$k]['shoucigenjin']              = '';
                $list[$k]['genjincishu']               = '';
                $list[$k]['zuihougenjin']              = '';
                $list[$k]['yaoyue']                    = '';
                $list[$k]['order_brand_name']          = '';//品牌
                $list[$k]['order_factory_name']        = '';//厂商
                $list[$k]['order_car_brand_type_name'] = '';//车系

                //拼接跟进时间 次数
                if (!empty($talkShouciList)) {
                    foreach ($talkShouciList as $val) {
                        if ($v['id'] == $val['clue_id']) {
                            $list[$k]['shoucigenjin'] = $val['create_time'];
                            $list[$k]['genjincishu']  = $val['count'];
                            break;
                        }
                    }
                }

                //拼接最后跟进时间
                if (!empty($talkZhuiHouList)) {
                    foreach ($talkZhuiHouList as $val) {
                        if ($v['id'] == $val['clue_id']) {
                            $list[$k]['zuihougenjin'] = $val['create_time'];
                            break;
                        }
                    }
                }

                //拼接邀约到点时间
                if (!empty($yaoyue)) {
                    foreach ($yaoyue as $val) {
                        if ($v['id'] == $val['clue_id'] && $list[$k]['zuihougenjin'] > $val['task_date']) {
                            $list[$k]['yaoyue'] = $val['task_date'];
                            break;
                        }
                    }
                }

                //订单车系，品牌，厂商
                $arr = [];
                array_push($arr, $v['car_type_id']);


                $c = $obj->getBrandAndFactoryInfoByTypeId($arr);
                //拼接订单车系，品牌，厂商
                if (!empty($c)) {

                    foreach ($c as $key => $val) {

                        if ($key == $v['car_type_id']) {

                            $list[$k]['order_brand_name']          = $val['brand_name'];
                            $list[$k]['order_factory_name']        = $val['factory_name'];
                            $list[$k]['order_car_brand_type_name'] = $val['car_brand_type_name'];
                            break;

                        }
                    }

                }

            }
        }

        $arrColumns = ['客户姓名', '手机号码', '性别', '渠道来源', '信息来源', '品牌',
            '厂商', '意向车系', '意向车型', '提车门店', '拟购时间', '线索创建时间', '线索创建人',
            '顾问', '首次跟进时间', '最近一次跟进时间',
            '意向级别', '跟进次数', '邀约到店时间', '预约店面', '实际到店时间',
            '签单时间', '订单品牌', '订单车型厂商', '订单车系', '订单车型', '指导价', '付款金额'];
        $arrModels  = [];

        foreach ($list as $k => $v) {
            $v['qiandanshijan'] = empty($v['qiandanshijan']) ? '' : date('Y-m-d H:i:s', $v['qiandanshijan']);
            $v['create_time']   = empty($v['create_time']) ? '' : date('Y-m-d H:i:s', $v['create_time']);
            $v['sex']           = $v['sex'] == 1 ? '男' : '女';
            $arrModels[$k]      = [
                $v['customer_name'],
                $v['customer_phone'],
                $v['sex'],
                $v['clue_input_type_name'],
                $v['source_name'],
                $v['brand_name'],
                $v['factory_name'],
                $v['car_brand_type_name'],
                $v['intention_des'],
                $v['shop_name'],
                $v['planned_purchase_time_name'],
                $v['create_time'],
                $v['create_person_name'],
                $v['salesman_name'],
                $v['shoucigenjin'],
                $v['zuihougenjin'],
                $v['intention_level_des'],
                $v['genjincishu'],
                $v['yaoyue'],
                $v['shop_name'],
                $v['create_time'],
                $v['qiandanshijan'],
                $v['order_brand_name'],
                $v['order_factory_name'],
                $v['order_car_brand_type_name'],
                $v['car_type_name'],
                $v['zhidaojia'],
                $v['delivery_price'],
            ];

        }
        //$this->dump($arrModels);
        $this->outPutExcel('已订车', $arrColumns, $arrModels);


    }


}
