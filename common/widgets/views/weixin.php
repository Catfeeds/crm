<script type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    var ojbShare = {
        "title": "<?=$share['title']?>",
        "link": "<?=$weixin['url']?>",
        "image": "<?=$share['image']?>",
        "desc": "<?=$share['desc']?>",
    };

    wx.config({
        debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
        appId: '<?=$weixin['appId']?>', // 必填，公众号的唯一标识
        timestamp: <?=$weixin['timestamp']?>, // 必填，生成签名的时间戳
        nonceStr: '<?=$weixin['nonceStr']?>', // 必填，生成签名的随机串
        signature: '<?=$weixin['signature']?>',// 必填，签名，见附录1
        jsApiList: ['onMenuShareTimeline','onMenuShareAppMessage','onMenuShareQQ','onMenuShareWeibo','onMenuShareQZone'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
    });

    wx.ready(function(){
        // 分享到朋友圈
        wx.onMenuShareTimeline({
            title: ojbShare.title,  // 分享标题
            link: ojbShare.link,    // 分享链接
            imgUrl: ojbShare.image, // 分享图标
            success: function () {
                // alert('分享到朋友圈ok');
            },
            cancel: function () {
                // alert('分享到朋友圈no');
            }
        });

        // 分享给朋友
        wx.onMenuShareAppMessage({
            title: ojbShare.title,      // 分享标题
            desc: ojbShare.desc,        // 分享描述
            link: ojbShare.link,        // 分享链接
            imgUrl: ojbShare.image,     // 分享图标
            type: '', // 分享类型,music、video或link，不填默认为link
            dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
            success: function () {
                // alert('分享给朋友ok');
            },
            cancel: function () {
                // alert('分享给朋友no');
            }
        });

        // 分享到QQ
        wx.onMenuShareQQ({
            title: ojbShare.title,  // 分享标题
            desc: ojbShare.desc,    // 分享描述
            link: ojbShare.link,    // 分享链接
            imgUrl: ojbShare.image  // 分享图标
        });

        // 分享到腾讯微博
        wx.onMenuShareWeibo({
            title: ojbShare.title,  // 分享标题
            desc: ojbShare.desc,    // 分享描述
            link: ojbShare.link,    // 分享链接
            imgUrl: ojbShare.image  // 分享图标
        });

        // 分享到QQ空间
        wx.onMenuShareQZone({
            title: ojbShare.title,      // 分享标题
            desc: ojbShare.desc,        // 分享描述
            link: ojbShare.link,        // 分享链接
            imgUrl: ojbShare.image      // 分享图标
        });


        wx.error(function(res){
            // alert('error');
            // config信息验证失败会执行error函数，如签名过期导致验证失败，具体错误信息可以打开config的debug模式查看，也可以在返回的res参数中查看，对于SPA可以在这里更新签名。

        });
        // config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，所以如果需要在页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，则可以直接调用，不需要放在ready函数中。
    });

</script>