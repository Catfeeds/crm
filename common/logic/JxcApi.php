<?php

namespace common\logic;

use Yii;
use yii\base\Object;

class  JxcApi
{
    public $b_token;//crm请求进销存token
    public $z_token;//进销存请求crm token
    public $url;//进销存接口地址

    public function __construct()
    {
        $jxc           = Yii::$app->params['jxc'];
        $this->b_token = $jxc['b_token'];
        $this->z_token = $jxc['z_token'];
        $this->url     = $jxc['url'];
    }

    /**
     * 发送post请求
     * @param string $url 请求地址
     * @param array $post_data post键值对数据
     * @return string
     */
    public static function send_post($url, $post_data, $timeout = 5)
    {
        $postdata = http_build_query($post_data);
        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL,$url.'?'.$postdata);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_HEADER, false);

        $result = curl_exec($ch);
        $rinfo=curl_getinfo($ch);

        if ($rinfo['http_code'] == 0) {
            $obj = (object)array();
            $obj->statusCode = 0;
            $obj->content = '请求地址异常错误！';
            return json_encode($obj);
        }

        return $result;


//        $postdata = http_build_query($post_data);
//
//        $options = array(
//            'http' => array(
//                'method' => 'POST',
//                'header' => 'Content-type:application/x-www-form-urlencoded',
//                'content' => $postdata,
//                'timeout' => 15 * 60 // 超时时间（单位:s）
//            )
//        );
//        $context = stream_context_create($options);
//
//        $result = file_get_contents($url, false, $context);
//
//        return $result;
    }
}
