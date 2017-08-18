<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Buy_type */

$this->title = 'Create Buy Type';
$this->params['breadcrumbs'][] = ['label' => 'Buy Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="buy-type-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
