<?php
/**
 * 逾期控制器
 */
namespace backend\controllers;

use Yii;
use common\logic\JsSelectDataLogic;
use common\models\Yuqi;
use yii\data\Pagination;
use common\logic\DataDictionary;
class YuQiController extends BaseController
{
    public $enableCsrfValidation = false;
    private $intPageSize = 20;

    /**
     *列表
     */
    public function actionIndex()
    {
        //权限控制 - 所有
        $this->checkPermission('/yu-qi/index');
        $session = Yii::$app->getSession();
        $orgIds = $session['userinfo']['permisson_org_ids'];
        $strOrgIds = implode(',', $orgIds);
        $where = " and y.shop_id in({$strOrgIds})";
        $orgWhere = "id in ({$strOrgIds})";
        $arrOutPut['shop'] = \common\models\OrganizationalStructure::find()->where($orgWhere)->andWhere('is_delete=0')->asArray()->all();

        $get = Yii::$app->request->get();

        if (!empty($get['addtime'])) {
            list($startDate, $endDate) = explode(' - ', trim($get['addtime']));
            $startDate = $startDate.' 00:00:00';
            $endDate = $endDate.' 23:59:59';
            $where .= " and y.start_time >= '{$startDate}' and y.start_time <= '{$endDate}'";
        }else{
            $get['addtime'] = null;
        }

        if (!empty($get['keyword'])) {
            $where .= " and (c.customer_name like '%{$get['keyword']}%' 
                or c.customer_phone like '%{$get['keyword']}%' 
                or c.salesman_name like '%{$get['keyword']}%' 
                or c.intention_des like '%{$get['keyword']}%' 
            )";
        }else{
            $get['keyword'] = null;
        }

        $get['start_time'] = !empty($get['start_time']) ? "{$get['start_time']}" :  "desc";
        //意向等级筛选
        if (!empty($get['intention'])) {
            $inten = implode(',',$get['intention']);
            $where .= " and intention_level_id in ({$inten})";
            $arrOutPut['intentions'] = json_encode($get['intention']);
        } else {
            $arrOutPut['intentions'] = null;
        }

        //状态筛选
        if (!empty($get['is_lianxi'])) {
            $is_lianxi = implode(',',$get['is_lianxi']);
            $where .= " and is_lianxi in ({$is_lianxi})";
            $arrOutPut['is_lianxis'] = json_encode($get['is_lianxi']);
        } else {
            $arrOutPut['is_lianxis'] = null;
        }

        //门店筛选
        if (!empty($get['shop'])) {
            $shop = implode(',',$get['shop']);
            $where .= " and c.shop_id in ({$shop})";
            $arrOutPut['shops'] = json_encode($get['shop']);
        } else {
            $arrOutPut['shops'] = null;
        }

        $clm      = "
        y.clue_id,
        y.start_time,
        y.is_lianxi,
        y.lianxi_time,
        y.end_time,
        c.customer_name,
        c.customer_phone,
        c.intention_level_des,
        c.intention_des,
        c.salesman_name,
        c.shop_name";
        $query    = Yuqi::find()
            ->select($clm)
            ->from('crm_yuqi as y')
            ->join('INNER JOIN', 'crm_clue as c', 'y.clue_id = c.id')
            ->where("((y.is_lianxi = 0 and now() > end_time) or
                    (is_lianxi = 1 and lianxi_time > end_time)) $where");

        //die($query->createCommand()->getRawSql()); // 查看拼接的sql语句

        $countQuery = clone $query;
        $intTotal   = $countQuery->count();
        $pagination      = new Pagination(['totalCount' => $intTotal]);//分页信息
        $pagination->setPageSize($this->intPageSize);

        $list = $query->orderBy("y.start_time {$get['start_time']}")->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
        $objDataDic = new DataDictionary();//数据字典操作
        //意向等级
        $intention = $objDataDic->getDictionaryData('intention');
       // $this->dump($list);
        return $this->render('index', ['list'=>$list,'pagination'=>$pagination,'get'=>$get,'arrOutPut'=>$arrOutPut,'intention'=>$intention]);


    }


}
