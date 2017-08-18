<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/3/6
 * Time: 16:37
 */

namespace frontend\modules\glsb\controllers;
use common\auth\BaseAuth;
use common\models\OrganizationalStructure;
use common\models\Role;
use common\models\UserRoleOrgids;
use yii\db\Expression;
/**
 * 需要验证身份控制器
 * Class AuthController
 * @package frontend\modules\glsb\controllers
 */
class AuthController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => BaseAuth::className(),
        ];
        return $behaviors;
    }

    /**
     * 验证该用户是否有该门店权限
     */
    public function checkShop($user,$shop_id){
        if(isset($this->userinfo['role_id']))
        {
            $role_id = intval($this->userinfo['role_id']);
            $objRole = Role::findOne( $role_id );
            if($objRole && ($objRole->role_level < 30 || $objRole->remarks == 'shopowner'))
            {
                $objUserRole = UserRoleOrgids::find()->where(['user_id' => $user->id, 'role_id' => $role_id])
                        ->andWhere(new Expression('FIND_IN_SET(' . $shop_id .', org_ids)'))->one();
                if($objUserRole)
                {
                    return true;
                }
                else
                {
                    $this->echoData(400,'没有该门店权限');
                }
            }
            else
            {
                $this->echoData(400,'角色信息错误');
            }
        }
        else
        {
            $this->echoData(400,'没有登录');
        }
    }

    //获取当前用户shop_id
    public function getShopId(){
        
        if(!isset($this->userinfo['shop_id']) || empty($this->userinfo['shop_id']))
        {
            return 0;
        }
        return intval($this->userinfo['shop_id']);//登录时候选中的门店
    }
}