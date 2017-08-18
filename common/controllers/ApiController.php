<?php
/**
 * Created by PhpStorm.
 * User: liujinxing
 * Date: 2017/6/16
 * Time: 11:18
 */

namespace common\controllers;

use yii;
use yii\helpers\Json;
use common\helpers\Helper;

class ApiController extends \yii\rest\Controller
{
    /**
     * 定义默认验证密钥的盐值
     * @var string
     */
    protected $token = '4ff9fc6e4e5d5f590c4f2134a8cc96d1';

    /**
     * 定义接口默认的版本号
     * @var string
     */
    protected $version = '1.0';

    /**
     * 定义默认的错误信息
     * @var array
     */
    protected $error = [];

    /**
     * 定义默认请求的参数
     * @var array
     */
    public $mixRequest = [];

    /**
     * 定义使用的错误信息[错误码 => 对应错误信息]
     * @var array
     */
    public $errCode = [];   // 错误信息


    /**
     * 定义返回的json数据
     * @var array
     */
    public $json = [
        'code' => 1,    // 错误码
        'msg' => '',    // 错误信息
        'data' => null, // 返回数据
    ];

    /**
     * 处理返回的 JSON 数组
     * @param int   $intCode 错误码
     * @param array $params  返回数组
     */
    protected function handleJson($intCode, $params = [])
    {
        $this->json['code'] = $intCode;
        if (!empty($params)) $this->json = array_merge($this->json, $params);
    }

    /**
     * 返回数组交给 response 返回json 字符串
     * @param  bool  $data 默认空数组 返回之前需要打印日志的信息
     * @return array
     */
    protected function returnJson($data = false)
    {

        if (empty($this->json['msg'])) {
            $this->json['msg'] = $this->errCode[$this->json['code']];
        }

        // 处理日志信息
        if ($data === false) $data = ['request' => $this->mixRequest];
        $data['response'] = $this->json;
        if ($this->error) $data['error'] = $this->error;

        // 记录日志
        $this->writeLogs($data);

        // 响应JSON格式
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $this->json;
    }

    /**
     * 直接输出json字符串暂停
     * @param  int  $intError 默认返回错误2 (请求参数错误)
     * @param string $strMessage 提示信息
     * @return void 执行操作没有返回
     */
    protected function echoJson($intError = 1, $strMessage = '')
    {
        // 设置相应类型
        header('Content-Type: application/json; charset=UTF-8');
        $this->json['code'] = $intError;
        $this->json['msg'] = $strMessage;
        exit(Json::encode($this->returnJson()));
    }

    /**
     * 通过数组的方式将数据写入日志
     * @param array  $arrParams 需要写入的日志
     * @param string $prefix    文件的后缀名
     */
    public function writeLogs($arrParams = [], $prefix = '')
    {
        // 处理请求参数问题
        $arrLogs = [
            'time' => date('Y-m-d H:i:s'),
            'ip' => Helper::getIpAddress(),
        ];

        if (!empty($arrParams)) $arrLogs = array_merge($arrLogs, $arrParams);
        $arrLogs['version'] = $this->version;

        // 记录日志
        Helper::logs($this->module->id.'/'.$this->id.'/'.date('Ymd').'-'.$this->action->id.$prefix.'.log', $arrLogs);
    }

    /**
     * 验证数据是否存在并且不为空
     * @param  array $params 验证的数组数据
     * @param  array $keys   验证存在的必须不为空的key
     * @return bool  验证通过返回true
     */
    protected function validateParams($params, $keys)
    {
        // 验证数据必须存在
        if ($params) {
            foreach ($keys as $value) {
                if (!isset($params[$value]) || empty($params[$value])) return false;
            }

            return true;
        }

        return false;
    }

    /**
     * 生成密钥
     * @param mixed $params
     * @param string|bool $strToken
     * @return bool|string
     */
    protected function sign($params, $strToken = false)
    {
        if (isset($params['sign'])) unset($params['sign']);
        ksort($params);
        $strSign = implode('', $params);
        $strSign .= $this->token;
        $strSign = md5($strSign);
        if ($strToken !== false) $strSign = ($strSign === $strToken);
        return $strSign;
    }

    /**
     * 验证数据存在 并且 密钥正确
     * @param  array $params        验证的数据数组
     * @param  array $keys          必须存在的键组成的数组
     * @param  bool  $isToken      是否验证token
     * @return bool
     */
    protected function validateData($params, $keys, $isToken = true)
    {
        // 请求数据为空
        $isReturn = false;
        if (!empty($params) && is_array($params)) {
            // 验证数据必须存在
            $this->json['code'] = 2;
            if ($this->validateParams($params, $keys)) {
                $this->json['code'] = 3;
                // 验证秘钥
                if ($isToken) {
                    if ($this->sign($params, $params['sign']) === true) $isReturn = true;
                } else {
                    $isReturn = true;
                }
            }
        }

        return $isReturn;
    }

    /**
     * 验证秘钥
     * @param array $data  验证的参数
     * @param string $sign 传递过来的秘钥
     * @param string $prefix 加密数据的前缀字符串
     * @return string
     */
    protected function validateSign($data, $sign, $prefix = '')
    {
        $strToken = md5($prefix . implode('', $data) . $this->token);
//        $this->json['sign'] = $strToken;
        return $strToken === $sign;
    }

    /**
     * 验证请求数据
     * @param  array $params             验证的请求参数
     * @param  array $keys               验证
     * @param  bool  $isValidateToken    是否验证签名
     * @param  string $prefix            验证字符串的前缀
     * @return bool
     */
    public function validateRequest($params, $keys, $isValidateToken = true, $prefix = '')
    {
        $isReturn = false;
        if ($keys) {
            $this->json['code'] = 2;
            $arrRequest = [];
            $isReturn = true;
            foreach ($keys as $value) {
                if (isset($params[$value]) && !empty($params[$value])) {
                    $arrRequest[$value] = $params[$value];
                } else {
                    $isReturn = false;
                    break;
                }
            }

            // 验证数据通过(判断是否验证秘钥)
            if (true === $isReturn && $isValidateToken === true) {
                $this->json['code'] = 3;
                $sign = $arrRequest['sign'];
                unset($arrRequest['sign']);
                $isReturn = $this->validateSign($arrRequest, $sign, $prefix);
            }
        }

        return $isReturn;
    }
}