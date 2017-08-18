<?php

namespace console\controllers;

use yii;
use common\models\Share;
use yii\console\Controller;

/**
 * Class 清除未分享的数据
 * @package console\controllers
 */
class ShareController extends Controller
{
    public function actionDelete()
    {
        Share::deleteAll(['=','shop_id','0']);
    }
}