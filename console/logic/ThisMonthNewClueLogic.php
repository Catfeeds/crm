<?php
/**
 * 当月新增线索（销售顾问 + 渠道+ 月份）数据统计逻辑层
 * 作    者：王雕
 * 功    能：当月新增线索（销售顾问 + 渠道+ 月份）数据统计逻辑层
 * 修改日期：2017-4-6
 */
namespace console\logic;
use Yii;
use common\models\Clue;
use common\models\TjThisMonthNewClue;
class ThisMonthNewClueLogic extends BaseLogic
{    
    /**
     * 功    能：统计当月的新增线索 - 按照渠道划分
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function countThisMonthNewClue()
    {
        $thisMonthStart = strtotime(date('Y-m'));//本月初时间戳
        $thisMonthEnd = strtotime(date('Y-m', strtotime('+ 1 month'))); //本月末时间戳
        
        //本月新增线索 - clue表 和 clue_wuxiao表中的当月的新建线索
        //clue表中的新建线索
        $sql1 = " SELECT id,salesman_id,shop_id,clue_input_type as input_type_id FROM crm_clue WHERE create_time >={$thisMonthStart} and create_time < {$thisMonthEnd} ";
        //无效线索的log表crm_clue_wuxiao中的数据
        $sql2 = " SELECT id,salesman_id,shop_id,clue_input_type as input_type_id FROM crm_clue_wuxiao WHERE create_time >={$thisMonthStart} and create_time < {$thisMonthEnd} ";
        $sql = " SELECT count(1) as num,input_type_id, salesman_id,shop_id from ( {$sql1} union {$sql2}) as tmp group by salesman_id,shop_id,input_type_id";
        $arrListTmp = Yii::$app->db->createCommand($sql)->queryAll();
        $this->arrAddItems = ['year_and_month' => date('Y-m')];
        $this->strTableNow = TjThisMonthNewClue::tableName();
        $this->arrUnsetKeys = ['salesman_id', 'input_type_id', 'year_and_month', 'shop_id'];
        $arrList = $this->addUserOrgInfo($arrListTmp);
        //先清空今天的新增线索数字段的数据，再入库 - 事务
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try
        {
            TjThisMonthNewClue::updateAll(['num' => 0], " year_and_month='".date('Y-m')."' ");
            $intRtn = $this->insertOrUpdateData($arrList);
            $transaction->commit();
            return $intRtn;
        } catch (Exception $ex) {
            $transaction->rollBack();
            return 0;
        }
        return $intRtn;
    }
}
