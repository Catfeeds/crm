<?php

namespace backend\controllers;

use Yii;
use common\models\FailTags;
use backend\models\FailTagsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\Pagination;

/**
 * FailTagsController implements the CRUD actions for FailTags model.
 */
class FailTagsController extends BaseController
{
    public $arrFailTypeConfig = [
        'clue_fail' => '线索客户战败',
        'intention_fail' => '意向客户战败',
        'order_fail' => '订车客户战败',
    ];

    public $arrOrderFailGroup = [
//        'price'=> '价格',
        'product' => '产品问题',
//        'service' => '服务问题',
        'bank' => '贷款问题',
        'others' => '合同终止',
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
     * Lists all FailTags models.
     * @return mixed
     */
    public function actionIndex()
    {
        //权限控制 - 总部 
        $this->checkPermission('/tags/index', 0);

        $strType = Yii::$app->request->get('type');
        !in_array($strType, array_keys($this->arrFailTypeConfig)) && $strType = 'clue_fail';
        $intPageSize = 1000;
        $intPage = 1;
        $query = FailTags::find()->select(['*'])->where(['type' => $strType]);
        $countQuery = clone $query;
        $intTotal = $countQuery->count();
        $pages = new Pagination(['totalCount' => $intTotal]);//分页信息
        $pages->setPageSize($intPageSize);
        $pages->setPage($intPage - 1); //坑爹的  页码它是从0算起的
        $arrList = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();
        return $this->render('index', [
            'list' => $arrList,
            'count' => $intTotal,
            'failType' => $this->arrFailTypeConfig,
            'selectType' => $strType,
            'groupType' => $this->arrOrderFailGroup
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
        if(isset($post['des']) && isset($post['group']) && in_array($post['group'], array_keys($this->arrOrderFailGroup)))
        {
            $arrSave['des'] = $post['des'];
            $arrSave['group'] = $post['group'];
        }
        $failTags = new FailTags();
        if($intId)
        {
            //编辑
            $strTableName = $failTags->tableName();
            $strWhere = " id = $intId ";
            $res = Yii::$app->db->createCommand()->update($strTableName, $arrSave, $strWhere)->execute();
        }
        else
        {
            //新建
            $failTags->setAttributes($arrSave);
            $res = $failTags->save();
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
     * Finds the FailTags model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return FailTags the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FailTags::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
