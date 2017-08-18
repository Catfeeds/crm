<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\Update_xlsx_log */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Update Xlsx Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="update-xlsx-log-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'update_file',
            'error_file',
            'success_num',
            'error_num',
            'update_time:datetime',
            'update_person_id',
            'update_person_name',
            'update_type',
            'update_from',
        ],
    ]) ?>

</div>
