<?php
namespace common\logic;

use common\models\NoticeInbox;

class NoticeInboxLogic
{
    public function noticeInboxList($get_person_id,$perPage,$currentPage){

        //查询该门店公告列表
        $model = new NoticeInbox();

        $query = $model->find()->select('id,title,send_time,content,notice_type,notice_param')->where(['=','get_person_id',$get_person_id]);

        $totalCount = (int)$query->count();
        $pageCount = ceil($totalCount/$perPage);

        $list = $query->orderBy('id DESC')->offset(($currentPage-1)*$perPage)->limit($perPage)->asArray()->all();

        foreach ($list as $key=>$value){
            if ($value['notice_type'] == 101 || $value['notice_type'] == 201) {

                $list[$key]['show_type'] = 1;

            } elseif ($value['notice_type'] == 102 || $value['notice_type'] == 202) {

                $list[$key]['show_type'] = 2;

            } elseif ($value['notice_type'] == 103) {

                $list[$key]['show_type'] = 3;

            } elseif ($value['notice_type'] == 104) {

                $list[$key]['show_type'] = 4;

            } elseif ($value['notice_type'] == 105) {

                $list[$key]['show_type'] = 5;

            } elseif (in_array($value['notice_type'], [106, 107, 108, 109, 110, 111, 112, 116, 117, 118])) {

                $list[$key]['show_type'] = 6;
            } elseif ($value['notice_type'] == 113) {

                $list[$key]['show_type'] = 7;

            } elseif ($value['notice_type'] == 203) {

                $list[$key]['show_type'] = 8;
                // edited by liujx 2017-07-28 show_type = 9 为提车任务 -- 需要跳转到提车任务列表 start :
            } elseif (in_array($value['notice_type'], [114, 115])) {
                $list[$key]['show_type'] = 9;
            } else {
                // endl
                $list[$key]['show_type'] = 0;
            }

            if($value['notice_param']){
                $notice_param = json_decode($value['notice_param'],true);
            }else{
                $notice_param = (object)array();
            }

            $list[$key]['id'] = (int)$value['id'];
            $list[$key]['title'] = (string)$value['title'];
            $list[$key]['send_time'] = (int)$value['send_time'];
            $list[$key]['content'] = (string)$value['content'];
            $list[$key]['notice_type'] = (int)$value['notice_type'];
            $list[$key]['notice_param'] = $notice_param;

        }

        //$list,$totalCount,$perPage,$pageCount,$currentPage
        $data['list'] = $list;
        $data['totalCount'] = $totalCount;
        $data['perPage'] = $perPage;
        $data['pageCount'] = $pageCount;
        $data['currentPage'] = $currentPage;

        return $data;

    }



}









?>