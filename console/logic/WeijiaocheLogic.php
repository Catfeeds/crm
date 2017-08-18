<?php
/**
 * 未交车数据统计逻辑层
 * 作    者：王雕
 * 功    能：未交车数据统计逻辑层
 * 修改日期：2017-4-6
 */
namespace console\logic;
use common\models\Order;
use common\models\TjWeijiaoche;
class WeijiaocheLogic extends BaseLogic
{
    /**
     * 功    能：未交车 - 未交车列表（已经下过单，订单状态为财务到账）
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function weijiaoche()
    {
        $arrWhere = [
            'and',
            ['>', 'cai_wu_dao_zhang_time', 0],//财务到账
            ['<>', 'status', 6],//但是还没有交车
            ['<>', 'status', 4],//战败的不算
        ];
        $strSelect = ' count(1) as num, salesman_id,shop_id ';
        $query = Order::find()->select($strSelect)->where($arrWhere)->groupBy('salesman_id,shop_id');
        $arrListTmp = $query->asArray()->all();
        $this->strTableNow = TjWeijiaoche::tableName();
        $arrList = $this->addUserOrgInfo($arrListTmp);
        $intRtn = $this->deleteAndInsert($arrList, '');//先删除后入库
        return $intRtn;
    }
}
