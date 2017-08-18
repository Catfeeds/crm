<?php
/**
 * 用户管理
 */
namespace backend\controllers;

use Yii;
use common\logic\CompanyUserCenter;


class UserController extends BaseController
{
    public $enableCsrfValidation = false;

    /**
     * 密码修改入口
     */
    public function actionUpdatePassword()
    {

        return $this->render('index', []);

    }
    /**
     * 密码修改
     */
    public function actionSave()
    {
        $session = Yii::$app->getSession();

        $access_token = $session['userinfo']['access_token'];
        $company = new CompanyUserCenter();



        $res = $company->curlChangePassword($_POST['new_password'],$access_token,$_POST['password']);
        if ($res['code'] == 0) {
            Yii::$app->user->logout();
            //删除session中选择的level
            Yii::$app->getSession()->destroy();
            $this->res();
        }else {
            $this->res('300','修改失败');
        }
    }
    
    
    /**
     * 退出登录
     */
    public function actionLogout()
    {
        //删除session
        $session = Yii::$app->getSession();
        $strCacheKey = 'login_14_' . $session->id;
        \Yii::$app->cache->delete($strCacheKey);
        if(isset($session['selfLogin']) && $session['selfLogin'] == 1)
        {
            //自己的登录
            $session->destroy();
            header('Location: /site/login');
            exit();
        }
        else
        {
            //权限系统的登录
            $session->destroy();
            $loginUrl = Yii::$app->params['quan_xian']['auth_sso_login_url'];
            header('Location: ' . $loginUrl);
            exit();
        }
    }


}
