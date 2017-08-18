<?php

namespace backend\controllers;

use common\common\PublicMethod;
use common\logic\CompanyUserCenter;
use common\logic\DataDictionary;
use common\models\Clue;
use common\models\ClueWuxiao;
use common\models\InputType;
use common\models\Order;
use common\models\OrganizationalStructure;
use common\models\TjDingcheDateCount;
use common\models\TjDingcheDateInputtype;
use common\models\TjFailIntentionTagCount;
use common\models\TjInputtypeclueAll;
use common\models\TjInputtypeclueFail;
use common\models\TjInputtypeclueZhuanhua;
use common\models\TjIntentionGenjinzhong;
use common\models\TjIntentionLevelCount;
use common\models\TjZhuanhualoudou;
use common\models\User;
use Yii;
use common\models\TjJichushuju;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\logic\CustomerAnalysisLogic;
use common\logic\BaseLogic;
use common\logic\TongJiLogic;

/**
 * ConversionFunnelController implements the CRUD actions for TjJichushuju model.
 */
class CustomerAnalysisController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * 客户分析-线索
     * Lists all TjJichushuju models.
     * @return mixed
     */
    public function actionClue()
    {
        //权限控制 - 所有
        $this->checkPermission('/customer-analysis/clue');

        //获取当前用户session 及组织架构id
        $session = Yii::$app->getSession();
        //查询该用户所能查看的组织架构目录
        $info_owner_id = 6;

        //店长层级默认不展示区域门店选择框
        $info_owner_display = 'block';
        if($session['userinfo']['role_level'] == 30){
            $info_owner_display = 'none';
        }

        //默认搜索时间
        $start_date = date('Y-m-d',strtotime("-1 month + 1 day"));
        $end_date = date('Y-m-d');

        $search_time = "{$start_date} - {$end_date}";

        $info_owner_name = OrganizationalStructure::find()->where(['=','id',$info_owner_id])->one()->name;
        $data_common['data_update_time'] = PublicMethod::data_update_time(1);
        $data_common['search_time'] = $search_time;
        $data_common['info_owner_id'] = $info_owner_id;
        $data_common['info_owner_name'] = $info_owner_name;
        $data_common['info_owner_display'] = $info_owner_display;

        $objSelectDataLogic = new \common\logic\JsSelectDataLogic();
        $arrSelectorgList = $objSelectDataLogic->getSelectOrgNew($session['userinfo']['permisson_org_ids'], $session['userinfo']['role_level'], true);
        //渲染页面
        return $this->render('clue',[
                'data_common'=>$data_common,
                'selectOrgJson' => json_encode($arrSelectorgList),
            ]);

    }

    /**
     * ajax获取线索数据
     */
    public function actionGetClueData()
    {
        //接收参数
        $info_owner = Yii::$app->request->post('info_owner_id');
        $search_time = Yii::$app->request->post('search_time');

        //获取session
        $session = Yii::$app->getSession();

        //判断当前所查询数据所有者
        $objTongJiLogic = new TongJiLogic();
        if($info_owner){
            $info_owner_arr = explode(',',$info_owner);
            $info_owner_id = array_pop($info_owner_arr);
            if($info_owner_id == -1)
            {
                $info_owner_id = array_pop($info_owner_arr);
            }
        }
        else
        {
            $info_owner_id = 6;
        }
        $arrWhereAndGroup = $objTongJiLogic->getSelectFieldByLevelAndOrgId($info_owner_id, $session['userinfo']['permisson_org_ids']);

        //查询时间
        if(empty($search_time)){
            $start_date =  date('Y-m-d', strtotime('-1 month'));
            $end_date = date('Y-m-d');
        }else{
            $date_arr = explode(' - ',$search_time);
            $start_date = $date_arr[0];
            $end_date = $date_arr[1];
        }

        //查询时间  时间戳
        $start_time = strtotime($start_date);
        $end_time = strtotime($end_date) + (24*3600) - 1;

        //根据$info_owner_id查询当前所属层级  及对应user表字段名称
        $org_info = OrganizationalStructure::findOne($info_owner_id);
        $level = $org_info->level;
        if($level == 10){
            $this_search_str = '1 = 1';
            $next_level_field = 'ot.company_id';
        }elseif ($level == 15){
            $this_search_str = 'ot.company_id = '.$info_owner_id;
            $next_level_field = 'ot.area_id';
        }elseif ($level == 20){
            $this_search_str = 'ot.area_id = '.$info_owner_id;
            $next_level_field = 'ot.shop_id';
        }elseif ($level == 30){
            $this_search_str = 'ot.shop_id = '.$info_owner_id;
            $next_level_field = 'nc.salesman_id';
        }

        //处理渠道来源列表
        $input_type = InputType::find()->asArray()->all();
        $input_type_new = [];
        foreach ($input_type as $value){
            $input_type_new[$value['id']] = $value;
        }

        //获取下一层级信息
        $baselogic = new BaseLogic();
        $organizational_info = $baselogic->getOrganizationalInfoAndChild($info_owner_id);
        $child_list = $organizational_info['child_list'];

        //顾问已被删除时  在统计表中存储为顾问id=0 的数据
        $this_level_field = $organizational_info['this_level_field'];
        if($this_level_field == 'shop_id'){
            $child_list[0] = [
                'id' => '0',
                'name' => '无顾问'
            ];
        }

        //查询所有未被删除的 门店  公司大区被删除  门店会同时被删除
        $org_list = OrganizationalStructure::find()->select('id')
            ->where(['=','level',30])
            ->andWhere(['=','is_delete',0])
            ->andWhere(['in','id',$session['userinfo']['permisson_org_ids']])
            ->asArray()->all();
        //取出shop_idlist
        $org_id_arr = array_column($org_list,'id');
        $org_id_str = implode(',',$org_id_arr);

        //将组织架构生成零时表的sql  shop_id,area_id,company_id
        $orgTmpSQL = '
                SELECT 
                    t.shop_id, t.area_id, t1.company_id 
                FROM 
                    ( SELECT id AS shop_id, pid AS area_id FROM crm_organizational_structure WHERE `level` = 30 AND is_delete = 0 ) AS t 
                LEFT JOIN 
                    ( SELECT id AS area_id, pid AS company_id FROM crm_organizational_structure WHERE `level` = 20 ) AS t1 
                ON t.area_id = t1.area_id
            ';

        //获取 全部线索
        //新增线索查询sql clue表加clue_wuxiao表
        $select = 'id,clue_input_type,shop_id,salesman_id';
        $newClueSQL = "
                SELECT {$select} FROM crm_clue WHERE create_time > {$start_time} AND create_time < {$end_time} AND shop_id IN ($org_id_str)
            UNION 
                SELECT {$select} FROM crm_clue_wuxiao WHERE create_time > {$start_time} AND create_time < {$end_time} AND shop_id IN ($org_id_str)
            ";

        //按照渠道分组
        //新增线索表的零时表连组织架构的零时表  按照组织id聚合
        $sql_all = "SELECT nc.clue_input_type,count(*) as sum FROM ({$newClueSQL}) as nc LEFT JOIN  ({$orgTmpSQL}) as ot 
                on nc.shop_id=ot.shop_id where $this_search_str GROUP BY nc.clue_input_type";

        $sql_all_child = "SELECT $next_level_field as info_owner_id,count(*) as sum FROM ({$newClueSQL}) as nc LEFT JOIN  ({$orgTmpSQL}) as ot 
                on nc.shop_id=ot.shop_id where $this_search_str GROUP BY $next_level_field";
        
        $new_clue_list = Yii::$app->db->createCommand($sql_all)->queryAll();;

        $clue_info_all_list = Yii::$app->db->createCommand($sql_all_child)->queryAll();;

        //处理全部线索数据
        $clue_info_all = array();
        foreach ($new_clue_list as $key=>$value){
            $info = [];
            //此种情况几乎不出现   数据错误时可能出现
            if($value['clue_input_type'] == 0){
                continue;
            }
            $info['value'] = (int)$value['sum'];
            $info['name'] = $input_type_new[$value['clue_input_type']]['name'];
            $clue_info_all[] = $info;
        }

        //下一级新增线索数据
        $clue_info_all_new = [];
        foreach ($clue_info_all_list as $item){
            $clue_info_all_new[$item['info_owner_id']] = $item;
        }
        
        //获取 未分配线索
        //新增线索查询sql clue表加clue_wuxiao表
        $select = 'id,clue_input_type,shop_id,salesman_id';
        $newClueSQL = "
                SELECT {$select} FROM crm_clue WHERE create_time > {$start_time} AND create_time < {$end_time} AND shop_id IN ($org_id_str) AND is_assign = 0
            ";

        //按照渠道分组
        //新增线索表的零时表连组织架构的零时表  按照组织id聚合

        $sql_unassign_child = "SELECT $next_level_field as info_owner_id,count(*) as sum FROM ({$newClueSQL}) as nc LEFT JOIN  ({$orgTmpSQL}) as ot 
                on nc.shop_id=ot.shop_id where $this_search_str GROUP BY $next_level_field";

        $clue_info_unassign_list = Yii::$app->db->createCommand($sql_unassign_child)->queryAll();;

        //下一级未分配线索数据
        $clue_info_unassign_new = [];
        foreach ($clue_info_unassign_list as $item){
            $clue_info_unassign_new[$item['info_owner_id']] = $item;
        }



        //获取 转化线索
        //新增线索查询sql clue表加clue_wuxiao表
        $select = 'id,clue_input_type,shop_id,salesman_id';
        $newClueSQL = "
                SELECT {$select} FROM crm_clue WHERE create_time > {$start_time} AND create_time < {$end_time} AND 
                create_card_time > {$start_time} AND create_card_time < {$end_time} AND shop_id IN ($org_id_str)
            ";

        //按照渠道分组
        //新增线索表的零时表连组织架构的零时表  按照组织id聚合
        $sql_zhuanhua = "SELECT nc.clue_input_type,count(*) as sum FROM ({$newClueSQL}) as nc LEFT JOIN  ({$orgTmpSQL}) as ot 
                on nc.shop_id=ot.shop_id where $this_search_str GROUP BY nc.clue_input_type";

        $sql_zhuanhua_child = "SELECT $next_level_field as info_owner_id,count(*) as sum FROM ({$newClueSQL}) as nc LEFT JOIN  ({$orgTmpSQL}) as ot 
                on nc.shop_id=ot.shop_id where $this_search_str GROUP BY $next_level_field";

        $zhuanhua_clue_list = Yii::$app->db->createCommand($sql_zhuanhua)->queryAll();;

        $clue_info_zhuanhua_list = Yii::$app->db->createCommand($sql_zhuanhua_child)->queryAll();;


        //处理无效线索数据
        $clue_info_zhuanhua = [];
        foreach ($zhuanhua_clue_list as $key=>$value){
            $info = [];
            //此种情况几乎不出现   数据错误时可能出现
            if($value['clue_input_type'] == 0){
                continue;
            }
            $info['value'] = (int)$value['sum'];
            $info['name'] = $input_type_new[$value['clue_input_type']]['name'];
            $clue_info_zhuanhua[] = $info;
        }


        //子级转化线索
        $clue_info_zhuanhua_new = [];
        foreach ($clue_info_zhuanhua_list as $item){
            $clue_info_zhuanhua_new[$item['info_owner_id']] = $item;
        }


        //获取 无效线索
        //新增线索查询sql clue表加clue_wuxiao表
        $select = 'id,clue_input_type,shop_id,salesman_id';
        $newClueSQL = "
                SELECT {$select} FROM crm_clue_wuxiao WHERE create_time > {$start_time} AND create_time < {$end_time} AND 
                last_fail_time > {$start_time} AND last_fail_time < {$end_time} AND shop_id IN ($org_id_str)
            ";

        //按照渠道分组
        //新增线索表的零时表连组织架构的零时表  按照组织id聚合
        $sql_fail = "SELECT nc.clue_input_type,count(*) as sum FROM ({$newClueSQL}) as nc LEFT JOIN  ({$orgTmpSQL}) as ot 
                on nc.shop_id=ot.shop_id where $this_search_str GROUP BY nc.clue_input_type";

        $sql_fail_child = "SELECT $next_level_field as info_owner_id,count(*) as sum FROM ({$newClueSQL}) as nc LEFT JOIN  ({$orgTmpSQL}) as ot 
                on nc.shop_id=ot.shop_id where $this_search_str GROUP BY $next_level_field";

        $fail_clue_list = Yii::$app->db->createCommand($sql_fail)->queryAll();;

        $clue_info_fail_list = Yii::$app->db->createCommand($sql_fail_child)->queryAll();;

        //处理无效线索数据
        $clue_info_fail = [];
        foreach ($fail_clue_list as $key=>$value){
            $info = [];
            //此种情况几乎不出现   数据错误时可能出现
            if($value['clue_input_type'] == 0){
                continue;
            }
            $info['value'] = (int)$value['sum'];
            $info['name'] = $input_type_new[$value['clue_input_type']]['name'];
            $clue_info_fail[] = $info;
        }

        //子级无效线索数据
        $clue_info_fail_new = [];
        foreach ($clue_info_fail_list as $item){
            $clue_info_fail_new[$item['info_owner_id']] = $item;
        }



        //处理子级数据  如果为空默认为0
        $table_list = array();
        //计算总计栏数据
        $num_all = 0;
        $num_unassign = 0;
        $num_fail = 0;
        $num_zhuanhua = 0;
        $num_genjinzhong = 0;

        foreach ($arrWhereAndGroup['nextList'] as $key=>$value){
            $info = array();
            $info['num_all'] = empty($clue_info_all_new[$value['id']]['sum']) ? '0' : $clue_info_all_new[$value['id']]['sum'];
            $info['num_unassign'] = empty($clue_info_unassign_new[$value['id']]['sum']) ? '0' : $clue_info_unassign_new[$value['id']]['sum'];
            $info['num_fail'] = empty($clue_info_fail_new[$value['id']]['sum']) ? '0' : $clue_info_fail_new[$value['id']]['sum'];
            $info['num_zhuanhua'] = empty($clue_info_zhuanhua_new[$value['id']]['sum']) ? '0' : $clue_info_zhuanhua_new[$value['id']]['sum'];
            $info['num_genjinzhong'] = $info['num_all'] - $info['num_fail'] - $info['num_zhuanhua'] - $info['num_unassign'];
            $info['rate'] = @round($info['num_zhuanhua']*100/$info['num_all']);
            $info['info_owner_id'] = $value['id'];
            $info = array_merge($info,$value);

            $num_all += $info['num_all'];
            $num_unassign += $info['num_unassign'];
            $num_fail += $info['num_fail'];
            $num_zhuanhua += $info['num_zhuanhua'];
            $num_genjinzhong += $info['num_genjinzhong'];
            $table_list[] = $info;
        }

        //总计栏数据
        $table_list_sum['num_all'] = $num_all;
        $table_list_sum['num_unassign'] = $num_unassign;
        $table_list_sum['num_fail'] = $num_fail;
        $table_list_sum['num_zhuanhua'] = $num_zhuanhua;
        $table_list_sum['num_genjinzhong'] = $num_genjinzhong;
        $table_list_sum['rate'] = @round($num_zhuanhua*100/$num_all);


        if(empty($clue_info_all)){
            $clue_info_all[] = ['value'=>0,'name'=>'无数据'];
        }
        if(empty($clue_info_fail)){
            $clue_info_fail[] = ['value'=>0,'name'=>'无数据'];
        }
        if(empty($clue_info_zhuanhua)){
            $clue_info_zhuanhua[] = ['value'=>0,'name'=>'无数据'];
        }
        //返回数据
        $data = [
//            'clue_source_list' => $clue_source_list,
            'clue_info_all' => $clue_info_all,
            'clue_info_fail' => $clue_info_fail,
            'clue_info_zhuanhua' => $clue_info_zhuanhua,
            'table_list' => $table_list,
            'table_list_sum' => $table_list_sum,
        ];
        echo json_encode($data);
    }

    /**
     * 渠道有效率
     */
    public function actionGetClueRate()
    {
        //接收参数
        $info_owner = Yii::$app->request->post('info_owner_id');
        $search_time = Yii::$app->request->post('search_time');

        //获取信息所属id
        $objTongJiLogic = new TongJiLogic();
        $session = Yii::$app->getSession();
        if($info_owner){
            $info_owner_arr = explode(',',$info_owner);
            $info_owner_id = array_pop($info_owner_arr);
            if($info_owner_id == -1)
            {
                $info_owner_id = array_pop($info_owner_arr);
            }
        }else{
            $info_owner_id = 6;
        }
        $arrWhereAndGroup = $objTongJiLogic->getSelectFieldByLevelAndOrgId($info_owner_id, $session['userinfo']['permisson_org_ids']);

        //默认查询时间
        if(empty($search_time)){
            $start_date = date('Y-m-d',strtotime("-1 month"));
            $end_date = date('Y-m-d');
        }else{
            $date_arr = explode(' - ',$search_time);
            $start_date = $date_arr[0];
            $end_date = $date_arr[1];
        }

        //查询时间  时间戳
        $start_time = strtotime($start_date);
        $end_time = strtotime($end_date) + (24*3600) - 1;

        //获取渠道列表 并处理数组
        $input_type = InputType::find()->where(['=','status',1])->asArray()->all();
        $input_type_new = array();
        foreach ($input_type as $value){
            $input_type_new[$value['id']] = $value;
        }


        //根据$info_owner_id查询当前所属层级  及对应user表字段名称
        $org_info = OrganizationalStructure::findOne($info_owner_id);
        $level = $org_info->level;
        if($level == 10){
            $this_search_str = '1 = 1';
        }elseif ($level == 15){
            $this_search_str = 'ot.company_id = '.$info_owner_id;
        }elseif ($level == 20){
            $this_search_str = 'ot.area_id = '.$info_owner_id;
        }elseif ($level == 30){
            $this_search_str = 'ot.shop_id = '.$info_owner_id;
        }

        //处理渠道来源列表
        $input_type = InputType::find()->asArray()->all();
        $input_type_new = [];
        foreach ($input_type as $value){
            $input_type_new[$value['id']] = $value;
        }
        //查询所有未被删除的 门店  公司大区被删除  门店会同时被删除
        $org_list = OrganizationalStructure::find()->select('id')
            ->where(['=','level',30])
            ->andWhere(['=','is_delete',0])
            ->andWhere(['in','id', $session['userinfo']['permisson_org_ids']])
            ->asArray()->all();
        //取出shop_idlist
        $org_id_arr = array_column($org_list,'id');
        $org_id_str = implode(',',$org_id_arr);

        //将组织架构生成零时表的sql  shop_id,area_id,company_id
        $orgTmpSQL = '
                SELECT 
                    t.shop_id, t.area_id, t1.company_id 
                FROM 
                    ( SELECT id AS shop_id, pid AS area_id FROM crm_organizational_structure WHERE `level` = 30 AND is_delete = 0 ) AS t 
                LEFT JOIN 
                    ( SELECT id AS area_id, pid AS company_id FROM crm_organizational_structure WHERE `level` = 20 ) AS t1 
                ON t.area_id = t1.area_id
            ';

        //获取 全部线索
        //新增线索查询sql clue表加clue_wuxiao表
        $select = 'id,clue_input_type,shop_id,salesman_id';
        $newClueSQL = "
                SELECT {$select} FROM crm_clue WHERE create_time > {$start_time} AND create_time < {$end_time} AND shop_id IN ($org_id_str)
            UNION 
                SELECT {$select} FROM crm_clue_wuxiao WHERE create_time > {$start_time} AND create_time < {$end_time} AND shop_id IN ($org_id_str)
            ";

        //按照渠道分组
        //新增线索表的零时表连组织架构的零时表  按照组织id聚合
        $sql_all = "SELECT nc.clue_input_type,count(*) as sum FROM ({$newClueSQL}) as nc LEFT JOIN  ({$orgTmpSQL}) as ot 
                on nc.shop_id=ot.shop_id where $this_search_str GROUP BY nc.clue_input_type";


        $clue_info_all_list = Yii::$app->db->createCommand($sql_all)->queryAll();;


        //获取 转化线索
        //新增线索查询sql clue表加clue_wuxiao表
        $select = 'id,clue_input_type,shop_id,salesman_id';
        $newClueSQL = "
                SELECT {$select} FROM crm_clue WHERE create_time > {$start_time} AND create_time < {$end_time} AND 
                create_card_time > {$start_time} AND create_card_time < {$end_time} AND shop_id IN ($org_id_str)
            ";

        //按照渠道分组
        //新增线索表的零时表连组织架构的零时表  按照组织id聚合
        $sql_zhuanhua = "SELECT nc.clue_input_type,count(*) as sum FROM ({$newClueSQL}) as nc LEFT JOIN  ({$orgTmpSQL}) as ot 
                on nc.shop_id=ot.shop_id where $this_search_str GROUP BY nc.clue_input_type";

        $clue_info_zhuanhua_list = Yii::$app->db->createCommand($sql_zhuanhua)->queryAll();;

        //处理数据 以渠道id作为键值
        foreach ($clue_info_all_list as $value_all){
            $clue_info_all_list_new[$value_all['clue_input_type']] = $value_all;
        }
        foreach ($clue_info_zhuanhua_list as $value_zhuanhua){
            $clue_info_zhuanhua_list_new[$value_zhuanhua['clue_input_type']] = $value_zhuanhua;
        }

        //计算各渠道有效率
        $clue_rate_list = array();
        foreach ($input_type_new as $key=>$item){
            //如果为空 默认为0
            if(empty($clue_info_all_list_new[$key])){
                $clue_rate = 0;
            }else{
                $clue_rate = @round(intval($clue_info_zhuanhua_list_new[$key]['sum'])*100/intval($clue_info_all_list_new[$key]['sum']));
            }
            $info['input_type_id'] = $key;
            $info['input_type_name'] = $item['name'];
            $info['rate'] = $clue_rate;
            $clue_rate_list[] = $info;
        }

        //返回数据
        echo json_encode($clue_rate_list);
    }

    //客户意向等级页面
    public function actionIntentionLevel(){
        //权限控制 - 所有
        $this->checkPermission('/customer-analysis/intention-level');

        //接收参数 包括渠道信息、区域门店信息
        //接收数据
        $input_type_id = Yii::$app->request->post('input_type_id');
        $info_owner = Yii::$app->request->post('shop_id');
        $level = Yii::$app->request->post('level');

        $objTongJiLogic = new TongJiLogic();
        $session = Yii::$app->getSession();
        if($info_owner){
            $info_owner_arr = explode(',',$info_owner);
            $info_owner_id = array_pop($info_owner_arr);
            if($info_owner_id == -1)
            {
                $info_owner_id = array_pop($info_owner_arr);
            }
        }else{
            $info_owner_id = 6;
        }

        if(empty($input_type_id))
        {
            $input_type_id = 'all';
        }

        //根据区域门店、时间参数查询该层级各来源数据列表   !!!
        //查询渠道列表
        $data_dictionary = new DataDictionary();
        $input_type_list = $data_dictionary->getDictionaryData('input_type');

        $logic = new CustomerAnalysisLogic();
        $arrOrgIds = $session['userinfo']['permisson_org_ids'];
        $data = $logic->actionIntentionLevel($info_owner_id,$input_type_id, $arrOrgIds, $session['userinfo']['role_level']);

        $info_owner_display = 'block';
        if($session['userinfo']['role_level'] == 30){
            $info_owner_display = 'none';
        }
        $info_owner_name = @OrganizationalStructure::find()->where(['=','id',$info_owner_id])->one()->name;
        if($input_type_id == 'all'){
            $input_type_name = '全部';
        }else{
            $input_type_name = InputType::find()->where(['=','id',$input_type_id])->one()->name;
        }
        $data_common['data_update_time'] = PublicMethod::data_update_time(1);
        $data_common['info_owner_id'] = $info_owner_id;
        $data_common['info_owner_name'] = $info_owner_name;
        $data_common['input_type_id'] = $input_type_id;
        $data_common['input_type_name'] = $input_type_name;
        $data_common['info_owner_display'] = $info_owner_display;
        //渲染页面
        $info_sum = array();
        $sum = 0;
        foreach ($data['intention_info'] as $item){
            $info_sum[$item['intention_level_name']] = $item;
            $sum += $item['sum_num'];
        }
        $info_sum['sum_all'] = $sum;
        $objSelectDataLogic = new \common\logic\JsSelectDataLogic();
        $arrSelectorgList = $objSelectDataLogic->getSelectOrgNew($session['userinfo']['permisson_org_ids'], $session['userinfo']['role_level'], true);
        return $this->render('intentionlevel',[
            'intention_info'=>$data['intention_info'],
            'one_info_owner_new_list'=>$data['intention_info_child'],
            'intention_list_table'=>$data['intention_list_table'],
            'input_type_list'=>$input_type_list,
            'data_common'=>$data_common,
            'info_sum'=>$info_sum,
            'selectOrgJson' => json_encode($arrSelectorgList),
            'post' => Yii::$app->request->post()
        ]);
    }

    //战败客户
    public function actionFailCustomer(){
        //权限控制 - 所有
        $this->checkPermission('/customer-analysis/fail-customer');

        $session = Yii::$app->getSession();
        //查询该用户所能查看的组织架构目录
        $info_owner_id = 6;

        $info_owner_display = 'block';
        if($session['userinfo']['role_level'] == 30){
            $info_owner_display = 'none';
        }

        $start_date = date('Y-m-d',strtotime("-1 month + 1 day"));
        $end_date = date('Y-m-d');

        $search_time = "{$start_date} - {$end_date}";

        $info_owner_name = OrganizationalStructure::find()->where(['=','id',$info_owner_id])->one()->name;
        $data_common['data_update_time'] = PublicMethod::data_update_time(1);
        $data_common['search_time'] = $search_time;
        $data_common['info_owner_id'] = $info_owner_id;
        $data_common['info_owner_name'] = $info_owner_name;
        $data_common['info_owner_display'] = $info_owner_display;

        if(empty($input_type_id)){
            $input_type_id = 3;
        }
        $data_common['input_type_id'] = '';
        $data_common['input_type_name'] = '全部';

        $objSelectDataLogic = new \common\logic\JsSelectDataLogic();
        $arrSelectorgList = $objSelectDataLogic->getSelectOrgNew($session['userinfo']['permisson_org_ids'], $session['userinfo']['role_level'], true);
        return $this->render('failcustomer',[
            'data_common'=>$data_common,
             'selectOrgJson' => json_encode($arrSelectorgList),
       ]);
    }

    //ajax获取意向战败信息
    public function actionGetIntentionFailCustomer(){
        //接收数据
        $input_type_id = Yii::$app->request->post('input_type_id');
        $info_owner = Yii::$app->request->post('info_owner_id');
        $level = Yii::$app->request->post('level');
        $search_time = Yii::$app->request->post('search_time');

        $objTongJiLogic = new TongJiLogic();
        $session = Yii::$app->getSession();
        if($info_owner){
            $info_owner_arr = explode(',',$info_owner);
            $info_owner_id = array_pop($info_owner_arr);
            if($info_owner_id == -1)
            {
                $info_owner_id = array_pop($info_owner_arr);
            }
        }else{
            $info_owner_id = 6;
        }
        if(empty($input_type_id)){
            $input_type_id = 'all';
        }


        if(empty($search_time)){
            $start_date = date('Y-m-d',strtotime("-1 month"));
            $end_date = date('Y-m-d');
        }else{
            $date_arr = explode(' - ',$search_time);
            $start_date = $date_arr[0];
            $end_date = $date_arr[1];
        }

        $logic = new CustomerAnalysisLogic();
        $arrOrgIds = $session['userinfo']['permisson_org_ids'];
        $data = $logic->getIntentionFailCustomer($info_owner_id,$input_type_id,$start_date,$end_date, $arrOrgIds, $session['userinfo']['role_level']);
        $data_sum = $data['fail_info'];
        $data_sum_new = array();
        foreach ($data_sum as $item){
            $data_sum_new[$item['tag_id']] = $item;
        }
        $data['data_sum_new'] = $data_sum_new;
        echo json_encode($data);
    }


    //获取订车战败客户数据
    public function actionGetOrderFailCustomer()
    {
        //接收数据
        $search_time = Yii::$app->request->post('search_time');

        if(empty($search_time)){
            $start_date = date('Y-m-d',strtotime("-1 month"));
            $end_date = date('Y-m-d');
        }else{
            $date_arr = explode(' - ',$search_time);
            $start_date = $date_arr[0];
            $end_date = $date_arr[1];
        }

        $currentPage = Yii::$app->request->post('currentPage');
        $perPage = Yii::$app->request->post('perPage');

//        $currentPage = 1; //门店区域信息
//        $perPage = 10; //渠道来源信息

        $logic = new CustomerAnalysisLogic();
        $data = $logic->getOrderFailCustomer($start_date,$end_date,$currentPage,$perPage);

        echo json_encode($data);
    }


    //交车客户
    public function actionDeliveryCarCustomer(){
        //权限控制 - 所有
        $this->checkPermission('/customer-analysis/delivery-car-customer');

        $session = Yii::$app->getSession();
        //查询该用户所能查看的组织架构目录
        $info_owner_id = 6;

        $info_owner_display = 'block';
        if($session['userinfo']['role_level'] == 30){
            $info_owner_display = 'none';
        }

        $start_date = date('Y-m-d',strtotime("-1 month + 1 day"));
        $end_date = date('Y-m-d');

        $search_time = "{$start_date} - {$end_date}";

        $info_owner_name = OrganizationalStructure::find()->where(['=','id',$info_owner_id])->one()->name;
        $data_common['data_update_time'] = PublicMethod::data_update_time(1);
        $data_common['search_time'] = $search_time;
        $data_common['info_owner_id'] = $info_owner_id;
        $data_common['info_owner_name'] = $info_owner_name;
        $data_common['info_owner_display'] = $info_owner_display;

        if(empty($input_type_id)){
            $input_type_id = 3;
        }
        $input_type_name = InputType::find()->where(['=','id',$input_type_id])->one()->name;
        $data_common['input_type_id'] = $input_type_id;
        $data_common['input_type_name'] = $input_type_name;

        $objSelectDataLogic = new \common\logic\JsSelectDataLogic();
        $arrSelectorgList = $objSelectDataLogic->getSelectOrgNew($session['userinfo']['permisson_org_ids'], $session['userinfo']['role_level'], true);
        return $this->render('deliverycarcustomer',[
            'data_common'=>$data_common,
            'selectOrgJson' => json_encode($arrSelectorgList),
        ]);
    }


    //交车客户成交周期分析
    public function actionGetDealPeriod()
    {
        //接收数据

        $info_owner = Yii::$app->request->post('info_owner_id');

        $search_time = Yii::$app->request->post('search_time');
        $objTongJiLogic = new TongJiLogic();
        $session = Yii::$app->getSession();
        if($info_owner){
            $info_owner_arr = explode(',',$info_owner);
            $info_owner_id = array_pop($info_owner_arr);
            if($info_owner_id == -1)
            {
                $info_owner_id = array_pop($info_owner_arr);
            }
        }else{
            $info_owner_id = 6;
        }
        $arrWhereAndGroup = $objTongJiLogic->getSelectFieldByLevelAndOrgId($info_owner_id, $session['userinfo']['permisson_org_ids']);
        $field_str = $arrWhereAndGroup['where'];
        $groupBy = $arrWhereAndGroup['groupby'];
        
        if(empty($search_time)){
            $start_date = date('Y-m-d',strtotime("-1 month"));
            $end_date = date('Y-m-d');
        }else{
            $date_arr = explode(' - ',$search_time);
            $start_date = $date_arr[0];
            $end_date = $date_arr[1];
        }

        //查询饼图基础数据
        $period_info = TjDingcheDateCount::find()->select('sum(num) as sum_num,date_type')->where($field_str)
            ->andWhere(['>=','create_date',$start_date])->andWhere(['<=','create_date',$end_date])
            ->groupBy('date_type')->asArray()->all();

        foreach ($period_info as $key=>$value){
            if($value['date_type'] == 1){
                $period_info[$key]['date_type_name'] = '7天以内';
            }elseif ($value['date_type'] == 2){
                $period_info[$key]['date_type_name'] = '7天-14天';
            }elseif ($value['date_type'] == 3){
                $period_info[$key]['date_type_name'] = '14天-30天';
            }elseif ($value['date_type'] == 4){
                $period_info[$key]['date_type_name'] = '30-60天';
            }elseif ($value['date_type'] == 5){
                $period_info[$key]['date_type_name'] = '60天以上';
            }else{
                unset($period_info[$key]);
            }
        }

        //查询子级基础数据
        $period_info_child = TjDingcheDateCount::find()->select('sum(num) as sum_num,date_type,'.$groupBy.' as info_owner_id')
            ->where($field_str)
            ->andWhere(['>=','create_date',$start_date])->andWhere(['<=','create_date',$end_date])
            ->groupBy(['date_type',$groupBy])->asArray()->all();

        //对数据按照用户分组
        foreach ($period_info_child as $item_p_ch){
            $period_info_child_new[$item_p_ch['info_owner_id']][] = $item_p_ch;
        }

        //对子级以id作为键值
        $child_list_new = array();
        foreach ($arrWhereAndGroup['nextList'] as $item_ch_l) {
            $child_list_new[$item_ch_l['id']] = $item_ch_l;
        }

        //按照用户处理每个子级的数据  没有数值用0补齐
        $child_list_info_list = array();
        foreach ($child_list_new as $key_ch_n=>$value_ch_n){

            $info = $value_ch_n;

            if(!empty($period_info_child_new[$key_ch_n])){

                foreach ($period_info_child_new[$key_ch_n] as $item){
                    $info[$item['date_type']] = $item['sum_num'];
                }
            }

            $child_list_info_list[$key_ch_n] = $info;
        }

        foreach ($child_list_info_list as $key=>$value){

            $is_all_null = 0; //判断是否所有项目为空
            if(empty($value[1])){
                $child_list_info_list[$key][1] = '0';
            }else{
                $is_all_null = 1;
            }
            if(empty($value[2])){
                $child_list_info_list[$key][2] = '0';
            }else{
                $is_all_null = 1;
            }
            if(empty($value[3])){
                $child_list_info_list[$key][3] = '0';
            }else{
                $is_all_null = 1;
            }
            if(empty($value[4])){
                $child_list_info_list[$key][4] = '0';
            }else{
                $is_all_null = 1;
            }
            if(empty($value[5])){
                $child_list_info_list[$key][5] = '0';
            }else{
                $is_all_null = 1;
            }
            if($key == 0 && $is_all_null == 0){ //如果被删除顾问所有项目数据为0 不显示该数据
                unset($child_list_info_list[$key]);
            }
        }



        $data_sum = array();
        foreach ($period_info as $item){
            $data_sum[$item['date_type']] = $item;
        }
        $data_sum_new[1]['sum_num'] = empty($data_sum[1]['sum_num']) ? 0 : $data_sum[1]['sum_num'];
        $data_sum_new[2]['sum_num'] = empty($data_sum[2]['sum_num']) ? 0 : $data_sum[2]['sum_num'];
        $data_sum_new[3]['sum_num'] = empty($data_sum[3]['sum_num']) ? 0 : $data_sum[3]['sum_num'];
        $data_sum_new[4]['sum_num'] = empty($data_sum[4]['sum_num']) ? 0 : $data_sum[4]['sum_num'];
        $data_sum_new[5]['sum_num'] = empty($data_sum[5]['sum_num']) ? 0 : $data_sum[5]['sum_num'];

        if(empty($period_info)){
            $period_info[] = ['sum_num'=>0,'date_type_name'=>'无数据'];
        }

        $data['period_info'] = $period_info;
        $data['child_info'] = $child_list_info_list;
        $data['data_sum'] = $data_sum_new;

        echo json_encode($data);
    }


    //成交客户渠道来源分析
    public function actionGetInputType()
    {

        $info_owner = Yii::$app->request->post('info_owner_id');

        $search_time = Yii::$app->request->post('search_time');
        $objTongJiLogic = new TongJiLogic();
        $session = Yii::$app->getSession();
        if($info_owner){
            $info_owner_arr = explode(',',$info_owner);
            $info_owner_id = array_pop($info_owner_arr);
            if($info_owner_id == -1)
            {
                $info_owner_id = array_pop($info_owner_arr);
            }
        }else{
            $info_owner_id = 6;
        }
        $arrWhereAndGroup = $objTongJiLogic->getSelectFieldByLevelAndOrgId($info_owner_id, $session['userinfo']['permisson_org_ids']);
        $field_str = $arrWhereAndGroup['where'];
        $groupBy = $arrWhereAndGroup['groupby'];
//        $arrWhereAndGroup['nextList']

        if(empty($search_time)){
            $start_date = date('Y-m-d',strtotime("-1 month"));
            $end_date = date('Y-m-d');
        }else{
            $date_arr = explode(' - ',$search_time);
            $start_date = $date_arr[0];
            $end_date = $date_arr[1];
        }


        //按照参数时间查询当前层级信息
        //查询饼图基础数据
        $input_type_info = TjDingcheDateInputtype::find()->select('sum(num) as sum_num,input_type_id')->where($field_str)
            ->andWhere(['>=','create_date',$start_date])->andWhere(['<=','create_date',$end_date])
            ->groupBy('input_type_id')->asArray()->all();

        //处理信息
        $input_type = InputType::find()->asArray()->all();
        foreach ($input_type as $value){
            $input_type_new[$value['id']] = $value;
        }

        foreach ($input_type_info as $key=>$value){

            $input_type_info[$key]['input_type_name'] = $input_type_new[$value['input_type_id']]['name'];
        }

        //按照数值冒泡排序
        $count = count($input_type_info);
        for($i=0;$i<$count;$i++){
            for($j=0;$j<$count-$i-1;$j++){
                if( $input_type_info[$j]['sum_num'] < $input_type_info[$j+1]['sum_num'] ){
                    $temp=$input_type_info[$j];
                    $input_type_info[$j]=$input_type_info[$j+1];
                    $input_type_info[$j+1]=$temp;
                }
            }
        }

        //取出前五个数据
        //拼接数据错误原因
        $sum_num = 0;
        foreach ($input_type_info as $key_in=>$item_in){

            if($key_in < 5){
            }else{
                $sum_num += $item_in['sum_num'];
                unset($input_type_info[$key_in]);
            }
        }

        $input_type_info[5]['sum_num'] = strval($sum_num);
        $input_type_info[5]['input_type_id'] = '0';
        $input_type_info[5]['input_type_name'] = '其他';

        //对子级以id作为键值
        $child_list_new = array();
        foreach ($arrWhereAndGroup['nextList'] as $item_ch_l) {
            $child_list_new[$item_ch_l['id']] = $item_ch_l;
        }

        //取出前五原因tag_id 用来处理子级数据
        $input_type_arr = array_column($input_type_info,'input_type_id');

        //查询子级基础数据
        $input_type_info_child = TjDingcheDateInputtype::find()->select('sum(num) as sum_num,input_type_id,'.$groupBy.' as info_owner_id')
            ->where($field_str)
            ->andWhere(['>=','create_date',$start_date])->andWhere(['<=','create_date',$end_date])
            ->groupBy(['input_type_id',$groupBy])->asArray()->all();

        //对数据按照用户分组
        $input_type_info_child_new = array();
        foreach ($input_type_info_child as $item_in_ch){
            $input_type_info_child_new[$item_in_ch['info_owner_id']][] = $item_in_ch;
        }

        $child_info_list = array();
        foreach ($input_type_info_child_new as $key=>$value){
            foreach ($value as $item){
                $child_info_list[$key][$item['input_type_id']] = $item['sum_num'];
            }
        }

        //按照用户处理每个子级的数据  没有数值用0补齐
        $child_list_info_list = [];
        foreach ($child_list_new as $key_ch_n=>$value_ch_n){

            $is_all_null = 0; //判断是否所有项目为空

            $num = 0;
            if(empty($child_info_list[$key_ch_n])){
                foreach ($input_type_arr as $input_type_id){
                    $info[$input_type_id] = '0';
                }
            }else{
                foreach ($child_info_list[$key_ch_n] as $key=>$value){
                    if(!in_array($key,$input_type_arr)){
                        $num += $value;
                    }
                }
                foreach ($input_type_arr as $input_type_id){
                    if(empty($child_info_list[$key_ch_n][$input_type_id])){
                        $info[$input_type_id] = '0';
                    }else{
                        $is_all_null = 1;
                        $info[$input_type_id] = strval($child_info_list[$key_ch_n][$input_type_id]);
                    }
                }
            }

            $info['info_owner_id'] = $key_ch_n;
            $info['info_owner_name'] = $value_ch_n['name'];
            $info['0'] = strval($num);

            if($key_ch_n ==0 && $is_all_null == 0){  //如果被删除顾问数据为空 不显示该条数据
                continue;
            }

            $child_list_info_list[] = $info;
        }

        foreach ($input_type_info as $key=>$value){
            $info_tag = array();
            $info_tag['input_type_name'] = $value['input_type_name'];
            $info_tag['input_type_id'] = strval($value['input_type_id']);
            $top_input_type_list[] = $info_tag;
        }

        $info_sum = array();
        foreach ($input_type_info as $item){
            $info_sum[$item['input_type_id']] = $item;
        }

        $data['input_type_info'] = $input_type_info;
        $data['child_input_type_info'] = $child_list_info_list;
        $data['top_input_type_list'] = $top_input_type_list;
        $data['info_sum'] = $info_sum;
        //返回信息

        echo json_encode($data);
    }

    /**
     * Displays a single TjJichushuju model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new TjJichushuju model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new TjJichushuju();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing TjJichushuju model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing TjJichushuju model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the TjJichushuju model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return TjJichushuju the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TjJichushuju::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }



    //定义组织结构信息数组  按照id作为键值
    private $cids = array();

    /**
     * 根据id信息查询所有子级id列表
     * @param $id_str        当前层级id列表  多个用英文逗号分隔
     * @return array         子级id数组
     */
    protected function getChildsIds($id_str){
        $organizational_info = OrganizationalStructure::find()->select('id,pid')->asArray()->all();
        $organizational_list = array();
        foreach ($organizational_info as $item){
            $organizational_list[$item['pid']][] = $item;
        }
        $this->cids = $organizational_list;

        $id_arr = explode(',',$id_str);

        return $this->getCids($id_arr);
    }

    /**使用递归查询子级id
     * @param $id_arr      当前层级id数组
     * @return array
     */
    private function getCids($id_arr){

        $cids = $this->cids;

        static $cid_list = array();
        $cid_arr = array();

        foreach ($id_arr as $key=>$value){

            if(!empty($cids[$value])){
                $cid_arr = array_column($cids[$value],'id');
                $cid_list = array_merge($cid_list,$cid_arr);
                $this->getCids($cid_arr);
            }
        }
        return $cid_list;
    }
}
