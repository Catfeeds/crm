<?php
namespace frontend\modules\glsb\controllers;

use Yii;

class SavePushTokenController extends AuthController
{
    /**
     * 保存用户token值
     */
    public function actionIndex()
    {
        //接收参数
        $r = json_decode(Yii::$app->request->post('r'),true);
        $p = json_decode(Yii::$app->request->post('p'),true);

        if( empty($r['os_type']) ||empty($p['huawei_push_token']) ){
            $this->echoData(400,'参数不全');
        }

        //取出用户id拼接key值
		$arrCache = \Yii::$app->cache->get(md5($r['access_token']) . '_glsb');
        $user_id = $arrCache['id'];

        //取出版本号拼接value值  如果非空拼接到value上
        //兼容以前版本 ver_code参数 默认为0
        $ver_code = empty($r['ver_code']) ? 0 : $r['ver_code'];

        //拼接value
        $huawei_push_token = $p['huawei_push_token'].'|||'.$ver_code;

        //取出版本号 拼接key
        $os_type = $r['os_type'];

        $app_type = 'glsb';
        $key = $app_type.$os_type.$user_id;

        if(Yii::$app->cache->set(md5($key), $huawei_push_token)){

            file_put_contents('../runtime/logs/glsbtoken.log',date("Y-m-d h:i:sa").'----'. \Yii::$app->request->url.'----addkey----'.$key.'----huawei_push_token----'.$huawei_push_token."\n", FILE_APPEND);

            $this->echoData(200,'保存成功');

        }else{
            $this->echoData(400,'保存失败');

        }
    }


    /**
     * 保存极光推送Alias
     */
    public function actionJpush()
    {
        //接收参数
        $r = json_decode(Yii::$app->request->post('r'),true);
        $p = json_decode(Yii::$app->request->post('p'),true);

        if( empty($r['os_type']) ||empty($p['alias']) ){
            $this->echoData(400,'参数不全');
        }

		$arrCache = \Yii::$app->cache->get(md5($r['access_token']) . '_glsb');
        $user_id = $arrCache['id'];

        $alias = $p['alias'];
        $os_type = $r['os_type'];

        $app_type = 'glsb';
        $key = 'Jpush'.$app_type.$os_type.$user_id;

        if(Yii::$app->cache->set(md5($key), $alias)){

            file_put_contents('../runtime/logs/Jpushalias.log',date("Y-m-d H:i:s")." ---- ".\Yii::$app->request->url."\naddkey =>".$key."\nJpushalias =>".$alias."\n\n", FILE_APPEND);

            $this->echoData(200,'保存成功');

        }else{
            $this->echoData(400,'保存失败');

        }
    }
}
?>