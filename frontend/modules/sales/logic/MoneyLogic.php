<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/4/6
 * Time: 10:13
 */

namespace frontend\modules\sales\logic;


use common\models\User;
use common\models\UserMoneyLog;
use common\server\Logic;
use yii\data\Pagination;

/**
 * 钱包逻辑
 *
 * Class MoneyLogic
 * @package frontend\modules\sales\logic
 */
class MoneyLogic extends Logic
{
    /**
     * 统计信息
     *
     * @param User $user
     * @return array
     */
    protected function getTotal($user)
    {
        $income = UserMoneyLog::find()->where([
            'salesman_id' => $user->id,
            'type' => 1
        ])->sum('money');
        $income = $income ? : 0;
        $money = $user->money ? : 0;
        $expenditure = $income - $money;
        return [
            'income' => number_format($income,2),
            'expenditure' => number_format($expenditure,2),
            'balance' => $money
        ];

    }

    /**
     * 收入明细
     *
     * @param User $user
     * @param $pData
     * @return array
     */
    public function income($user, $pData)
    {
        $query = UserMoneyLog::find()->where([
            'salesman_id' => $user->id,
            'type' => 1
        ]);
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination([
            'totalCount' => $totalCount,
        ]);
        $page = isset($pData['currentPage']) ? $pData['currentPage'] : 1;
        $perPage = isset($pData['perPage']) ? $pData['perPage'] : 20;
        $pagination->setPage($page -1);
        $pagination->setPageSize($perPage, true);
        $models = $query->limit(
            $pagination->getLimit()
        )->offset($pagination->getPage() * $pagination->pageSize)->orderBy(['addtime' => SORT_DESC])->all();
        $data = [];
        if (!empty($models)) {
            foreach ($models as $k => $model) {
                $data[$k]['e_name'] = $model->e_name;
                $data[$k]['des'] = $model->des;
                $data[$k]['addtime'] = date('Y-m-d H:i', strtotime($model->addtime));
                $data[$k]['money'] = $model->money;
            }
        }
        return [
            'models' => $data,
            'pages' => BaseLogic::instance()->pageFix($pagination),
            'total' => $this->getTotal($user)
        ];
    }

    /**
     * 支出明细
     *
     * @param User $user
     * @param $pData
     * @return array
     */
    public function expenditure($user, $pData)
    {
        $query = UserMoneyLog::find()->where([
            'salesman_id' => $user->id,
            'type' => 2
        ]);
        $countQuery = clone $query;
        $totalCount = $countQuery->count();
        $pagination = new Pagination([
            'totalCount' => $totalCount,
        ]);

        $page = isset($pData['currentPage']) ? $pData['currentPage'] : 1;
        $perPage = isset($pData['perPage']) ? $pData['perPage'] : 20;
        $pagination->setPage($page -1);
        $pagination->setPageSize($perPage, true);

        $models = $query->limit(
            $pagination->getLimit()
        )->offset($pagination->getPage() * $pagination->pageSize)->orderBy(['addtime' => SORT_DESC])->all();
        $data = [];
        if (!empty($models)) {
            foreach ($models as $k => $model) {
                $data[$k]['e_name'] = $model->e_name;
                $data[$k]['addtime'] = date('Y-m-d H:i', strtotime($model->addtime));
                $data[$k]['money'] = $model->money;
                $data[$k]['status'] = $model->status;
            }
        }
        return [
            'models' => $data,
            'pages' => BaseLogic::instance()->pageFix($pagination),
            'total' => $this->getTotal($user)
        ];
    }

    /**
     * 体现申请
     *
     * @param User $user
     * @param $pData
     * @return boolean
     */
    public function cashApply($user, $pData)
    {
        if ($user->money < $pData['money']) {
            $this->setError('提现金额大于余额');
            $this->errorCode = 400;
            return false;
        }
        $model = new UserMoneyLog();
        $model->salesman_id = $user->id;
        $model->addtime = date('Y-m-d H:i:s');
        $model->type = 2;
        $model->status = 1;
        $model->money = $pData['money'];
        $model->e_name = '提现';
        $model->des = '提现';
        if (!$model->save()) {
            $this->setError('申请失败');
            $this->errorCode = 400;
            return false;
        }
        $user->money -= $model->money;
        $user->ice_money += $model->money;
        $user->save();
        return true;
    }
}