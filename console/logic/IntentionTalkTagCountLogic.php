<?php
/**
 * 当前的意向客户的商谈记录中的标签使用情况的统计
 * 作    者：王雕
 * 功    能：当前的意向客户的商谈记录中的标签使用情况的统计
 * 修改日期：2017-4-7
 */
namespace console\logic;
use Yii;
use common\models\Clue;
use common\models\Talk;
use common\models\TjIntentionTalkTagCount;
use common\logic\CompanyUserCenter;
class IntentionTalkTagCountLogic extends BaseLogic {
    
    //统计交谈中使用的标签
    public function countTalkTags()
    {
        $intRtn = 0;
        $arrWhere = [
            'and',
            ['=', 'status', '1'],//意向客户
            ['<>', 'is_fail', 1]//非战败客户
        ];
        $arrSalesIdsTmp = Clue::find()->select('salesman_id')->distinct()->where($arrWhere)->asArray()->all();
        $arrSalesIds = array_map(function($v){return $v['salesman_id'];}, $arrSalesIdsTmp);
        $strSelect = 'GROUP_CONCAT(select_tags) as select_tags,salesman_id,shop_id';
        $arrTmp = Talk::find()->select($strSelect)->where(['in', 'salesman_id', $arrSalesIds])->groupBy('salesman_id,shop_id')->asArray()->all();
        if($arrTmp)
        {
            $arrList = [];
            foreach($arrTmp as $val)
            {
                //将标签id统计出来各个标签id的使用次数
                $arrTagsTmp = array_filter( explode(',', $val['select_tags']) );
                $arrTags = array_count_values($arrTagsTmp);
                foreach($arrTags as $tagId => $num)
                {
                    unset($val['select_tags']);
                    $arrList[] = array_merge($val, ['tag_id' => $tagId, 'num' => $num]);//统计各个标签的使用次数
                }
            }
            $arrUpdate = $this->addUserOrgInfo($arrList);
            if($arrUpdate)
            {
                $db = Yii::$app->db;
                $transaction = $db->beginTransaction();
                try
                {
                    //入库 - 先清表 后插入
                    $strTable = TjIntentionTalkTagCount::tableName();
                    $arrColumns = array_keys($arrUpdate[0]);
                    //清表
                    Yii::$app->db->createCommand()->delete($strTable)->execute();
                    //插入
                    $intRtn = Yii::$app->db->createCommand()
                            ->batchInsert($strTable, $arrColumns, $arrUpdate)->execute();
                    $transaction->commit();
                    return $intRtn;
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return 0;
                }
            }
        }
        return $intRtn;
    }
    
    
}
