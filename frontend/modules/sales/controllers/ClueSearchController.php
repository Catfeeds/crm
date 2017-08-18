<?php
/**
 * 任务-搜索
 * Created by PhpStorm.
 * User: yukai
 * Date: 2017/3/16
 */

namespace frontend\modules\sales\controllers;

use Yii;
use frontend\modules\sales\logic\ClueSearch;


class ClueSearchController extends AuthController
{

    /**
     *
     */
    public function actionIndex()
    {


        $params = null;

        $strKeyWord = null;

        if (!empty($_POST['p'])) {

            $post = json_decode($_POST['p']);

            $clueSearch = new ClueSearch();
            return $clueSearch->search($post);

        }else{
            Yii::$app->params['code'] = -1;
            Yii::$app->params['message'] = '搜索条件不可为空！';
            return [];
        }



    }


}