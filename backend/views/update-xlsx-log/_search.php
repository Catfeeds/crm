<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Update_xlsx_log_Search */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="update-xlsx-log-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'update_file') ?>

    <?= $form->field($model, 'error_file') ?>

    <?= $form->field($model, 'success_num') ?>

    <?= $form->field($model, 'error_num') ?>

    <?php // echo $form->field($model, 'update_time') ?>

    <?php // echo $form->field($model, 'update_person_id') ?>

    <?php // echo $form->field($model, 'update_person_name') ?>

    <?php // echo $form->field($model, 'update_type') ?>

    <?php // echo $form->field($model, 'update_from') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
