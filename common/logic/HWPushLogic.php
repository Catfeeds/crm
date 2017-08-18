<?php
/**
 * Created by PhpStorm.
 * User: lzx
 * Date: 2017/3/20
 * Time: 16:38
 */

namespace common\logic;

use common\helpers\Helper;
use common\models\User;
use JPush\Client;  //v1.2无此功能
use Yii;
use common\models\NoticeInbox;
use common\models\NoticeSend;

/**
 * 推送控制器
 * Class PushController
 * @package frontend\modules\v1\controllers
 * @desc 推送流程
 *  --- 1、先将推送人、接收人、标题、内容等写入数据 对应方法 $this->saveData()
 *  --- 2、通过用户使用的app 版本 获取到 推送的 android intent 信息 $this->getHuaweipushString()
 *  --- 3、执行华为推送 $this->pushMsg()
 */
class HWPushLogic
{
    // 定义华为推送的地址
    private $strPushUrl = 'https://api.vmall.com/rest.php';

    // 定义华为获取授权地址
    private $strAccessTokenUrl = 'https://login.vmall.com/oauth2/token';

    /**
     * androidPushMsg() 推送消息
     * @param  int    $sendId          发送者
     * @param  string $user_id_list    接收者
     * @param  string $push_title      消息标题
     * @param  string $notice_title    消息标题
     * @param  int    $notice_type     消息类型
     * @param  string $app_type        app 类型 sales or glsb
     * @param  string $content         消息内容
     * @param  string $notice_param    其他参数
     * @param  string $func            是否更加功能推送 android 中 intent 内容
     * @return bool
     */
    public function androidPushMsg($sendId, $user_id_list, $push_title, $notice_title, $notice_type, $app_type, $content = '', $notice_param = '', $func = '')
    {
        $isReturn = true;
        $intTime = time();

        // 判断信息保存是否成功
        $intNoticeSendId = $this->saveData($sendId, $notice_title, $user_id_list, $intTime, $notice_type, $content, $notice_param);
        if ($intNoticeSendId) {

            // 用以判断是否所有用户token都未获取
            $is_empty = 0;

            // 收件人
            $arrUserIds = explode(',', $user_id_list);

            // 推送消息到多个用户  考虑到不同用户APP版本可能不同  按照用户循环 推送消息
            foreach ($arrUserIds as $user_id) {
                // 变量初始化
                $deviceTokenArr = array();

                // 获取存储华为token的键值 $app_type 为 sales 或 glsb
                $cache_key = $app_type.'android'.$user_id;

                // 取出华为token
                $hwtoken = Yii::$app->cache->get(md5($cache_key));

                if ($hwtoken) {
                    // 取出华为token和版本信息
                    $hwtoken_arr = explode('|||', $hwtoken);
                    $deviceTokenArr[] = $hwtoken_arr[0];

                    // 获取的huawei token 才处理
                    if ($deviceTokenArr) {
                        $is_empty = 1;

                        // 取出版本信息 如果为空默认0 兼容老版本
                        $ver_code = isset($hwtoken_arr[1]) ? $hwtoken_arr[1] : 0 ;

                        // 取出intent键值  如果不为空 拼接code值  否则使用当前APP参数
                        $intent_code = $func ? $func.'_'.$app_type : $app_type;

                        // 获取intent 信息
                        $intent = $this->getHuaweipushString($intent_code, $ver_code);

                        // 如果参数为空  则判定当前APP版本没有此功能
                        if ($intent){
                            // 推送人信息需要 urlencode 处理
                            $deviceTokenList = urlencode(json_encode($deviceTokenArr));

                            // 执行推送
                            $android = [
                                'intent' => $intent,
                                'notification_title' => (string)$push_title,
                                'notification_content' => '打开应用',
                                'doings' => 2,
                            ];

                            $android_str = urlencode(json_encode($android));
                            $mixReturn = $this->pushMsg($android_str, $deviceTokenList, $intNoticeSendId, $intTime, $app_type);

                            // 记录日志
                            Helper::logs('huawei/'.date('Ymd').'-send.log', [
                                'time' => date('Y-m-d H:i:s'),
                                'params' => [
                                    'user_id' => $user_id,
                                    'deviceToken' => $deviceTokenArr,
                                    'android' => $android
                                ],
                                'return' => $mixReturn,
                            ]);
                        }
                    }
                }
            }

            // 保存极光推送alias值
            $arrAlias = [];
            $strKey = $app_type === 'sales' ? 'Jpushsalesios' : 'Jpushglsbios';
            foreach ($arrUserIds as $intUserId) {
                $strAlias = Yii::$app->cache->get(md5($strKey . $intUserId));
                if ($strAlias) $arrAlias[] = $strAlias;
            }

            // 获取当前数据对象
            $notice_send = NoticeSend::findOne($intNoticeSendId);

            // 如果华为token和极光alias全部为空 保存失败信息
            if ($is_empty == 0 && empty($arrAlias)){
                $message = '获取接收人token失败';
                $isReturn = false;
            } else {
                $message = '';
                // 发送极光推送
                if (!empty($arrAlias)) {
                    $app_key = '4deac7a435c7287d4bd224ec';
                    $master_secret = 'ae78e5cac143f9973c22cde2';
                    $rootPath = \Yii::getAlias('@backend/runtime/logs/');
                    $client = new Client($app_key, $master_secret,$rootPath.'jpush.log');

                    $pusher = $client->push();
                    $pusher->setPlatform('ios');
                    $pusher->addAlias($arrAlias);
                    $pusher->setNotificationAlert($push_title);

                    try {
                        $pusher->send();
                        $message = '--success';
                    } catch (\JPush\Exceptions\JPushException $e) {
                        $message = '-- code: '.$e->getCode() .' message:' . $e->getMessage();
                        $isReturn = false;
                    }
                }
            }

            // 修改错误信息
            if ($notice_send) {
                $notice_send->huawei_request_fail_des = $notice_send->huawei_request_fail_des . $message;
                $notice_send->save();
            }
        }

        return $isReturn;
    }

    /**
     * saveData() 保存推送消息
     * @param  int      $sendId         推送人
     * @param  string   $title          消息标题
     * @param  string   $user_id_list   接收人字符串列表
     * @param  int      $time           推送时间
     * @param  int      $notice_type    消息类型
     * @param  string   $content        消息内容
     * @param  string   $notice_param   其他参数
     * @return bool
     */
    private function saveData($sendId, $title, $user_id_list, $time, $notice_type, $content, $notice_param)
    {
        // 确定发送人
        $strSendName = '总部';
        if ($sendId != 0) {
            $user = User::findOne($sendId);
            $strSendName = $user ? $user->name : '未知';
        }

        // 根据需接收推送的user_id 获取pushtoken 值
        $arrUserIds = explode(',', $user_id_list);

        // 查询user对应的token
        $arrUsers = User::find()->select('name')->where(['in', 'id', $arrUserIds])->asArray()->all();

        // 接受用户姓名列表
        $arrNames = array_column($arrUsers, 'name');
        $strName = implode(',', $arrNames);

        // 默认返回false
        $isReturn = false;

        // 开启事务
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {

            // 保存推送信息
            $notice_send_model = new NoticeSend();
            $notice_send_model->send_person_id   = $sendId;
            $notice_send_model->addressee_id     = strval($user_id_list);
            $notice_send_model->addressee_des    = $strName;
            $notice_send_model->title            = $title;
            $notice_send_model->send_person_name = $strSendName;
            $notice_send_model->content          = $content;
            $notice_send_model->send_time        = $time;

            // 保存数据
            if ($notice_send_model->save()) {

                // 分发到收件箱模型
                $arrInserts = [];
                foreach ($arrUserIds as $item) {
                    $arrInserts[] = [
                        'send_person_id' => $notice_send_model->send_person_id,
                        'get_person_id' => intval($item),
                        'addressee_des' => $notice_send_model->addressee_des,
                        'title' => $notice_send_model->title,
                        'content' => $notice_send_model->content,
                        'notice_param' => $notice_param,
                        'send_time' => $notice_send_model->send_time,
                        'notice_type' => $notice_type,
                        'send_person_name' => $notice_send_model->send_person_name,
                        'send_id' => $notice_send_model->id,
                    ];
                }

                // 多条插入
                $isReturn = $db->createCommand()
                    ->batchInsert(NoticeInbox::tableName(), [
                        'send_person_id', 'get_person_id', 'addressee_des',
                        'title', 'content', 'notice_param', 'send_time',
                        'notice_type', 'send_person_name', 'send_id'
                    ], $arrInserts)
                    ->execute();
            }

            // 最终成功提交这个事物
            if ($isReturn) {
                $transaction->commit();
                $isReturn = $notice_send_model->id;
            } else {
                $transaction->rollBack();
                $isReturn = false;
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            $isReturn = false;
        }

        return $isReturn;
    }

    /**
     * getHuaweipushString() 根据当前版本号判断使用的推送参数(intent参数)
     * @param  string   $os_type
     * @param  int      $ver_code
     * @return string
     */
    public function getHuaweipushString($os_type, $ver_code)
    {
        $strReturn = '';
        if (isset(Yii::$app->params['huaweipush_string'][$os_type])) {
            $arrHuaWeiPush = Yii::$app->params['huaweipush_string'][$os_type];

            // 取出参数数据  按照大小排序
            ksort($arrHuaWeiPush);

            // 遍历数组 取出小于当前版本号的最大版本号对应值
            foreach ($arrHuaWeiPush as $k => $v) {
                if ($k > $ver_code) {
                    break;
                } else {
                    $strReturn = $v;
                }
            }
        }

        // 返回数据
        return $strReturn;
    }

    /**
     * pushMsg() 推送消息
     * @param  string $android_str      推送的 android_str 字符串信息
     * @param  string $deviceTokenList
     * @param  int    $notice_send_id   推送的消息信息
     * @param  string $nsp_ts
     * @param  string $app_type
     * @return bool
     */
    private function pushMsg($android_str, $deviceTokenList, $notice_send_id, $nsp_ts, $app_type)
    {
        // 默认返回
        $isReturn = false;

        // 获取到access token
        $strAccessToken = $this->getRedisAccessToken($app_type);

        if ($strAccessToken) {
            // 执行推送 处理返回数据
            $data = $this->executepush($strAccessToken, $android_str, $deviceTokenList, $nsp_ts);
            $response = json_decode($data, true);

            // 获取到推送的消息对象
            $notice_send = NoticeSend::findOne($notice_send_id);

            // 判断是否请求错误
            if (isset($response['error']) || (isset($response['message']) && $response['message'] != 'success')) {
                // 定义错误信息
                if (isset($response['error'])) {
                    $message = $response['error'];
                } elseif (isset($response['message'])) {
                    $message = $response['message'];
                } else {
                    $message = '推送失败';
                }

                // access_token 已经过期 重新生成 access token
                if ($message === 'session timeout' && $this->getAccessToken($app_type)) {
                    $strAccessToken = $this->getRedisAccessToken($app_type);
                    if ($strAccessToken) {
                        $data = $this->executepush($strAccessToken, $android_str, $deviceTokenList, $nsp_ts);
                        $response = json_decode($data, true);
                        $isReturn = true;
                    }
                }
            } else {
                $isReturn = true;
                $message = '';
            }

            // 推送成功有返回值
            if ($isReturn) {
                // 判断是否推送成功
                if ($response && isset($response['requestID']) && isset($response['resultcode']) && isset($response['message'])) {
                    // 推送返回的 request_id
                    if ($notice_send) $notice_send->huawei_push_request_id = $response['requestID'];

                    $message = $response['message'];
                    // 判断推送到达率
                    $isReturn = $response['resultcode'] == 0;
                } else {

                    // 定义错误信息
                    if (isset($response['error'])) {
                        $message = $response['error'];
                    } elseif (isset($response['message'])) {
                        $message = $response['message'];
                    } else {
                        $message = '推送失败';
                    }

                    $isReturn = false;
                }
            }

            // 判断存在推送消息
            if ($notice_send) {
                $notice_send->huawei_request_fail_des = $message;
                $notice_send->save();
            }
        }

        return $isReturn;
    }

    /**
     * executepush() 执行推送 返回结果状态
     * @param  string $access_token        推送的token
     * @param  string $android_str         推送的其它信息
     * @param  string $deviceTokenList     推送人信息
     * @param  string $nsp_ts              推送时间戳
     * @return mixed
     */
    private function executepush($access_token, $android_str, $deviceTokenList, $nsp_ts)
    {
        // 请求地址 https://api.vmall.com/rest.php
        $url = $this->strPushUrl;

        $access_token = urlencode($access_token);

        // 消息
        $nsp_svc = 'openpush.message.psBatchSend';

        // 拼接请求参数(使用字符串因为有些信息已经经过[urlencode]处理)
        $str = "access_token={$access_token}&android={$android_str}&nsp_svc={$nsp_svc}&deviceTokenList={$deviceTokenList}&nsp_ts={$nsp_ts}";
        // 固定参数
        $str .= '&msgType=1&cacheMode=1&nsp_fmt=JSON';

        // 执行请求获取返回值
        $response = $this->getCurlResponse($url, $str);

        // 记录请求日志
        Helper::logs('huawei/'.date('Ymd').'-push.log', [
            'time' => date('Y-m-d H:i:s'),
            'url' => $url,
            'request' => [
                'access_token' => $access_token,
                'android' => $android_str,
                'deviceTokenList' => $deviceTokenList,
                'nsp_ts' => $nsp_ts,
            ],
            'response' => $response,
        ]);

        return $response;
    }

    /**
     * getAccessToken() 获取推送access_token 并保存 redis
     * @param  string $app_type 推送类型 sales 和 glsb ()
     * @return bool
     */
    private function getAccessToken($app_type)
    {
        // 请求地址 https://login.vmall.com/oauth2/token
        $url = $this->strAccessTokenUrl;

        // 确定使用的参数
        if ($app_type == 'sales') {
            $str = 'grant_type=client_credentials&client_secret=8ce777c4fb9de609230bc8f4416e40ee&client_id=10908029';// 拼接post数据
        } else {
            $str = 'grant_type=client_credentials&client_secret=16a17874c21483827c4b2ff99e051916&client_id=10908039';// 拼接post数据
        }

        // 执行请求获取返回值
        $response = $this->getCurlResponse($url, $str);
        $response = json_decode($response, true);

        // 记录请求日志
        Helper::logs('huawei/'.date('Ym').'-get-access-token.log', [
            'time' => date('Y-m-d H:i:s'),
            'url' => $url,
            'request' => $str,
            'response' => $response
        ]);

        // 确认返回数据 保存数据
        if ($response && isset($response['access_token'])) {
            // 确定存储的redis key
            $strRedisKey = $app_type === 'sales' ? 'sales_huawei_push_access_token' : 'glsb_huawei_push_access_token';
            // 重新存入缓存(过期时间为一周)
            Yii::$app->cache->set($strRedisKey, $response['access_token'], !empty($response['expires_in']) ? $response['expires_in'] : 604800);
            return true;
        } else {
            return false;
        }
    }

    /**
     * getRedisAccessToken() 获取redis 中的 access_token
     * @param  string $strAppType 类型
     * @return mixed
     */
    private function getRedisAccessToken($strAppType)
    {
        // 使用的redis key
        $strRedisKey = $strAppType === 'sales' ? 'sales_huawei_push_access_token' : 'glsb_huawei_push_access_token';
        $strAccessToken = Yii::$app->cache->get($strRedisKey);
        // redis key 过期，表示 $access_token 已经过期
        if (!$strAccessToken) {
            if ($this->getAccessToken($strAppType)) {
                $strAccessToken = Yii::$app->cache->get($strRedisKey);
            }
        }

        return $strAccessToken;
    }

    /**
     * 获取curl 请求返回数据
     * @param string $url       请求的地址
     * @param string $params    请求的参数
     * @param array $options    curl 的其它配置
     * @return mixed
     */
    private function getCurlResponse($url, $params, $options = [])
    {
        // curl 执行请求
        $ch = curl_init($url);
        // 设置默认配置
        $default = [
            CURLOPT_POST => true,               // 默认使用POST 请求
            CURLOPT_SSL_VERIFYPEER => false,    // 不验证证书
            CURLOPT_POSTFIELDS => $params,      // 请求参数
            CURLOPT_RETURNTRANSFER => 1         // 不直接输出内容
        ];

        // 存在其他curl 配置
        if ($options) {
            foreach ($options as $key => $value) {
                $default[$key] = $value;
            }
        }

        // 设置请求配置
        curl_setopt_array($ch, $default);

        // 执行请求获取返回信息
        $data = curl_exec($ch);

        // 关闭连接
        curl_close($ch);

        // 返回数据
        return $data;
    }
}