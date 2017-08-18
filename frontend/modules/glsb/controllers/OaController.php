<?php
namespace frontend\modules\glsb\controllers;

use yii;


/**
 * @desc oa跳转
 */
class OaController extends AuthController
{
    public function actionJump(){
        $user = \Yii::$app->user->identity;
        $osType = 'crm';
        $uid = $user->getId();
        $time = time();
        $sign = md5($osType.$uid.$time.'che.com');
        $url = Yii::$app->params['oa']['url'];
        $url .= "oa_v1/login?os_type={$osType}&uid={$uid}&time={$time}&sign={$sign}";

        $data['code'] = 200;
        $data['message'] = '';
        $data['data']['url'] = $url;
        return $data;
    }


}