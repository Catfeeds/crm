<?php
/*
 * 汽车品牌  厂商 车型 车系等数据逻辑操作层
 */

namespace common\logic;
use yii;
use common\models\CarBrand;
use common\models\CarFactory;
use common\models\CarBrandType;
use common\models\CarBrandSonType;
class CarBrandAndType 
{

    /**
     * 获取汽车品牌列表
     */
    public function getCarBrandList()
    {
        $arrSelect = [
            'brand_id' => 'int',
            'brand_name'=> 'string',
            'pic_url' => 'string',
            'first_num' => 'string'
        ];
        $strSelect = implode(',', array_keys($arrSelect));
        $arrWhere = [
            'and',
            ['=', 'isused', 1]//使用中的
        ];
        $arrList = CarBrand::find()->select($strSelect)->where($arrWhere)->asArray()->all();
        foreach($arrList as &$val)
        {
            foreach($val as $k => &$v)
            {
                $v = ($arrSelect[$k] == 'int' ? intval($v) : strval($v));
            }
        }
        return $arrList;
    }
    
    /**
     * 获取库中所有车系名称 - 后台导入数据的时候用到
     */
    public function getAllCarTypeList()
    {
        $arrWhere = [];
        $arrList = CarBrandType::find()->select('car_brand_type_id,car_brand_type_name,brand_id')->where($arrWhere)->asArray()->all();
        return $arrList;
    }
    
    /**
     * 根据品牌id获取车型数据 - 包括厂商信息
     */
    public function getCarTypeListByBrandId($intBrandId)
    {
        $arrRtn = [];
        $strSelect= 'car_brand_type_id,car_brand_type_name,pic_url,zhidao_price,factory_id';
        $arrWhere = [
            'and',
            ['=', 'brand_id', $intBrandId],
            ['<>', 'factory_id', ''],
            ['=', 'isused', 1],
            ['<>', 'zhidao_price' ,'']
        ];
        $brandList = CarBrandType::find()->select($strSelect)->where($arrWhere)->asArray()->all();
        $arrFactoryIdsTmp = $list = [];
        foreach($brandList as $val)
        {
            $arrFactoryIdsTmp[] = $val['factory_id'];
            $list[$val['factory_id']][] = [
                'car_brand_type_id' => intval($val['car_brand_type_id']),
                'car_brand_type_name' => strval($val['car_brand_type_name']),
                'pic_url' => strval($val['pic_url']),
                'zhidao_price' => strval($val['zhidao_price'])
            ];
        }
        //获取车厂商信息
        $arrFactoryIds = array_filter(array_unique($arrFactoryIdsTmp));
        $arrFactroyWhere = ['and', ['in', 'factory_id', $arrFactoryIds]];
        $arrFactoryInfo = CarFactory::find()->select('factory_id,factory_name')->where($arrFactroyWhere)->asArray()->all();
        $arrFactorys = [];
        foreach($arrFactoryInfo as $val)
        {
            $arrFactorys[$val['factory_id']] = $val['factory_name'];
        }
        foreach($list as $k => $v)
        {
            $arrRtn[] = [
                'factory_name' => strval($arrFactorys[$k]),
                'list' => $v
            ];
        }
        return $arrRtn;
    }
    
    /*
     * 根据车系id获取车型列表
     */
    public function getCarSonTypeByTypeId($intTypeId)
    {
        $strSelect = 'car_brand_son_type_id as car_type_id,car_brand_son_type_name as car_type_name,pic_url,';
        $arrWhere = ['and', ['car_brand_type_id' => $intTypeId]];
        $list = CarBrandSonType::find()->select($strSelect)->where($arrWhere)->asArray()->all();
        foreach($list as &$val)
        {
            $val['car_type_id'] = intval($val['car_type_id']);
            $val['car_type_name'] = strval($val['car_type_name']);
            $val['pic_url'] = strval($val['pic_url']);
        }
        return $list;
    }
    
    
    /**
     * 根据车系id获取车型名称 - 项目现在只精确到车系  不需要精确到车型
     * $mixSonId = int / array  查一个或者多个
     */
//    public function getCarTypeNameBySonTypeId($mixSonId)
//    {
//        $strSelect = 'car_brand_son_type_id,car_brand_type_id,car_brand_son_type_name as car_type_name';
//        $arrWhere = [
//            'and', 
//            [(is_array($mixSonId) ? 'in' : '='), 'car_brand_son_type_id', $mixSonId]
//        ];
//        $infos = CarBrandSonType::find()->select($strSelect)->where($arrWhere)->asArray()->all();
//        $brandTypeIds = array_map(function($v){ return $v['car_brand_type_id'];}, $infos);
//        //查询车型表 获取车型名称
//        $arrBrandTypeWhere = ['in', 'car_brand_type_id', $brandTypeIds];
//        $brandTypeList = CarBrandType::find()->select('car_brand_type_id,car_brand_type_name')->where($arrBrandTypeWhere)->asArray()->all();
//        $TypeLists = [];
//        foreach($brandTypeList as $v)
//        {
//            $TypeLists[$v['car_brand_type_id']] = $v['car_brand_type_name'];
//        }
//        $rtn = [];
//        foreach($infos as $v)
//        {
//            $rtn[$v['car_brand_son_type_id']] = $TypeLists[$v['car_brand_type_id']] . '(' . $v['car_type_name'] . ')';
//        }
//        //返回结果
//        return (is_array($mixSonId) ? $rtn : (isset($rtn[$mixSonId]) ? strval($rtn[$mixSonId]) : ''));
//    }
    
    //根据车系id获取车系名称 eg ： 奔驰B级
    public function getCarTypeNameByTypeId($mixTypeId)
    {
        $strSelect = 'car_brand_type_name,car_brand_type_id';
        $arrWhere = [(is_array($mixTypeId) ? 'in' : '='), 'car_brand_type_id', $mixTypeId];
        $typeList = CarBrandType::find()->select($strSelect)->where($arrWhere)->asArray()->all();
        $rtn = [];
        foreach($typeList as $val)
        {
            $rtn[$val['car_brand_type_id']] = $val['car_brand_type_name'];
        }
        return (is_array($mixTypeId) ? $rtn : (isset($rtn[$mixTypeId]) ? $rtn[$mixTypeId] : ''));
    }
    
    
    /**
     * 根据车系id获取汽车的品牌名 厂商车系名等信息
     */
    public function getBrandAndFactoryInfoByTypeId($mixTypeId)
    {
        //车系id获取车系信息 包括车系名称  品牌  厂商等
        $strSelect = 'car_brand_type_name,car_brand_type_id,brand_id,factory_id';
        $arrWhere = [(is_array($mixTypeId) ? 'in' : '='), 'car_brand_type_id', $mixTypeId];
        $typeList = CarBrandType::find()->select($strSelect)->where($arrWhere)->asArray()->all();
        
        //品牌信息
        $arrBrandId = array_map(function($v){return $v['brand_id'];}, $typeList);
        $arrBrandListTmp = CarBrand::find()->select('brand_id,brand_name')->distinct()->where(['in', 'brand_id', $arrBrandId])->asArray()->all();
        $arrBrandList = [];
        foreach($arrBrandListTmp as $val)
        {
            $arrBrandList[$val['brand_id']] = $val['brand_name'];
        }
        //厂商信息
        $arrFactoryId = array_map(function($v){return $v['factory_id'];}, $typeList);
        $arrFactoryListTmp = CarFactory::find()->select('factory_id,factory_name')->distinct()->where(['in', 'factory_id', $arrFactoryId])->asArray()->all();
        $arrFactoryList = [];
        foreach($arrFactoryListTmp as $val)
        {
            $arrFactoryList[$val['factory_id']] = $val['factory_name'];
        }
        //将车系信息 厂商信息  品牌信息合并
        $arrRtn = [];
        foreach($typeList as $val)
        {
            $k = $val['car_brand_type_id'];
            $arrRtn[$k]['car_brand_type_id'] = $val['car_brand_type_id'];
            $arrRtn[$k]['car_brand_type_name'] = $val['car_brand_type_name'];
            $arrRtn[$k]['factory_id'] = $val['factory_id'];
            $arrRtn[$k]['factory_name'] = (isset($arrFactoryList[$val['factory_id']]) ? $arrFactoryList[$val['factory_id']] : '');
            $arrRtn[$k]['brand_id'] = $val['brand_id'];
            $arrRtn[$k]['brand_name'] = (isset($arrBrandList[$val['brand_id']]) ? $arrBrandList[$val['brand_id']] : '');
        }
        return $arrRtn;
    }

    /**
     * 通过车型ID 获取的车型品牌信息（品牌ID 和 品牌名称）
     * @param int $intCarTypeId 车型ID
     * @return array
     */
    public function getBrandByCarTypeIdOne($intCarTypeId)
    {
        $mixReturn = null;
        // 查询到汽车车系表信息
        $one = CarBrandSonType::find()->select(['car_brand_son_type_id', 'brand_id'])
            ->where(['car_brand_son_type_id' => $intCarTypeId])
            ->asArray()->one();
        if ($one) {
            // 查询到品牌信息
            $mixReturn = CarBrand::find()->select(['brand_id', 'brand_name'])
                ->where(['brand_id' => $one['brand_id']])
                ->asArray()->one();
        }

        return $mixReturn;
    }

    /**
     * 通过车系ID 查询到品牌信息（品牌ID 和 品牌名称）
     * @param integer $intIntentionId 车系ID
     * @return array
     */
    public function getBrandByIntentionIdOne($intIntentionId)
    {
        $mixReturn = CarBrandType::find()->select(['brand_id', 'brand_name'])
            ->where(['car_brand_type_id' => $intIntentionId])
            ->asArray()->one();
        return $mixReturn;
    }

    /**
     * 通过车系信息查询到品牌信息（品牌ID 和 品牌名称）数组key 为车系ID
     * @param array $arrIntentionIds 车系ID数组
     * @return array
     */
    public function getBrandByIntentionIdAll($arrIntentionIds)
    {
        $mixReturn = CarBrandType::find()->select(['brand_id', 'brand_name', 'car_brand_type_id'])
            ->where(['car_brand_type_id' => $arrIntentionIds])
            ->asArray()
            ->indexBy('car_brand_type_id')
            ->all();
        return $mixReturn;
    }
}
