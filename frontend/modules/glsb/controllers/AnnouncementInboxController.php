<?php

namespace frontend\modules\glsb\controllers;

use common\logic\AnnouncementLogic;
use common\models\AnnouncementSalesmanReadLog;
use common\models\AnnouncementSend;
use Yii;
use common\models\AnnouncementInbox;
use frontend\modules\glsb\logic\AnnouncementInboxLogic;

/**
 * AnnouncementInboxController implements the CRUD actions for AnnouncementInbox model.
 */
class AnnouncementInboxController extends AuthController
{
    /**
     * 获取门店公告列表
     */
    public function actionList()
    {
        $user = \Yii::$app->getUser()->identity;
        
        $p = json_decode(Yii::$app->request->post('p'),true);
        if(empty($p['perPage']) || empty($p['currentPage'])){
            $this->echoData(400,'参数不全');
        }

        $perPage = (int)$p['perPage'];
        $currentPage = (int)$p['currentPage'];

        $logic = new AnnouncementLogic();
        $arrOriIds = isset($this->userinfo['user_role_info']) ? $this->userinfo['user_role_info'] : [];
        $data = $logic->getList($user,$perPage,$currentPage, $arrOriIds);

        //返回数据
        $this->echoData(200,'获取成功',$data);
    }

    //添加已读公告接口
    public function actionAddRead(){
        $p = json_decode(Yii::$app->request->post('p'),true);
        if(empty($p['announcement_id'])){
            $this->echoData(400,'参数不全');
        }
        $announcement_id = $p['announcement_id'];

        $user = \Yii::$app->getUser()->identity;
        $model = new AnnouncementSalesmanReadLog();

        $model->salesman_id = $user->id;
        $model->announcement_id = $announcement_id;
        $model->read_time = time();

        $model->save();

        $this->echoData(200,'操作成功');
    }


    //检查新公告假接口
    public function actionCheckNew(){
        $user = \Yii::$app->getUser()->identity;

        $logic = new AnnouncementLogic();
        $arrOriIds = isset($this->userinfo['user_role_info']) ? $this->userinfo['user_role_info'] : [];
        $rtn = $logic->checkNew($user, $arrOriIds);

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

        $this->echoData(200,$msg,$data);
    }
}
