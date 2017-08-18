<?php
/**
 * 版本更新检查 - 王雕
 */
namespace frontend\modules\sales\controllers;

use common\models\AppSelfUpdate;
use Yii;

/**
 * SelfUpdateController implements the CRUD actions for Clue model.
 */
class SelfUpdateController extends AuthController
{
    public function actionIndex()
    {
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'), true);
        $r = json_decode(Yii::$app->request->post('r'), true);

        //判断参数
        if (empty($p['app_id']) || empty($p['versionCode'])) {
            $this->echoData(400, '参数不全');
        }
        $info = [];
        foreach ($r as $k => $v) {
            if ($k != 'access_token') {
                $info[$k] = $v;
            }
        }
        $infos = json_encode($info);
        $user  = Yii::$app->user->identity;
        $phone = $user->phone;
        //更新用户使用版本信息
        $sql = "insert into crm_app_userinfo(phone,info)values('{$phone}','{$infos}')
                on duplicate key update 
                 phone=values(phone),
                 info=values(info)
                ";
        Yii::$app->db->createCommand($sql)->execute();

        $app_id      = intval($p['app_id']);
        $versionCode = intval($p['versionCode']);

        //检查数据库是否有新版本信息
        $appselfupdate = new AppSelfUpdate();

        $new_code = $appselfupdate->find()->where(['=', 'app_id', $app_id])->max('versionCode');

        if ($versionCode < intval($new_code)) {
            $data = $appselfupdate->find()->select('*')->where(['=', 'app_id', $app_id])
                ->andWhere(['=', 'versionCode', $new_code])->asArray()->one();

            $data['id']                  = intval($data['id']);
            $data['ios_or_android']      = strval($data['ios_or_android']);
            $data['app_id']              = intval($data['app_id']);
            $data['app_name']            = strval($data['app_name']);
            $data['create_time']         = intval($data['create_time']);
            $data['versionCode']         = intval($data['versionCode']);
            $data['versionName']         = strval($data['versionName']);
            $data['is_forced_update']    = intval($data['is_forced_update']);
            $data['content']             = strval($data['content']);
            $data['tips']                = strval($data['tips']);
            $data['file_url']            = strval($data['file_url']);
            Yii::$app->params['code']    = 200;
            Yii::$app->params['message'] = '需要升级';
            return $data;
        } else {
            Yii::$app->params['code']    = 200;
            Yii::$app->params['message'] = '需要升级';
            return [];
        }
    }
}
