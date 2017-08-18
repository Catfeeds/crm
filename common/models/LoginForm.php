<?php
namespace common\models;

use Yii;
use yii\base\Model;
use common\common\PublicMethod;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $roleId;
    public $shopId;
    public $roleAndShop;
    public $rememberMe = true;

    private $_user;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password', 'roleId'], 'required'],
            // rememberMe must be a boolean value
//            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->checkUserName()) {
            return Yii::$app->user->login($this->getUser(), 3600 * 24 * 300);
        } else {
            return false;
        }
    }

    /**
     * 登录校验逻辑调整，本方法校验用户名密码 正确返回true 错误返回false
     */
    public function checkUserName()
    {
        $blnRtn = false;
        $objUserCenter = new \common\logic\CompanyUserCenter();
        $result = $objUserCenter->curlLogin($this->username, $this->password);
        //判断，当用户的层级是门店级别的时候，判断用户的角色中是否有店长这个角色，有的话允许登录，否则不允许登录
        if($result['code'] == 0 && !empty($result['userinfo']))//没有userinfo  说明不是本项目人员
        {
            $arrUserInfoTmp = $objUserCenter->getUserInfoById($result['userinfo']['id']);
            $arrUserInfo = !empty($arrUserInfoTmp) ? $arrUserInfoTmp['userinfo'] : [];
            if(empty($arrUserInfo))
            {
                PublicMethod::noticeJump('/site/login', '用户不存在于该项目或者已被注销', 3);
            }
            //session 中没有角色选中信息，走角色选中逻辑
            $intRoleId = $this->roleId;//切换角色
            if ($intRoleId > 0 || !isset($arrUserInfo['permisson_org_ids']) || empty($arrUserInfo['permisson_org_ids'])) {
                $arr = $objUserCenter->getUserRoleOrgIds($arrUserInfo['id'],$intRoleId);
                //数据权限
                $arrUserInfo['permisson_org_ids'] = $arr;
                //目录 uri slug权限
                $objRole = Role::findOne(['id' => $intRoleId]);
                $arrMenusTmp = json_decode($objRole->authority_info, true);  
                $arrUserInfo['menu_url'] = $arrUserInfo['slug'] = [];
                foreach ($arrMenusTmp as $val) {
                    $arrUserInfo['menu_url'][] = trim($val['url']);
                    $arrUserInfo['slug'][] = trim($val['slug']);
                }
                $arrUserInfo['menu_url'] = array_unique($arrUserInfo['menu_url']);
                $arrUserInfo['slug'] = array_unique($arrUserInfo['slug']);
                $arrUserInfo['role_level'] = $objRole->role_level;
            }
            $session = Yii::$app->getSession();
            $session->set('userinfo', $arrUserInfo);
            $session->set('selfLogin', 1);
            $blnRtn = true;
        }
        return $blnRtn;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByPhone($this->username);
        }

        return $this->_user;
    }
}
