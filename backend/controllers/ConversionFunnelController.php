<?php

namespace backend\controllers;

use common\common\PublicMethod;
use common\logic\CustomerAnalysisLogic;
use common\logic\DataDictionary;
use common\models\InputType;
use common\models\OrganizationalStructure;
use common\models\TjZhuanhualoudou;
use Yii;
use common\models\TjJichushuju;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\logic\TongJiLogic;

/**
 * ConversionFunnelController implements the CRUD actions for TjJichushuju model.
 */
class ConversionFunnelController extends BaseController
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
        $this->checkPermission('/conversion-funnel/index');

        //获取session数据
        $session = Yii::$app->getSession();
        $post = Yii::$app->request->post();
        //接收数据
        $input_type_id = Yii::$app->request->post('input_type_id');
        $info_owner = Yii::$app->request->post('shop_id');
        $search_time = Yii::$app->request->post('search_time');

        //处理查询时间参数
        if(empty($search_time)){
            $start_date = date('Y-m-d',strtotime("-1 month + 1 day"));
            $end_date = date('Y-m-d');
            $search_time = "{$start_date} - {$end_date}";
        }else{
            $date_arr = explode(' - ',$search_time);
            $start_date = $date_arr[0];
            $end_date = $date_arr[1];
        }

        //如果当前用户为店长  不展示选择区域门店选择框
        $info_owner_display = 'block';
        if($session['userinfo']['role_level'] == 30){
            $info_owner_display = 'none';
        }

        //读取所展示数据所属组织结构id
        if($info_owner){
            $info_owner_arr = explode(',',$info_owner);
            $info_owner_id = array_pop($info_owner_arr);
            if($info_owner_id == -1)
            {
                $info_owner_id = array_pop($info_owner_arr);
            }
        }else{
            //默认当前用户组织结构id
            $info_owner_id = 0;
            if($session['userinfo']['role_level'] == 30 && (!isset($get['shop_id'])))
            {
                $info_owner_id = $get['shop_id'] = $this->getDefaultShopId();
            }
        }
        
        //默认值
        if(empty($input_type_id)){
            $input_type_id = 'all';
        }

        if(empty($input_type_id) || empty($info_owner_id) || empty($start_date) || empty($end_date)){
//            echo 1;die;
        }


        //查询所有线索来源数据
        $clue_source_model = new DataDictionary();
        $input_type_list = $clue_source_model->getDictionaryData('source');

        //查询所需展示数据
        $arrOrgIds = $session['userinfo']['permisson_org_ids'];
        $roleLevel = $session['userinfo']['role_level'];
        $logic = new CustomerAnalysisLogic();
        $data = $logic->getConversionFunnel($input_type_id,$info_owner_id,$start_date,$end_date, $arrOrgIds, $roleLevel);

        //保存表格所展示数据
        $table_data = $data['table_data'];

        //处理漏斗图实际数据  在页面直接展示
        $real_data_rate = array();
        foreach ($data['funnel_real_data_rate'] as $key=>$value){

            $info = array();

            if($key == 'new_clue_num'){
                $item = '新增线索';
            }elseif ($key == 'new_intention_num'){
                $item = '新增意向';
            }elseif ($key == 'to_shop_num'){
                $item = '来店数量';
            }elseif ($key == 'dingche_num'){
                $item = '订车数量';
            }else{
                continue;
            }

            $info['name'] = $item;
            $info['value'] = empty($value) ? 0 : $value;
            $real_data_rate[] = $info;
        }

        //处理漏斗图期望数据  在页面直接展示
        $expect_data = array();
        foreach ($data['funnel_expect_data'] as $key=>$value){
            $info = array();

            if($key == 'new_clue_num'){
                $item = '新增线索';
            }elseif ($key == 'new_intention_num'){
                $item = '新增意向';
            }elseif ($key == 'to_shop_num'){
                $item = '来店数量';
            }elseif ($key == 'dingche_num'){
                $item = '订车数量';
            }else{
                continue;
            }

            $info['name'] = $item;

            $info['value'] = $value;
            $expect_data[] = $info;
        }
        $data_funnel['real_data'] = $data['funnel_real_data'];  //表格总计栏
        $data_funnel['real_data_rate'] = $real_data_rate;       //漏斗真实数据
        $data_funnel['expect_data'] = $expect_data;             //漏斗期望数据

        if($input_type_id == 'all'){
            $input_type_name = '全部';
        }else{
            $input_type_name = InputType::find()->where(['=','id',$input_type_id])->one()->name;
        }

        $data_common['data_update_time'] = PublicMethod::data_update_time(1);    //数据更新时间
        $data_common['search_time'] = $search_time;                              //页面展示搜索时间
        $data_common['info_owner_id'] = $info_owner_id;                          //组织架构id
        $data_common['input_type_id'] = $input_type_id;                          //渠道来源id
        $data_common['input_type_name'] = $input_type_name;                       //渠道来源名称
        $data_common['info_owner_display'] = $info_owner_display;                //区域门店是否展示

        $objSelectDataLogic = new \common\logic\JsSelectDataLogic();
        $arrSelectorgList = $objSelectDataLogic->getSelectOrgNew($arrOrgIds, $session['userinfo']['role_level'], true);
        //渲染页面
        return $this->render('index', [
            'data_funnel' => json_encode($data_funnel),
            'table_data' => $table_data,
            'input_type_list' => $input_type_list,
            'data_common' => $data_common,
            'selectOrgJson' => json_encode($arrSelectorgList),
            'post' => $post
        ]);
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
