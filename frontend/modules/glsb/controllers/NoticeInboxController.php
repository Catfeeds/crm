<?php

namespace frontend\modules\glsb\controllers;

use common\logic\NoticeInboxLogic;
use common\models\NoticeInbox;
use frontend\modules\glsb\logic\BaseLogic;
use Yii;

/**
 * AnnouncementInboxController implements the CRUD actions for AnnouncementInbox model.
 */
class NoticeInboxController extends AuthController
{
    /**
     * 获取门店公告列表
     */
    public function actionList()
    {
        //接收参数
        $r = json_decode(Yii::$app->request->post('r'),true);
        $p = json_decode(Yii::$app->request->post('p'),true);
        if(empty($p['perPage']) || empty($p['currentPage'])){
            $this->echoData(400,'参数不全');
        }

		$arrCache = \Yii::$app->cache->get(md5($r['access_token']) . '_glsb');
        $get_person_id = $arrCache['id'];

        $perPage = (int)$p['perPage'];
        $currentPage = (int)$p['currentPage'];

        $noticeInbox = new NoticeInboxLogic();
        $data = $noticeInbox->noticeInboxList($get_person_id,$perPage,$currentPage);

        //统一返回数据格式
        $rtn = BaseLogic::instance()->excute_list($data['list'],$data['totalCount'],$data['perPage'],$data['pageCount'],$data['currentPage']);

        //返回数据
        $this->echoData(200,$rtn['msg'],$rtn['data']);
    }
}
