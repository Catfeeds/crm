<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\AnnouncementSend */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Announcement Sends', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="announcement-send-view">

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
            'title',
            [                      // the owner name of the model
                'label' => '收件人',
                'value' => $model->addressee_des,
            ],
//            'addressee_des',
            'send_time:datetime',
            'content:ntext',
            'send_person_name',
//            'addressee_id',
//            'id',
//            'addressee_des',
//            'send_person_id',
//            'is_success',
        ],
    ]) ?>

</div>
