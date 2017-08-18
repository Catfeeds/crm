<?php
use backend\assets\AppAsset;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */

dmstr\web\AdminLteAsset::register($this);
//覆盖Yii插件中的css文件 - 前端调整风格了
    /*$this->registerCssFile('/dist/css/AdminLTE.min.css', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);
    $this->registerCssFile('/dist/css/skins/_all-skins.min.css', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);*/
    $this->registerCssFile('/dist/css/datepicker3.css', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);
    //覆盖 - end
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="login-page">

<?php $this->beginBody() ?>

    <?= $content ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
