<?php
/**
 * 意向等级的渠道分布数据统计逻辑层
 * 作    者：王雕
 * 功    能：意向等级的渠道分布数据统计逻辑层
 * 修改日期：2017-4-7
 */
namespace console\logic;
use common\models\Clue;
use common\models\TjIntentionLevelCount;
use common\models\Intention;
class IntentionLevelCountLogic extends BaseLogic
{
    
    /**
     * 统计意向等级分布
     */
    public function intentionLevel()
    {
        $intRtn = 0;
        //意向等级分析，预设意向等级不参与该数据分析
        $intentionIdsTmp = Intention::find()->select('id')->where(['is_special' => 0])->asArray()->all();
        $arrIntentionIds = array_map(function($v){return $v['id'];}, $intentionIdsTmp);
        if($arrIntentionIds)
        {
            $arrWhere = [
                'and',
                ['=', 'status', '1'],//意向客户
                ['<>', 'is_fail', 1],//非战败客户
                ['in', 'intention_level_id', $arrIntentionIds],//只统计非预设的意向等级
            ];
            $strSelect = 'count(1) as num, salesman_id,shop_id, clue_input_type as input_type_id,intention_level_id';
            $query = Clue::find()->select($strSelect)->where($arrWhere)->groupBy('salesman_id,clue_input_type,intention_level_id,shop_id');
            $arrListTmp = $query->asArray()->all();
            $arrList = $this->addUserOrgInfo($arrListTmp);
            $this->strTableNow = TjIntentionLevelCount::tableName();
            $intRtn = $this->deleteAndInsert($arrList, '');
        }
    }
    
    
    
}
