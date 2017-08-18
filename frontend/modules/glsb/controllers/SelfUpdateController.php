<?php

namespace frontend\modules\glsb\controllers;

use common\models\AppSelfUpdate;
use Yii;

/**
 * SelfUpdateController implements the CRUD actions for Clue model.
 */
class SelfUpdateController extends BaseController
{
    public function actionIndex()
    {
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);

        //判断参数
        if(empty($p['app_id']) || empty($p['versionCode'])){
            $this->echoData(400,'参数不全');
        }

        $app_id = intval($p['app_id']);
        $versionCode = intval($p['versionCode']);

        //检查数据库是否有新版本信息
        $appselfupdate = new AppSelfUpdate();

        $new_code = $appselfupdate->find()->where(['=','app_id',$app_id])->max('versionCode');

        if($versionCode < intval($new_code)){
            $data = $appselfupdate->find()->select('*')
                    ->where(['=','app_id',$app_id])
                    ->andWhere(['=','versionCode',$new_code])->asArray()->one();

            $data['id'] = intval($data['id']);
            $data['ios_or_android'] = strval($data['ios_or_android']);
            $data['app_id'] = intval($data['app_id']);
            $data['app_name'] = strval($data['app_name']);
            $data['create_time'] = intval($data['create_time']);
            $data['versionCode'] = intval($data['versionCode']);
            $data['versionName'] = strval($data['versionName']);
            $data['is_forced_update'] = intval($data['is_forced_update']);
            $data['content'] = strval($data['content']);
            $data['tips'] = strval($data['tips']);
            $data['file_url'] = strval($data['file_url']);

            $this->echoData(200,'需要升级',$data);
        }else{
            $this->echoData(200,'不需升级', null);
        }
    }
}
