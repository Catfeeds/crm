<?php
namespace frontend\modules\thirdpartyapi\controllers;

use Yii;
use common\logic\JxcApi;
use yii\rest\Controller;

/**
 * erp文件上传
 */
class FilesController extends Controller
{
    public function actionFileSave() {
        $jxc  = new JxcApi();
        echo json_encode(['url'=>$jxc->url]);
        $sign = md5(  '_tk' . $jxc->b_token);//签名
//        $url = $jxc->url . 'api/file/upload';
//        $url = 'http://172.4.0.187:8088/api/file/upload2';
//
//        $arr['file'] = $_FILES['file']['name'];
//        $arr['sign'] = 'xxx';
//        $res = json_decode(JxcApi::send_post($url, $_FILES['file']), true);

    }

    public function dump($data) {
        echo "<pre>";
        print_r($data);
        exit;
    }
}