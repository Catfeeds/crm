<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/3/10
 * Time: 13:31
 */

namespace common\server;


class HttpException extends \yii\web\HttpException
{
    public function __construct($status, $message = null, $code = 0, \Exception $previous = null)
    {
        $this->statusCode = $status;
        parent::__construct($message, $code, $previous);
    }
}