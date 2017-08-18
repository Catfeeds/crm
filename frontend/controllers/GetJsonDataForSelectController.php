<?php
/**
 * 提供下拉选项的json数据，本接口不需要校验登录等信息不继承base基类
 */
namespace frontend\controllers;
use yii\web\Controller;
use Yii;
use common\logic\CompanyUserCenter;
use common\logic\JsSelectDataLogic;
use common\models\User;
use \common\models\Role;
use common\models\UserRoleOrgids;
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
        die(json_encode($data));
    }
    
    /**
     * 组织架构信息
     */
    private function getOrgInfo()
    {
        //根据token获取登录信息
        $rData = json_decode(Yii::$app->request->get('r'), true);
        $accessToken = isset($rData['access_token']) ? $rData['access_token'] : '';
        $arrCache = Yii::$app->cache->get(md5($accessToken) . '_glsb');//管理速报才有
        if(empty($arrCache))
        {
            die('未登录');//没有登录
        }
        $id = $arrCache['id'];
        $roleId = $arrCache['role_id'];
        $arrOrgIds = explode(',', @UserRoleOrgids::findOne(['user_id' => $id, 'role_id' => $roleId])->org_ids);
        if(empty($arrOrgIds))
        {
            $arrOrgIds = [-1];
        }
        //组织id从session等等了信息中获取默认值
        $level = Role::findOne($roleId)->role_level;
        $addAll = (Yii::$app->request->get('all_org') == 'true');//是否显示所有的有权限的组织架构
        $objJsSelectData = new JsSelectDataLogic();
        $data = $objJsSelectData->getSelectOrgNew($arrOrgIds, $level, $addAll = false);
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
    
}
