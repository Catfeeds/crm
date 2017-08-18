<?php
/**
 * 后台登录功能封装
 * User: wangdiao
 * Date: 2017/7/20
 */
namespace backend\logic;

use Yii;
use Jasny\SSO\Broker;
use yii\helpers\ArrayHelper;
use common\server\Logic;
use common\logic\CompanyUserCenter;
use common\common\PublicMethod;
use common\models\Role;

/**
 * 登录逻辑类
 */
class LoginLogic extends Logic
{
    //单点登录配置信息
    private $serverUrl;
    private $brokerId;
    private $brokerSecret;
    private $loginUrl;
    private $selfLoginUrl = '/site/login';//后门登录地址

    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->loginUrl         = Yii::$app->params['quan_xian']['auth_sso_login_url'];//登录页面
        $this->serverUrl        = Yii::$app->params['quan_xian']['auth_sso_url'];//单点登录service地址
        $this->brokerId         = Yii::$app->params['quan_xian']['auth_broker_id'];//项目appID
        $this->brokerSecret     = Yii::$app->params['quan_xian']['auth_broker_secret'];//配置的项目 Secret
    }
    
    /**
     * 后台校验用户是否登录 
     * @param   int         $roleId         角色id，切换角色的时候将角色id传入进来
     * @return  boolean                     true ,校验通过的时候会跳转到相应的登录页面
     */
    public function checkIsLogin($roleId = 0)
    {
        if (!Yii::$app->getSession()->get('userinfo')) {
            $this->nologinJumpToLoginUrl();//未登录
        }
        
        if ($roleId) {
            $userinfo = Yii::$app->getSession()->get('userinfo');
            $this->setRoleInfo($roleId, $userinfo);
            Yii::$app->getSession()->set('userinfo', $userinfo);
        }
        return true;
    }
    
    /**
     * 没有登录的时候跳转到登录页面
     */
    private function nologinJumpToLoginUrl()
    {
        if (Yii::$app->getSession()->get('selfLogin') == 1) {
            header('Location: ' . $this->selfLoginUrl);
        } else {
            $broker = new Broker($this->serverUrl, $this->brokerId, $this->brokerSecret);
            $broker->clearToken();
            header('Location: ' . $this->loginUrl);
        }
        exit();
    }
    
    /**
     * 项目登录功能入口函数，作用是先清除单点登录的token信息，之后307跳转到处理登录逻辑的页面
     * @param   tring   $returnUrl  需要跳转的处理登录逻辑的页面
     */
    public function ssoClearToken($returnUrl)
    {
        $broker = new Broker($this->serverUrl, $this->brokerId, $this->brokerSecret);
        $broker->clearToken();
        $broker->attach($returnUrl);
    }
    
    /**
     * 项目登录时处理登录逻辑的页面，承接上一步清除token
     * @param int $roleId   选中的角色
     */
    public function ssoLogin($roleId = 0)
    {
        $broker         = new Broker($this->serverUrl, $this->brokerId, $this->brokerSecret);
        $broker->attach(true);
        $ssoUserinfo    = $broker->getUserInfo();//获取用户信息，这里会curl单点登录获取用户信息,但是不全
        $userinfo       = $this->getUserinfo($ssoUserinfo['id'], $roleId);
        Yii::$app->getSession()->set('userinfo', $userinfo);
        $this->headerToIndex();
    }
    
    /**
     * 登录完成后获取用户的基本信息，便于后面存储到session中去
     * @param int       $userId         用户id
     * @param int       $roleId         用户选中的角色id
     * @return array    $arrUserInfo    用户的基本信息
     */
    private function getUserinfo($userId, $roleId)
    {
        $userCenter         = new CompanyUserCenter();
        $arrUserInfoTmp     = $userCenter->getUserInfoById($userId);
        $arrUserInfo        = !empty($arrUserInfoTmp) ? $arrUserInfoTmp['userinfo'] : [];
        if (empty($arrUserInfo)) {
            PublicMethod::noticeJump($this->loginUrl, '用户不存在于该项目或者已被注销！', 3);
        }
        $this->setRoleInfo($roleId, $arrUserInfo);
        return $arrUserInfo;
    }
    
    /**
     * 补充完整用户信息中的角色信息模块，根据传入的角色id补全
     * @param int       $roleId         角色id
     * @param array     $arrUserInfo    用户信息，引用传参
     * @return array    $arrUserInfo    补全角色信息后的用户信息
     */
    private function setRoleInfo($roleId, &$arrUserInfo)
    {
        if (!$roleId) {
            $roleId = ($roleId ? $roleId : $this->getDefaultRoleId($arrUserInfo['role_info']));
        }
        if (!in_array($roleId, $arrUserInfo['role_info'])) {
            PublicMethod::noticeJump($this->loginUrl, '没有选择角色或者您没有改角色！', 3);
        }
        
        $objRole = Role::findOne(['id' => $roleId]);
        if (in_array($objRole->remarks, ['salesman'])) {
            PublicMethod::noticeJump($this->loginUrl, '您没有权限使用该项目！', 3);//店员没有权限进入后台
        }
        $arrMenusTmp                        = json_decode($objRole->authority_info, true);
        $arrUserInfo['menu_url']            = array_unique(ArrayHelper::getColumn($arrMenusTmp, 'url'));
        $arrUserInfo['slug']                = array_unique(ArrayHelper::getColumn($arrMenusTmp, 'slug'));
        $arrUserInfo['role_level']          = $objRole->role_level;
        $userCenter                         = new CompanyUserCenter();
        $arrUserInfo['permisson_org_ids']   = $userCenter->getUserRoleOrgIds($arrUserInfo['id'], $roleId);
        return $arrUserInfo;
    }
    
    /**
     * 当用户登录的时候没有选择角色的时候，默认选中一个角色给用户
     * @param array     $roleList   用户拥有的角色列表
     * @return int                  默认选中的角色id
     */
    private function getDefaultRoleId($roleList)
    {
        $roleCount = count($roleList);
        return $roleList[rand(0,$roleCount-1)];
    }
    
    /**
     * 跳转到项目首页
     */
    private function headerToIndex()
    {
        header('Location: /index/index');
        exit;
    }
}