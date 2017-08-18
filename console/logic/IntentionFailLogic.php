<?php
/**
 * 销售顾问的基本数据统计逻辑层
 * 作    者：王雕
 * 功    能：销售顾问的基本数据统计逻辑层
 * 修改日期：2017-4-5
 */
namespace console\logic;
use Yii;
use common\models\Clue;
use common\models\TjFailIntentionTagCount;
class IntentionFailLogic extends BaseLogic
{
    /**
     * 功    能：意向战败 - 战败标签统计
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function countIntentionFailTag()
    {
        $intRtn = 0;
        $intTodayTime = strtotime(date('Y-m-d'));//今天
        $arrWhere = [
            'and',
            ['=', 'status', 1],//状态为意向
            ['=', 'is_fail', 1],//战败的
            ['>=', 'last_fail_time', $intTodayTime],
            ['<', 'last_fail_time', $intTodayTime + 86400],
            //战败时间为今天
        ];
        $strSelect  = 'GROUP_CONCAT(fail_tags) as fail_tags,shop_id,salesman_id,clue_input_type as input_type_id';
        $arrTmp = Clue::find()->select($strSelect)
                ->where($arrWhere)->groupBy('salesman_id,clue_input_type,shop_id')->asArray()->all();
        if($arrTmp)
        {
            foreach($arrTmp as $val)
            {
                //将标签id统计出来各个战败的标签id的使用次数
                $arrTagsTmp = array_filter( explode(',', $val['fail_tags']) );
                $arrTags = array_count_values($arrTagsTmp);
                foreach($arrTags as $tagId => $num)
                {
                    unset($val['fail_tags']);
                    $arrList[] = array_merge($val, ['tag_id' => $tagId, 'num' => $num, 'fail_type' =>2]);//意向战败
                }
            }
            $this->strTableNow = TjFailIntentionTagCount::tableName();
            $this->arrUnsetKeys = ['salesman_id', 'create_date', 'input_type_id', 'tag_id', 'shop_id'];
            $this->arrAddItems = ['create_date' => date('Y-m-d')];
            $arrList = $this->addUserOrgInfo($arrList);
            //先清空今天的数据，再入库 - 事务
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();
            try
            {
                TjFailIntentionTagCount::updateAll(['num' => 0], " create_date='".date('Y-m-d')."'  AND fail_type=2 ");
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
     * 功    能：订车战败 - 战败标签统计
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function countDingcheFailTag()
    {
        $intRtn = 0;
        $intTodayTime = strtotime(date('Y-m-d'));//今天
        $arrWhere = [
            'and',
            ['=', 'status', 2],//状态为订车客户
            ['=', 'is_fail', 1],//战败的
            ['>=', 'last_fail_time', $intTodayTime],
            ['<', 'last_fail_time', $intTodayTime + 86400],
            //战败时间为今天
        ];
        $strSelect  = 'GROUP_CONCAT(fail_tags) as fail_tags,shop_id,salesman_id,clue_input_type as input_type_id';
        $arrTmp = Clue::find()->select($strSelect)
                ->where($arrWhere)->groupBy('salesman_id,clue_input_type,shop_id')->asArray()->all();
        if($arrTmp)
        {
            foreach($arrTmp as $val)
            {
                //将标签id统计出来各个战败的标签id的使用次数
                $arrTagsTmp = array_filter( explode(',', $val['fail_tags']) );
                $arrTags = array_count_values($arrTagsTmp);
                foreach($arrTags as $tagId => $num)
                {
                    unset($val['fail_tags']);
                    $arrList[] = array_merge($val, ['tag_id' => $tagId, 'num' => $num, 'fail_type' =>3]);//订车战败
                }
            }
            $this->strTableNow = TjFailIntentionTagCount::tableName();
            $this->arrUnsetKeys = ['salesman_id', 'create_date', 'input_type_id', 'tag_id', 'shop_id'];
            $this->arrAddItems = ['create_date' => date('Y-m-d')];
            $arrList = $this->addUserOrgInfo($arrList);
            //先清空今天的数据，再入库 - 事务
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();
            try
            {
                TjFailIntentionTagCount::updateAll(['num' => 0], " create_date='".date('Y-m-d')."' AND fail_type=3  ");
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
