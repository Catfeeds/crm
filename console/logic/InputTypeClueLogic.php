<?php
/**
 * 该功能已废弃，相关数据改为实时查表
 * 
 * 按照渠道分析线索数据统计逻辑层 （总线索 无效线索  已转化线索）
 * 作    者：王雕
 * 功    能：按照渠道分析线索数据统计逻辑层 （总线索 无效线索  已转化线索）
 * 修改日期：2017-4-7
 */
namespace console\logic;
use common\models\Clue;
use common\models\TjInputtypeclueAll;
use common\models\TjInputtypeclueFail;
use common\models\TjInputtypeclueZhuanhua;
class InputTypeClueLogic extends BaseLogic
{    
    /**
     * 功    能：线索 - 总线索 - 分配时间等于今天的
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function allClue()
    {
        return 0;
        $this->strTableNow = TjInputtypeclueAll::tableName();
        $intTodayTime = strtotime(date('Y-m-d'));//今天
        $arrWhere = [
            'and',
            ['>', 'salesman_id', 0],//有销售顾问跟进
//            ['=', 'status', 0],//线索客户
            ['>=', 'assign_time', $intTodayTime],//今天分配的
            ['<', 'assign_time', $intTodayTime + 86400]//今天分配的
        ];
        $strSelect = 'count(1) as num, salesman_id, clue_input_type as input_type_id';
        $query = Clue::find()->select($strSelect)->where($arrWhere)->groupBy('salesman_id, clue_input_type');
        $arrListTmp = $query->asArray()->all();
        $this->arrUnsetKeys = ['salesman_id', 'create_date', 'input_type_id'];
        $this->arrAddItems = ['create_date' => date('Y-m-d')];
        $arrList = $this->addUserOrgInfo($arrListTmp);
        $intRtn = $this->insertOrUpdateData($arrList);
        return $intRtn;
    }
    
    /**
     * 功    能：无效线索 - 战败时间等于今天的线索
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function failClue()
    {
        return 0;
        $this->strTableNow = TjInputtypeclueFail::tableName();
        $intTodayTime = strtotime(date('Y-m-d'));//今天
        $arrWhere = [
            'and',
            ['>', 'salesman_id', 0],//有销售顾问跟进
            ['=', 'status', 0],//线索客户
            ['=', 'is_fail', 1],//战败的
            ['>=', 'last_fail_time', $intTodayTime],//今天战败的
            ['<', 'last_fail_time', $intTodayTime + 86400]//今天战败的
        ];
        $strSelect = 'count(1) as num, salesman_id, clue_input_type as input_type_id';
        $query = Clue::find()->select($strSelect)->where($arrWhere)->groupBy('salesman_id, clue_input_type');
        $arrListTmp = $query->asArray()->all();
        $this->arrUnsetKeys = ['salesman_id', 'create_date', 'input_type_id'];
        $this->arrAddItems = ['create_date' => date('Y-m-d')];
        $arrList = $this->addUserOrgInfo($arrListTmp);
        $intRtn = $this->insertOrUpdateData($arrList);
        return $intRtn;
    }
    
    /**
     * 功    能：已转化线索 - 建卡时间是今天
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function zhuanhuaClue()
    {
        return 0;
        $this->strTableNow = TjInputtypeclueZhuanhua::tableName();
        $intTodayTime = strtotime(date('Y-m-d'));//今天
        $arrWhere = [
            'and',
            ['>', 'salesman_id', 0],//有销售顾问跟进
            ['>', 'status', 0],//状态已经不是线索了已经转化过了
            ['>=', 'create_card_time', $intTodayTime],//建卡时间是今天
            ['<', 'create_card_time', $intTodayTime + 86400]//建卡时间是今天
        ];
        $strSelect = 'count(1) as num, salesman_id, clue_input_type as input_type_id';
        $query = Clue::find()->select($strSelect)->where($arrWhere)->groupBy('salesman_id, clue_input_type');
        $arrListTmp = $query->asArray()->all();
        $this->arrUnsetKeys = ['salesman_id', 'create_date', 'input_type_id'];
        $this->arrAddItems = ['create_date' => date('Y-m-d')];
        $arrList = $this->addUserOrgInfo($arrListTmp);
        $intRtn = $this->insertOrUpdateData($arrList);
        return $intRtn;
    }
}
