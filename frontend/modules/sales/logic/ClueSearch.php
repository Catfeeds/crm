<?php
/**
 *任务-搜索
 */

namespace frontend\modules\sales\logic;


use frontend\modules\sales\models\Clue;
use yii\data\Pagination;
use Yii;


class ClueSearch extends BaseLogic
{
    public function search($post)
    {
        $user = Yii::$app->user->identity;
        $intUserId = $user->getId();
        $intShopId = $user->shop_id;

        $params = "salesman_id = {$intUserId} and shop_id = {$intShopId}";

        if (!empty($post->keyword)) {
            $params .= " and (
                customer_name like '%{$post->keyword}%' 
                or customer_phone like '%{$post->keyword}%'
                or intention_des like '%{$post->keyword}%'
                or intention_level_des like '%{$post->keyword}%'
                )";
        }
        $query = Clue::find()->select([
            'id', 'customer_name', 'status', 'customer_phone', 'intention_des', 'intention_level_des', 'is_fail'
        ])
            ->where($params)
            ->orderBy('id desc');

        $countQuery = clone $query;
        $totalCount = $countQuery->count();


        //分页
        $pagination = new Pagination([
            'defaultPageSize' => $post->perPage,
            'totalCount' => $totalCount,
        ]);

        $models = $query
            ->offset(($post->currentPage - 1) * $post->perPage)
            ->limit($pagination->limit)
            ->asArray()
            ->all();
        //  die($query->createCommand()->getRawSql());

        $data = [];
        foreach ($models as $k => $model) {

            $data[$k]['clue_id']             = intval($model['id']);
            $data[$k]['customer_name']       = strval($model['customer_name']);
            $data[$k]['customer_phone']      = strval($model['customer_phone']);
            $data[$k]['intention_des']       = strval($model['intention_des']);
            $data[$k]['intention_level_des'] = strval($model['intention_level_des']);
            $data[$k]['status']              = intval($model['status']);

            if ($model['is_fail'] == 1)
                $data[$k]['status_des'] = '战败客户';
            else if ($model['status'] == 0)
                $data[$k]['status_des'] = '线索客户';
            else if ($model['status'] == 1)
                $data[$k]['status_des'] = '意向客户';
            else if ($model['status'] == 2)
                $data[$k]['status_des'] = '订车客户';
            else if ($model['status'] == 3)
                $data[$k]['status_des'] = '保有客户';

        }
        if ($totalCount > 0) {
            return [
                'models' => $data,
                'pages' => $this->pageFix($pagination),
            ];
        } else {
            return null;
        }
    }

}