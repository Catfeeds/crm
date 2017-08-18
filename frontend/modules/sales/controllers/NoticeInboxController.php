<?php

namespace frontend\modules\sales\controllers;

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
            return $this->paramError();
        }

        $arrCache = \Yii::$app->cache->get(md5($r['access_token']));
        $get_person_id = intval($arrCache['id']);
        $perPage = (int)$p['perPage'];
        $currentPage = (int)$p['currentPage'];

        $noticeInbox = new NoticeInboxLogic();
        $info = $noticeInbox->noticeInboxList($get_person_id,$perPage,$currentPage);

        $data['models'] = $info['list'];

        $data['pages'] = [
            'totalCount' => $info['totalCount'],
            'pageCount' => $info['pageCount'],
            'currentPage' => $info['currentPage'],
            'perPage' => $info['perPage'],
        ];

        die(json_encode(['code'=>200,'message'=>'请求成功','data'=>$data]));
    }
}
