<?php
/**
 * 网站首页控制器以及门店选择功能
 */
namespace backend\controllers;
use Yii;
use yii\web\Controller;
use common\logic\CompanyUserCenter;
use common\common\PublicMethod;
class MytestController  extends Controller{
    
    public function __construct($id, $module, $config = array()) {
        parent::__construct($id, $module, $config);
        //注册结束的时候执行的函数
//        register_shutdown_function(['backend\controllers\TestController', 'abc']);
    }
    
    /**
     * 拉取用户
     */
    public function actionUser()
    {
        $obj = new CompanyUserCenter();
        $a = $obj->curlUpdateProjectUserList();
        $obj->curlUpdateUserRoleOrgPermission();
        var_dump($a);
        echo '已经更新了组织人员信息';
    }
    
    /**
     * 拉取组织架构
     */
    public function actionOrg()
    {
        $obj = new CompanyUserCenter();
        $a = $obj->curlUpdateOrganizationalStructure();
        var_dump($a);
        echo '组织架构更新成功';
    }
        
    /**
     * 拉取角色信息
     */
    public function actionRole()
    {
        $obj = new CompanyUserCenter();
        $a = $obj->curlUpdateRoleInfo();
        $obj->curlUpdateUserRoleOrgPermission();
        print_r($a);
        echo '更新角色权限信息成功';
    }
    
    //订车数基础数据中统计的不对  此处后门
    ///mytest/reset-ding-che-num
    public function actionResetDingCheNum()
    {
        set_time_limit(-1);
        $obj = new \console\logic\JichushujuLogic();
        $thisday = date('d');
        for($i = 1; $i <= $thisday; $i++)
        {
            $datetime = date("Y-m-{$i}");
            echo $datetime . '<br />';
            $obj->dingche($datetime);
        }
        echo 'ok';
    }
    
}