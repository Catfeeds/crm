<?php

namespace common\logic;

use Yii;

class  MemberApi
{
    public $tokenKey;//会员中心请求进销存token
    public $url;//进销存接口地址

    public function __construct()
    {
        $jxc            = Yii::$app->params['member'];
        $this->tokenKey = $jxc['tokenKey'];
        $this->url      = $jxc['url'];
    }

}
