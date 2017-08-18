<?php
/**
 * 上月结余的意向数据统计逻辑层
 * 作    者：王雕
 * 功    能：上月结余的意向数据统计逻辑层
 * 修改日期：2017-4-6
 */
namespace console\logic;
use Yii;
use common\models\Clue;
use common\models\TjLastMonthIntention;
class LastMonthIntentionLogic extends BaseLogic
{
    /**
     * 功    能：月末跑一次，记录本月的意向剩余数据
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function lastMonthIntention()
    {
        $intRtn = 0;
        $arrWhere = [
            'and',
            ['<>', 'is_fail', 1],//未战败
            ['=', 'status', 1]//意向客户
        ];
        $strSelect = 'count(1) as num, salesman_id,clue_input_type as input_type_id,shop_id ';
        $query = Clue::find()->select($strSelect)->where($arrWhere)->groupBy('salesman_id, clue_input_type, shop_id');
        $arrListTmp = $query->asArray()->all();
        if($arrListTmp)
        {
            $this->strTableNow = TjLastMonthIntention::tableName();
            $this->arrUnsetKeys = ['salesman_id', 'input_type_id', 'year_and_month', 'shop_id'];
            $this->arrAddItems = ['year_and_month' => date('Y-m')];
            $arrList = $this->addUserOrgInfo($arrListTmp);
            //事务处理，此处本来用不着清除当月的数据的，但怕脚本运行一月内运行多次，做先清后入的机制
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();
            try
            {
                TjLastMonthIntention::updateAll(['num' => 0], " year_and_month='".date('Y-m')."' ");
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
