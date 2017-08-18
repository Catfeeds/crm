<?php
/**
 * js多层级下拉结构的数据提供逻辑层
 */
namespace common\logic;
class JsSelectDataLogic {
    
    /**
     * 提供省市区层级的下拉单
     */
    public function getShengShiQu()
    {
        $list = \common\models\Area::find()->asArray()->all();
        $arrList = [];
        foreach($list as $val)
        {
            $key = 'id_' . $val['pid'];
            $data = ['id' => intval($val['id']), 'name' => strval($val['name'])];
            $data['submenu'] = ( ($val['level'] < 3) ? 1 : 0 );
            $arrList[$key][] = $data;
        }
        return $arrList;
    }

    /**
     * 获取省市区
     * @param bool $all 添加全部按钮
     * @return array
     */
    public function getShengShiQuNew($all = false)
    {
        $data = $this->getShengShiQu();
        $newData = [];
        foreach ($data['id_0'] as $k => $v) {
            $newData[$k]['value'] = $v['id'];
            $newData[$k]['label'] = $v['name'];
            if (!empty($data['id_'.$v['id']])){
                foreach ($data['id_'.$v['id']] as $key => $val) {
                    $newData[$k]['children'][$key]['value'] = $val['id'];
                    $newData[$k]['children'][$key]['label'] = $val['name'];
                    if (!empty($data['id_'.$val['id']])){
                        foreach ($data['id_'.$val['id']] as $mk => $mv) {
                            $newData[$k]['children'][$key]['children'][$mk]['value'] = $mv['id'];
                            $newData[$k]['children'][$key]['children'][$mk]['label'] = $mv['name'];
                        }

                        // 添加全部
                        if ($all) {
                            array_unshift($newData[$k]['children'][$key]['children'], [
                                'value' => $val['id'],
                                'label' => '全部',
                            ]);
                        }
                    }
                }

                // 添加全部
                if ($all) {
                    array_unshift($newData[$k]['children'], [
                        'value' => $v['id'],
                        'label' => '全部',
                    ]);
                }
            }
        }

        return $newData;
    }
    
    /**
     * 根据组织id获取下拉单列表
     */
    public function getSelectOrg($intOrgId, $intEndLevel = -1, $arrOrgIds = [])
    {
        //此处有点取巧 - 利用level从小到大排列循环的时候用到level，level越小 层级越高
        //是否设置了只选取到哪一层
        $arrWhere = ['and',['=', 'is_delete', 0]];
        $arrOrgIds && $arrWhere[] = ['in', 'id', $arrOrgIds];
//        in_array($intEndLevel, [10, 15, 20, 30]) && $arrWhere[] = ['<=', 'level', $intEndLevel];
        $listTmp = \common\models\OrganizationalStructure::find()->where($arrWhere)->orderBy('level')->asArray()->all();
        $arrPids = [$intOrgId];
        $arrListTmp = [];
        //找到组织id下属的子子孙孙元素
        foreach($listTmp as $val)
        {
            if(in_array($val['pid'], $arrPids))
            {
                array_push($arrPids, $val['id']);
                $arrListTmp[] = $val;
            }
        }
        //格式化成json格式
        $arrList = [];
        foreach($arrListTmp as $val)
        {
            $strKey = 'id_' . ($val['pid'] == $intOrgId ? 0 : $val['pid']);
            !in_array($intEndLevel, [0, 1, 2, 3]) && $intEndLevel = 3;
            $arrList[$strKey][] = [
                'id' => intval($val['id']),
                'name' => strval($val['name']),
                'submenu' => ($val['level'] < $intEndLevel ? 1 : 0),
                'level' => $val['level'],
            ];
        }
        return $arrList;
    }
    
    /**
     * 获取下拉列表，组织架构中允许的
     * @param type $arrOrgIds 用户有权限的组织id数组
     * @param type $level
     * $addAll 是否增加个全部选项
     * @return array
     */
    public function getSelectOrgNew($arrOrgIds = [], $level = -1, $addAll = false)
    {
        $objCompanyUser = new CompanyUserCenter();
        $arrOrgList = $objCompanyUser->getLocalOrganizationalStructure();
        $arrLevels = array_column($arrOrgList, 'level');
        array_multisort($arrLevels, SORT_ASC, $arrOrgList);//组织按照总部 公司 大区 门店排序
        $arrUserOrgList = [];
        foreach($arrOrgList as $val)
        {
            if(empty($arrOrgIds) ||in_array($val['id'], $arrOrgIds))//用户有该组织权限
            {
                $arrUserOrgList[] = $val;
            }
        }
        //组建 children
        $arrChildrenList = [];
        foreach($arrUserOrgList as $val)
        {
            //大区 门店增加个全部选项
            if($addAll && !isset($arrChildrenList['pid_' . $val['pid']]) && in_array($val['level'], [20, 30]))
            {
                $arrChildrenList['pid_' . $val['pid']][] = [
                    'value' => '-1',
                    'label' => '全部',
                ];
            }
            $arrChildrenList['pid_' . $val['pid']][] = [
                'value' => $val['id'],
                'label' => strval($val['name']),
            ]; 
        }
        //总部  公司  大区  门店
        $arrData = [
            'value' => $arrUserOrgList[0]['id'],
            'label' => strval($arrUserOrgList[0]['name']),
        ];
        if(isset($arrChildrenList['pid_' . $arrUserOrgList[0]['id']]))
        {
            $arrData['children'] = $arrChildrenList['pid_' . $arrUserOrgList[0]['id']];
        }
        //对公司循环 补充大区
        if(!empty($arrData['children']))
        {
            foreach($arrData['children'] as &$val)
            {
                if(isset($arrChildrenList['pid_' . $val['value']]))
                {
                    $val['children'] = $arrChildrenList['pid_' . $val['value']];
                    //对大区层级循环 补充门店
                    foreach($val['children'] as &$v)
                    {
                        if(isset($arrChildrenList['pid_' . $v['value']]))
                        {
                            $v['children'] = $arrChildrenList['pid_' . $v['value']];
                        }
                    }
                }
            }
        }
        $arrData = $arrData['children'];//返回数据从公司层级开始
        switch($level)
        {
            case 20: //看大区和店铺
                $arrRtn = [];
                foreach($arrData as $val)
                {
                    if(isset($val['children']))//大区列表
                    {
                        $arrRtn = array_merge($arrRtn, $val['children']);
                        if(isset($arrRtn[0]) && $arrRtn[0]['value'] == -1)//过滤掉全部选项
                        {
                            array_shift($arrRtn);
                        }
                    }
                }
                break;
            case 30://只看店铺层级
                $arrRtn = [];
                foreach($arrData as $val)
                {
                    if(isset($val['children']))//大区
                    {
                        $arrArea = $val['children'];
                        for($i = 0;$i< count($arrArea);$i++)
                        {
                            if(isset($arrArea[$i]['children']))
                            {
                                $arrShops = $arrArea[$i]['children'];
                                if(isset($arrShops[0]) && $arrShops[0]['value'] == -1)//过滤掉全部选项
                                {
                                    array_shift($arrShops);
                                }
                                $arrRtn = array_merge($arrRtn, $arrShops);
                            }
                        }
                    }
                }
                break;
            case 15://看公司 大区 店铺
            default :
                $arrRtn = $arrData;
                break;
        }
        return $arrRtn;
        
    }
    
    
    /**
     * 渠道来源
     */
    public function getInputTypeList()
    {
        $objData = new DataDictionary();
        $list = $objData->getDictionaryData('input_type');
        $arrList = [];
        foreach($list as $val)
        {
            $arrList['id_0'][] = [
                'id' => intval($val['id']),
                'name' => strval($val['name']),
                'submenu' => 0
            ];
        }
        return $arrList;
    }
    /**
     * 信息来源
     */
    public function getSourceList()
    {
        $objData = new DataDictionary();
        $list = $objData->getDictionaryData('source');
        $arrList = [];
        foreach($list as $val)
        {
            $arrList['id_0'][] = [
                'id' => intval($val['id']),
                'name' => strval($val['name']),
                'submenu' => 0
            ];
        }
        return $arrList;
    }

    /**
     * 品牌车型
     */
    public function getCarList()
    {

        $car = new \common\logic\CarBrandAndType();
        //获取品牌信息
        $brand = $car->getCarBrandList();
        $arrList = [];
        foreach ($brand as $v) {
            $arrList['id_0'][] = [
                'id' => intval($v['brand_id']),
                'name' => strval($v['brand_name']),
                'submenu' => 1
            ];
        }
        //获取车型信息
        $cars     = $car->getAllCarTypeList();
        foreach ($cars as $v) {
            $arrList['id_'.$v['brand_id']][] = [
                'id' => intval($v['car_brand_type_id']),
                'name' => strval($v['car_brand_type_name']),
                'submenu' => 0
            ];
        }

        return $arrList;
    }
    
}
