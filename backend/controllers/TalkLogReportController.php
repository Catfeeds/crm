<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/4/17
 * Time: 11:46
 */

namespace backend\controllers;


use common\logic\DataDictionary;
use common\models\Clue;
use common\models\ClueWuxiao;
use common\models\Customer;
use common\models\OrganizationalStructure;
use common\models\Talk;
use common\models\User;
use moonland\phpexcel\Excel;
use yii\data\Pagination;

/**
 * 商谈记录报表
 *
 * Class TalkLogController
 * @package backend\controllers
 */
class TalkLogReportController extends BaseController
{
    /**
     * 商谈记录
     */
    public function actionIndex()
    {
        //权限控制 - 所有
        $this->checkPermission('/talk-log-report/index');
        
        $param = \Yii::$app->request->get();
        $andWhere[] = 'and';
        $orWhere = '';
        if (isset($param['keyword']) && $param['keyword']) {
            $strKeyword = trim($param['keyword']);
            //商谈内容
            $orWhere .= " content like '%{$strKeyword}%' ";
            //顾问
            $salesIdsTmp = User::find()->select('id')->where(['like', 'name', $strKeyword])->asArray()->all();
            if($salesIdsTmp)
            {
                $salesIds = array_map(function($v){ return $v['id']; }, $salesIdsTmp);
                $orWhere .= " OR salesman_id in (" . implode(',', $salesIds) .") ";
            }
            //姓名
            $customerIdsTmp = Customer::find()->select('id')->where(['like', 'name', $strKeyword])->asArray()->all();
            if($customerIdsTmp)
            {
                $customerIds = array_map(function($v){ return $v['id']; }, $customerIdsTmp);
                $orWhere .= " OR castomer_id in (" . implode(',', $customerIds) . ") ";
            }
            //标签
            $arrTagId = \common\models\Tags::find()->select('id')->where(['=', 'name', $strKeyword])->asArray()->one();
            if($arrTagId)
            {
                $orWhere .= " OR FIND_IN_SET({$arrTagId['id']}, select_tags) ";
            }
        }
        if (isset($param['date_time']) && $param['date_time']) {
            $date = explode(' - ', $param['date_time']);
            $andWhere[] = [
                '>=', 'create_time', strtotime($date['0']),
            ];
            $andWhere[] = [
                '<=', 'create_time', strtotime('+1days', strtotime($date['1']))
            ];
        }
        $userInfo = \Yii::$app->session->get('userinfo');
        $shopIds = $userInfo['permisson_org_ids'];
        if ($shopIds) {
            $andWhere[] = [
                'in', 'shop_id', $shopIds
            ];
        }

        $query = Talk::find()->where([
            'in', 'talk_type', [2, 3, 5, 6, 7, 8, 9, 10]
        ])->andWhere($andWhere)->andWhere($orWhere);

        $countQuery = clone $query;
        $totalCount = $countQuery->count();

        $pagination = new Pagination(compact('totalCount'));
        $models = $query->offset($pagination->offset)->limit($pagination->limit)->orderBy([
            'create_time' => SORT_DESC
        ])->asArray()->all();

        $data = $this->getTalkList($models);

        $startDate = isset($date) ? $date[0] : '';
        $endDate = isset($date) ? $date[1] : '';
        return $this->render('index', compact('data', 'pagination', 'startDate', 'endDate'));
    }

    /**
     * 商谈记录导出
     */
    public function actionExport()
    {
        $param = \Yii::$app->request->get();

        $andWhere[] = 'and';
        $orWhere = '';
        if (isset($param['keyword']) && $param['keyword']) {
            $strKeyword = trim($param['keyword']);
            //商谈内容
            $orWhere .= " content like '%{$strKeyword}%' ";
            //顾问
            $salesIdsTmp = User::find()->select('id')->where(['like', 'name', $strKeyword])->asArray()->all();
            if($salesIdsTmp)
            {
                $salesIds = array_map(function($v){ return $v['id']; }, $salesIdsTmp);
                $orWhere .= " OR salesman_id in (" . implode(',', $salesIds) .") ";
            }
            //姓名
            $customerIdsTmp = Customer::find()->select('id')->where(['like', 'name', $strKeyword])->asArray()->all();
            if($customerIdsTmp)
            {
                $customerIds = array_map(function($v){ return $v['id']; }, $customerIdsTmp);
                $orWhere .= " OR castomer_id in (" . implode(',', $customerIds) . ") ";
            }
            //标签
            $arrTagId = \common\models\Tags::find()->select('id')->where(['=', 'name', $strKeyword])->asArray()->one();
            if($arrTagId)
            {
                $orWhere .= " OR FIND_IN_SET({$arrTagId['id']}, select_tags) ";
            }
        }

        if (isset($param['date_time']) && $param['date_time']) {
            $date['0'] = substr($param['date_time'],0,10);
            $date['1'] = substr($param['date_time'],-10);
            $andWhere[] = [
                '>=', 'create_time', strtotime($date['0']),
            ];
            $andWhere[] = [
                '<=', 'create_time', strtotime('+1days', strtotime($date['1']))
            ];
        }
        $userInfo = \Yii::$app->session->get('userinfo');
        $shopIds = $userInfo['permisson_org_ids'];

        if ($shopIds) {
            $andWhere[] = [
                'in', 'shop_id', $shopIds
            ];
        }

        $query = Talk::find()->where([
            'in', 'talk_type', [2, 3, 5, 6, 7, 8, 9, 10]
        ])->andWhere($andWhere)->andWhere($orWhere);
        $models = $query->asArray()->all();

        $data = $this->getTalkList($models);
        Excel::export([
            'models' => $data,
            'headers' => [
                'create_time' => '联系时间',
                'salesman_name' => '顾问',
                'shop_name' => '门店',
                'customer_name' => '姓名',
                'status' => '客户状态',
                'type_name' => '商谈类型',
                'tag_name' => '标签',
                'content' => '商谈内容',
            ],
            'columns' => [
                'create_time',
                'salesman_name',
                'shop_name',
                'customer_name',
                'status',
                'type_name',
                'tag_name',
                'content',
            ],
            'fileName' => date('YmdHi').'.xlsx'
        ]);
    }


    /**
     * 交谈记录详情
     *
     * @param array $models
     * @return array $data
     */
    public function getTalkList($models)
    {
        $data = [];
        foreach ($models as $k => $model) {
            $clue = Clue::findOne($model['clue_id']);
            if(empty($clue)){
                $clue = ClueWuxiao::findOne($model['clue_id']);
            }
            if (empty($clue)) {
                continue;
            }
            switch ($model['talk_type']) {
                case 2;
                    $data[$k]['type_name'] = '来电';
                    break;
                case 3;
                    $type_name = null;
                    if ($model['is_type'] == 1) $type_name = '-手动';
                    else if ($model['is_type'] == 2) $type_name = '-电话';
                    $data[$k]['type_name'] = '去电'.$type_name;
                    break;
                case 5;
                    $data[$k]['type_name'] = '到店商谈';
                    break;
                case 6;
                    $data[$k]['type_name'] = '到店订车';
                    break;
                case 7;
                    $data[$k]['type_name'] = '到店交车';
                    break;
                case 8;
                    $data[$k]['type_name'] = '上门商谈';
                    break;
                case 9;
                    $data[$k]['type_name'] = '上门订车';
                    break;
                case 10;
                    $data[$k]['type_name'] = '上门交车';
                    break;
            }
            $imgArr = [];
            if(isset($model['imgs']) && $model['imgs']) {
                $imgs = explode(',', $model['imgs']);
                foreach ($imgs as $v) {
                    $imgArr[] = [
                        'src' => $v,
                    ];
                }
                $data[$k]['img_count'] = count($imgs);
                $data[$k]['imgs'] = json_encode([
                    'data' => $imgArr
                ]);
            } else {
                $data[$k]['img_count'] = 0;
                $data[$k]['imgs'] = "{}";
            }
            $data[$k]['voices'] = "";
            if (!empty($model['voices'])) {//验证音频

                $data[$k]['voices'] = $model['voices'];
            }

            $data[$k]['create_time'] = date("Y-m-d H:i", $model['create_time']);


            if (!empty($model['content']))
                $data[$k]['content'] = $model['content'];
            else
                $data[$k]['content'] = '--';

            $data[$k]['tag_name'] = '';
            if (!empty($model['select_tags'])) {
                $objDataDic = new DataDictionary();//数据字典操作
                $tagNames = $objDataDic->getTagNamebyIds(explode(',', $model['select_tags']));
                $data[$k]['tag_name'] = implode('、', $tagNames);
            }else{
                $data[$k]['tag_name'] = '--';
            }


            $data[$k]['salesman_name'] = empty($clue->salesman_name) ? '--' : $clue->salesman_name;
            $customer_name = Customer::findOne($model['castomer_id']);
            $data[$k]['customer_name'] = empty($customer_name->name) ? '--' : $customer_name->name;

            $shop = OrganizationalStructure::findOne($model['shop_id']);
            $data[$k]['shop_name'] = $shop ? $shop->name  : '--';

            if ($clue['is_fail'] == 1) {
                $status = '战败客户';
            } else {
                $statusArr = [
                    0 => '线索客户',
                    1 => '意向客户',
                    2 => '订车客户',
                    3 => '成交客户',
                ];

                $status = $statusArr[$clue->status];
            }
            $data[$k]['status'] = $status;
        }
        return $data;
    }
}
