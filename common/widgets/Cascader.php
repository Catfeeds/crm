<?php
/**
 * Created by PhpStorm.
 * User: Think
 * Date: 2017/7/7
 * Time: 10:52
 */

namespace common\widgets;

use yii\base\Widget;

class Cascader extends Widget
{
    public $id = 'cascader-1';

    public $div = [];

    public $options = [];

    public $defaultOptions = [
        'placeholder' => '请选择',
        'size' => 'small',
        ':options' => 'options2',
        '@change' => 'handleChange',
        'filterable' => true,
        'clearable' => true,
    ];

    public function init()
    {
        // 注入指定的 js 和 css
        \Yii::$app->view->registerJsFile('/dist/plugins/vue-element/vue.min.js', [
            'depends'=> ['backend\assets\AdminLteAsset']
        ]);

        \Yii::$app->view->registerJsFile('/dist/plugins/vue-element/index.js', [
            'depends'=> ['backend\assets\AdminLteAsset']
        ]);

        \Yii::$app->view->registerCssFile('/dist/plugins/vue-element/index.css', [
            'depends'=> ['backend\assets\AdminLteAsset']
        ]);

        parent::init(); // TODO: Change the autogenerated stub
        if ($this->options) {
            $this->options = array_merge($this->defaultOptions, $this->options);
        } else {
            $this->options = $this->defaultOptions;
        }
    }

    /**
     * 处理参数
     * @param array $params
     * @return string
     */
    private function handleParams(array $params)
    {
        $strHtml = '';
        foreach ($params as $key => $value) {
            $strHtml .= $key .'="'.$value.'" ';
        }

        return empty($strHtml) ? '' : ' ' . $strHtml;
    }

    public function run()
    {
        \Yii::$app->view->registerJs('
            var obj = new Vue({
                el: \'#'.$this->id.'\',
                data:function(){
                    return {
                        formInline:{
                            desc:[]
                        },
                        options2: <?=$areas?>
                    }
                },
            });
        ', V);
        return '<div '.$this->handleParams(array_merge($this->div, ['id' => $this->id])).'>
                    <el-cascader '.$this->handleParams($this->options).'></el-cascader>
                    <input id="area" name="area" type="hidden" value="">
                </div>';
    }
}