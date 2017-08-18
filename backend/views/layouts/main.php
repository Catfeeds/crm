<?php
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */


if (Yii::$app->controller->action->id === 'login') {
/**
 * Do not use this code in your template. Remove it.
 * Instead, use the code  $this->layout = '//main-login'; in your controller.
 */
    echo $this->render(
        'main-login',
        ['content' => $content]
    );
} else {

    if (class_exists('backend\assets\AppAsset')) {
        backend\assets\AppAsset::register($this);
    } else {
        app\assets\AppAsset::register($this);
    }

    backend\assets\AdminLteAsset::register($this);

    //覆盖Yii插件中的css文件 - 前端调整风格了
//    $this->registerCssFile('/dist/css/AdminLTE.min.css', [
//        'depends'=> ['backend\assets\AdminLteAsset']
//    ]);
//    $this->registerCssFile('/dist/css/skins/_all-skins.min.css', [
//        'depends'=> ['backend\assets\AdminLteAsset']
//    ]);
//    $this->registerCssFile('/dist/css/datepicker3.css', [
//        'depends'=> ['backend\assets\AdminLteAsset']
//    ]);
    //覆盖 - end

    //下拉控件载入
    $this->registerJsFile('/dist/plugins/dropdown-autocomplate/dropdown-autocomplate-plugin.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);

    $directoryAsset = Yii::$app->assetManager->getPublishedUrl('@webroot/dist');
    ?>
    <?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta http-equiv="X-UA-Compatible" content="chrome=1,IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
        <link rel="stylesheet" href="/dist/plugins/daterangepicker/daterangepicker.css">
        <link rel="stylesheet" href="/dist/plugins/treeview/jquery.treeview.css">
        <link rel="stylesheet" href="/dist/css/style.css">
        <!--script src="/dist/js/ie-emulation-modes-warning.js"></script-->
        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
          <script src="/dist/js/html5shiv.min.js"></script>
          <script src="/dist/js/respond.min.js"></script>
        <![endif]-->
    </head>
    <body class="hold-transition skin-blue sidebar-mini">
    <?php $this->beginBody() ?>
    <div class="wrapper">

        <?= $this->render(
            'header.php',
            ['directoryAsset' => $directoryAsset]
        ) ?>

        <?= $this->render(
            'left.php',
            ['directoryAsset' => $directoryAsset]
        )
        ?>

        <?= $this->render(
            'content.php',
            ['content' => $content, 'directoryAsset' => $directoryAsset]
        ) ?>

    </div>

    <?php $this->endBody() ?>
    <?=$this->blocks['javascript']?>
    </body>
    </html>
    <?php $this->endPage() ?>
<?php } ?>
