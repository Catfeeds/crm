<?php

namespace frontend\modules\sales\controllers;

use Yii;

/**
 * 销售助手log日志
 */
class XszsController extends AuthController
{
    /**
     * 销售助手log日志
     */
    public function actionLog()
    {
        $data = \Yii::$app->request->post();
        if (empty($data['p'])) {
            return $this->paramError();
        }
        $rootPath = \Yii::getAlias('@frontend/runtime/logs/');
        file_put_contents($rootPath.'xszs.log',date("Y-m-d H:i:s")."\t".
            'url=>'. \Yii::$app->request->url."\t".
            'mes=>'.$data['p']."\n"
            , FILE_APPEND);
        Yii::$app->params['code']    = 200;
        Yii::$app->params['message'] = '添加成功';
        return [];

    }

}
