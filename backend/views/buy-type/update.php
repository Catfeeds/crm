<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Buy_type */

$this->title = 'Update Buy Type: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Buy Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="buy-type-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
