<?php
namespace frontend\modules\sales\controllers;

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

        \Yii::$app->params['code']    = 200;
        \Yii::$app->params['message'] = '';
        return ['url'=>$url];
    }


}