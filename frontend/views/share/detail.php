<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Url;

$this->registerCssFile('@web/css/share.style.css');
?>
<?php $this->beginPage() ?>
<!doctype html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>车型详情</title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<img src="/img/share.png" width="0" height="0" />
    <div class="container share-detail">
        <div id="slideBox" class="slideBox">
                <div class="bd">
                    <ul>
                        <li>
                            <a class="pic" href="#"><img src="<?=$info['img']?>" alt="" width="100%"></a>
                        </li>
                    </ul>
                </div>

                <div class="hd">
                    <ul></ul>
                </div>
            </div>


        <div class="infobox">
            <h3><?=$info['title']?></h3>
            <strong>¥ <b><?=round($info['price']/10000, 2)?></b> 万 </strong>
            <div class="priceinfo">
                <p class="p1">厂商指导价：¥ <?=round($info['factoryPrice']/10000, 2)?> 万 </p>
                <?php $price = $info['factoryPrice'] - $info['price']; ?>
                <p class="p2">直降：¥ <?=($price > 0 ? round($price/10000, 2) : '0.00')?> 万</p>
            </div>
        </div>
        <h3 class="title">车型介绍</h3>
        <div class="carinfo">
            <?=$html?>
        </div>
        <?php $form = ActiveForm::begin([
            'id' => 'clue-form',
            'action' => Url::toRoute(['share/clue']),
            'method' => 'POST'
        ]); ?>
        <div class="zxbox">
            <div class="zx-left">
                <input type="text" value="" maxlength="11" name="phone" id="phone" placeholder="请输入手机号码">
            </div>
            <div class="zx-right">
                <!-- 分享ID -->
                <input type="hidden" name="share_id" value="<?=$share->id?>"/>
                <input type="hidden" name="token" value="<?=$share->token?>"/>

                <!-- 车系信息 -->
                <input type="hidden" name="intention_id" value="<?=$info['intention_id']?>">
                <input type="button" value="立即咨询" id="submit-form">
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
<?php $this->endBody() ?>
<?php $this->registerJsFile('/js/TouchSlide.1.1.js'); ?>
<?=\common\widgets\WeixinShare::widget([
    'share' => [
        'title' => $share->title,
        'image' => '/img/share.png',
        'desc' => $share->title
    ],
])?>
<script>
    var isClick = false;
    $(function(){
//        TouchSlide({
//            slideCell:"#slideBox",
//            titCell:".hd ul", //开启自动分页 autoPage:true ，此时设置 titCell 为导航元素包裹层
//            mainCell:".bd ul",
//            effect:"leftLoop",
//            autoPage:true,//自动分页
//            autoPlay:true //自动播放
//        });

        // 表单提交
        $("#submit-form").click(function(){
            $("#clue-form").trigger("submit");
        });

        $(document).on("beforeSubmit", "#clue-form", function(){
            // 验证手机号
            if (!/^\d{11}$/.test($("#phone").val())) {
                alert("请输入正确的手机号");
            } else {
                if (isClick === false) {
                    isClick = true;
                    $.ajax({
                        url: "<?=Url::toRoute(['share/clue'])?>",
                        data: $("#clue-form").serialize(),
                        type: "post",
                        dataType: "json"
                    }).done(function(json){
                        alert(json.errMsg);
                        if (json.errCode !== 0) {
                            isClick = false;
                        }
                    }).fail(function(){
                        alert("服务器繁忙，请稍后再试...")
                    });
                } else {
                    alert("收到您的需求，稍后我会和您联系");
                }
            }

            return false;
        });
    });

</script>
</body>
</html>
<?php $this->endPage() ?>
