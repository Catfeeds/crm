<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/3/29
 * Time: 14:45
 */

namespace frontend\modules\sales\controllers;

use common\logic\CompanyUserCenter;
use Yii;


/**
 * 用户中心
 * Class UserController
 * @package frontend\modules\sales\controllers
 */
class UserController extends AuthController
{
    /**
     * 修改密码
     */
    public function actionChangePassword()
    {
        $pData = $this->getPData();
        if (!isset($pData['new_password']) || !$pData['new_password'] || !isset($pData['old_password'])
            || !$pData['old_password']) {
            return $this->paramError();
        }
        $oldPassword = $pData['old_password'];
        $newPassword = $pData['new_password'];
        //密码验证
        if (!preg_match("/^[a-zA-Z\d_]{6,20}$/", $newPassword)) {
            Yii::$app->params['code']    = 400;
            Yii::$app->params['message'] = '密码不合法';
            return false;
        }
        $obj = new CompanyUserCenter();
        $rData = json_decode(Yii::$app->request->post('r'), true);
        $result = $obj->curlChangePassword($newPassword, $rData['access_token'],$oldPassword);
        if($result['code'] == 0) {
            return true;
        }
        Yii::$app->params['code'] = $result['code'];
        Yii::$app->params['message'] = $result['msg'];
        return false;
    }
}