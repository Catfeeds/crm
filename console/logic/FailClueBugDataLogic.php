<?php
/**
 * 战败线索在线索表中存储的意向等级状态不是战败级的bug做个定时脚本去清除异常数据
 * 作    者：王雕
 * 功    能：战败线索在线索表中存储的意向等级状态不是战败级的bug做个定时脚本去清除异常数据
 * 修改日期：2017-6-23
 */
namespace console\logic;
use common\models\Clue;
use common\logic\TaskLogic;
class FailClueBugDataLogic extends BaseLogic
{
    /**
     * 功    能：战败线索在线索表中存储的意向等级状态不是战败级的bug做个定时脚本去清除异常数据
     * 作    者：王雕
     * 修改日期：2017-6-23
     */
    public function clearFailClueTask()
    {
        $intRtn = 0;
        $arrWhere = [
            'and',
            ['=', 'is_fail', 1],//1、战败了
            ['<>', 'intention_level_id', 7]//2、现在的意向等级不是战败级别
        ];
        $arrDatas = Clue::find()->where($arrWhere)->all();
        foreach($arrDatas as $objClue)
        {
            if(is_object($objClue))
            {
                $objClue->intention_level_id;
                $objClue->intention_level_des = '战败级';
                $objClue->intention_level_id = 7;
                if($objClue->save()){
                    $intRtn++;
                }
//                $objTaskLogic->exchangeIntentionUpdateTask($objClue->id, $oldIntentionId, 7);//在clue的model中已经有钩子，在数据变化之后会更新
            }
        }
        return $intRtn;
    }
}
