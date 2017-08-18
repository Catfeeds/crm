<?php
/**
 * 跟进中的线索统计逻辑层
 * 作    者：王雕
 * 功    能：跟进中的线索统计逻辑层
 * 修改日期：2017-4-6
 */
namespace console\logic;
use common\models\Clue;
use common\models\TjClueGenjinzhong;
class ClueGjzLogic extends BaseLogic
{
    /**
     * 功    能：跟进中的线索 - 无时间维度
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function gjzClue()
    {
        $intRtn = 0;
        $arrWhere = [
            'and',
            ['=', 'status', 0],//1、线索状态 status = 0
            ['=', 'is_assign', 1]//2、被分配过 is_assign  = 1
        ];
        $strSelect = ' count(1) as num, salesman_id, shop_id ';
        $query = Clue::find()->select($strSelect)
                ->where($arrWhere)->groupBy('salesman_id, shop_id');
        $arrListTmp = $query->asArray()->all();
        if($arrListTmp)
        {
            $this->strTableNow = TjClueGenjinzhong::tableName();
            $this->arrUnsetKeys = ['salesman_id', 'shop_id'];
            $arrList = $this->addUserOrgInfo($arrListTmp);
            $intRtn = $this->deleteAndInsert($arrList, '');//先删除后入库 - 里面有事务机制
        }
        return $intRtn;
    }
}
