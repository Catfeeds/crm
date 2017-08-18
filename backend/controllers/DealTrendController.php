<?php

namespace backend\controllers;

use common\common\PublicMethod;
use common\logic\CompanyUserCenter;
use common\logic\DataDictionary;
use common\models\Clue;
use common\models\InputType;
use common\models\Order;
use common\models\OrganizationalStructure;
use common\models\TjDingcheDateCount;
use common\models\TjDingcheNum;
use common\models\TjFailIntentionTagCount;
use common\models\TjInputtypeclueAll;
use common\models\TjInputtypeclueFail;
use common\models\TjInputtypeclueZhuanhua;
use common\models\TjIntentionGenjinzhong;
use common\models\TjIntentionLevelCount;
use common\models\TjLastMonthIntention;
use common\models\TjThisMonthIntention;
use common\models\TjZhuanhualoudou;
use common\models\User;
use Yii;
use common\models\TjJichushuju;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\logic\DealTrendLogic;

/**
 * ConversionFunnelController implements the CRUD actions for TjJichushuju model.
 */
class DealTrendController extends BaseController
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
     * Lists all TjJichushuju models.
     * @return mixed
     */
    public function actionIndex()
    {
        //权限控制 - 所有
        $this->checkPermission('/deal-trend/index');

        //根据区域门店、时间参数查询该层级各来源数据列表   !!!
        //查询该用户所能查看的组织架构目录
        $session = Yii::$app->getSession();
        $info_owner_id = 1;

        $info_owner_display = 'block';
        if($session['userinfo']['role_level'] == 30){
            $info_owner_display = 'none';
        }
        $data_common['input_type_id'] = '';
        $data_common['input_type_name'] = '全部';
        $info_owner_name = OrganizationalStructure::find()->where(['=','id',$info_owner_id])->one()->name;
        $data_common['data_update_time'] = PublicMethod::data_update_time(1);
        $data_common['search_time'] = date('Y');
        $data_common['info_owner_id'] = $info_owner_id;
        $data_common['info_owner_name'] = $info_owner_name;
        $data_common['info_owner_display'] = $info_owner_display;

        $data = json_encode($data_common);
        //渲染页面
        $objSelectDataLogic = new \common\logic\JsSelectDataLogic();
        $arrSelectorgList = $objSelectDataLogic->getSelectOrgNew($session['userinfo']['permisson_org_ids'], $session['userinfo']['role_level'], true);
        return $this->render('index',[
                'data_common'=>$data,
                'selectOrgJson' => json_encode($arrSelectorgList),
            ]);
    }


    public function actionGetCountData()
    {
        //接收数据
        $input_type_id = Yii::$app->request->post('input_type_id');
        $info_owner = Yii::$app->request->post('info_owner_id');

        $session = Yii::$app->getSession();
        if($info_owner){
            $info_owner_arr = explode(',',$info_owner);
            $info_owner_id = array_pop($info_owner_arr);
            if($info_owner_id == -1)
            {
                $info_owner_id = array_pop($info_owner_arr);
            }
        }else{
            $info_owner_id = 1;
        }

        $year = Yii::$app->request->post('year'); //渠道来源信息

        if(empty($year)){
            $year = date('Y');
        }
        if(empty($input_type_id)){
            $input_type_id = 'all';
        }


        $logic = new DealTrendLogic();
        $data = $logic->getCountData($year,$info_owner_id,$input_type_id, $session['userinfo']['permisson_org_ids'], $session['userinfo']['role_level']);
        echo json_encode($data);
    }


    public function actionGetRateData()
    {
        //接收数据
        $input_type_id = Yii::$app->request->post('input_type_id');
        $info_owner = Yii::$app->request->post('info_owner_id');

        $session = Yii::$app->getSession();
        if($info_owner){
            $info_owner_arr = explode(',',$info_owner);
            $info_owner_id = array_pop($info_owner_arr);
            if($info_owner_id == -1)
            {
                $info_owner_id = array_pop($info_owner_arr);
            }
        }else{
            $info_owner_id = 1;
        }
        $year = Yii::$app->request->post('year'); //渠道来源信息

        if(empty($year)){
            $year = date('Y');
        }

        if(empty($input_type_id)){
            $input_type_id = 'all';
        }

        $logic = new DealTrendLogic();
        $data = $logic->getRateData($year,$info_owner_id,$input_type_id, $session['userinfo']['permisson_org_ids'], $session['userinfo']['role_level']);
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
}
