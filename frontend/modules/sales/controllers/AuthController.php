<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/3/6
 * Time: 16:37
 */

namespace frontend\modules\sales\controllers;


use common\auth\Auth;
use common\helpers\Helper;
use yii;

/**
 * 需要验证身份控制器
 * Class AuthController
 * @package frontend\modules\v1\controllers
 */
class AuthController extends BaseController
{

    /**
     * 定义行为
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => Auth::className(),
        ];
        return $behaviors;
    }

    /**
     * 创建订单id
     */
    public function createOrderId()
    {
        return date('YmdHis') . rand(100,999) . rand(100, 999);
    }

    // 第三方进销存接口错误日志
    public function writeErrorLog($error) {
        $rootPath = \Yii::getAlias('@frontend/runtime/logs/jxc/');
        if (! file_exists ( $rootPath ))
            mkdir ( $rootPath, 0777, true );

        file_put_contents($rootPath.date("Y-m-d").'.jxc.log',date("Y-m-d H:i:s")."\t".
            'url=>'. \Yii::$app->request->url."\t".
            'mes=>'.$error."\n"
            , FILE_APPEND);

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
     * handleParams() 处理返回参数
     * @param string $message 返回提示信息
     * @param int $code 状态码
     */
    protected function handleParams($message, $code = 400)
    {
        Yii::$app->params['code'] = $code;
        Yii::$app->params['message'] = $message;
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

}