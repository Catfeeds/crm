<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/3/8
 * Time: 16:20
 */

namespace backend\assets;


class AdminLteAsset extends \dmstr\web\AdminLteAsset
{
    public $sourcePath = '@webroot/dist';

    public $baseUrl = '@web';

    public $css = [
        'css/home/font-awesome.min.css',
        'css/home/ionicons.min.css',
        'css/home/AdminLTE.min.css',
    ];


    public $js = [
        'js/app.js',
        'js/custom-common.js',
        'plugins/layer/layer.js'
    ];
    public $depends = [
        /*'rmrevin\yii\fontawesome\AssetBundle',*/
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];
}