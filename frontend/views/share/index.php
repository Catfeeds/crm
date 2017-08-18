<?php

/* @var $share \common\models\Share; */

/* @var $user \common\models\User */
$this->registerCssFile('@web/css/share.style.css');
?>
<?php $this->beginPage() ?>
<!doctype html>
<html lang="en">
<head>
    <title><?=$share->title?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=no">
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
    <div class="container">
        <header>
            <div class="header">
                <div class="title">
                    <h3><?=$share->salesman_name?></h3>
                    <p>车城 <?=$share->shop_name?></p>
                    <p class="phone"><?=$user ? $user->phone : $share->salesman_name?></p>
                </div>
            </div>
        </header>
        <div class="list">
            <?php if ($info) : ?>
            <ul>
                <?php foreach ($info as $key => $value) : ?>
                <li>
                    <img src="<?=$value['img']?>" alt="<?=$value['title']?>">
                    <div class="detail">
                        <h3><?=$value['title']?></h3>
                        <a href="<?=\yii\helpers\Url::toRoute(['share/detail', 'id' => $share->id, 'key' => md5($key)])?>">查看详情</a>
                        <strong>¥ <?=round($value["price"]/10000, 2)?> 万</strong>
                        <span>厂商指导价：<?=round($value['factoryPrice']/10000, 2)?> 万</span>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>
<?php $this->endBody() ?>
</body>
<?=\common\widgets\WeixinShare::widget([
    'share' => [
        'title' => $share->title,
        'image' => '/img/share.png',
        'desc' => $share->title
    ],
])?>
</html>
<?php $this->endPage() ?>