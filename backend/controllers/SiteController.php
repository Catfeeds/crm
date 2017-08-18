<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use backend\logic\LoginLogic;
use Jasny\SSO\Broker;//单点登录包
use common\models\User;
use common\models\Role;
use common\models\UserRoleOrgids;
use common\models\OrganizationalStructure;
/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error', 'ssologin', 'sso-relogin','new-error', 'get-select-roles'],
                        'allow' => true,
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
//                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }
    
    /**
     * 自定义报错页面，屏蔽掉报错信息
     */
    public function actionNewError()
    {
        //错误页面
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        $session = Yii::$app->getSession();
        if (isset($session['selfLogin'])  && $session['selfLogin'] == 1 && isset($session['userinfo'])) {//已经登录过的直接进入到首页
            return $this->redirect('/index/index');
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect('/index/index');
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }
    
    //项目登录接口 - 只做清除token之后跳到逻辑处理页面
    public function actionSsologin()
    {
        $queryString = (isset($_SERVER['REDIRECT_QUERY_STRING']) ? '?' . $_SERVER['REDIRECT_QUERY_STRING'] : '');
        $returnUrl = "http://{$_SERVER['HTTP_HOST']}/site/sso-relogin{$queryString}";
        $objLoginLogic = LoginLogic::instance();
        $objLoginLogic->ssoClearToken($returnUrl);
    }
    
    //项目登录逻辑处理接口
    public function actionSsoRelogin()
    {
        $objLoginLogic = LoginLogic::instance();
        $roleId = \Yii::$app->request->get('role_id', 0);
        $objLoginLogic->ssoLogin($roleId);
    }
    
    //get-select-roles
    public function actionGetSelectRoles()
    {
        $arrRtn = ['code' => 0, 'msg' => '', 'data' => []];
        $phone = \Yii::$app->request->get('phone', '');//13041665260
        $user = User::findOne(['phone' => $phone, 'is_delete' => 0]);
        if (empty($user)) {
            $arrRtn['code'] = 1;//
            $arrRtn['msg'] = '手机号无效';
        } else {
            //找这个人的角色信息和可选门店信息
            $roleIds = explode(',', $user->role_info);
            $orWhere = [
                'or',
                ['<', 'role_level', 30],//非店铺层级角色 或者店长
                ['=', 'remarks', 'shopowner']
            ]; 
            $arrRoleList = Role::find()->select('id,remarks,name')->where(['in', 'id', $roleIds])
                    ->andWhere($orWhere)
                    ->orderBy('role_level asc')
                    ->asArray()->all();
            //获取所有角色信息
            if ($arrRoleList) {
                foreach ($arrRoleList as $val) {
                    if ($val['remarks'] == 'shopowner') {
                        //店长角色需要提供可选的门店，其他角色不需要，直接选中角色就行 -- 获取可选门店
                        $objUserRoleOrgids = UserRoleOrgids::findOne(['user_id' => $user->id, 'role_id' => $val['id']]);
                        if ($objUserRoleOrgids && !empty($objUserRoleOrgids->org_ids)) {
                            $orgIds = explode(',', $objUserRoleOrgids->org_ids);
                            $arrOrgWhere = [
                                'and',
                                ['=', 'is_delete', 0],
                                ['in', 'id', $orgIds],
                                ['=', 'level', 30]//4S店层级
                            ];
                            $arrShopList = OrganizationalStructure::find()->where($arrOrgWhere)->asArray()->all();
                            if ($arrShopList) {
                                //格式化店铺列表
                                $arrShopListTmp = [];
                                foreach ($arrShopList as $shop) {
                                    $arrShopListTmp[] = ['shop_id' => intval($shop['id']), 'shop_name' => strval($shop['name']) ];
                                }
                                //构造返回数据
                                $arrRtn['data'][] = [
                                    'role_id' => intval($val['id']),
                                    'role_name' => strval($val['name']),
                                    'shop_list' => $arrShopListTmp
                                ];
                            }
                        }
                    } else {
                        $arrRtn['data'][] = [
                            'role_id' => intval($val['id']),
                            'role_name' => strval($val['name']),
                        ];
                    }
                }
                if (empty($arrRtn['data'])) {
                    $arrRtn['code'] = 2;
                    $arrRtn['msg'] = '不能登录，没有合适的角色供选择';
                }
            } else {
                $arrRtn['code'] = 3;
                $arrRtn['msg'] = '不能登录，没有合适的角色供选择';
            }
        }
        die(json_encode($arrRtn));
    }
}
