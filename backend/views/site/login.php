<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

$this->title = '登陆';

$fieldOptions1 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-envelope form-control-feedback'></span>"
];

$fieldOptions2 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-lock form-control-feedback'></span>"
];
$fieldOptions3 = [
    'options' => ['class' => ''],
    'inputTemplate' => "{input}"
];

$this->registerJsFile('/dist/js/site/login.js', [
    'depends' => ['backend\assets\AdminLteAsset']
]);
?>

<div class="login-box">
    <div class="login-logo">
        <a href="#">后台登陆</a>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">登陆</p>

        <?php $form = ActiveForm::begin(['id' => 'login-form', 'enableClientValidation' => false]); ?>

        <?= $form
            ->field($model, 'username', $fieldOptions1)
            ->label(false)
            ->textInput(['placeholder' => '手机号'])?>

        <?= $form
            ->field($model, 'shopId', $fieldOptions3)
            ->label(false)
            ->hiddenInput(['placeholder' => '角色id'])?>
        <?= $form
            ->field($model, 'roleId', $fieldOptions3)
            ->label(false)
            ->hiddenInput(['placeholder' => '门店id'])?>
        <?= $form
            ->field($model, 'roleAndShop', $fieldOptions3)
            ->label(false)
            ->dropDownList(['placeholder' => '角色和门店选择'])?>

        <?= $form
            ->field($model, 'password', $fieldOptions2)
            ->label(false)
            ->passwordInput(['placeholder' => '密码'])?>

        <div class="row">
            <div class="col-xs-8">
                <?= $form->field($model, 'rememberMe')->checkbox() ?>
            </div>
            <!-- /.col -->
            <div class="col-xs-4">
                <?= Html::submitButton('登陆', ['class' => 'btn btn-primary btn-block btn-flat', 'name' => 'login-button']) ?>
            </div>
            <!-- /.col -->
        </div>


        <?php ActiveForm::end(); ?>


    </div>
    <!-- /.login-box-body -->
</div><!-- /.login-box -->
