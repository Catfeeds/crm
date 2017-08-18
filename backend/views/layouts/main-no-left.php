<?php
use yii\helpers\Html;
if (class_exists('backend\assets\AppAsset')) {
        backend\assets\AppAsset::register($this);
    } else {
        app\assets\AppAsset::register($this);
    }

    backend\assets\AdminLteAsset::register($this);
//覆盖Yii插件中的css文件 - 前端调整风格了
    $this->registerCssFile('/dist/css/AdminLTE.min.css', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);
    $this->registerCssFile('/dist/css/skins/_all-skins.min.css', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);
    $this->registerCssFile('/dist/css/datepicker3.css', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);
    //覆盖 - end
    $directoryAsset = Yii::$app->assetManager->getPublishedUrl('@webroot/dist');
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
        
<!--        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/
        ionicons.min.css">-->
        <link rel="stylesheet" href="/dist/plugins/daterangepicker/daterangepicker.css">
        <link rel="stylesheet" href="/dist/plugins/treeview/jquery.treeview.css">
    </head>
    <body class="hold-transition skin-blue sidebar-mini">
    <?php $this->beginBody() ?>
        
    <?= $this->render(
        'header.php',
        ['directoryAsset' => $directoryAsset]
    ) ?>

    <?= $this->render(
        'content.php',
        ['content' => $content, 'directoryAsset' => $directoryAsset]
    ) ?>

    <?php $this->endBody() ?>
    </body>
    </html>
    <?php $this->endPage() ?>