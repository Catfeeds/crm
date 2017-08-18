<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\AnnouncementSend */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="announcement-send-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'addressee_des')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'addressee_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'send_person_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'send_person_id')->textInput() ?>

    <?= $form->field($model, 'content')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'send_time')->textInput() ?>

    <?= $form->field($model, 'is_success')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
