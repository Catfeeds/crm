<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/4/10
 * Time: 9:43
 */

namespace backend\controllers;


use backend\logic\ExcitationLogic;
use common\logic\AnnouncementLogic;
use common\logic\CompanyUserCenter;
use common\models\Excitation;
use common\models\ExcitationLog;
use common\models\ExcitationShop;
use common\models\OrganizationalStructure;
use common\models\User;
use common\models\UserMoneyLog;
use Yii;
use yii\data\Pagination;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

/**
 * 激励管理
 *
 * Class ExcitationController
 * @package backend\controllers
 */
class ExcitationController extends  BaseController
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'end' => ['post'],
                ],
            ],
        ];
    }

    /**
     * 激励首页
     *
     * @return string
     */
    public function actionIndex()
    {
        //权限控制 - 总部
        $this->checkPermission('/excitation/index', 0);

        $all = ExcitationLogic::instance()->shopOptions();

        $query = Excitation::find()->orderBy(['id' => SORT_DESC]);
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(compact('totalCount'));
        $models = $query->offset($pagination->offset)->limit($pagination->limit)->all();

        $models_sort = clone $query;


        $models_sort = $countQuery->all();
        $list = [];
        $sort = 0;
        foreach ($models_sort as $key=>$value){
            $info = [];
            $sort ++;

            $info['e_id'] = $value->id;
            $info['e_sort'] = $sort;
            $list[] = $info;
        }

        $tree = $this->actionExcitationShop($list);

        return $this->render('index',compact('pagination', 'models','areaList', 'shopList', 'companyArray', 'all','tree'));
    }

    /**
     * 新建激励
     *
     * @return string
     */
    public function actionCreate()
    {
        $userInfo = Yii::$app->session->get('userinfo');
        $data = Yii::$app->request->post();
        $model = ExcitationLogic::instance()->add($data, $userInfo);
        if (!$model) {
            return json_encode([
                'code' => 4001,
                'message' => '新增失败'
            ]);
        }


        $this->arrLogParam = [
            'e_name' => $data['name']
        ];
        return json_encode([
            'code' => 200,
            'message' => '新增成功'
        ]);
    }

    /**
     * 激励详情
     *
     * @param $id
     * @return string
     */
    public function actionView($id)
    {
        $model = Excitation::findOne($id);
        $excitationLog = ArrayHelper::map(ExcitationLog::find()->select(['type_id', 'sum(e_money) as totalMoney'])->where([
            'e_id' => $id
        ])->groupBy('type_id')->asArray()->all(),'type_id', 'totalMoney');
        $sum = array_sum($excitationLog);
        $excitationLogs = [];
        foreach ($excitationLog as $k => $log) {
            $excitationLogs[] = [
                'title' =>   $this->list[$k],
                'rate' => round($log/$sum*100,2),
                'totalMoney' => $log
            ];
        }
        $rank = ExcitationLog::find()->select(['salesman_id', 'sum(e_money) as totalMoney'])->where([
            'e_id' => $id
        ])->groupBy('salesman_id')->limit(7)->orderBy('totalMoney desc')->asArray()->all();
        foreach ($rank as $k => $v) {
            $rank[$k]['salesman_name'] = User::findOne($v['salesman_id'])->name;
        }
        $excitationShop = ExcitationShop::find()->where(['e_id' => $id])->all();
        $shops = [];
        foreach ($excitationShop as $k => $value) {
            $excitation = ExcitationLogic::instance()->getShopLevel($value->shop_id);
            $shops[$excitation['area']][] = $excitation['shop'];
        }

        $query = Excitation::find()->orderBy(['id' => SORT_DESC]);

        $models_sort = $query->all();
        $list = [];
        $sort = 0;
        foreach ($models_sort as $key=>$value){
            $info = [];
            $sort ++;

            $info['e_id'] = $value->id;
            $info['e_sort'] = $sort;
            $list[] = $info;
        }


        $tree = $this->actionExcitationShop($list,$id);

        return $this->render('view',compact('model', 'excitationLogs', 'rank', 'sum', 'shops','tree'));
    }

    /**
     * crm_excitation_log type_id 对应标签
     *
     * @var array
     */
    public $list = [
        1 => '新增线索',
        2 => '线索转换',
        3 => '新增意向',
        4 => '完成电话任务',
        5 => '客户到店',
        6 => '上门拜访',
        7 => '客户订车',
        8 => '客户成交'
    ];

    /**
     * 结束激励
     *
     * @param $id
     * @return string|\yii\web\Response
     */
    public function actionEnd($id)
    {
        $userInfo = Yii::$app->session->get('userinfo');
        $model = Excitation::findOne($id);
        $model->status = 1;
        $model->end_person = $userInfo['name'];
        $model->end_person_id = $userInfo['id'];
        $model->end_time = date('Y-m-d H:i:s');
        if ($model->save()) {
            $this->arrLogParam = [
                'e_name' => $model->name
            ];
            return $this->redirect(['view', 'id' => $id]);
        } else {
            return '';
        }
    }

    /**
     * 体现处理
     *
     * @return string
     */
    public function actionCashApply()
    {
        //权限控制 - 总部
        $this->checkPermission('/excitation/cash-apply', 0);

        $param = Yii::$app->request->get();
        if(isset($param['addtime']) && $param['addtime']) {
            $sort = 'addtime '.$param['addtime'];
        } else {
            $sort = ['id' => SORT_DESC];
        }
        $query = UserMoneyLog::find()->select([
            'crm_user_money_log.id',
            'crm_user.area_id',
            'crm_user.shop_id',
            'crm_user.name',
            'crm_user.money',
            'crm_user_money_log.money as e_money',
            'crm_user_money_log.addtime',
        ])->innerJoin('crm_user', 'crm_user.id = crm_user_money_log.salesman_id')->where([
            'crm_user_money_log.type' => 2,
            'crm_user_money_log.status' => 1
        ])->orderBy($sort);
        if(isset($param['area_ids']) && $param['area_ids']) {
            $area = explode(',', $param['area_ids']);
            $query->andWhere(['in', 'crm_user.area_id', $area]);
        }
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(compact('totalCount'));
        $models = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
        $data = [];
        $area = [];
        foreach ($models as $model) {
            $organizational = OrganizationalStructure::findOne($model['area_id']);
            $area[$model['area_id']] = $organizational->name;
            $data[] = [
                'id' => $model['id'],
                'salesman_name' => $model['name'],
                'area' => $organizational->name,
                'shop_name' => OrganizationalStructure::findOne($model['shop_id'])->name,
                'money' => $model['e_money'],
                'has_money' => $model['money'],
                'addtime' => $model['addtime']
            ];
        }

        return $this->render('cash-apply',compact('pagination', 'data', 'area'));
    }

    /**
     * 确认 驳回
     *
     * @return string
     */
    public function actionConfirm()
    {
        $post = Yii::$app->request->post();
        if (empty($post) || !$post['id'] || !$post['type']) {
            return json_encode([
                'code' => 4001,
                'message' => '参数错误'
            ]);
        }
        $userMoney = UserMoneyLog::findOne($post['id']);
        if($post['type'] == "pass") {
            $userMoney->status = 3;
        } else {
            $userMoney->status = 2;
        }
        if (!$userMoney->save()) {
            return json_encode([
                'code' => 4001,
                'message' => '操作失败'
            ]);
        }
        if ($userMoney->status == 3) {
            User::updateAllCounters(['ice_money' => -$userMoney->money], ['id' => $userMoney->salesman_id]);
        } else {
            User::updateAllCounters([
                'money' => $userMoney->money,
                'ice_money' => -$userMoney->money
            ], ['id' => $userMoney->salesman_id]);
        }
        return json_encode([
            'code' => 200,
            'message' => '操作成功'
        ]);
    }


    /**
     * 正在激励门店列表
     * @return string
     */
    public function actionExcitationShop($list,$e_id = null)
    {
        $sort_list = ArrayHelper::index($list,'e_id');

        //查询所有门店
        $shop_list = OrganizationalStructure::find()
//            ->select('id,name')
//            ->where(['=','level',3])
            ->where(['=','is_delete',0])
            ->asArray()
            ->all();

        if($e_id == null){
            //查询当前有效激励列表
            $excitation_list = Excitation::find()
                ->select('id')
                ->where(['=','status',0])
                ->asArray()
                ->all();
            $excitation_id_arr = array_column($excitation_list,'id');
        }else{
            $excitation_id_arr[] = $e_id;
        }


        //查询所有门店的对应的有效激励情况
        $excitation_shop = ExcitationShop::find()->select('e_id,shop_id')->where(['in','e_id',$excitation_id_arr])->asArray()->all();

        //处理激励数据  以shop_id为键值
        $excitation_shop_new = array();
        foreach ($excitation_shop as $item){
            $excitation_shop_new[$item['shop_id']][] = $item;
        }

        //处理数据 对每个shop下e_id合并 以英文逗号分隔
        $excitation_shop_new2 = array();
        //考虑多个激励情况  （不会发生）
        foreach ($excitation_shop_new as $key=>$value){
            $e_id = array_column($value,'e_id');
            $e_sort_list = [];
            foreach ($e_id as $item){
                $e_sort_list[] = $sort_list[$item]['e_sort'];
            }
            $excitation_shop_new2[$key] = implode(',',$e_sort_list);
        }


        foreach ($shop_list as $key=>$value)
        {
            if(!empty($excitation_shop_new2[$value['id']])){
                $shop_list[$key]['e_id'] = $excitation_shop_new2[$value['id']];

            }
        }


        //无限极分类
        $tree=array();
        $packData=array();
        foreach ($shop_list as  $data) {
            $packData[$data['id']] = $data;
        }
        foreach ($packData as $key =>$val){
            if($val['pid']==1){//代表跟节点
                $tree[]=& $packData[$key];
            }else{
                //找到其父类
                $packData[$val['pid']]['child'][]=& $packData[$key];
            }
        }

        return $tree;
//        return $this->render('excitation-shop',['tree'=>$tree]);
    }
}