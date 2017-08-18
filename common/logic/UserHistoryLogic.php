<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/4/17
 * Time: 10:25
 */

namespace common\logic;


use common\models\Clue;
use common\models\User;
use common\models\UserHistoryClue;
use common\server\Logic;

/**
 * 历史客户逻辑
 *
 * Class UserHistoryLogic
 * @package common\logic
 */
class UserHistoryLogic extends Logic
{
    /**
     * 增加历史客户
     *
     * @param Clue $clue
     * @param string $reason 重新分配原因
     * @param int $operatorId 操作人id
     * @param string $operatorName 操作人
     *
     * @return boolean
     */
    public function addUserHistory($clue, $reason, $operatorId, $operatorName)
    {
        if (!$operatorName || !$operatorId || !$reason || !is_object($clue)) {
            $this->setError('参数错误');
            return false;
        }
        $model = new UserHistoryClue();
        $model->clue_id = $clue->id;
        $model->customer_id = $clue->customer_id;
        $model->salesman_id = $clue->salesman_id;
        $model->reason = $reason;
        $model->operator_id = $operatorId;
        $model->operator_name = $operatorName;
        $model->create_time = time();
        if ($model->save()) {
            $this->setError('历史客户保存失败');
            return false;
        }
        return true;
    }
}