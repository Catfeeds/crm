<?php
namespace frontend\modules\glsb\controllers;

use frontend\logic\FeedbackLogic;
use Yii;

class FeedbackController extends AuthController
{
    public function actionIndex()
    {
//        $user = \Yii::$app->getUser()->identity;
        $r = json_decode(Yii::$app->request->post('r'),true);
        $p = json_decode(Yii::$app->request->post('p'),true);

        //判断参数是否齐全  接收反馈内容 反馈截图信息
        if(empty($p['content'])){
            $this->echoData(400,'参数不全');
        }
        $user_ip = Yii::$app->request->userIP;
        $content = $p['content'];
        $os_type = $r['os_type'];

        $file = array();
        if(!empty($_FILES['imgs'])){
            $file = $_FILES['imgs'];
        }


        //判断app_id
        if($os_type == 'android'){
            $app_id = 2;
        }else{
            $app_id = 4;
        }

        $logic = new FeedbackLogic();

        //保存反馈
        $rtn = $logic->feedback($content,$user_ip,$app_id,$file);

        if($rtn){
            $this->echoData(200,'提交成功');
        }else{
            $this->echoData(400,'提交失败');
        }
    }

}
?>