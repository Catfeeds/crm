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
use common\models\Order;
use common\models\Task;
use \common\models\Talk;
use common\models\TjJichushuju;
class JichushujuLogic extends BaseLogic 
{
    /**
     * 构造 INSERT INTO ......  ON DUPLICATE UPDATE 方式更新数据的sql的时候用到的一些初始化数据
     */
    public function __construct() 
    {
        $this->strTableNow = TjJichushuju::tableName();
        //表索引调整 - salesman_id shop_id create_date 索引  多加一个shop_id
        $this->arrUnsetKeys = ['salesman_id', 'create_date', 'shop_id'];
        $this->arrAddItems = ['create_date' => date('Y-m-d')];
    }
    
    /**
     * 功    能：交车客户数（成交数）
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function jiaoChe()
    {
        $intTodayTime = strtotime(date('Y-m-d'));//今天
        $arrWhere = [
            'and',
            ['>=', 'car_delivery_time', $intTodayTime],//交车时间为今天
            ['<', 'car_delivery_time', $intTodayTime + 86400],//交车时间为今天
        ];
        $strSelect = 'count(1) as chengjiao_num, salesman_id, shop_id';//交车数
        $query = Order::find()->select($strSelect)->where($arrWhere)->groupBy('salesman_id, shop_id');
        $arrListTmp = $query->asArray()->all();
        $arrList = $this->addUserOrgInfo($arrListTmp);
        //先清空今天的数据，再入库 - 事务
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try
        {
            TjJichushuju::updateAll(['chengjiao_num' => 0], " create_date='".date('Y-m-d')."' ");
            $intRtn = $this->insertOrUpdateData($arrList);
            $transaction->commit();
            return $intRtn;
        } catch (Exception $ex) {
            $transaction->rollBack();
            return 0;
        }
    }
    
    /**
     * 功    能：订车数 - 订单状态为财务到账 - 不考虑战败因素
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function dingche($strDate = '')
    {
        if($strDate && preg_match('/\d{4}-\d{2}-\d{1,2}/', $strDate))
        {
            $intTodayTime = strtotime($strDate);//今天
        }
        else
        {
            $intTodayTime = strtotime(date('Y-m-d'));//今天
        }        
        $arrWhere = [
            'and',
            ['>=', 'cai_wu_dao_zhang_time', $intTodayTime],//财务到账时间为今天
            ['<', 'cai_wu_dao_zhang_time', $intTodayTime + 86400],//财务到账时间为今天
        ];
        $strSelect = 'count(1) as ding_che_num, salesman_id, shop_id';//订车数
        $query = Order::find()->select($strSelect)->where($arrWhere)->groupBy('salesman_id, shop_id');
        $arrListTmp = $query->asArray()->all();
        $this->arrAddItems = ['create_date' => date('Y-m-d', $intTodayTime)];//后门
        $arrList = $this->addUserOrgInfo($arrListTmp);
        //先清空今天的数据，再入库 - 事务
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try
        {
            TjJichushuju::updateAll(['ding_che_num' => 0], " create_date='".date('Y-m-d', $intTodayTime)."' ");
            $intRtn = $this->insertOrUpdateData($arrList);
            $transaction->commit();
            return $intRtn;
        } catch (Exception $ex) {
            $transaction->rollBack();
            return 0;
        }
    }
    
    /**
     * 功    能：新增线索数
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function newClue()
    {
        /**
         * 新增线索数，创建时间为今天的，没有分配的也要统计
         */
        $intTodayTime = strtotime(date('Y-m-d'));//今天00:00:00
        $intTodayEnd = $intTodayTime + 86400;//今天23:59:59
        //clue表中的新建线索
        $sql1 = " SELECT id,salesman_id,shop_id FROM crm_clue WHERE create_time >={$intTodayTime} and create_time < {$intTodayEnd} ";
        //无效线索的log表crm_clue_wuxiao中的数据
        $sql2 = " SELECT id,salesman_id,shop_id FROM crm_clue_wuxiao WHERE create_time >={$intTodayTime} and create_time < {$intTodayEnd} ";
        $sql = " SELECT count(1) as new_clue_num, salesman_id,shop_id from ( {$sql1} union {$sql2}) as tmp group by salesman_id,shop_id";
        $arrListTmp = Yii::$app->db->createCommand($sql)->queryAll();
        $arrList = $this->addUserOrgInfo($arrListTmp);
        
        //先清空今天的新增线索数字段的数据，再入库 - 事务
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try
        {
            TjJichushuju::updateAll(['new_clue_num' => 0], " create_date='".date('Y-m-d')."' ");
            $intRtn = $this->insertOrUpdateData($arrList);
            $transaction->commit();
            return $intRtn;
        } catch (Exception $ex) {
            $transaction->rollBack();
            return 0;
        }
    }
    
    
    /**
     * 功    能：新增意向客户数 - 不考虑战败因素
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function newIntentionClue()
    {
        $intTodayTime = strtotime(date('Y-m-d'));//今天
        $arrWhere = [
            'and',
            ['>=', 'create_card_time', $intTodayTime],//建卡时间为今天
            ['<', 'create_card_time', $intTodayTime + 86400],//建卡时间为今天
        ];
        $strSelect = 'count(1) as new_intention_num, salesman_id, shop_id';//意向客户数
        $query = Clue::find()->select($strSelect)->where($arrWhere)->groupBy('salesman_id,shop_id');
        $arrListTmp = $query->asArray()->all();
        $arrList = $this->addUserOrgInfo($arrListTmp);
        //先清空今天的新增线索数字段的数据，再入库 - 事务
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try
        {
            TjJichushuju::updateAll(['new_intention_num' => 0], " create_date='".date('Y-m-d')."' ");
            $intRtn = $this->insertOrUpdateData($arrList);
            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollBack();
            return 0;
        }
        return $intRtn;
    }
    
    /**
     * 功    能：战败客户数 - 统计每天的，不考虑战败激活后 一段时间后又战败对时间段内的统计数据的影响
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function failCustomerNum()
    {
        $intTodayTime = strtotime(date('Y-m-d'));//今天
        //今天战败过就算，不管之后是否激活
        $arrWhere = [
            'and',
            ['>=', 'last_fail_time', $intTodayTime],//战败时间为今天
            ['<', 'last_fail_time', $intTodayTime + 86400],//战败时间为今天
        ];
        $strSelect = 'count(1) as fail_num, salesman_id, shop_id';//战败客户数 - 线索战败  意向战败 订车状态战败
        $query = Clue::find()->select($strSelect)->where($arrWhere)->groupBy('salesman_id, shop_id');
        $arrListTmp = $query->asArray()->all();
        $arrList = $this->addUserOrgInfo($arrListTmp);
        //先清空今天的新增线索数字段的数据，再入库 - 事务
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try
        {
            TjJichushuju::updateAll(['fail_num' => 0], " create_date='".date('Y-m-d')."' ");
            $intRtn = $this->insertOrUpdateData($arrList);
            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollBack();
            return 0;
        }
        return $intRtn;
    }
    
    /**
     * 功    能：电话任务数
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function phoneTask()
    {
        $arrWhere = [
            'and',
            ['=', 'task_type', 1],//电话任务
            ['=', 'task_date', date('Y-m-d')]//时间等于今天
        ];
        $strSelect = 'count(1) as phone_task_num, salesman_id, shop_id';//电话任务数
        $query = Task::find()->select($strSelect)->where($arrWhere)->groupBy('salesman_id, shop_id');
        $arrListTmp = $query->asArray()->all();
        $arrList = $this->addUserOrgInfo($arrListTmp);
        //先清空今天的数据，再入库 - 事务
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try
        {
            TjJichushuju::updateAll(['phone_task_num' => 0], " create_date='".date('Y-m-d')."' ");
            $intRtn = $this->insertOrUpdateData($arrList);
            $transaction->commit();
            return $intRtn;
        } catch (Exception $ex) {
            $transaction->rollBack();
            return 0;
        }
    }
    
    /**
     * 功    能：取消电话任务数
     * 作    者：王雕
     * 修改日期：2017-5-23
     */
    public function cancelPhoneTask()
    {
        $arrWhere = [
            'and',
            ['=', 'task_type', 1],//电话任务
            ['=', 'task_date', date('Y-m-d')],//时间等于今天
            ['=', 'is_cancel', 1]//任务已取消
        ];
        $strSelect = 'count(1) as cancel_phone_task_num, salesman_id, shop_id';//已完成电话任务数
        $query = Task::find()->select($strSelect)->where($arrWhere)->groupBy('salesman_id, shop_id');
        $arrListTmp = $query->asArray()->all();
        $arrList = $this->addUserOrgInfo($arrListTmp);
        //先清空今天的数据，再入库 - 事务
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try
        {
            TjJichushuju::updateAll(['cancel_phone_task_num' => 0], " create_date='".date('Y-m-d')."' ");
            $intRtn = $this->insertOrUpdateData($arrList);
            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollBack();
            return 0;
        }
        return $intRtn;
    }
    
    
    /**
     * 功    能：已完成电话任务数
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function finishPhoneTask()
    {
        $arrWhere = [
            'and',
            ['=', 'task_type', 1],//电话任务
            ['=', 'task_date', date('Y-m-d')],//时间等于今天
            ['=', 'is_finish', 2]//任务已完成
        ];
        $strSelect = 'count(1) as finish_phone_task_num, salesman_id, shop_id';//已完成电话任务数
        $query = Task::find()->select($strSelect)->where($arrWhere)->groupBy('salesman_id, shop_id');
        $arrListTmp = $query->asArray()->all();
        $arrList = $this->addUserOrgInfo($arrListTmp);
        //先清空今天的数据，再入库 - 事务
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try
        {
            TjJichushuju::updateAll(['finish_phone_task_num' => 0], " create_date='".date('Y-m-d')."' ");
            $intRtn = $this->insertOrUpdateData($arrList);
            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollBack();
            return 0;
        }
        return $intRtn;
    }
    
    /**
     * 功    能：商谈数 (来电 + 去电 + 到店 + 上门)
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function talkNum()
    {
        $arrWhere = [
            'and',
            ['in', 'talk_type', [2, 3, 5, 6, 7, 8, 9, 10]],
            ['=', 'talk_date', date('Y-m-d')]
        ];
        $strSelect = 'count(1) as talk_num, salesman_id, shop_id';//商谈数 包括：来电 去电 上门  到店
        $query = Talk::find()->select($strSelect)->where($arrWhere)->groupBy('salesman_id, shop_id');
        $arrListTmp = $query->asArray()->all();
        $arrList = $this->addUserOrgInfo($arrListTmp);
        //先清空今天的新增线索数字段的数据，再入库 - 事务
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try
        {
            TjJichushuju::updateAll(['talk_num' => 0], " create_date='".date('Y-m-d')."' ");
            $intRtn = $this->insertOrUpdateData($arrList);
            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollBack();
            return 0;
        }
        return $intRtn;
    }
    
    
    /**
     * 功    能：来电数
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function laiDianNum()
    {
        $arrWhere = [
            'and',
            ['=', 'talk_type', 2],//2. 来电
            ['=', 'talk_date', date('Y-m-d')]
        ];
        $strSelect = 'count(1) as lai_dian_num, salesman_id, shop_id';//来电数
        $query = Talk::find()->select($strSelect)->where($arrWhere)->groupBy('salesman_id, shop_id');
        $arrListTmp = $query->asArray()->all();
        $arrList = $this->addUserOrgInfo($arrListTmp);
        //先清空今天的新增线索数字段的数据，再入库 - 事务
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try
        {
            TjJichushuju::updateAll(['lai_dian_num' => 0], " create_date='".date('Y-m-d')."' ");
            $intRtn = $this->insertOrUpdateData($arrList);
            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollBack();
            return 0;
        }
        return $intRtn;
    }

    /**
     * 功    能：去电数
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function quDianNum()
    {
        $arrWhere = [
            'and',
            ['=', 'talk_type', 3],//3. 去电
            ['=', 'talk_date', date('Y-m-d')]
        ];
        $strSelect = 'count(1) as qu_dian_num, salesman_id, shop_id';//去电数
        $query = Talk::find()->select($strSelect)->where($arrWhere)->groupBy('salesman_id, shop_id');
        $arrListTmp = $query->asArray()->all();
        $arrList = $this->addUserOrgInfo($arrListTmp);
        //先清空今天的新增线索数字段的数据，再入库 - 事务
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try
        {
            TjJichushuju::updateAll(['qu_dian_num' => 0], " create_date='".date('Y-m-d')."' ");
            $intRtn = $this->insertOrUpdateData($arrList);
            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollBack();
            return 0;
        }
        return $intRtn;
    }
    
    /**
     * 功    能：到店数
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function toShopNum()
    {
        $arrWhere = [
            'and',
            ['in', 'talk_type', [5, 6, 7]],//5. 到店-商谈 6. 到店-订车7. 到店-交车
            ['=', 'talk_date', date('Y-m-d')]
        ];
        $strSelect = 'count(1) as to_shop_num, salesman_id, shop_id';//到店数
        $query = Talk::find()->select($strSelect)->where($arrWhere)->groupBy('salesman_id, shop_id');
        $arrListTmp = $query->asArray()->all();
        $arrList = $this->addUserOrgInfo($arrListTmp);
        //先清空今天的新增线索数字段的数据，再入库 - 事务
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try
        {
            TjJichushuju::updateAll(['to_shop_num' => 0], " create_date='".date('Y-m-d')."' ");
            $intRtn = $this->insertOrUpdateData($arrList);
            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollBack();
            return 0;
        }
        return $intRtn;
    }
    
    /**
     * 功    能：上门数
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function toHomeNum()
    {
        $arrWhere = [
            'and',
            ['in', 'talk_type', [8, 9, 10]],//8. 上门-商谈 9. 上门-订车 10. 上门-交车
            ['=', 'talk_date', date('Y-m-d')]
        ];
        $strSelect = 'count(1) as to_home_num, salesman_id, shop_id';//上门数
        $query = Talk::find()->select($strSelect)->where($arrWhere)->groupBy('salesman_id, shop_id');
        $arrListTmp = $query->asArray()->all();
        $arrList = $this->addUserOrgInfo($arrListTmp);
        //先清空今天的新增线索数字段的数据，再入库 - 事务
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try
        {
            TjJichushuju::updateAll(['to_home_num' => 0], " create_date='".date('Y-m-d')."' ");
            $intRtn = $this->insertOrUpdateData($arrList);
            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollBack();
            return 0;
        }
        return $intRtn;
    }
}
