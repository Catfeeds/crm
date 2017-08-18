<?php

namespace backend\controllers;

use Yii;
use common\models\Tags;
use backend\models\TagsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\Pagination;

/**
 * TagsController implements the CRUD actions for Tags model.
 */
class TagsController extends BaseController
{
    /**
     * 根据type值区分标签类型
     */
    public $arrTagsTypeConfig = [
        'to_shop' => '到店类型',
        'to_home' => '上门类型',
        'need' => '确定需求',
        'budget' => '确定预算',
        'car_type' => '选择车型',
        'money' => '议价',
    ];
    
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
     * Lists all Tags models.
     * @return mixed
     */
    public function actionIndex()
    {
        $strType = Yii::$app->request->get('type');
        !in_array($strType, array_keys($this->arrTagsTypeConfig)) && $strType = 'to_shop';

        
        $intPageSize = 1000;
        $intPage = 1;
        $query = Tags::find()->select(['*'])->where(['type' => $strType]);
        $countQuery = clone $query;
        $intTotal = $countQuery->count();
        $pages = new Pagination(['totalCount' => $intTotal]);//分页信息
        $pages->setPageSize($intPageSize);
        $pages->setPage($intPage - 1); //坑爹的  页码它是从0算起的
        $arrList = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();
        return $this->render('index', [
            'list' => $arrList,
            'count' => $intTotal,
            'typeConfig' => $this->arrTagsTypeConfig,
            'selectType' => $strType,
        ]);
    }

    public function actionUpdateOrCreate()
    {
        $post = Yii::$app->request->post();
        $intId = intval($post['id']);
        $arrSave = [
            'name' => strval($post['name']),
            'type' => strval($post['type']),
        ];
        $tags = new Tags();
        if($intId)
        {
            //编辑
            $strTableName = $tags->tableName();
            $strWhere = " id = $intId ";
            $res = Yii::$app->db->createCommand()->update($strTableName, $arrSave, $strWhere)->execute();
        }
        else
        {
            //新建
            $tags->setAttributes($arrSave);
            $res = $tags->save();
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
        $post           = Yii::$app->request->post();
        $model         = $this->findModel($post['id']);
        if($model->is_special == 0)//is_special  = 1 表示是特殊的初始化的等级，不能修改状态
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
     * Finds the Tags model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Tags the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Tags::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
