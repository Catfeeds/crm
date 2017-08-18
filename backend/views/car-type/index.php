<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\CarTypeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Car Types';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="car-type-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Car Type', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            'pid',
            'level',
            'logo',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
