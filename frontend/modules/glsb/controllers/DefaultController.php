<?php

namespace frontend\modules\glsb\controllers;

use yii\web\Controller;
use common\models\User;
use common\models\UserRoleOrgids;
use common\models\Role;
use common\models\OrganizationalStructure;

/**
 * Default controller for the `glsb` module
 */
class DefaultController extends BaseController
{
    /**
     * 修改密码
     * Renders the index view for the module
     * @return string
     */
    public function actionNotifyPassword()
    {
        return [];
    }

    /**
     * 发送验证码
     * @return array
     */
    public function actionValidateCode()
    {
        return [
            'this is a test'
        ];
    }
    
    //get-select-roles
    public function actionGetSelectRoles()
    {
        $phone = \Yii::$app->request->get('phone', '');//13041665260
        $user = User::findOne(['phone' => $phone, 'is_delete' => 0]);
        if (empty($user)) 
        {
            $this->echoData(400,'未找到该账号');
        }
        //找这个人的角色信息和可选门店信息
        $roleIds = explode(',', $user->role_info);
        //获取所有角色信息
        $orWhere = [
            'or',
            ['<', 'role_level', 30],//非店铺层级角色 或者店长
            ['=', 'remarks', 'shopowner']
        ]; 
        $arrRoleList = Role::find()->select('id,remarks,name')->where(['in', 'id', $roleIds])
                ->andWhere($orWhere)
                ->orderBy('role_level asc')
                ->asArray()->all();
        if($arrRoleList) //该人员是店长或者店员
        {
            foreach($arrRoleList as $val)
            {
                if($val['remarks'] == 'shopowner')//店长角色需要提供可选的门店，其他角色不需要，直接选中角色就行
                {
                    //获取可选门店
                    $objUserRoleOrgids = UserRoleOrgids::findOne(['user_id' => $user->id, 'role_id' => $val['id']]);
                    if($objUserRoleOrgids && !empty($objUserRoleOrgids->org_ids))
                    {
                        $orgIds = explode(',', $objUserRoleOrgids->org_ids);
                        $arrOrgWhere = [
                            'and',
                            ['=', 'is_delete', 0],
                            ['in', 'id', $orgIds],
                            ['=', 'level', 30]//4S店层级
                        ];
                        $arrShopList = OrganizationalStructure::find()->where($arrOrgWhere)->asArray()->all();
                        if($arrShopList)//有可选门店，店长角色放出去
                        {
                            //格式化店铺列表
                            $arrShopListTmp = [];
                            foreach($arrShopList as $shop)
                            {
                                $arrShopListTmp[] = ['shop_id' => intval($shop['id']), 'shop_name' => strval($shop['name']) ];
                            }
                            //构造返回数据
                            $arrRtn[] = [
                                'role_id' => intval($val['id']),
                                'role_name' => strval($val['name']),
                                'shop_list' => $arrShopListTmp
                            ];
                        }
                    }
                }
                else
                {
                    $arrRtn[] = [
                        'role_id' => intval($val['id']),
                        'role_name' => strval($val['name']),
                    ];
                }
            }
            if($arrRtn)//有数据
            {
                $this->echoData(200,'获取成功',$arrRtn);
            }
            else//无可用数据
            {
                $this->echoData(400,'不能登录，没有合适的角色供选择');
            }
        }
        else
        {
            $this->echoData(400,'不能登录，没有合适的角色供选择');
        }
        
    }
    
    /**
     * 重要的联系人列表接口
     * @return array
     */    
    public function actionImportantPhoneList()
    {
        $arrImportantPhoneList = [
            [
                'icon_name'         => '采购',
                'position_name'     => '采购负责人',
                'name'              => '陈强',
                'phone'             => '17327002295',
            ],
            [
                'icon_name'         => '保险',
                'position_name'     => '保险负责人',
                'name'              => '陈强',
                'phone'             => '17327002295',
            ],
            [
                'icon_name'         => '贷款',
                'position_name'     => '贷款负责人',
                'name'              => '齐玛丽',
                'phone'             => '18801771738',
            ],
            [
                'icon_name'         => '技术',
                'position_name'     => 'CRM技术帮助',
                'name'              => '周恺',
                'phone'             => '13262562609',
            ],
        ];
        $this->echoData(200,'',$arrImportantPhoneList);
    }
}
