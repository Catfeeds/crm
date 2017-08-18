<?php

namespace backend\controllers;

use Yii;
use common\models\PlannedPurchaseTime;
use backend\models\PlannedPurchaseTimeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\Pagination;

/**
 * PlannedPurchaseTimeController implements the CRUD actions for PlannedPurchaseTime model.
 */
class PlannedPurchaseTimeController extends BaseController
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
     * Lists all PlannedPurchaseTime models.
     * @return mixed
     */
    public function actionIndex()
    {
        //权限控制 - 总部 客户信息栏目里面多个小页面，统一使用客户信息目录的别名判断
        $this->checkPermission('ke_hu_xin_xi_she_zhi', 0);
        
        $intPageSize = 1000;
        $intPage = 1;
        $query = PlannedPurchaseTime::find()->select(['*']);
        $countQuery = clone $query;
        $intTotal = $countQuery->count();
        $pages = new Pagination(['totalCount' => $intTotal]);//分页信息
        $pages->setPageSize($intPageSize);
        $pages->setPage($intPage - 1); //坑爹的  页码它是从0算起的
        $arrList = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();
        return $this->render('index', [
            'list' => $arrList,
            'count' => $intTotal,
        ]);
    }
    
    public function actionUpdateOrCreate()
    {
        $post = Yii::$app->request->post();
        $intId = intval($post['id']);
        $arrSave = [
            'name' => strval($post['name']),
            'des' => strval($post['des']),
        ];
        $plannedPurchaseTime = new PlannedPurchaseTime();
        if($intId)
        {
            //编辑
            $this->strRoute = 'customerinfo/update-or-create/update';
            $this->arrLogParam = ['type_name' => '拟购时间', 'tag_name' => $arrSave['name']];
            $strTableName = $plannedPurchaseTime->tableName();
            $strWhere = " id = $intId ";
            $res = Yii::$app->db->createCommand()->update($strTableName, $arrSave, $strWhere)->execute();
        }
        else
        {
            //新建
            $this->strRoute = 'customerinfo/update-or-create/create';
            $this->arrLogParam = ['type_name' => '拟购时间', 'tag_name' => $arrSave['name']];
            $plannedPurchaseTime->setAttributes($arrSave);
            $res = $plannedPurchaseTime->save();
        }

        if($res){
            $this->res();
        }else {
            $this->res('300', '操作失败！');
        }
    }
    
    //修改意向等级的状态（禁用与启用之间切换）
    public function actionUpdateStatus()
    {
        $get           = Yii::$app->request->get();
        $model         = $this->findModel($get['id']);
        if($model->is_special == 0)//is_special  = 1 表示是特殊的初始化的等级，不能修改状态
        {
            $model->status = $get['status'];
            $model->save();
        }
        return $this->redirect(['index']);
    }  

    /**
     * Finds the PlannedPurchaseTime model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return PlannedPurchaseTime the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PlannedPurchaseTime::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
