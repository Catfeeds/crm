<?php
/**
 * 提供下拉选项的json数据，本接口不需要校验登录等信息不继承base基类
 */
namespace backend\controllers;
use yii\web\Controller;
use Yii;
use common\logic\CompanyUserCenter;
use common\logic\JsSelectDataLogic;
class GetJsonDataForSelectController extends Controller{

    /**
     * 统一入口
     */
    public function actionIndex()
    {
        $strFunction = Yii::$app->request->get('type', '');
        if($strFunction && method_exists($this, $strFunction))
        {
            $this->$strFunction();
        }
    }
    
    /**
     * 省市区
     */
    private function getShengShiQu()
    {
        $objJsSelectData = new JsSelectDataLogic();
        $data = $objJsSelectData->getShengShiQu();
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
                    }
                }
            }
        }
        die(json_encode($newData));
    }
    
    /**
     * 组织架构信息
     */
    private function getOrgInfo()
    {
        //组织id从session等等了信息中获取默认值
        $session = Yii::$app->getSession();
        $intOrgId = 1;
        $arrOrgIds = $session['userinfo']['permisson_org_ids'];
        $roleLevel = $session['userinfo']['role_level'];
        $intEndLevel = Yii::$app->request->get('endLevel', $roleLevel);
        $objJsSelectData = new JsSelectDataLogic();
        $data = $objJsSelectData->getSelectOrg($intOrgId, $intEndLevel, $arrOrgIds);
        die(json_encode($data));
    }

    /**
     * 新增线索组织架构下拉列表
     */
    private function getOrgInfos()
    {
        //组织id从session等等了信息中获取默认值
        $session = Yii::$app->getSession();
        $showAll = Yii::$app->request->get('showAll', 0);
        $permissionOrgIds = ($showAll ? [] : $session['userinfo']['permisson_org_ids']);
        $startEndLevel = Yii::$app->request->get('endLevel', -1);
        $objJsSelectData = new JsSelectDataLogic();
        $data = $objJsSelectData->getSelectOrgNew($permissionOrgIds, $startEndLevel);
        die(json_encode($data));
    }
    
    /**
     * 渠道来源
     */
    private function getInputType()
    {
        $objJsSelectData = new JsSelectDataLogic();
        $data = $objJsSelectData->getInputTypeList();
        die(json_encode($data));
    }
    
    /**
     * 信息来源
     */
    private function getSource()
    {
        $objJsSelectData = new JsSelectDataLogic();
        $data = $objJsSelectData->getSourceList();
        die(json_encode($data));
    }

    /**
     * 品牌车型
     */
    private function getCar() {
        $objJsSelectData = new JsSelectDataLogic();
        $data = $objJsSelectData->getCarList();

        $newData = [];
        foreach ($data['id_0'] as $k => $v) {
            $newData[$k]['value'] = $v['id'];
            $newData[$k]['label'] = $v['name'];
            if (!empty($data['id_'.$v['id']])){
                foreach ($data['id_'.$v['id']] as $key => $val) {
                    $newData[$k]['children'][$key]['value'] = $val['id'];
                    $newData[$k]['children'][$key]['label'] = $val['name'];
                }
            }

        }
        die(json_encode($newData,true));

    }
    
}
