<?php

namespace frontend\modules\sales\logic;

use common\helpers\Helper;
use common\models\Customer;
use common\common\PublicMethod;
use common\logic\MemberApi;
use common\models\CustomerErrorLog;
use yii\helpers\Json;

/**
 * 会员业务逻辑
 * 于凯
 */
class MemberLogic
{
    /**
     * 注册会员
     */
    public function addMember($customer_id)
    {
        //检测是否已经注册会员
        $customer = Customer::findOne($customer_id);
        if (empty($customer->member_id)) {

            $url    = 'inside/user/reg';
            //没有检测到会员id 注册会员
            $data['phone'] = $customer->phone;
            $jsonData = $this->memberHttpPostParames($url,$data);
            if ($jsonData['err_code'] == 0){
                $customer->member_id = $jsonData['data']['uid'];
                if ($customer->save())return true;
            } else {
                // edited by liujx 2017-07-10 注册失败，添加记录方便定时任务执行 start :
                if (!CustomerErrorLog::findOrInsert($customer->phone)) {
                    Helper::logs('error/'.date('Ymd').'-add-member-error.log', [
                        'time' => date('Y-m-d H:i:s'),
                        'customer' => $customer->toArray(),
                        'error' => '请求用户中心返回错误',
                        'response' => $jsonData
                    ]);
                }
                // end
            }
            return false;
        }
        return true;
    }

    /**
     * 会员接口
     * @param $params
     * @return mixed
     */
    public function memberHttpPostParames($url,$data) {

        $member = new MemberApi();
        $httpUrl = $member->url.$url;
        $params = [
            'client_type' => 'pc',
            'domain' => 'crm',
        ];
        foreach ($data as $k => $v) {
            $params[$k] = $v;
        }

        ksort($params); // 对请求的参数升序排序
        $tokenKey = $member->tokenKey;

        $paramStr = http_build_query($params); // 生成URL-encode

        $paramStr = $paramStr . $tokenKey; // 连接申请的tokenKey

        $accessToken = md5($paramStr); // md5 加密

        $params['access_token'] = $accessToken; // 将生成的token放到请求参数中

        $jsonData = json_decode(PublicMethod::http_post($httpUrl, $params), true);
        $this->writeErrorLog($httpUrl,json_encode($jsonData));
        return $jsonData;
    }

    //记录注册日志
    public function writeErrorLog($httpUrl,$jsonData) {
        $rootPath = \Yii::getAlias('@frontend/runtime/logs/');
        file_put_contents($rootPath.'member_res.log',date("Y-m-d H:i:s")."\t".
            'url=>'. (PHP_SAPI === 'cli' ? '' : \Yii::$app->request->url)."\t".
            'mes=>'.$httpUrl.'--->返回结果'.$jsonData."\n"
            , FILE_APPEND);
    }
    private function dump($data) {
        echo "<pre>";
        print_r($data);
        exit;
    }

    private $mixRequestInfo = [];

    /**
     * getRequestInfo() 获取请求信息
     * @return array
     */
    public function getRequestInfo()
    {
        return $this->mixRequestInfo;
    }

    /**
     * get() 向用户中心发送 get 请求
     * @param  string $url    用户中心接口名
     * @param  array  $params 请求参数
     * @return bool|mixed
     */
    public function get($url, $params)
    {
        return $this->httpRequest('get', $url, $params);
    }

    /**
     * post() 向用户中心发送POST请求
     * @param  string $url    用户中心接口名
     * @param  array  $params 请求参数
     * @return bool|mixed
     */
    public function post($url, $params)
    {
        return $this->httpRequest('post', $url, $params);
    }

    /**
     * httpRequest() 向用户中心发送请求
     * @param  string $method   请求方法 get 和 post
     * @param  string $url      请求的API接口名称
     * @param  array  $params   请求的参数
     * @return bool|mixed
     */
    public function httpRequest($method = 'get', $url, $params)
    {
        $member = new MemberApi();
        // 处理请求地址
        $url = rtrim($member->url, '/') . '/' . ltrim($url, '/');

        // 添加默认请求参数
        $params = array_merge(['client_type' => 'pc', 'domain' => 'crm'], $params);

        // 处理请求参数
        $params['access_token'] = $this->getAccessToken($params, $member->tokenKey);

        // 记录请求信息
        $this->mixRequestInfo = [
            'url' => $url,
            'request' => $params,
        ];

        // 发送请求
        try {
            if ($method === 'post') {
                $response = PublicMethod::http_post($url, $params);
            } else {
                $response = PublicMethod::http_get($url, $params);
            }
        } catch (\Exception $e) {
            $response = null;
            $this->mixRequestInfo['error'] = $e->getMessage();
        }

        // 记录请求信息
        $this->mixRequestInfo['response'] = $response;


        // 处理返回
        if ($response) {
            $mixReturn = Json::decode($response);
        } else {
            $mixReturn = false;
        }

        return $mixReturn;
    }

    /**
     * getAccessToken() 获取ACCESS_TOKEN
     * @param  array  $params   请求参数
     * @param  string $tokenKey 加密密签名
     * @return string
     */
    public function getAccessToken($params, $tokenKey)
    {
        // 1.对请求的参数升序排序
        ksort($params);
        // 2.生成URL-encode
        $strToken = http_build_query($params);
        // 3.连接申请的tokenKey
        $strToken .= $tokenKey;
        // 4 返回加密
        return md5($strToken);
    }
}
