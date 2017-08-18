<?php
/**
 * Created by PhpStorm.
 * User: liujx
 * Date: 2017/6/29
 * Time: 13:14
 */

namespace common\traits;

use yii;

/**
 * Trait Json
 * 定义json 返回数据
 * @package common\traits
 */
trait Json
{
    /**
     * 定义返回的 json 数据
     * @var array
     */
    protected $arrJson = [
        'errCode' => 201,
        'errMsg'  => '请求参数为空',
        'data'    => [],
    ];

    /**
     * returnJson() 响应ajax 返回
     * @param  array $array
     * @return mixed|string
     */
    protected function returnJson($array = [])
    {
        if ($array) $this->arrJson = array_merge($this->arrJson, $array) ;

        // 设置JSON返回
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $this->arrJson;
    }

    /**
     * handleJson() 处理返回数据
     * @param mixed $data     返回数据
     * @param int   $errCode  返回状态码
     * @param null  $errMsg   提示信息
     */
    protected function handleJson($data, $errCode = 0, $errMsg = null)
    {
        $this->arrJson['errCode'] = $errCode;
        $this->arrJson['data']    = $data;
        if ($errMsg !== null) {
            $this->arrJson['errMsg'] = $errMsg;
        }
    }
}