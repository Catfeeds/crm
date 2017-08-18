<?php
namespace common\logic;

use common\models\OrganizationalStructure;
use common\logic\CompanyUserCenter;
class TongJiLogic
{

    /**
     * 没有选中某个组织的时候  默认的查询条件
     */
    public function getStrFieldByLevel($intLevel, $arrOrgIds)
    {        
        $arrRtn = [];
        $strOrgIds = implode(',', $arrOrgIds);
        switch($intLevel)
        {
            case 10://总部
                $arrRtn['where'] = ' company_id in (' . $strOrgIds . ') and area_id in (' . $strOrgIds . ') and  shop_id in (' . $strOrgIds . ') ';
                $arrRtn['groupby'] = 'company_id';
                $arrRtn['org_level_name'] = '总部';
                $list = OrganizationalStructure::find()->where([
                    'and',
                    ['=', 'level', 15],//下一级为公司
                    ['=', 'is_delete', 0],
                    ['in', 'id', $arrOrgIds]
                ])->asArray()->all();
                $arrRtn['nextList'] = [];
                foreach($list as $val)
                {
                    $arrRtn['nextList'][] = [
                        'id' => $val['id'],
                        'name' => $val['name'],
                        'pid' => $val['pid'],
                    ];
                }
                break;
            case 15://公司
                $arrRtn['where'] = ' area_id in (' . $strOrgIds . ') and  shop_id in (' . $strOrgIds . ') ';
                $arrRtn['groupby'] = 'area_id';
                $arrRtn['org_level_name'] = '公司';
                $list = OrganizationalStructure::find()->where([
                    'and',
                    ['=', 'level', 20],//下一级为公司
                    ['=', 'is_delete', 0],
                    ['in', 'id', $arrOrgIds]
                ])->asArray()->all();
                $arrRtn['nextList'] = [];
                foreach($list as $val)
                {
                    $arrRtn['nextList'][] = [
                        'id' => $val['id'],
                        'name' => $val['name'],
                        'pid' => $val['pid']
                    ];
                }
                break;
            case 20://大区
                $arrRtn['where'] = ' shop_id in (' . $strOrgIds . ') ';
                $arrRtn['groupby'] = 'shop_id';
                $arrRtn['org_level_name'] = '大区';
                $list = OrganizationalStructure::find()->where([
                    'and',
                    ['=', 'level', 30],//下一级为大区
                    ['=', 'is_delete', 0],
                    ['in', 'id', $arrOrgIds]
                ])->asArray()->all();
                $arrRtn['nextList'] = [];
                foreach($list as $val)
                {
                    $arrRtn['nextList'][] = [
                        'id' => $val['id'],
                        'name' => $val['name'],
                        'pid' => $val['pid']
                    ];
                }
                break;
//            case 30://门店
//                $arrRtn['where'] = ' shop_id in (' . $strOrgIds . ')  ';
//                $arrRtn['groupby'] = 'shop_id';
//                $arrRtn['org_level_name'] = '门店';
//                $list = OrganizationalStructure::find()->where([
//                    'and',
//                    ['=', 'level', 30],//下一级为门店
//                    ['=', 'is_delete', 0],
//                    ['in', 'id', $arrOrgIds]
//                ])->asArray()->all();
//                $arrRtn['nextList'] = [];
//                foreach($list as $val)
//                {
//                    $arrRtn['nextList'][] = [
//                        'id' => $val['id'],
//                        'name' => $val['name'],
//                        'pid' => $val['pid']
//                    ];
//                }
//                break;
        }
        return $arrRtn;
    }
    
    /**
     * 选中某个组织后 条件以及下一层数据
     */
    public function getSelectFieldByLevelAndOrgId($orgId, $arrOrgIds)
    {
        $arrRtn = [];
        $objOrg = OrganizationalStructure::findOne($orgId);
        if($objOrg)
        {
            $strOrgIds = implode(',', $arrOrgIds);
            $objOrg = OrganizationalStructure::findOne($orgId);
            switch($objOrg->level)
            {
                case 10 ://总部
                    $arrRtn['where'] = " company_id in ({$strOrgIds}) and area_id in ({$strOrgIds}) and shop_id in ({$strOrgIds}) ";
                    $arrRtn['groupby'] = 'company_id';
                    $arrRtn['org_level_name'] = (is_object($objOrg) ? $objOrg->name : '总部');
                    $list = OrganizationalStructure::find()->where([
                        'and',
                        ['=', 'level', 15],//下一级为公司
                        ['=', 'is_delete', 0],
                        ['=', 'pid', $orgId],
                        ['in', 'id', $arrOrgIds]
                    ])->asArray()->all();
                    $arrRtn['nextList'] = [];
                    foreach($list as $val)
                    {
                        $arrRtn['nextList'][] = [
                            'id' => $val['id'],
                            'name' => $val['name'],
                            'pid' => $val['pid']
                        ];
                    }
                    break;
                case 15 ://公司
                    $arrRtn['where'] = " company_id={$orgId} and area_id in ({$strOrgIds}) and shop_id in ({$strOrgIds}) ";
                    $arrRtn['groupby'] = 'area_id';
                    $arrRtn['org_level_name'] = (is_object($objOrg) ? $objOrg->name : '公司');
                    $list = OrganizationalStructure::find()->where([
                        'and',
                        ['=', 'level', 20], //下一级为大区
                        ['=', 'pid', $orgId],
                        ['=', 'is_delete', 0],
                        ['in', 'id', $arrOrgIds]
                    ])->asArray()->all();
                    $arrRtn['nextList'] = [];
                    foreach($list as $val)
                    {
                        $arrRtn['nextList'][] = [
                            'id' => $val['id'],
                            'name' => $val['name'],
                            'pid' => $val['pid']
                        ];
                    }
                    break;
                case 20 ://大区
                    $arrRtn['where'] = " area_id={$orgId} and shop_id in ({$strOrgIds}) ";
                    $arrRtn['groupby'] = 'shop_id';
                    $arrRtn['org_level_name'] = (is_object($objOrg) ? $objOrg->name : '大区');
                    $list = OrganizationalStructure::find()->where([
                        'and',
                        ['=', 'level', 30],//下一级为门店
                        ['=', 'is_delete', 0],
                        ['=', 'pid', $orgId],
                        ['in', 'id', $arrOrgIds]
                    ])->asArray()->all();
                    $arrRtn['nextList'] = [];
                    foreach($list as $val)
                    {
                        $arrRtn['nextList'][] = [
                            'id' => $val['id'],
                            'name' => $val['name'],
                            'pid' => $val['pid']
                        ];
                    }
                    break;
                case 30 ://门店
                    $arrRtn['where'] = 'shop_id = ' . $orgId;
                    $arrRtn['groupby'] = 'salesman_id';
                    $arrRtn['org_level_name'] = (is_object($objOrg) ? $objOrg->name : '门店');
                    $objCompanyUserCenter = new CompanyUserCenter();
                    $arrSales = $objCompanyUserCenter->getShopSales($orgId);
                    $arrRtn['nextList'] = [];
                    foreach($arrSales as $val)
                    {
                        $arrRtn['nextList'][] = [
                            'id' => $val['id'],
                            'name' => $val['name'],
                            'pid' => $orgId
                        ];
                    }
                    //门店里面增加一个特殊的顾问id  = 0  标记门店中无人跟进的
                    $arrRtn['nextList'][] = [
                            'id' => 0,
                            'name' => '无顾问',
                            'pid' => $orgId
                    ];
                    break;
            }
        }
        return $arrRtn;
    }
    

}
