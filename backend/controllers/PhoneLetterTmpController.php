<?php

namespace backend\controllers;

use Yii;
use common\models\PhoneLetterTmp;
use backend\models\PhoneLetterTmpSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\Pagination;

/**
 * PhoneLetterTmpController implements the CRUD actions for PhoneLetterTmp model.
 */
class PhoneLetterTmpController extends BaseController
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
     * Lists all PhoneLetterTmp models.
     * @return mixed
     */
    public function actionIndex()
    {
        //权限控制 - 总部
        $this->checkPermission('/phone-letter-tmp/index', 0);

        $intType = Yii::$app->request->get('type');
        !in_array($intType, [1, 2, 3]) && $intType = 2;
        $intPageSize = 1000;
        $intPage = 1;
        $query = PhoneLetterTmp::find()->select(['*'])->where(['type' => $intType]);
        $countQuery = clone $query;
        $intTotal = $countQuery->count();
        $pages = new Pagination(['totalCount' => $intTotal]);//分页信息
        $pages->setPageSize($intPageSize);
        $pages->setPage($intPage - 1); //坑爹的  页码它是从0算起的
        $arrList = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();
//        print_r($arrList);
//        exit;
            return $this->render('index', [
            'list' => $arrList,
            'count' => $intTotal,
            'selectType' => $intType,
        ]);
    }

    public function actionUpdateOrCreate()
    {
        $post = Yii::$app->request->post();
        $intId = intval($post['id']);
        $arrSave = [
            'title' => strval($post['title']),
            'type' => strval($post['type']),
            'content' => strval($post['content']),
        ];
        if(isset($post['use_scene']))
        {
            $arrSave['use_scene'] = $post['use_scene'];
        }
        $phoneLetterTmp = new PhoneLetterTmp();
        if($intId)
        {
            //编辑
            $strTableName = $phoneLetterTmp->tableName();
            $strWhere = " id = $intId ";
            $res = Yii::$app->db->createCommand()->update($strTableName, $arrSave, $strWhere)->execute();
        }
        else
        {
            //新建
            $phoneLetterTmp->setAttributes($arrSave);
            $res = $phoneLetterTmp->save();
        }

        if($res){
            $this->res();
        }else {
            $this->res('300', '操作失败！');
        }
    }

    /**
     * update status
     */
    public function actionUpdateStatus()
    {
        $post           = Yii::$app->request->post();
        $model         = $this->findModel($post['id']);
        if($model->is_special == 0)//is_special  = 1 表示是特殊的初始化的数据，不能修改状态
        {
            $model->status = $post['status'];
            $model->save();
        }
        $arrRtn = [
            'code' => 0,
            'errMsg' => '',
        ];
        die(json_encode($arrRtn));
    }
        
    /**
     * Deletes an existing CarType model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete()
    {
        $id = Yii::$app->request->post('id');
        $model = $this->findModel($id);
        if($model->is_special == 0)
        {
            $model->delete();
            $arrRtn = ['code' => 0, 'errMsg' => ''];
        }
        else
        {
            $arrRtn = ['code' => 1, 'errMsg' => '特殊的初始化数据，不能删除'];
        }
        die(json_encode($arrRtn));
    }

    /**
     * Finds the PhoneLetterTmp model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return PhoneLetterTmp the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PhoneLetterTmp::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
