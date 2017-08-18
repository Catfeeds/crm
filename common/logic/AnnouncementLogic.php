<?php
namespace common\logic;

use common\models\AnnouncementInbox;
use common\models\AnnouncementSalesmanReadLog;
use common\models\AnnouncementSend;
use common\models\OrganizationalStructure;
use Yii;

class AnnouncementLogic extends BaseLogic
{
    /**
     * 获取用户的公告列表
     * @param type $user
     * @param type $perPage
     * @param int $currentPage
     * @param type $arrOrgIds
     * @return type
     */
    public function getList($user,$perPage,$currentPage, $arrOrgIds){
        $query = AnnouncementInbox::find()->select('id,title,send_time,content,send_person_name');
        //使用人员有权限的门店有公告的
//        $query_list = $query->where(['in', 'shop_id', $arrOrgIds]);
        $query_list = $query->where(['in', 'shop_id', $user['shop_id']]);
        $totalCount = (int)$query_list->count();
        $pageCount = ceil($totalCount/$perPage);

        $list = $query_list->orderBy('id DESC')->offset(($currentPage-1)*$perPage)->limit($perPage)->asArray()->all();

        //查询是否阅读信息
        //取出当前用户id
        $salesman_id = $user->id;

        //取出公告id列表
        $announcement_id_list = array_column($list,'id');

        //查询该顾问公告列表阅读情况
        $read_list = AnnouncementSalesmanReadLog::find()->select('salesman_id,announcement_id,read_time')
            ->where(['=','salesman_id',$salesman_id])
            ->andWhere(['in','announcement_id',$announcement_id_list])->asArray()->all();;

        foreach ($read_list as $item){
            $read_list_new[$item['announcement_id']] = $item;
        }

        foreach ($list as $k_list=>$v_list){
            $list[$k_list]['id'] = (int)$v_list['id'];
            $list[$k_list]['title'] = (string)$v_list['title'];
            $list[$k_list]['send_time'] = (int)$v_list['send_time'];
            $list[$k_list]['content'] = (string)$v_list['content'];
            $list[$k_list]['send_person_name'] = (string)$v_list['send_person_name'];

            if(!empty($read_list_new[(int)$v_list['id']])){
                $list[$k_list]['is_read'] = 1;
            }else{
                $list[$k_list]['is_read'] = 0;
            }
        }
        if($list){
            $data['models'] = $list;
            $msg = '获取成功';
        }else{
            $data['models'] = array();
            $msg = '数据为空';
            $pageCount = 0;
            $currentPage = 0;
        }

        $data['pages'] = [
            'totalCount' => intval($totalCount),
            'pageCount' => intval($pageCount),
            'currentPage' => intval($currentPage),
            'perPage' => intval($perPage),
        ];

        return $data;

    }

    //检查当前用户是否有未阅读公告
    public function checkNew($user, $arrOrgIds)
    {
        //取出用户id
        $salesman_id = $user->id;
        empty($arrOrgIds) && $arrOrgIds = [-1];
        //取出当前用户所属组织架构 下的最新公告
        $info = [];
        $objAnn = AnnouncementInbox::find()->select('id,title,send_time,content,send_person_name')
            ->where(['in','shop_id', $arrOrgIds])
            ->orderBy('id DESC');
        if($objAnn)
        {
            $info = $objAnn->asArray()->one();
        }
        
        //判断是否有公告
        if(empty($info))
        {
            $data['code'] = 1; //没有公告
            $data['info'] = $info;
        }
        else
        {
            //检查是否阅读
            //取出公告id
            $announcement_id = $info['id'];
            //检查该用户是否已阅读该公告  是否已有阅读记录  非空为已阅读
            $rtn = AnnouncementSalesmanReadLog::find()->where(['=','salesman_id',$salesman_id])
                ->andWhere(['=','announcement_id',$announcement_id])
                ->one();

            if(empty($rtn))
            {
                $data['code'] = 0; //有公告 没有阅读
                $data['info'] = $info;

            }
            else //有公告 已阅读
            {
                $data['code'] = 2; //有公告 已阅读
                $data['info'] = $info;
            }
        }
        return $data;

    }


    /**
     * 发送公告
     * @param $user_info   当前用户信息
     * @param $options     收件人id类别  公司、大区、门店
     * @param $id_arr      收件人id
     * @param $title       公告标题
     * @param $send_person_name  发送公告人名称
     * @param $content     公告内容
     * @return bool
     */
    public function executeAnnouncementSend($user_info,$options,$id_arr,$title,$send_person_name,$content)
    {

        //获取当前用户id、name
        $send_person_id = $user_info['id'];//

        if($options == 'all'){
            $id_arr = [1];
            $addressee_des = '全部';
        }elseif($options == 'company'){

//            $id_arr = Yii::$app->request->post('id_arr');
            //查询公司名称
            $company_list = OrganizationalStructure::find()->select('id,name')->where(['in','id',$id_arr])->asArray()->all();
            $company_name = array_column($company_list,'name');
            $addressee_des = implode(',',$company_name);

        }elseif($options == 'area'){

//            $id_arr = Yii::$app->request->post('id_arr');
            //查询大区信息
            $area_list = OrganizationalStructure::find()->select('id,name')->where(['in','id',$id_arr])->asArray()->all();

            $area_name = array_column($area_list,'name');
            $addressee_des = implode(',',$area_name);

        }elseif ($options == 'shop'){

//            $id_arr = Yii::$app->request->post('id_arr');
            //查询大区信息
            $shop_list = OrganizationalStructure::find()->select('id,name')->where(['in','id',$id_arr])->asArray()->all();

            $shop_name = array_column($shop_list,'name');
            $addressee_des = implode(',',$shop_name);
        }

        $id_str = implode(',',$id_arr);
        //查询所有父级id
        $pids = $this->getParentsIds($id_str);
        //查询所有子级id
        $cids = $this->getChildsIds($id_str);

        $id_all = array_merge($id_arr,$pids,$cids);
        $send = new AnnouncementSend();

        $send->addressee_des = $addressee_des;       //发布公告时选择的组织架构名称
        $send->addressee_id = implode(',',$id_all);  //修改为存储全部组织架构id
        $send->title = $title;
        $send->content = $content;
        $send->send_person_id = $send_person_id; //通过session获取
        $send->send_person_name = $send_person_name; //通过session获取
        $send->send_time = time();
        $send->is_success = 1;

        //开启事务
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            //保存数据
            $send->save();
            //分发到收件箱模型
            $addressee_arr =$id_all;

            $info_list = null;
            foreach ($addressee_arr as $item){
                $info = null;
                $info['shop_id'] = $item;
                $info['addressee_id'] =  $send->addressee_id;
                $info['addressee_des'] = $send->addressee_des;
                $info['title'] = $send->title;
                $info['send_person_name'] = $send->send_person_name;
                $info['send_person_id'] = $send->send_person_id;
                $info['content'] = $send->content;
                $info['send_time'] = $send->send_time;
                $info['send_id'] = $send->id;
                $info_list[] = $info;
            }

            //多条插入
            Yii::$app->db->createCommand()
                ->batchInsert(AnnouncementInbox::tableName(), ['shop_id','addressee_id','addressee_des','title','send_person_name','send_person_id','content','send_time','send_id'], $info_list)
                ->execute();
            $transaction->commit();

            return true;

        } catch(\Exception $e) {
            $transaction->rollBack();
//            throw $e;
            return false;
        }
    }



}
?>