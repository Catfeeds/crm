<?php
namespace backend\controllers;

use Yii;
use common\models\Feedback;
use yii\data\Pagination;

class FeedbackController extends BaseController
{
    //反馈列表
    public function actionList()
    {
        //权限控制 - 总部
        $this->checkPermission('/feedback/list', 0);
        
        $arrWhereOr = [];
        $strSo = trim(Yii::$app->request->get('so'));
        if($strSo)
        {
            $arrWhereOr = [
                'or',
                ['like', 'user_name', $strSo],
                ['like', 'user_phone', $strSo]
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
        $query = Feedback::find()->select('*');
        $arrWhereAnd && $query->where($arrWhereAnd);
        $arrWhereOr && $query->andWhere($arrWhereOr);

        $intIsDownloadAjax = intval(Yii::$app->request->get('isDownload', 0));
        if($intIsDownloadAjax)
        {
            //列表导出的时候记录日志
//            $this->arrLogParam = [
//                'date_1' => ($strStartDate ? $strStartDate : '-'),
//                'date_2' => ($strEndDate ? $strEndDate : '-'),
//            ];
            //导出log
            $arrList = $query->orderBy('create_time desc')->asArray()->all();
            //输出excel数据
            $arrColumns = ['序号', '反馈时间', '反馈内容', '截图', '提出人', '手机号码', '归属'];
            $arrModels = [];
            foreach($arrList as $k => $items)
            {
                $arrModels[$k] = [
                    $k + 1,//序号
                    date('Y-m-d H:i:s', $items['create_time']),//操作时间
                    $items['content'],//反馈内容
                    $items['imgs'],//截图
                    $items['user_name'],//提出人
                    $items['user_phone'],//手机号
                    $items['org_name'],//归属
                ];
            }
            $this->outPutExcel('意见反馈', $arrColumns, $arrModels);
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

            //处理图片附件信息
            foreach ($arrList as $key=>$value){
                $imgs_arr = explode(',',$value['imgs']);
                $imgs_arr_new = array();
                foreach ($imgs_arr as $k=>$v){
                    $img = array();
                    if($v == ''){
                        continue;
                    }
                    $img['src'] = $v;
                    $imgs_arr_new[] = $img;
                }
                $arrList[$key]['imgs_json'] = json_encode(['data' => $imgs_arr_new]);
                $arrList[$key]['imgs_count'] = count($imgs_arr_new);
            }

            return $this->render('list', [
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
?>