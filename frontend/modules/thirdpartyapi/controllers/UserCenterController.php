<?php
/**
 * 与组织架构与权限组的对接功能
 */
namespace frontend\modules\thirdpartyapi\controllers;
use Yii;
use yii\rest\Controller;
use common\logic\CompanyUserCenter;
class UserCenterController extends Controller
{
    /**
     * 权限系统主动通知组织架构以及项目人员信息的变动
     */
    public function actionExchangeNotify()
    {
        $strType = Yii::$app->request->post('api');//请求接口的时候告知变动类型是什么
        $file = Yii::$app->runtimePath . '/notify-test.log';
        file_put_contents($file,  '[' . date('Y-m-d H:i:s') . ']:', FILE_APPEND);
        file_put_contents($file, 'post => ' . var_export(Yii::$app->request->post(), true) . "\n", FILE_APPEND);
        $objCompanyUser = new CompanyUserCenter();
        $intResult = 0;
        switch($strType)
        {
            //组织架构相关变动
            case 'organizations/tree'://组织架构树形结构
            case 'organizations/types'://组织架构类型
                $intResult = $objCompanyUser->curlUpdateOrganizationalStructure();
                break;
            
            //项目人员信息变动
            case 'users'://用户列表
            case 'organizations/positions'://职位
            case 'projects/users'://项目用户
                $intResult = $objCompanyUser->curlUpdateProjectUserList();
                $objCompanyUser->curlUpdateUserRoleOrgPermission();
                break;
            
            //角色权限目录相关信息变动
            case 'projects/roles'://项目角色
            case 'projects/permission-tree'://项目菜单权限树形结构
                $intResult = $objCompanyUser->curlUpdateRoleInfo();
                break;
        }
        file_put_contents($file, '处理结果: ' . $intResult . "\n\n", FILE_APPEND);
        die('ok');
    }
    
}
