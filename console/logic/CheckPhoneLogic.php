<?php

namespace console\logic;
use Yii;
use common\models\CheckPhone;

class CheckPhoneLogic extends BaseLogic
{
    /**
     * 清空每天验证首次录入的手机号码
     */
    public function deleteCheckPhone()
    {
        CheckPhone::deleteAll();
    }
}