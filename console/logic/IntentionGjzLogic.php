<?php
/**
 * 跟进中的意向统计逻辑层
 * 作    者：王雕
 * 功    能：跟进中的意向统计逻辑层
 * 修改日期：2017-4-6
 */
namespace console\logic;
use common\models\Clue;
use common\models\TjIntentionGenjinzhong;
class IntentionGjzLogic extends BaseLogic
{
    /**
     * 功    能：跟进中的意向 - 无时间维度
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function gjzIntention()
    {
        $intRtn = 0;
        $arrWhere = [
            'and',
            ['=', 'status', 1],//意向客户
            ['=', 'is_fail', 0],//没有战败
        ];
        $strSelect = ' count(1) as num, salesman_id, shop_id ';
        $query = Clue::find()->select($strSelect)
                    ->where($arrWhere)->groupBy('salesman_id, shop_id');
        $arrListTmp = $query->asArray()->all();
        if($arrListTmp)
        {
            $this->strTableNow = TjIntentionGenjinzhong::tableName();
            $this->arrUnsetKeys = ['salesman_id', 'shop_id'];
            $arrList = $this->addUserOrgInfo($arrListTmp);
            $intRtn = $this->deleteAndInsert($arrList, '');
        }
        return $intRtn;
    }
}
