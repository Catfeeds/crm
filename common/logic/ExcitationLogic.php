<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/4/11
 * Time: 18:20
 */

namespace common\logic;


use common\models\Excitation;
use common\models\ExcitationLog;
use common\models\ExcitationShop;
use common\models\User;
use common\models\UserMoneyLog;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * 销售助手获取激励逻辑
 *
 * Class ExcitationLogic
 * @package common\logic
 */
class ExcitationLogic extends \backend\logic\ExcitationLogic
{
    /**
     * @param User $user
     * @param $typeId
     *
     * @return boolean
     * @throws Exception
     */
    public function addExcitationLog($user, $typeId)
    {
        $eIds = ArrayHelper::getColumn(Excitation::find()->where([
            'status' => 0,
        ])->all(), 'id');
        $excitationShop = ArrayHelper::map(ExcitationShop::find()->where(['in','e_id', $eIds])->all(),'shop_id', 'e_id');
        if(empty($excitationShop)) {
            $this->setError('没奖励');
            return false;
        }
        if(!$user->shop_id) {
            $this->setError('没奖励');
            return false;
        }
        $eId = isset($excitationShop[$user->shop_id]) ? $excitationShop[$user->shop_id] : 0;
        if (!$eId) {
            return false;
        }
        $Excitation = Excitation::findOne($eId);
        $sumMoney = ExcitationLog::find()->where(['e_id' => $eId])->sum('e_money');
        $label = $this->list[$typeId];
        $eMoney = $Excitation->$label;
        if($eMoney == 0) {
            $this->setError('没奖励');
            return false;
        }
        if($Excitation->money - $sumMoney < $eMoney ) {
            $this->setError('余额不足');
            return false;
        }
        $db = \Yii::$app->db;
        $transaction = $db->beginTransaction();
        try{
            $excitationLog = new ExcitationLog();
            $excitationLog->addtime = date('Y-m-d H:i:s');
            $excitationLog->area_id = $user->area_id;
            $excitationLog->company_id = $this->getCompanyId($user->area_id);
            $excitationLog->e_id = $Excitation->id;
            $excitationLog->e_money = $eMoney;
            $excitationLog->salesman_id = $user->id;
            $excitationLog->type_id = $typeId;
            $excitationLog->shop_id = $user->shop_id;
            if (!$excitationLog->save()) {
                new Exception('新增激励日志失败',$excitationLog->errors);
            }
            $userMoneyLog = new UserMoneyLog();
            $userMoneyLog->addtime = date('Y-m-d H:i:s');;
            $userMoneyLog->salesman_id = $user->id;
            $userMoneyLog->type = 1;
            $userMoneyLog->money = $eMoney;
            $userMoneyLog->e_name = $Excitation->name;
            $userMoneyLog->des = $this->listLabel[$typeId].'的奖励';
            $userMoneyLog->status = 0;
            if (!$userMoneyLog->save()) {
                new Exception('新增收入日志失败',$excitationLog->errors);
            }
            User::updateAllCounters(['money' => $eMoney],['id' => $user->id]);
            // 余额不足自动结束
            if ($Excitation->money - $sumMoney - $eMoney <= 0) {
                $Excitation->status = 1;
                $Excitation->end_time = date('Y-m-d H:i:s');
                if (!$Excitation->save()) {
                    new Exception('激励结束失败',$Excitation->errors);
                }
            }
            $transaction->commit();
            return true;

        } catch (Exception $exception){
            $transaction->rollBack();
            throw $exception;
        }
    }

    public $list = [
        1 => 'clue_price',
        2 => 'clue_to_intention_price',
        3 => 'new_intention_price',
        4 => 'finish_phone_task_price',
        5 => 'to_shop_price',
        6 => 'to_home_price',
        7 => 'dingche_price',
        8 => 'jiaoche_price'
    ];

    public $listLabel = [
        1 => '新增线索',
        2 => '线索转换',
        3 => '新增意向',
        4 => '完成电话任务',
        5 => '客户到店',
        6 => '上门拜访',
        7 => '客户订车',
        8 => '客户成交'
    ];
}