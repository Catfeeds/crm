<?php

namespace frontend\modules\sales\controllers;

use common\logic\CarBrandAndType;
use common\logic\DataDictionary;
use common\logic\CompanyUserCenter;
use common\models\User;
use common\models\Role;
use common\models\UserRoleOrgids;
use common\models\OrganizationalStructure;
use Yii;
use common\models\DdSource;
use common\logic\GongHaiLogic;

/**
 * Default controller for the `v1` module
 */
class DefaultController extends BaseController
{
    /**
     * 获取数据字典
     *
     *
     * @return array
     */
    public function actionDataDictionary()
    {
        $pData = json_decode(\Yii::$app->request->get('p'), true);
        if  (empty($pData)) {
            $inputData = [
                //'area' => 0,//地区数据字典
                'buy_type' => 0, //购买方式数据字典
                'intention' => 0,//意向等级数据字典
                'input_type' => 0,//导入方式数据字典 - 客户端可能不需要
                'age_group' => 0,//年龄段数据字典
                'profession' => 0,//职业数据字典
                'source' => 0,//客户来源 - 细分客户来源
                'planned_purchase_time' => 0,//拟购时间数据字典
                'phone_letter_tmp' => 0,//短信数据字典
                'tags' => 0,//标签系统
                'fail_tags' => 0, //战败标签
                'gonghai_reason'=> 0//公海原因
            ];
        } else {
            $inputData = $pData;
        }
        $obj = new DataDictionary();
        $local_version = $obj->getDictionaryVersion();
        $arrRtn = [];
        foreach($local_version as $k => $v)
        {
            if($k == 'area')
            {
                continue;//地区数据字典客户端不跟新
            }
            if($v > $inputData[$k]) //客户端的版本比本地的小 - 更新客户端的数据字典
            {
                $arrRtn[$k] = [
                    'list' => $obj->getDictionaryData($k),
                    'v' => $v
                ];
            }
        }
        $arrRtn['gonghai']['list'] = GongHaiLogic::getGonghaiReasonInfo();
        //销售助手新增线索或者新增客户 信息来源只要 自然到店 外拓自建
        $arrDdSourceIds = [15,19];
        $arrRtn['clueSource']['list'] = DdSource::find()->select('id,name')->where(['in','id',$arrDdSourceIds])->all();

        return $arrRtn;
    }

    /**
     * 厂商和车型
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionCarList()
    {
        $obj = new CarBrandAndType();
        $band_id = \Yii::$app->request->get('band_id');
        if ($band_id) {
            return $obj->getCarTypeListByBrandId($band_id);
        }
        return $obj->getCarBrandList();
    }

    /**
     * 根据手机号得到店名
     * @return array |bool
     */
    public function actionGetShopName()
    {
        $phone = \Yii::$app->request->get('phone');
        $user = User::findOne(['phone' => $phone, 'is_delete' => 0]);
        if (empty($user)) {
            \Yii::$app->params['code'] = 400;
            \Yii::$app->params['message'] = '未找到该账号';
            return false;
        }
        $ver_code = \Yii::$app->request->get('ver_code');
        $os_type = \Yii::$app->request->get('os_type');
        if(empty($ver_code) && empty($os_type))//低版本的 不显示门店下拉数据
        {
            return [];
        }
        
        //找这个人的角色信息和可选门店信息
        $roleIds = explode(',', $user->role_info);
        //获取所有角色信息
        $arrRoleWhere = [
            'and',
            ['in', 'id', $roleIds],
            ['in', 'remarks', ['shopowner', 'salesman']],//只有店长和销售人员能参与销售助手的业务
        ];
        $arrRoleList = Role::find()->select('id,remarks,name')->where($arrRoleWhere)->asArray()->all();
        if($arrRoleList) //该人员是店长或者店员
        {
            //过滤后的角色id
            $arrRoleIds = array_column($arrRoleList, 'id');
            foreach($arrRoleList as $val)
            {
                $arrRoleTmp[$val['id']] = $val['name'];
            }
            //获取角色和人员关联的组织架构
            $arrUserRoleWhere = [
                'and',
                ['=', 'user_id', $user->id],
                ['in', 'role_id', $arrRoleIds]
            ];
            $arrUserRoleList = UserRoleOrgids::find()->where($arrUserRoleWhere)->asArray()->all();
            $arrRtn = [];
            foreach($arrUserRoleList as $val)
            {
                $orgIds = explode(',', $val['org_ids']);
                if(empty($orgIds))//org_ids 可能为空的
                {
                    continue;
                }
                $arrOrgWhere = [
                    'and',
                    ['=', 'is_delete', 0],
                    ['in', 'id', $orgIds],
                    ['=', 'level', 30]//4S店层级
                ];
                $arrShopList = OrganizationalStructure::find()->where($arrOrgWhere)->asArray()->all();
                if($arrShopList)
                {
                    //格式化店铺列表
                    $arrShopListTmp = [];
                    foreach($arrShopList as $shop)
                    {
                        $arrShopListTmp[] = ['shop_id' => intval($shop['id']), 'shop_name' => strval($shop['name']) ];
                    }
                    //构造返回数据
                    $arrRtn[] = [
                        'role_id' => intval($val['role_id']),
                        'role_name' => strval($arrRoleTmp[$val['role_id']]),
                        'shop_list' => $arrShopListTmp
                    ];
                }
            }
             if($arrRtn)//有数据
            {
                return $arrRtn;
            }
            else//无可用数据
            {
                \Yii::$app->params['code'] = 400;
                \Yii::$app->params['message'] = '不能登录，没有绑定门店';
                return false;
            }
        }
        else
        {
            \Yii::$app->params['code'] = 400;
            \Yii::$app->params['message'] = '不是店长或店员';
            return false;
        }
    }

    /**
     * 发送短信验证码
     */
    public function actionValidateCode()
    {
        $pData = $this->getPData();
        $phone = isset($pData['phone']) ? $pData['phone'] : null;
        if (!$phone) {
            return $this->paramError();
        }
        $obj = new CompanyUserCenter();
        //手机号重置密码
        //第一步 发送短信验证码
        $result = $obj->curlSendPhoneCode($phone);

        if($result['code'] == 0) {
            Yii::$app->params['code'] = 200;
            Yii::$app->params['message'] = '发送成功';
            return true;
        }
        Yii::$app->params['code'] = 400;
        Yii::$app->params['message'] = '发送失败';
        return false;
    }

    /**
     * 修改密码
     *
     * @return array|bool
     */
    public function actionNotifyPassword()
    {
        $pData = $this->getPData();
        $phone = isset($pData['phone']) ? $pData['phone'] : null;
        $password = isset($pData['password']) ? $pData['password'] : null;
        $code = isset($pData['code']) ? $pData['code'] : null;
        if (!$phone || !$password || !$code) {
            return $this->paramError();
        }
        if (!preg_match("/^[a-zA-Z\d_]{6,20}$/", $password)) {
            Yii::$app->params['code']    = 400;
            Yii::$app->params['message'] = '密码不合法';
            return false;
        }
        $obj = new CompanyUserCenter();
        $result = $obj->curlChangePasswordByPhone($phone, $code, $password);
        if($result['code'] == 0) {
            Yii::$app->params['code'] = 200;
            Yii::$app->params['message'] = '修改成功';
            return true;
        }
        Yii::$app->params['code'] = 400;
        Yii::$app->params['message'] = '修改失败';
        return false;
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
            ]
        ];
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $arrImportantPhoneList;
    }
    
    
}
