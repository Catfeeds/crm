<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/3/3
 * Time: 17:23
 */

namespace common\auth;


use common\models\User;
use yii\filters\auth\HttpBasicAuth;

class BaseAuth extends HttpBasicAuth
{
    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        $rData = json_decode($request->post('r'), true);
        $pData = json_decode($request->post('p'), true);
        //os_type和 ver_code  从r参数中获取，获取不到从p参数获取，之后再获取不到就为空
        //os_type和 ver_code 参数在登录接口中存放于p参数中，其他接口在r参数中
        $osType = isset($rData['os_type']) ? $rData['os_type'] : ( isset($pData['os_type']) ? $pData['os_type'] : '' );
        $verCode = isset($rData['ver_code']) ? $rData['ver_code'] : ( isset($pData['ver_code']) ? $pData['ver_code'] : 0 );        
        $arrCache = null;
        if (isset($rData['access_token'])) 
        {
            $arrCache = \Yii::$app->cache->get(md5($rData['access_token']) . '_glsb');
        }
        //登录后的缓存信息中存储的是数组的就是新版的，是int型的就是旧版的
        if(is_array($arrCache) || ($osType == 'android' && $verCode >= 12) || $osType == 'ios' )//安卓平台版本号低
        {
            //使用新版的登录逻辑
            return $this->newAuthenticate($user, $request, $response);
        }
        else
        {
            $strLogFile = \Yii::getAlias('@frontend/runtime/old_glsb_login.log');//登录校验log记录，便于查找app登录状态无故丢失的原因
            $strLog = '[' . date('Y-m-d H:i:s') . "] : \n";
            $pData = json_decode($request->post('p'), true);
            $strLog .= "r : " . $request->post('r') . "\n";
            $strLog .= "p : " . $request->post('p') . "\n";
            file_put_contents($strLogFile, $strLog . "\n####\n", FILE_APPEND);
            //使用旧版的登录逻辑
            return $this->oldAuthenticate($user, $request, $response);
        }
    }
    
    
    private function newAuthenticate($user, $request, $response)
    {
        $strLogFile = \Yii::getAlias('@frontend/runtime/login_error_glsb.log');//登录校验log记录，便于查找app登录状态无故丢失的原因
        $strLog = '[' . date('Y-m-d H:i:s') . '] : ';
        header("Content-Type:application/json; charset=UTF-8");
        $rData = json_decode($request->post('r'), true);
        $pData = json_decode($request->post('p'), true);
        \Yii::$app->response->format = 'json';
        if (empty($rData) && empty($pData)) {
            file_put_contents($strLogFile, $strLog . "参数不全 r, p;\n", FILE_APPEND);
            die(json_encode(['code' => 401, 'msg' => '参数不全', 'data' => null]));
        }

        if ($this->auth) {
            $phone = $pData['phone'];
            $password = $pData['password'];
            $selectRoleId = isset($pData['role_id']) ? $pData['role_id'] : 0;//登录时传过来 角色ID
            $selectShopId = isset($pData['shop_id']) ? $pData['shop_id'] : 0;//登录时传过来 角色ID
            if (!empty($phone) && !empty($password) && !empty($selectRoleId) ){
                $result = call_user_func($this->auth, $phone, $password, $selectRoleId, $selectShopId);
                if (is_object($result)) {
                    $identity = $result;
                    file_put_contents($strLogFile, $strLog . "$phone 登录成功;\n", FILE_APPEND);
                    $arrSave = [
                        'id' => $identity->id,
                        'role_id' => $selectRoleId,
                        'shop_id' => $selectShopId
                    ];
                    \Yii::$app->cache->set(md5($identity->access_token) . '_glsb', $arrSave);
                    $user->switchIdentity($identity);
                } else {
                    file_put_contents($strLogFile, $strLog . "$phone 用户名或密码错误，或者没权限登陆;\n", FILE_APPEND);
                    die(json_encode(['code' => 403, 'msg' => $result, 'data' => null]));
                }
                return $identity;
            }
        } else {
            if (!isset($rData['access_token'])) {
                file_put_contents($strLogFile, $strLog . "access_token 参数丢失;\n", FILE_APPEND);
                die(json_encode(['code' => 401, 'msg' => 'access_token no find', 'data' => null]));
            }
            $accessToken = $rData['access_token'];
            $arrCache = \Yii::$app->cache->get(md5($accessToken) . '_glsb');
            $id = intval($arrCache['id']);
            if ($id) {
                $identity = User::findOne($id);
                if ($identity === null) {
                    file_put_contents($strLogFile, $strLog . "access_token 缓存的id无效了;\n", FILE_APPEND);
                    die(json_encode(['code' => 401, 'msg' => '请重新登录', 'data' => null]));
                }
                elseif($identity->is_delete == 1)
                {
                    die(json_encode(['code' => 401, 'message' => '该用户已被注销', 'data' => null]));
                }
                $user->switchIdentity($identity);
                return $identity;
            }
            file_put_contents($strLogFile, $strLog . "access_token 缓存失效了;\n", FILE_APPEND);
            die(json_encode(['code' => 401, 'msg' => '请重新登录', 'data' => null]));
        }

        return null;
    }
    
    
    private function oldAuthenticate($user, $request, $response)
    {
        $strLogFile = \Yii::getAlias('@frontend/runtime/login_error_glsb.log');//登录校验log记录，便于查找app登录状态无故丢失的原因
        $strLog = '[' . date('Y-m-d H:i:s') . '] : ';
        header("Content-Type:application/json; charset=UTF-8");
        $rData = json_decode($request->post('r'), true);
        $pData = json_decode($request->post('p'), true);
        \Yii::$app->response->format = 'json';
        if (empty($rData) && empty($pData)) {
            file_put_contents($strLogFile, $strLog . "参数不全 r, p;\n", FILE_APPEND);
            die(json_encode(['code' => 401, 'msg' => '参数不全', 'data' => null]));
        }

        if ($this->auth) {
            $phone = $pData['phone'];
            $password = $pData['password'];
            if ($phone !== null || $password !== null) {
                $result = call_user_func($this->auth, $phone, $password);
                if (is_object($result)) {
                    $identity = $result;
                    file_put_contents($strLogFile, $strLog . "$phone 登录成功;\n", FILE_APPEND);
                    \Yii::$app->cache->set(md5($identity->access_token), $identity->id);
//                    if (isset($pData['huawei_push_token']) && $pData['huawei_push_token'] != $identity->huawei_push_token) {
//                        $identity->huawei_push_token = $pData['huawei_push_token'];
//                        $identity->save();
//                    }
                    $user->switchIdentity($identity);
                } else {
                    file_put_contents($strLogFile, $strLog . "$phone 用户名或密码错误，或者没权限登陆;\n", FILE_APPEND);
                    die(json_encode(['code' => 403, 'msg' => $result, 'data' => null]));
                }
                return $identity;
            }
        } else {
            if (!isset($rData['access_token'])) {
                file_put_contents($strLogFile, $strLog . "access_token 参数丢失;\n", FILE_APPEND);
                die(json_encode(['code' => 401, 'msg' => 'access_token no find', 'data' => null]));
            }
            
            //旧版本的，如果不是更新检查接口则直接退出，提示500错误需要更新升级
            if( !(\Yii::$app->controller->id == 'self-update' && \Yii::$app->controller->action->id == 'check-version') )//不是更新检查接口
            {
//                die(json_encode(['code' => 500, 'message' => '旧版本已经不能使用，请升级最新版本', 'data' => null]));
            }
            
            $accessToken = $rData['access_token'];
            $id = \Yii::$app->cache->get(md5($accessToken));
            if ($id) {
                $identity = User::findOne($id);
                if ($identity === null) {
                    file_put_contents($strLogFile, $strLog . "access_token 缓存的id无效了;\n", FILE_APPEND);
                    die(json_encode(['code' => 401, 'msg' => '请重新登录', 'data' => null]));
                }
                elseif($identity->is_delete == 1)
                {
                    die(json_encode(['code' => 401, 'message' => '该用户已被注销', 'data' => null]));
                }
                $user->switchIdentity($identity);
                return $identity;
            }
            file_put_contents($strLogFile, $strLog . "access_token 缓存失效了;\n", FILE_APPEND);
            die(json_encode(['code' => 401, 'msg' => '请重新登录', 'data' => null]));
        }
        return null;
    }
    
}