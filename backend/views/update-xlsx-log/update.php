<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Update_xlsx_log */

$this->title = 'Update Update Xlsx Log: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Update Xlsx Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="update-xlsx-log-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
