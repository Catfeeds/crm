<?php

namespace backend\controllers;
use Yii;
use common\models\LogPcCaozuo;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\Pagination;
/**
 * IntentionController implements the CRUD actions for Intention model.
 */
class LogsController extends BaseController{
    //put your code here
    
    public function actionShowLogs()
    {
        //权限控制 - 总部
        $this->checkPermission('/logs/show-logs', 0);
        
        $arrWhereOr = [];
        $strSo = trim(Yii::$app->request->get('so'));
        if($strSo)
        {
            $arrWhereOr = [
                'or',
                ['like', 'user', $strSo],
                ['like', 'phone', $strSo]
            ];
        }
        $arrWhereAnd = [];
        $strSearchTime = trim(Yii::$app->request->get('search_time'));
        //输入了时间搜索条件 且时间条件格式正确
        if($strSearchTime && preg_match('/^\d{4}\-\d{2}\-\d{2} \- \d{4}\-\d{2}\-\d{2}$/', $strSearchTime))
        {
            list($strStartDate, $strEndDate) = explode(' - ', $strSearchTime);
            $arrWhereAnd = [
                'and',
                ['>=', 'create_time', strtotime($strStartDate)],
                ['<', 'create_time', (strtotime($strEndDate) + 24*3600)],
            ];
        }
        else
        {
            $strStartDate = $strEndDate = '';
        }
        $query = LogPcCaozuo::find()->select('*');
        $arrWhereAnd && $query->where($arrWhereAnd);
        $arrWhereOr && $query->andWhere($arrWhereOr);
        
        $intIsDownloadAjax = intval(Yii::$app->request->get('isDownload', 0));
        if($intIsDownloadAjax)
        {
            //列表导出的时候记录日志
            $this->arrLogParam = [
                    'date_1' => ($strStartDate ? $strStartDate : '-'), 
                    'date_2' => ($strEndDate ? $strEndDate : '-'),
                ];
            //导出log
            $arrList = $query->orderBy('create_time desc')->asArray()->all();
            //输出excel数据
            $arrColumns = ['序号', '操作时间', '操作类型', '操作人', '手机号', '归属', 'IP地址/IMEI', '备注'];
            $arrModels = [];
            foreach($arrList as $k => $items)
            {
                $arrModels[$k] = [
                  $k + 1,//序号  
                  date('Y-m-d H:i:s', $items['create_time']),//操作时间  
                  $items['type_name'],//操作类型
                  $items['user'],//操作人
                  $items['phone'],//手机号
                  $items['org_name'],//归属  
                  $items['ip'],//IP地址/IMEI
                  $items['content'],//备注 - 具体操作内容
                ];
            }
            $this->outPutExcel('操作日志', $arrColumns, $arrModels);
        }
        else
        {
            //页面显示
            $intPageSize = 20;
            $countQuery = clone $query;
            $intTotal = $countQuery->count();
            $pages = new Pagination(['totalCount' => $intTotal]);//分页信息
            $pages->setPageSize($intPageSize);
            $arrList = $query->orderBy('create_time desc')->offset($pages->offset)->limit($pages->limit)->asArray()->all();
            return $this->render('showLogs', [
                'list' => $arrList,
                'count' => $intTotal,
                'objPage' => $pages,
                'strStartDate' => $strStartDate,
                'strEndDate' => $strEndDate,
                'so' => $strSo
            ]);
        }
        
        
        
    }
    
    
}
