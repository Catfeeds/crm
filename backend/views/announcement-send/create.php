<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\AnnouncementSend */

$this->title = 'Create Announcement Send';
$this->params['breadcrumbs'][] = ['label' => 'Announcement Sends', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="announcement-send-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
