<?php
/**
 * 订车成交期统计逻辑层
 * 作    者：王雕
 * 功    能：订车成交期统计逻辑层
 * 修改日期：2017-4-6
 */
namespace console\logic;
use Yii;
use common\models\Order;
use common\models\TjDingcheDateCount;
class DingcheDateCountLogic extends BaseLogic
{
    
    /**
     * 功    能：订车成交期统计 建卡时间与订车财务到账时间之差
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function dateCount()
    {
        $intRtn = 0;
        $intTodayTime = strtotime(date('Y-m-d'));//今天
        $arrWhere = [
            'and',
            ['>=', 'o.cai_wu_dao_zhang_time', $intTodayTime],//财务到账时间在今天
            ['<', 'o.cai_wu_dao_zhang_time', $intTodayTime + 86400],//财务到账时间在今天
        ];
        $strSelect = 'ceil( (o.cai_wu_dao_zhang_time - c.create_card_time)/86400 ) as date_count,o.salesman_id,c.shop_id';
        $query = Order::find()->select($strSelect)->from('crm_order as o')
                ->leftJoin('crm_clue as c', 'o.clue_id=c.id')->where($arrWhere);
        $arrTmp = $query->asArray()->all();
        $arrListTmp = [];
        foreach($arrTmp as $val)
        {
            $intDateType = $this->getDateType(intval($val['date_count']));
            $strKey = $val['salesman_id'] . '_' . $intDateType;
            if(isset($arrListTmp[$strKey]))
            {
                $arrListTmp[$strKey]['num']++;
            }
            else
            {
                $arrListTmp[$strKey] = [
                    'shop_id'       => $val['shop_id'],
                    'salesman_id'   => $val['salesman_id'],
                    'date_type'     => $intDateType,
                    'num'           => 1,
                ];
            }
        }
        if(!empty($arrListTmp))
        {
            $arrListT = array_values($arrListTmp);
            $this->strTableNow = TjDingcheDateCount::tableName();
            $this->arrUnsetKeys = ['salesman_id', 'create_date', 'shop_id'];
            $this->arrAddItems = ['create_date' => date('Y-m-d')];
            $arrList = $this->addUserOrgInfo($arrListT);
            //先清空今天的数据，再入库 - 事务
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();
            try
            {
                TjDingcheDateCount::updateAll(['num' => 0], " create_date='".date('Y-m-d')."' ");
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
    
    /**
     * 功    能：根据 （财务到账时间 - 建卡时间）/ 86400 天计算成交期，之后格式化成几个成交期区间
     * 参    数：$intDateCount  int         成交期 - 天数
     * 返    回：$intType       int         成交期所在的区间
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    private function getDateType($intDateCount)
    {
        if($intDateCount <= 7) //(1 - 7] 1-7天
        {
            $intType = 1;
        }
        else if($intDateCount <= 14) //(7 - 14] 7-14天
        {
            $intType = 2;
        }
        else if($intDateCount <= 14) //(14 - 30] 1个月内
        {
            $intType = 3;
        }
        else if($intDateCount <= 14) //(30 - 60] 2个月内
        {
            $intType = 4;
        }
        else //2个月以上
        {
            $intType = 5;
        }
        return $intType;
    }
}
