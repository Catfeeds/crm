<?php

namespace backend\controllers;

use Yii;
use common\models\Intention;
use backend\models\IntentionSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\Pagination;
/**
 * IntentionController implements the CRUD actions for Intention model.
 */
class IntentionController extends BaseController
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
                    'update-or-create' => ['POST'],
                ],
            ],
        ];
    }
    
    /**
     * Lists all Intention models.
     * @return mixed
     */
    public function actionIndex()
    {
        //权限控制 - 总部
        $this->checkPermission('/intention/index', 0);

        $intPageSize = 1000;
        $intPage = 1;
        $query = Intention::find()->select(['*']);
        $countQuery = clone $query;
        $intTotal = $countQuery->count();
        $pages = new Pagination(['totalCount' => $intTotal]);//分页信息
        $pages->setPageSize($intPageSize);
        $pages->setPage($intPage - 1); //坑爹的  页码它是从0算起的
        $arrList = $query->orderBy('sort desc')->offset($pages->offset)->limit($pages->limit)->asArray()->all();
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
            'frequency_day' => intval($post['frequency_day']),
            'total_times' => intval($post['total_times']),
            'has_today_task' => intval($post['has_today_task']),
        ];
        $intention = new Intention();
        $res = false;
        if($intId)
        {
            //记录log日志
            $this->strRoute = 'intention/update-or-create/update';
            $this->arrLogParam = ['intention_level_name' => $arrSave['name']];
            //编辑
            $strTableName = $intention->tableName();
            $strWhere = " id = $intId ";
            $res = Yii::$app->db->createCommand()->update($strTableName, $arrSave, $strWhere)->execute();
        }
        else
        {
            //记录log日志
            $this->strRoute = 'intention/update-or-create/create';
            $this->arrLogParam = ['intention_level_name' => $arrSave['name']];
            //新建
            $intention->setAttributes($arrSave);
            $res = $intention->save();
        }
        
        if ($res){
            $this->res();
        }else{
            $this->res(300,'操作失败！');
        }

    }
    
    //修改意向等级的状态（禁用与启用之间切换）
    public function actionUpdateStatus()
    {
//        $post           = Yii::$app->request->post();
        $post           = Yii::$app->request->get();  //17-04-27 李宗兴修改
        $model         = $this->findModel($post['id']);
        if($model->is_special == 0)//is_special  = 1 表示是特殊的初始化的等级，不能修改状态
        {
            $model->status = $post['status'];
            $model->save();
        }
        return $this->redirect(['index']);  //17-04-27 李宗兴修改
//        $arrRtn = [
//            'code' => 0,
//            'errMsg' => '',
//        ];
//        die(json_encode($arrRtn));
    }

    /**
     * Finds the Intention model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Intention the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Intention::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
