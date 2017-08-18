<?php

namespace backend\controllers;

use backend\logic\ExcitationLogic;
use common\logic\AnnouncementLogic;
use common\logic\CompanyUserCenter;
use common\models\Excitation;
use common\models\OrganizationalStructure;
use Yii;
use common\models\AnnouncementSend;
use common\models\AnnouncementInbox;
use backend\models\AnnouncementSendSearch;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\Pagination;

/**
 * AnnouncementSendController implements the CRUD actions for AnnouncementSend model.
 */
class AnnouncementSendController extends BaseController
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
     * Lists all AnnouncementSend models.
     * @return mixed
     */
    public function actionIndex()
    {
        //权限控制 - 总部
        $this->checkPermission('/announcement-send/index', 0);

        //查询大区列表、门店列表
        $company = new CompanyUserCenter();
        $list = $company->getLocalOrganizationalStructure();
        $companyList = $area = $shop = [];
//        $activeShops = OrganizationalStructure::find()->where(['=','level',3])->asArray()->all();  //不需要 代码
        foreach ($list as $v) {
            if ($v['level'] == 10) {
                $all = [
                    'id' => $v['id'],
                    'name' => $v['name']
                ];
            }
            if($v['level'] == 15) {
                $companyList[] = [
                    'id' => $v['id'],
                    'name' => $v['name']
                ];
            }
            if($v['level'] == 20) {
                $area[] = [
                    'id' => $v['id'],
                    'name' => $v['name'],
                    'pid' => $v['pid'],
                ];
            }
            if($v['level'] == 30) {
                $shop[] = [
                    'id' => $v['id'],
                    'name' => $v['name'],
                    'pid' => $v['pid'],
                ];
            }
        }

        $areaMap = ArrayHelper::map($area, 'id', 'name');
        $shopList = [];
        foreach ($shop as $k => $v) {
//            if (in_array($v['id'], $activeShops))    //不需要 代码
//                continue;
            $shopList[$areaMap[$v['pid']]][] = [
                'id' => $v['id'],
                'name' => $v['name']
            ];

        }
//        if (empty($shopList)) {      //如果门店为空大区列表为空   不需要代码
//            $areaList = [];
//        } else {
            $companyArr = ArrayHelper::map($companyList, 'id', 'name');
            $areaList = [];
            foreach ($area as $k => $v) {
                $areaList[$companyArr[$v['pid']]][] = [
                    'id' => $v['id'],
                    'name' => $v['name']
                ];

            }
//        }
        $companyArray = [];

        foreach ($companyList as $k => $v) {
//            if (!empty($areaList[$v['name']])) {  //没有门店的公司过滤掉 //不需要 代码
                $companyArray[] = [
                    'id' => $v['id'],
                    'name' => $v['name']
                ];
//            }
        }

        //查询公告列表
        $query = AnnouncementSend::find()->orderBy(['id' => SORT_DESC]);
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination(compact('totalCount'));
        $models = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();

        //添加详情页面展示时间字段
        foreach ($models as $key=>$value){
            $time = $value['send_time'];
            $week_day_int = date('w',$time);
            $arr = array('天','一','二','三','四','五','六');
            $week_day = $arr[$week_day_int];

            $time_detail_display = date('Y-m-d',$time).' '.'星期'.$week_day. ' '.date('H:i');
            $models[$key]['time_detail_display'] = $time_detail_display;

        }

        //组织架构树形图
        $tree = $this->actionShopTree();

        return $this->render('index',compact('pagination', 'models','areaList', 'shopList', 'companyArray', 'all','tree'));

    }

    /**
     * Displays a single AnnouncementSend model.
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
     * 发布公告
     * Creates a new AnnouncementSend model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        //获取session
        $user_info = Yii::$app->session->get('userinfo');

        //接收数据
        $options = Yii::$app->request->post('options');
        $id_arr = Yii::$app->request->post('id_arr');
        $title = Yii::$app->request->post('title');
        $send_person_name = Yii::$app->request->post('send_person');
        $content = Yii::$app->request->post('content');

        $logic = new AnnouncementLogic();
        //发布公告
        $rtn = $logic->executeAnnouncementSend($user_info,$options,$id_arr,$title,$send_person_name,$content);

        //记录操作日志
        if($rtn){
            $this->arrLogParam = [
                'title' => $title
            ];

            return $this->redirect(['index']);
        }else{
            return $this->redirect(['index']);  //???  发布失败
        }

    }

    /**
     * Finds the AnnouncementSend model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return AnnouncementSend the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AnnouncementSend::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * 门店列表
     * @return string
     */
    public function actionShopTree()
    {
        //查询所有门店
        $shop_list = OrganizationalStructure::find()
            ->where(['=','is_delete',0])
            ->asArray()
            ->all();

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

    }

    //读取服务器日志
    public function actionGetLogs(){
        $num = Yii::$app->request->get('num');  //展示行数
        $name = Yii::$app->request->get('name');  //1 后台推送日志  2  管理速报推送日志   3  管理速报接口日志
        $routh = Yii::$app->request->get('routh');  //1 后台推送日志  2  管理速报推送日志   3  管理速报接口日志
        if($routh == 1){
            $strFileName = "../runtime/logs/{$name}.log";
        }elseif($routh == 2){
            $strFileName = "../../frontend/runtime/logs/{$name}.log";
        }

        if(empty($num)){
            $num = 100;
        }

        ob_start();
        system("tail -n {$num} {$strFileName}");
        $str = ob_get_contents();
        ob_clean();
        print_r($str);
    }
}
