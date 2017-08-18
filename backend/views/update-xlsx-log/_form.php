<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Update_xlsx_log */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="update-xlsx-log-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'update_file')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'error_file')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'success_num')->textInput() ?>

    <?= $form->field($model, 'error_num')->textInput() ?>

    <?= $form->field($model, 'update_time')->textInput() ?>

    <?= $form->field($model, 'update_person_id')->textInput() ?>

    <?= $form->field($model, 'update_person_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'update_type')->textInput() ?>

    <?= $form->field($model, 'update_from')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
