<?php
/**
 * 脚本logic层级基类
 * 作    者：王雕
 * 功    能：脚本logic层级基类
 * 修改日期：2017-4-6
 */
namespace console\logic;
use Yii;
use common\logic\CompanyUserCenter;
use common\models\OrganizationalStructure;
class BaseLogic 
{
    protected $strTableNow = '';
    protected $arrUnsetKeys = [];
    protected $arrAddItems = [];
    
     /**
     * 功    能：根据列表中的销售人员id信息补全门店id，大区id，公司id等组织架构信息
     * 参    数：$arrData       array       包含salesman_id的二维数组参数列表
     * 返    回：$arrUpdate     array       补全门店等信息后的列表，销售人员id关联不到门店的数据删除了
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function addUserOrgInfo($arrList)
    {
        $arrUpdate = [];
        if(!empty($arrList))
        {
            //获取所有组织架构信息，包括被删除的
            $arrOrgInfoTmp = OrganizationalStructure::find()->select('id,pid,name')->asArray()->all();
            $arrOrgInfo = [];
            foreach($arrOrgInfoTmp as $val)
            {
                $arrOrgInfo[$val['id']] = $val;
            }
            //补全组织架构信息
            foreach($arrList as $val)
            {
                if(isset($val['shop_id']) && isset($arrOrgInfo[$val['shop_id']]))
                {
                    $val['salesman_id'] = intval($val['salesman_id']);
                    $shopId = $val['shop_id'];
                    $val['area_id'] = $areaId = $arrOrgInfo[$shopId]['pid'];//区域id
                    $val['company_id'] = $companyId = $arrOrgInfo[$areaId]['pid'];//公司id
                    $arrUpdate[] = array_merge($val, $this->arrAddItems);
                }
            }
        }
        return $arrUpdate;
    }
    
    
     /**
     * 功    能：统计后的数据入库 INSERT INTO ......  ON DUPLICATE UPDATE ...... 方式
     * 参    数：$arrData       array       二维数组，需要入库的数据列表，第二维的键名是表的列名
     * 返    回：$intResult     int         数据入库影响的行数
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function insertOrUpdateData($arrData)
    {
        $intResult = 0;
        if(!empty($this->strTableNow) && !empty($arrData))
        {
            $arrColumns = array_map(function(&$v){return "`{$v}`";}, array_keys($arrData[0]));
            foreach($arrData as $val)
            {
                $val = array_map(function($v){ return "'{$v}'";}, $val);
                $arrValues[] = '(' . implode(',', array_values($val)) . ')';
            }
            $strSql = 'INSERT INTO ' . $this->strTableNow . ' (' . implode(',', $arrColumns) . ') values ' . implode(',', $arrValues);
            $strSql .= ' ON DUPLICATE KEY UPDATE ';
            //去掉唯一索引字段
            $arrUnsetKeys = array_map(function(&$v){return "`{$v}`";}, $this->arrUnsetKeys);
            $arrNewColumns = array_diff($arrColumns, $arrUnsetKeys);
            foreach($arrNewColumns as $key)
            {
                $arrUpdateItems[] = "{$key}=VALUES({$key})";
            }
            $strSql .= implode(',', $arrUpdateItems);
            $intResult = Yii::$app->db->createCommand($strSql)->execute();
        }
        return $intResult;
    }
    
     /**
     * 功    能：先删除数据 后插入数据 - 慎用
     * 参    数：$arrData               array       二维数组，需要入库的数据列表，第二维的键名是表的列名
      *        ：$strDeleteCondition    string      删除数据的条件 - 一定要控制好，避免多删
     * 返    回：$intRtn                int         数据入库影响的行数
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function deleteAndInsert($arrData, $strDeleteCondition)
    {
        $intRtn = 0;
        if(!empty($this->strTableNow))
        {
            //事务处理
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();
            try{
                //清表
                Yii::$app->db->createCommand()->delete($this->strTableNow, $strDeleteCondition)->execute();
                if(!empty($arrData))
                {
                    $arrColumns = array_keys($arrData[0]);
                    //插入
                    $intRtn = Yii::$app->db->createCommand()->batchInsert($this->strTableNow, $arrColumns, $arrData)->execute();
                }
                $transaction->commit();
            } catch (Exception $ex) {
                $transaction->rollBack();
                return $intRtn;
            }
        }
        return $intRtn;
    }

    public function dump($data) {
        echo "<pre>";
        print_r($data);
        exit;
    }
    
}
