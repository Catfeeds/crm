<?php
/**
 * 订车数统计逻辑层
 * 作    者：王雕
 * 功    能：订车数统计逻辑层
 * 修改日期：2017-4-6
 */
namespace console\logic;
use Yii;
use common\models\Order;
use common\models\TjDingcheNum;
class DingcheNumLogic extends BaseLogic
{
    /**
     * 功    能：订车数 - 按照组织架构id + 顾问id + 渠道类型聚合
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function dingche()
    {
        $intRtn = 0;
        $thisMonthStart = strtotime(date('Y-m'));
        $thisMonthEnd = strtotime(date('Y-m', strtotime('+ 1 month')));
        $arrWhere = [
            'and',
            ['>=', 'o.cai_wu_dao_zhang_time', $thisMonthStart],//财务到账时间在本月
            ['<', 'o.cai_wu_dao_zhang_time', $thisMonthEnd],//财务到账时间在本月
        ];
        $strSelect = 'count(1) as num,o.salesman_id,c.clue_input_type as input_type_id,c.shop_id';
        $query = Order::find()->select($strSelect)
                ->from('crm_order as o')->leftJoin('crm_clue as c', 'o.clue_id=c.id')
                ->where($arrWhere)->groupBy('o.salesman_id,c.clue_input_type,c.shop_id');
        $arrListTmp = $query->asArray()->all();
        if($arrListTmp)
        {
            $this->strTableNow = TjDingcheNum::tableName();
            $this->arrUnsetKeys = ['salesman_id', 'year_and_month', 'input_type_id', 'shop_id'];
            $this->arrAddItems = ['year_and_month' => date('Y-m')];
            $arrList = $this->addUserOrgInfo($arrListTmp);
            //先清空本月的数据，再入库 - 事务
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();
            try
            {
                TjDingcheNum::updateAll(['num' => 0], " year_and_month='". date('Y-m') ."' ");
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
