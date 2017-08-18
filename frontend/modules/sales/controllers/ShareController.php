<?php

namespace frontend\modules\sales\controllers;

use common\models\Share;
use Yii;
use frontend\modules\sales\logic\ShareLogic;
use common\logic\CompanyUserCenter;

/**
 * 分享
 */
class ShareController extends AuthController
{
    public function actionUpdateShare()
    {
        $item = $this->getPData();
        if (empty($item['car_information'])) {
            Yii::$app->params['code'] = '401';
            Yii::$app->params['message'] = '参数不正确！';
            return [];
        }
        //检测当前分享信息是否存在
        $share = new Share();
        $user = \Yii::$app->user->identity;
        $company = new CompanyUserCenter();
        $shop_name = $company->getShopName($user->shop_id);

        $share->token =  md5(uniqid(mt_rand(), true));
        $share->salesman_id = $user->getId();
        $share->salesman_name = $user->name;
        $share->shop_id = $user->shop_id;
        $share->shop_name = $shop_name;
        $share->created_at = time();
        $share->title = $item['title'];
        $share->car_information = json_encode($item['car_information'],320);
        $data = [];
        if ($share->save()) {
            $data['url'] = 'http://'.Yii::$app->params['apiAddrUrl']['url'] . '/share/index/' . $share->id;
            $data['title'] = $item['title'];
            $data['des'] = '车城-'.$shop_name.'-'.$user->name.'为您推荐，欢迎随时咨询！';
        }else{
            Yii::$app->params['code'] = '401';
            Yii::$app->params['message'] = '录入失败！';
        }
        return $data;
    }

    /**
     * 获取分享地址
     */
    public function actionGetGuid()
    {

        $data = $this->getPData();
        $logic = new ShareLogic();
        $id = isset($data['shareId']) ? $data['shareId'] : null;

        $model = [];
        $res = $logic->add($id);
        if ($res) {
            $model['url'] = Yii::$app->params['apiAddrUrl']['url'] . '/share/index/' . $res;
            $model['shareId'] = $res;
        } else {
            Yii::$app->params['code'] = '401';
            Yii::$app->params['message'] = '录入GUID错误！';
        }
        return $model;
    }

}
