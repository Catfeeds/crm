<?php

namespace frontend\modules\glsb\controllers;

use common\models\OrganizationalStructure;
use common\models\Role;
use common\models\User;
use Yii;
//use frontend\modules\glsb\models\User;

use frontend\modules\glsb\logic\GetDateLogic;

/**
 * GetDataController implements the CRUD actions for Clue model.
 */
class GetDataController extends BaseController
{

    /**
     * 功能废弃了，add by 王雕   2017.06.13
     * 根据手机号判断是否有效账号、返回店铺列表
     */
    public function actionCheckPhonenum()
    {
        //接收参数
        $p = json_decode(Yii::$app->request->post('p'),true);

        if(empty($p['phonenum'])){
            $this->echoData(400,'参数不全');
        }
        $phonenum = $p['phonenum'];

        //获取用户组织架构id

        $user_info = User::find()->select('org_id,role_info,organizational_structure_level')
                ->where(['=','phone',$phonenum])->andWhere(['=', 'is_delete', 0])
                ->one();

        //        //判断改手机号是否注册
        if(empty($user_info)){
            $this->echoData(400,'该手机号未注册');
        }

        //如果是店员不能登录
        if($user_info->organizational_structure_level == 3) {
            $isShopowner = false;
            $role_info = str_replace('\"', '"', $user_info->role_info);
            $role_info = json_decode($role_info, true);
            foreach ($role_info as $arrRole) {

                $objRoles = Role::findOne(['id' => $arrRole['id']]);
                if (is_object($objRoles) && $objRoles->remarks === 'shopowner') {
                    $isShopowner = true;
                }
            }
            if(!$isShopowner)
            {
                $this->echoData(400,'该用户没有登录权限');
            }
        }

        $child_id_list = $this->getChildsIds($user_info->org_id);
//        $child_id_list[] = $org_id->org_id;

        array_unshift($child_id_list,$user_info->org_id);
        $models = array();
        foreach ($child_id_list as $item){
            $models[] = $this->cids_original[$item];
        }

        foreach ($models as $key=>$value){
            $models[$key]['id'] = intval($value['id']);
            $models[$key]['pid'] = intval($value['pid']);
        }

        
        if($models){
            $count = count($models);
            $data['models'] = $models;
        }else{
            $count = 0;
            $data['models'] = array();
        }
        $data['pages'] = [
            'totalCount' => $count,
            'pageCount' => 1,
            'currentPage' => 1,
            'perPage' => $count,
        ];

        $this->echoData(200,'获取成功',$data);
    }

    private $cids = array();
    private $cids_original = array();
    private function getChildsIds($id_str){
        $organizational_info = OrganizationalStructure::find()->select('id,name,pid')->asArray()->all();

        $organizational_list1 = array();
        foreach ($organizational_info as $item){
            $organizational_list1[$item['id']] = $item;
        }
        $this->cids_original = $organizational_list1;


        $organizational_list = array();
        foreach ($organizational_info as $item){
            $organizational_list[$item['pid']][] = $item;
        }
        $this->cids = $organizational_list;

        $id_arr = explode(',',$id_str);

        return $this->getCids($id_arr);
    }

    private function getCids($id_arr){

        $cids = $this->cids;

        static $cid_list = array();
        $cid_arr = array();

        foreach ($id_arr as $key=>$value){

            if(!empty($cids[$value])){
                $cid_arr = array_column($cids[$value],'id');
                $cid_list = array_merge($cid_list,$cid_arr);
                $this->getCids($cid_arr);
            }

        }

        return $cid_list;
    }
}
