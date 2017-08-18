<?php
/**
 * 本月新增的意向数据统计逻辑层
 * 作    者：王雕
 * 功    能：本月新增的意向数据统计逻辑层
 * 修改日期：2017-4-6
 */
namespace console\logic;
use Yii;
use common\models\Clue;
use common\models\TjThisMonthIntention;
class ThisMonthIntentionLogic extends BaseLogic
{
    /**
     * 功    能：本月新增的意向数据 - 每天都跑多次
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function thisMonthIntention()
    {
        $intRtn = 0;
        $thisMonthStart = strtotime(date('Y-m'));
        $thisMonthEnd = strtotime(date('Y-m', strtotime('+ 1 month')));
        $arrWhere = [
            'and',
            ['>=', 'create_card_time', $thisMonthStart],//建卡时间为本月的，不考虑现在线索已经是哪个状态了
            ['<', 'create_card_time', $thisMonthEnd]//建卡时间为本月的，不考虑现在线索已经是哪个状态了
        ];
        $strSelect = 'count(1) as num, salesman_id,clue_input_type as input_type_id,shop_id ';
        $query = Clue::find()->select($strSelect)->where($arrWhere)->groupBy('salesman_id, clue_input_type,shop_id');
        $arrListTmp = $query->asArray()->all();
        if($arrListTmp)
        {
            $this->strTableNow = TjThisMonthIntention::tableName();
            $this->arrUnsetKeys = ['salesman_id', 'input_type_id', 'year_and_month', 'shop_id'];
            $this->arrAddItems = ['year_and_month' => date('Y-m')];
            $arrList = $this->addUserOrgInfo($arrListTmp);
            //先清当月数据，后入库，事务
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();
            try
            {
                TjThisMonthIntention::updateAll(['num' => 0], " year_and_month='".date('Y-m')."' ");
                $intRtn = $this->insertOrUpdateData($arrList);
                $transaction->commit();
                return $intRtn;
            } catch (Exception $ex) {
                $transaction->rollBack();
                return 0;
            }
        }
        return $intRtn;
    }
}
