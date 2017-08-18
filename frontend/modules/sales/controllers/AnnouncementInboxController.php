<?php
namespace frontend\modules\sales\controllers;
use common\models\AnnouncementSalesmanReadLog;
use Yii;
use common\logic\AnnouncementLogic;


class AnnouncementInboxController extends AuthController
{
    /**
     * 获取门店公告列表
     */
    public function actionList()
    {
        $user = \Yii::$app->getUser()->identity;

        $p = json_decode(Yii::$app->request->post('p'),true);
        if(empty($p['perPage']) || empty($p['currentPage']))
        {
            die(json_encode(['code'=>400,'message'=>'参数不全']));
        }

        $perPage = (int)$p['perPage'];
        $currentPage = (int)$p['currentPage'];

        $logic = new AnnouncementLogic();
        
        $orgIds = [ $user->shop_id ];
        $data = $logic->getList($user, $perPage, $currentPage, $orgIds);

        die(json_encode(['code'=>200,'message'=>'获取成功','data'=>$data]));
    }

    //添加已读公告接口
    public function actionAddRead(){
        $p = json_decode(Yii::$app->request->post('p'),true);
        if(empty($p['announcement_id'])){
            die(json_encode(['code'=>400,'message'=>'参数不全']));
        }
        $announcement_id = $p['announcement_id'];

        $user = \Yii::$app->getUser()->identity;
        $model = new AnnouncementSalesmanReadLog();

        $model->salesman_id = $user->id;
        $model->announcement_id = $announcement_id;
        $model->read_time = time();

        $model->save();
        die(json_encode(['code'=>200,'message'=>'操作成功']));
    }


    //检查新公告假接口
    public function actionCheckNew(){
        $user = \Yii::$app->getUser()->identity;

        $logic = new AnnouncementLogic();
        $rtn = $logic->checkNew($user, [$user->shop_id]);

        if($rtn['code'] != 0){
            $data = null;
            $msg = '无更新';
        }else{
            $info = $rtn['info'];
            $data['id'] = intval($info['id']);
            $data['title'] = strval($info['title']);
            $data['send_time'] = intval($info['send_time']);
            $data['content'] = strval($info['content']);
            $data['is_read'] = 0;
            $msg = '有更新';
        }

        die(json_encode(['code'=>200,'message'=>$msg,'data'=>$data]));
//        $this->echoData(200,$msg,$data);

    }


}

?>