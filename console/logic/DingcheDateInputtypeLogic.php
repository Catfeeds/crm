<?php
/**
 * 订车 按照（渠道 + 日期 + 顾问）的统计逻辑层
 * 作    者：王雕
 * 功    能：订车 按照（渠道 + 日期 + 顾问）的统计逻辑层
 * 修改日期：2017-4-6
 */
namespace console\logic;
use Yii;
use common\models\Order;
use common\models\TjDingcheDateInputtype;
class DingcheDateInputtypeLogic extends BaseLogic
{
    /**
     * 功    能：按照（渠道 + 日期 + 顾问）统计订车数
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function countData()
    {
        $intRtn = 0;
        $intTodayTime = strtotime(date('Y-m-d'));//今天
        $arrWhere = [
            'and',
            ['>=', 'o.cai_wu_dao_zhang_time', $intTodayTime],//财务到账时间在今天
            ['<', 'o.cai_wu_dao_zhang_time', $intTodayTime + 86400],//财务到账时间在今天
        ];
        $strSelect = 'count(1) as num,o.salesman_id,c.clue_input_type as input_type_id,c.shop_id';
        $query = Order::find()->select($strSelect)->from('crm_order as o')
                ->leftJoin('crm_clue as c', 'o.clue_id=c.id')->where($arrWhere)
                ->groupBy('o.salesman_id,c.clue_input_type,c.shop_id');
        $arrListTmp = $query->asArray()->all();
        if(!empty($arrListTmp))
        {
            $this->strTableNow = TjDingcheDateInputtype::tableName();
            $this->arrUnsetKeys = ['salesman_id', 'input_type_id', 'create_date', 'shop_id'];
            $this->arrAddItems = ['create_date' => date('Y-m-d')];
            $arrList = $this->addUserOrgInfo($arrListTmp);
            //先清空今天的数据，再入库 - 事务
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();
            try
            {
                TjDingcheDateInputtype::updateAll(['num' => 0], " create_date='".date('Y-m-d')."' ");
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
