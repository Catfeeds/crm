<?php

namespace backend\controllers;

use common\logic\ActiveClueLogic;
use common\logic\AssignClueLogic;
use common\logic\ClueValidate;
use common\logic\DataDictionary;
use common\logic\Excel;
use common\logic\NoticeTemplet;
use common\logic\TaskLogic;
use common\models\Clue;
use common\models\Customer;
use common\models\Talk;
use common\models\Task;
use common\models\User;
use Yii;
use common\models\AgeGroup;
use backend\models\AgeGroupSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\Pagination;

/**
 * AgeGroupController implements the CRUD actions for AgeGroup model.
 */
class ActiveClueController extends BaseController
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
                    'update-or-create' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * 休眠客户页面
     * Lists all AgeGroup models.
     * @return mixed
     */
    public function actionUnconnectList()
    {
        //权限控制 - 门店
        $this->checkPermission('/active-clue/unconnect-list', 3);

        $search_time = Yii::$app->request->get('search_time');
        $search_key = Yii::$app->request->get('search_key');
        $export_data = Yii::$app->request->get('export_data');
        //获取session
        $session = Yii::$app->getSession();
//        $user_info = $session['userinfo'];
        $get = Yii::$app->request->get();
       //获取当前用户id、shop_id
        if(isset($get['shop_id']) && !empty($get['shop_id']) && in_array($get['shop_id'], $session['userinfo']['permisson_org_ids']))
        {
            $shop_id = $get['shop_id'];
        }
        else
        {
            $get['shop_id'] = $shop_id = $this->getDefaultShopId();
        }

        //如果有查询条件
        if($search_key){
            $select_str = "customer_name like '%{$search_key}%' 
            or customer_phone like '%{$search_key}%' 
            or intention_des like '%{$search_key}%' 
            or salesman_name like '%{$search_key}%'";
        }else{
            $select_str = '1=1';
        }

        //如果有查询时间
        if($search_time){
            $date = explode('-',$search_time);
            $start_day = $date[1];
            $end_day = $date[0];
        }else{
            $start_day = 7;
            $end_day = 5;
            $search_time = "{$end_day}-{$start_day}";
        }

        //查询时间   和当前时间比较
        $start_time = time() - 3600*24*$start_day;
        $end_time = time() - 3600*24*$end_day;

        //查询线索数据
        $model = new Clue();
        $query = $model->find()->select('id,customer_name,customer_phone,intention_level_des,intention_des,clue_source,salesman_name,last_view_time')
            ->where(['OR',['between','last_view_time',$start_time,$end_time],['last_view_time'=>null]])
            ->andWhere($select_str)
            ->andWhere(['=','is_fail',0])
            ->andWhere(['=','status',1])
            ->andWhere(['>','salesman_id',0])
            ->andWhere(['=','shop_id',$shop_id]);

        //不分页 输出时进行分页
        $list = $query->asArray()->all();

        //取出线索id列表
        $list_id = array_column($list,'id');

        //查询有未到期任务的线索  不属于休眠客户
        $task_model = new Task();
        $diff_id_list = $task_model->find()->select('clue_id')
            ->where(['in','clue_id',$list_id])
            ->andWhere(['>=','task_date',date("Y-m-d")])
            ->andWhere(['=','is_cancel',0])
            ->asArray()->all();

        //去重
        $diff_id_arr = array_unique(array_column($diff_id_list,'clue_id'));

        //有未到期任务的线索  不属于休眠客户
        foreach ($list as $k_list => $v_list){
            if(in_array($v_list['id'],$diff_id_arr)){
                unset($list[$k_list]);
            }
        }

        //取出剩余线索id列表
        $last_id_arr = array_column($list,'id');

        //查询线索联系次数  2-来电 3-去电 5 、6 、7-到店 8、 9、 10-上门
        $count_list = Talk::find()->select('clue_id,count(*) as count')
            ->where(['in','clue_id',$last_id_arr])
            ->andWhere(['in','talk_type',[2,3,5,6,7,8,9,10]])
            ->groupBy('clue_id')
            ->asArray()->all();

        //处理数组 以线索id作为键值
        $count_arr = null;
        foreach ($count_list as $count){
            $count_arr[$count['clue_id']] = $count;
        }

        //数据字典
        $obj = new DataDictionary();
        foreach ($list as $key=>$item){
            //最后联系时间
            $list[$key]['last_view_time'] = $item['last_view_time'] ? strval(date('Y-m-d H:i',$item['last_view_time'])) : '--';
            //线索来源
            $list[$key]['clue_source_name'] = $obj->getSourceName($item['clue_source']);
            //联系次数
            if(isset($count_arr[$item['id']]['count'])){
                $list[$key]['count'] = intval($count_arr[$item['id']]['count']);
            }else{
                $list[$key]['count'] = 0;
            }
        }

        if($export_data == 1){
            $this->arrLogParam = [
                'day' => $end_day.'-'.$start_day
            ];
            $arrColumns = ['序号', '客户姓名', '电话', '意向等级', '意向车型', '信息来源', '	顾问', '	最近联系时间', '联系次数'];

            $list_new = array();
            $i = 0;
            foreach ($list as $item){
                $info = array();
                $i++;
                $info['id'] = $i;
                $info['customer_name'] = (string)$item['customer_name'];
                $info['customer_phone'] = (string)$item['customer_phone'];
                $info['intention_level_des'] = (string)$item['intention_level_des'];
                $info['intention_des'] = (string)$item['intention_des'];
                $info['clue_source_name'] = (string)$item['clue_source_name'];
                $info['salesman_name'] = (string)$item['salesman_name'];
                $info['last_view_time'] = (string)$item['last_view_time'];
                $info['count'] = (int)$item['count'];
                $list_new[] = $info;
            }

            $this->outPutExcel('休眠客户', $arrColumns, $list_new);
        }

        //查询本店顾问
        $objCompanyUser = new \common\logic\CompanyUserCenter();
        $user_list = $objCompanyUser->getShopSales($shop_id);

        $list = array_values($list);
        //分页
        $intTotal = count($list);
        $pagination = new Pagination([
            'defaultPageSize' => 20,
            'totalCount' => $intTotal,
        ]);

        //获取当前页
        $page =  Yii::$app->request->get('page');
        if(empty($page)){
            $page = 1;
        }
        //按照每页大小对数据重新分组
        $list_page_arr = array_chunk($list,$pagination->defaultPageSize);

        $list_page = empty($list_page_arr[$page-1]) ? [] : $list_page_arr[$page-1];

        $search_data['search_key'] = $search_key;
        $search_data['search_time'] = $search_time;

        //获取门店 - 该界面是门店数据，等级传入固定的30 - 门店
        $objSelectDataLogic = new \common\logic\JsSelectDataLogic();
        $arrSelectorgList = $objSelectDataLogic->getSelectOrgNew($session['userinfo']['permisson_org_ids'], 30, true);
        return $this->render('index', [
            'list' => $list_page,
            'user_list' => $user_list,
            'end_day' => $end_day,
            'pagination' => $pagination,
            'search_data' => $search_data,
            'selectOrgJson' => json_encode($arrSelectorgList),
            'get' => $get,
        ]);
    }


    /**
     * 无人跟进客户页面
     * @return string
     */
    public function actionNofollow()
    {
        //权限控制 - 门店
        $this->checkPermission('/active-clue/nofollow', 3);

        $search_key = Yii::$app->request->get('search_key');

        $export_data = Yii::$app->request->get('export_data');
        //获取session
        $session = Yii::$app->getSession();
//        $user_info = $session['userinfo'];
        $get = Yii::$app->request->get();
       //获取当前用户id、shop_id
        if(isset($get['shop_id']) && !empty($get['shop_id']) && in_array($get['shop_id'], $session['userinfo']['permisson_org_ids']))
        {
            $shop_id = $get['shop_id'];
        }
        else
        {
            $get['shop_id'] = $shop_id = $this->getDefaultShopId();
        }


        //如果有搜索条件
        if($search_key){
            $select_str = "customer_name like '%{$search_key}%'
            or customer_phone like '%{$search_key}%'
            or intention_des like '%{$search_key}%'";
        }else{
            $select_str = '1=1';
        }

        //获取当前用户门店 顾问
        $objCompanyUser = new \common\logic\CompanyUserCenter();
        $user_list = $objCompanyUser->getShopSales($shop_id);

        //由于在删除顾问的时候会将clue表顾问id字段置为0，所以无人跟进客户为顾问id为0且被分配过的线索
        $list_query = Clue::find()
            ->select('id,customer_name,customer_phone,intention_level_des,intention_des,clue_source,last_view_time')
            ->where(['=','salesman_id',0])
            ->andWhere(['=','is_assign',1])
            ->andWhere(['=','is_fail',0])//战败不出现在无人跟进列表中
            ->andWhere($select_str)
            ->andWhere(['=','shop_id',$shop_id]);

        $intTotal = $list_query->count();

//        die($list_query->createCommand()->getRawSql());
        $pagination = new Pagination([
            'defaultPageSize' => 20,
            'totalCount' => $intTotal,
        ]);

        //查询所有线索  该页面不会有太多数据 所以无论导出数据还是页面显示都一次性查出
        $list = $list_query->asArray()->all();
        //取出剩余线索id列表
        $id_arr = array_column($list,'id');

        //查询线索联系次数  2-来电 3-去电 5 、6 、7-到店 8、 9、 10-上门
        $count_list = Talk::find()->select('clue_id,count(*) as count')
            ->where(['in','clue_id',$id_arr])
            ->andWhere(['in','talk_type',[2,3,5,6,7,8,9,10]])
            ->groupBy('clue_id')
            ->asArray()->all();

        //处理数组 以线索id作为键值
        $count_arr = null;
        foreach ($count_list as $count){
            $count_arr[$count['clue_id']] = $count;
        }

        //添加线索来源信息  添加联系次数信息
        $obj = new DataDictionary();

        //处理数组 添加线索来源 最后联系时间 联系数量
        foreach ($list as $key=>$item){

            $list[$key]['clue_source_name'] = $obj->getSourceName($item['clue_source']);

            $list[$key]['last_view_time'] = empty($item['last_view_time']) ? '--' : strval(date('Y-m-d',$item['last_view_time']));

            if(isset($count_arr[$item['id']]['count'])){
                $list[$key]['count'] = intval($count_arr[$item['id']]['count']);
            }else{
                $list[$key]['count'] = 0;
            }
        }


        //导出列表
        if($export_data == 1){

            $this->arrLogParam = [
                'date_1' => '-',
                'date_2' => '-'
            ];
            $arrColumns = ['序号', '客户姓名', '电话', '意向等级', '意向车型', '信息来源', '	最近联系时间', '联系次数'];

            $list_new = array();
            $i = 0;
            foreach ($list as $item){
                $info = array();
                $i++;
                $info['id'] = $i;
                $info['customer_name'] = (string)$item['customer_name'];
                $info['customer_phone'] = (string)$item['customer_phone'];
                $info['intention_level_des'] = (string)$item['intention_level_des'];
                $info['intention_des'] = (string)$item['intention_des'];
                $info['clue_source_name'] = (string)$item['clue_source_name'];
//                $info['salesman_name'] = (string)$item['salesman_name'];
                $info['last_view_time'] = (string)$item['last_view_time'];
                $info['count'] = (string)$item['count'];
                $list_new[] = $info;
            }

            $this->outPutExcel('无人跟进客户', $arrColumns, $list_new);
        }

        //获取当前页
        $page =  Yii::$app->request->get('page');
        if(empty($page)){
            $page = 1;
        }
        //按照每页大小对数据重新分组
        $list_page_arr = array_chunk($list,$pagination->defaultPageSize);

        $list_page = empty($list_page_arr[$page-1]) ? [] : $list_page_arr[$page-1];


        $search_data['search_key'] = $search_key;

        //获取门店 - 该界面是门店数据，等级传入固定的30 - 门店
        $objSelectDataLogic = new \common\logic\JsSelectDataLogic();
        $arrSelectorgList = $objSelectDataLogic->getSelectOrgNew($session['userinfo']['permisson_org_ids'], 30, true);
        return $this->render('nofollow', [
            'list' => $list_page,
            'user_list' => $user_list,
            'pagination' => $pagination,
            'search_data' => $search_data,
            'selectOrgJson' => json_encode($arrSelectorgList),
            'get' => $get,
        ]);

    }

    /**
     * 线索重新分配到同一顾问
     * @param $clue_id_list
     * @param $salesman_id
     * @return bool
     */
    public function actionReassign()
    {
        //获取session
        $user_info = Yii::$app->session->get('userinfo');
        //获取当前用户id、shop_id
//        $shop_id = $this->getDefaultShopId();//

        $who_assign_id = $user_info['id'];


        $source = Yii::$app->request->post('source');

        $clue_id_arr = Yii::$app->request->post('id_arr');
        $salesman_id = Yii::$app->request->post('salesman_id');
        $shop_id =  Yii::$app->request->post('shop_id');//获取选择的门店

        $salesman = User::findOne($salesman_id);
        $salesman_name = empty($salesman->name) ? '-' : $salesman->name;
//        $clue_id_arr = explode(',',$clue_id_list);

//        $shop_id = 4;//模拟
//        $who_assign_id  = Yii::$app->user->identity->id;//模拟

        $assignClueLogic = new AssignClueLogic();
//        $activelogic = new ActiveClueLogic();


        // 循环激活
        foreach ($clue_id_arr as $clue_id){
            // edited by liujx 2017-06-27 start:
            $objClue = Clue::findOne($clue_id);
            if (!$objClue) {
                return false;
            }

            // 如果该客户之前存在没有战败线索信息，并且线索状态为意向或者订车 不允许分配现在的线索
            $isExists = ClueValidate::validateExists([
                'and',
                ['=', 'customer_id', $objClue->customer_id],
                ['!=', 'id', $objClue->id],
                ['=', 'is_fail', 0],
                ['in', 'status', [1, 2]]
            ]);



            // 存在线索
            if ($isExists) {
                return false;
            }

            // end;

//            $rtn = $activelogic->activeClue($shop_id,$clue_id,$salesman_id,$who_assign_id);
            $rtn = $assignClueLogic->reassignClue($shop_id,$clue_id,$salesman_id,$who_assign_id,$source);
            if(!$rtn){
                return false;
            }

            //如果是无人跟进客户重新分配 判断当前意向等级 如果有意向等级 生成相应电话任务
            if($source == 'nofollow'){
                $clue_info = Clue::findOne($clue_id);
                if($clue_info->intention_level_id){
                    //添加电话任务
                    $logic = new TaskLogic();
                    $logic->newIntentionAddTask($clue_id,$clue_info->intention_level_id);
                }
            }
        }

        $this->arrLogParam = [
            'salesman_name_a' => $salesman_name
        ];

        $noticeTemplet = new NoticeTemplet();
        //无人跟进客户 重新分配不添加 电话任务
        if($source != 'nofollow'){
            //华为电话任务推送的总数量  如果当前线索状态为0 不添加电话任务
            $task_count = Clue::find()->where(['in','id',$clue_id_arr])->andWhere(['!=','status',0])->count();
            if($task_count != 0){
                $noticeTemplet->telephoneTaskNotice($who_assign_id, $salesman_id ,$task_count);
            }
        }


        $count = count($clue_id_arr);
        $clue_id_str = implode(',',$clue_id_arr);

        $noticeTemplet->reassignReminderNotice($who_assign_id, $salesman_id ,$count,$clue_id_str);


        return true;
    }

    /**
     * 激活到原顾问
     * @param $clue_id_list
     * @return bool
     */
    public function actionActive()
    {
        //获取session
        $user_info = Yii::$app->session->get('userinfo');
        //获取当前用户id、shop_id
//        $shop_id = $this->getDefaultShopId();//
        $shop_id =  Yii::$app->request->post('shop_id');//获取选择的门店
        $who_assign_id = $user_info['id'];

        $clue_id_arr = Yii::$app->request->post('id_arr');

        $clue_model = new Clue();
        $clue_list = $clue_model->find()->select('id,salesman_id,customer_id')->where(['in','id',$clue_id_arr])->asArray()->all();

        $activelogic = new ActiveClueLogic();

        //循环激活
        foreach ($clue_list as $clue) {

            /**
             * edited by liujx 2017-06-27 start:
             *
             * 如果该客户之前存在没有战败线索信息，并且线索状态为意向或者订车 不允许激活现在的线索
             */
            $isExists = ClueValidate::validateExists([
                'and',
                ['=', 'customer_id', $clue['customer_id']],
                ['!=', 'id', $clue['id']],
                ['=', 'is_fail', 0],
                ['in', 'status', [1, 2]]
            ]);

            // 存在线索
            if ($isExists) {
                return false;
            }

            $rtn = $activelogic->activeClue($shop_id,$clue['id'],$clue['salesman_id'],$who_assign_id);
            if(!$rtn){
                return false;
            }
            $noticeTemplet = new NoticeTemplet();
            $noticeTemplet->telephoneTaskNotice($who_assign_id, $clue['salesman_id'] ,1);
        }
        return true;
    }

    /**
     * Finds the AgeGroup model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return AgeGroup the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AgeGroup::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
