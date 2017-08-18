<?php
/**
 *电商接口相关逻辑
 */

namespace frontend\modules\sales\controllers;

use Yii;
use common\common\PublicMethod;
class OnlineRetailersController extends AuthController
{

    public static $url;
    public function init()
    {
        parent::init();
        self::$url = Yii::$app->params['che_com']['cheApi'];
    }

    /**
     * 电商-可售品牌
     */
    public function actionBrands()
    {
        $apiUrl = self::$url.'che/crm/brands';
        $response = json_decode(PublicMethod::http_get($apiUrl),true);

        if ($response['code'] == 200) {
            $data = [];
            $i = 0;
            foreach ($response['detail'] as $k => $v) {
                foreach ($v['brandList'] as $val) {
                    $data[$i]['brand_id'] = $val['brandId'];
                    $data[$i]['brand_name'] = $val['brandName'];
                    $data[$i]['pic_url'] = $val['url'];
                    $data[$i]['first_num'] = $v['firstNum'];
                    $i++;
                }
            }
            return $data;
        } else {
            Yii::$app->params['code'] = '401';
            Yii::$app->params['message'] = '电商-品牌接口出错！';
            return [];
        }
    }

    /**
     * 电商 - 根据品牌查询车系
     */
    public function actionSeries()
    {
        $item = $this->getPData();
        if (empty($item['brandId'])) {
            Yii::$app->params['code'] = '401';
            Yii::$app->params['message'] = '参数不正确！';
            return [];
        }
        $apiUrl = self::$url.'che/crm/series';
        $params['brandId'] = $item['brandId'];
        $response = json_decode(PublicMethod::http_get($apiUrl,$params),true);
        if ($response['code'] == 200) {
            return $response['detail'];
        } else {
            Yii::$app->params['code'] = '401';
            Yii::$app->params['message'] = '电商-车系接口出错！';
            return [];
        }
    }

    /**
     * 电商 - 电商可售车型
     */
    public function actionCars()
    {
        $item = $this->getPData();
        if (empty($item['seriesId'])) {
            Yii::$app->params['code'] = '401';
            Yii::$app->params['message'] = '参数不正确！';
            return [];
        }
        $apiUrl = self::$url.'che/crm/cars';
        $params['seriesId'] = $item['seriesId'];
        $response = json_decode(PublicMethod::http_get($apiUrl,$params),true);
        if ($response['code'] == 200) {
            return $response['detail'];
        } else {
            Yii::$app->params['code'] = '401';
            Yii::$app->params['message'] = '电商-车型接口出错！';
            return [];
        }
    }
}