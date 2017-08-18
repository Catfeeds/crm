<?php
/**
 * 转化漏斗数据统计逻辑层
 * 作    者：王雕
 * 功    能：转化漏斗数据统计逻辑层
 * 修改日期：2017-4-7
 */
namespace console\logic;
use Yii;
use common\models\Clue;
use common\models\Order;
use common\models\TjZhuanhualoudou;
class ZhuanhualoudouLogic extends BaseLogic
{
    public function __construct() {
        $this->strTableNow = TjZhuanhualoudou::tableName();
        $this->arrUnsetKeys = ['salesman_id', 'create_date', 'input_type_id', 'shop_id'];
        $this->arrAddItems = ['create_date' => date('Y-m-d')];
    }
    
    /**
     * 新增线索数统计
     */
    public function newClueNum()
    {
        
        $intTodayTime = strtotime(date('Y-m-d'));//今天00:00:00
        $intTodayEnd = $intTodayTime + 86400;//今天23:59:59
        //clue表中的新建线索
        $sql1 = " SELECT id,salesman_id,clue_input_type as input_type_id,shop_id FROM crm_clue WHERE create_time >={$intTodayTime} and create_time < {$intTodayEnd} ";
        //无效线索的log表crm_clue_wuxiao中的数据
        $sql2 = " SELECT id,salesman_id,clue_input_type as input_type_id,shop_id FROM crm_clue_wuxiao WHERE create_time >={$intTodayTime} and create_time < {$intTodayEnd} ";
        $sql = " SELECT count(1) as new_clue_num,input_type_id, salesman_id,shop_id from ( {$sql1} union {$sql2}) as tmp group by salesman_id,shop_id";
        $arrListTmp = Yii::$app->db->createCommand($sql)->queryAll();
        $arrList = $this->addUserOrgInfo($arrListTmp);
        //先清空今天的数据，再入库 - 事务
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try
        {
            TjZhuanhualoudou::updateAll(['new_clue_num' => 0], " create_date='".date('Y-m-d')."' ");
            $intRtn = $this->insertOrUpdateData($arrList);
            $transaction->commit();
            return $intRtn;
        } catch (Exception $ex) {
            $transaction->rollBack();
            return 0;
        }
    }
    
    
    /**
     * 新增意向数统计
     */
    public function newIntentionNum()
    {
        $intTodayTime =  strtotime(date('Y-m-d'));//今天
        $arrWhere = [
            'and',
            ['>=', 'create_card_time', $intTodayTime],//建卡时间为今天
            ['<', 'create_card_time', $intTodayTime + 86400],//建卡时间为今天
        ];
        $strSelect = 'count(1) as new_intention_num, salesman_id, clue_input_type as input_type_id, shop_id';//意向客户数
        $query = Clue::find()->select($strSelect)->where($arrWhere)->groupBy('salesman_id, clue_input_type, shop_id');
        $arrListTmp = $query->asArray()->all();
        $arrList = $this->addUserOrgInfo($arrListTmp);
        //先清空今天的数据，再入库 - 事务
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try
        {
            TjZhuanhualoudou::updateAll(['new_intention_num' => 0], " create_date='".date('Y-m-d')."' ");
            $intRtn = $this->insertOrUpdateData($arrList);
            $transaction->commit();
            return $intRtn;
        } catch (Exception $ex) {
            $transaction->rollBack();
            return 0;
        }
    }
    
    /**
     * 到店数统计
     */
    public function toShopNum()
    {
        //商谈记录表中查询数据得到子表
        $strTalkSql = "SELECT DISTINCT clue_id,salesman_id,shop_id FROM crm_talk  WHERE talk_type IN(5, 6, 7) AND talk_date = '" . date('Y-m-d') . "'";
        //连clue表   按照门店，渠道信息和销售顾问groupby聚合
        $strSelect = 'count(1) as to_shop_num, t.salesman_id,t.shop_id, c.clue_input_type as input_type_id';
        $strSql = "SELECT {$strSelect} FROM ( {$strTalkSql} ) as t LEFT JOIN crm_clue AS c ON t.clue_id = c.id GROUP BY	t.salesman_id, c.clue_input_type, t.shop_id";
        $arrListTmp = Yii::$app->db->createCommand($strSql)->queryAll();
        $arrList = $this->addUserOrgInfo($arrListTmp);
        //先清空今天的数据，再入库 - 事务
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try
        {
            TjZhuanhualoudou::updateAll(['to_shop_num' => 0], " create_date='".date('Y-m-d')."' ");
            $intRtn = $this->insertOrUpdateData($arrList);
            $transaction->commit();
            return $intRtn;
        } catch (Exception $ex) {
            $transaction->rollBack();
            return 0;
        }
    }
    
    
    /**
     * 订车数统计
     */
    public function dingcheNum()
    {
        $intTodayTime = strtotime(date('Y-m-d'));//今天
        $arrWhere = [
            'and',
            ['>=', 'o.cai_wu_dao_zhang_time', $intTodayTime],//财务到账时间为今天
            ['<', 'o.cai_wu_dao_zhang_time', $intTodayTime + 86400],//财务到账时间为今天
        ];
        $strSelect = 'count(1) as dingche_num, o.salesman_id, c.clue_input_type as input_type_id, o.shop_id';//订车数
        $query = Order::find()->select($strSelect)
                ->from('crm_order as o')
                ->leftJoin('crm_clue as c', 'o.clue_id=c.id')
                ->where($arrWhere)->groupBy('o.salesman_id, c.clue_input_type, o.shop_id');
        $arrListTmp = $query->asArray()->all();
        $arrList = $this->addUserOrgInfo($arrListTmp);
        //先清空今天的数据，再入库 - 事务
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try
        {
            TjZhuanhualoudou::updateAll(['dingche_num' => 0], " create_date='".date('Y-m-d')."' ");
            $intRtn = $this->insertOrUpdateData($arrList);
            $transaction->commit();
            return $intRtn;
        } catch (Exception $ex) {
            $transaction->rollBack();
            return 0;
        }
    }
}
