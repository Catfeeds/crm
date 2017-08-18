<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Update_xlsx_log */

$this->title = 'Create Update Xlsx Log';
$this->params['breadcrumbs'][] = ['label' => 'Update Xlsx Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="update-xlsx-log-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
