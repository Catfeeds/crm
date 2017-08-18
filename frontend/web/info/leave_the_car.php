<!DOCTYPE html>
<html lang="en" class="pixel-ratio-1">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Cache-Control" content="no-cache">
    <meta http-equiv="Expires" content="-1">
    <title>销售订单确认</title>
    <link rel="stylesheet" href="/css/info/weui.min.css">
    <link rel="stylesheet" href="/css/info/jquery-weui.min.css">
    <link rel="stylesheet" href="/css/info/style.css">
    <script type="text/javascript" src="/js/jquery.js" charset="utf-8"></script>
    <script type="text/javascript" src="/js/form.js" charset="utf-8"></script>
    <script type="text/javascript" src="/js/info/fastclick.js" charset="utf-8"></script>
    <script type="text/javascript" src="/js/info/jquery-weui.min.js" charset="utf-8"></script>
    <script type="text/javascript" src="/js/info/swiper.js" charset="utf-8"></script>
    <style type="text/css">
        .page__bd {
            min-height: 100px;
        }

        .froms {
            min-height: 93px;
            padding-top: 2%;
            margin-top: 0;
            padding-left: 10px;
            /*    border-bottom: 1px solid rgba(0,0,0,0); */
        }

        .froms .titles {
            display: block;
            color: #999;
            font-size: 14px;
            margin: 0 0 5px 0;
        }

    </style>
</head>
<script type="text/javascript">
    var order_id = window.CarStatus.getOrderId();
    var r = window.CarStatus.getToken();

</script>
<body>
<div class="content" style="padding-top: 0px;">
    <div class="weui-cells__title">订单信息</div>
    <div class="weui-cells">
        <div class="weui-cell">
            <div class="weui-cell__bd">
                <p>销售订单号</p>
            </div>
            <div class="weui-cell__ft" id="saleNo"></div>
        </div>

        <div class="weui-cell">
            <div class="weui-cell__bd">
                <p>销售合同号</p>
            </div>
            <div class="weui-cell__ft" id="saleContractNo"></div>
        </div>

        <div class="weui-cell">
            <div class="weui-cell__bd">
                <p>订单总额</p>
            </div>
            <div class="weui-cell__ft" id="totalPrice">元</div>
        </div>

        <div class="weui-cell">
            <div class="weui-cell__bd">
                <p>期望提车日期</p>
            </div>
            <div class="weui-cell__ft" id="expectedDate"></div>
        </div>

        <div class="weui-cell">
            <div class="weui-cell__bd">
                <p>客户姓名</p>
            </div>
            <div class="weui-cell__ft" id="cusName"></div>
        </div>

        <div class="weui-cell">
            <div class="weui-cell__bd">
                <p>客户电话</p>
            </div>
            <div class="weui-cell__ft" id="cusMobile"></div>
        </div>

        <div class="weui-cell">
            <div class="weui-cell__bd">
                <p>身份证号码</p>
            </div>
            <div class="weui-cell__ft" id="cusIdNo"></div>
        </div>

        <div class="weui-cell constom">
            <div class="weui-cell__bd">
                <p>车辆信息</p>
            </div>
            <div class="weui-cell__ft" id="modelName"></div>
        </div>

        <div class="weui-cell">
            <div class="weui-cell__bd">
                <p>车身颜色</p>
            </div>
            <div class="weui-cell__ft" id="outColor"></div>
        </div>

        <div class="weui-cell">
            <div class="weui-cell__bd">
                <p>内饰颜色</p>
            </div>
            <div class="weui-cell__ft" id="inColor"></div>
        </div>

        <div class="weui-cell">
            <div class="weui-cell__bd">
                <p>车辆价格</p>
            </div>
            <div class="weui-cell__ft" id="nakedPrice"></div>
        </div>

        <div class="weui-cell">
            <div class="weui-cell__bd">
                <p>所属销售</p>
            </div>
            <div class="weui-cell__ft" id="saleName"></div>
        </div>

    </div>
    <div class="weui-cells__title">购买方式</div>
    <div class="weui-cells">
        <div class="weui-cell">
            <div class="weui-cell__bd">
                <p>购买方式</p>
            </div>
            <div class="weui-cell__ft" id="payType"></div>
        </div>
        <div class="weui-cell">
            <div class="weui-cell__bd">
                <p>贷款方式</p>
            </div>
            <div class="weui-cell__ft" id="loanTypeName"></div>
        </div>
        <div class="weui-cell">
            <div class="weui-cell__bd">
                <p>贷款金额</p>
            </div>
            <div class="weui-cell__ft money" id="loanQuota"></div>
        </div>
        <div class="weui-cell">
            <div class="weui-cell__bd">
                <p>贷款期限</p>
            </div>
            <div class="weui-cell__ft" id="loanPeriod"></div>
        </div>

    </div>

    <div class="weui-cells__title">财务信息</div>
    <div class="weui-cells">
        <div class="weui-cell">
            <div class="weui-cell__bd">
                <p>定金</p>
            </div>
            <div class="weui-cell__ft" id="downpayment"></div>
        </div>
        <div class="weui-cell">
            <div class="weui-cell__bd">
                <p>尾款</p>
            </div>
            <div class="weui-cell__ft" id="finalpayment"></div>
        </div>
    </div>

    <div class="weui-cells__title">服务信息</div>
    <div class="weui-cells">
        <div class="weui-cell">
            <div class="weui-cell__bd">
                <p>保单费</p>
            </div>
            <div class="weui-cell__ft" id="insuranceFee"></div>
        </div>
        <div class="weui-cell">
            <div class="weui-cell__bd">
                <p>精品</p>
            </div>
            <div class="weui-cell__ft" id="fineFee"></div>
        </div>
    </div>

    <div class="weui-cells__title" style="display: none;">附件</div>
    <div class="weui-cells" style="display: none;">
        <div class="weui-cell">
            <div class="weui-cell__bd">
                <p>合同</p>
            </div>
            <div class="weui-cell__ft" id="saleContractUrl"><a></a></div>
        </div>
    </div>
    <div id="files">
        <div class="weui-cells__title">需上传</div>
        <div class="page__bd">

            <div class="weui-gallery" id="gallery">
                <span class="weui-gallery__img" id="galleryImg"></span>
                <div class="weui-gallery__opr">
                    <a href="javascript:" class="weui-gallery__del">
                        <i class="weui-icon-delete weui-icon_gallery-delete"></i>
                    </a>
                </div>
            </div>

            <form id="f1" method="post" action="" enctype="multipart/form-data">
                <input type="hidden" name="sign" value="xxx">
                <div class="weui-cells weui-cells_form froms ">
                    <p class="titles">交车照片</p>
                    <div class="weui-cell__bd">
                        <div class="weui-uploader">
                            <div class="weui-uploader__bd">
                                <ul class="weui-uploader__files">
                                </ul>
                                <div class="weui-uploader__input-box">
                                    <input id="u1" val="1" class="weui-uploader__input" name="file" type="file"
                                           accept="image/*" multiple="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <form id="f2" method="post" action="" enctype="multipart/form-data">
                <input type="hidden" name="sign" value="xxx">
                <div class="weui-cells weui-cells_form froms ">
                    <p class="titles">客户接车单</p>
                    <div class="weui-cell__bd">
                        <div class="weui-uploader">
                            <div class="weui-uploader__bd">
                                <ul class="weui-uploader__files">
                                </ul>
                                <div class="weui-uploader__input-box">
                                    <input id="u2" val="2" class="weui-uploader__input" name="file" type="file"
                                           accept="image/*" multiple="">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
            <form id="f3" method="post" action="" enctype="multipart/form-data">
                <input type="hidden" name="sign" value="xxx">
                <div class="weui-cells weui-cells_form froms ">
                    <p class="titles">结算单</p>
                    <div class="weui-cell__bd">
                        <div class="weui-uploader">
                            <div class="weui-uploader__bd">
                                <ul class="weui-uploader__files">
                                </ul>
                                <div class="weui-uploader__input-box">
                                    <input id="u3" val="3" class="weui-uploader__input" name="file" type="file"
                                           accept="image/*" multiple="">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <div class="weui-btn-area" id="isCheck" style="display: none;">
        <a class="weui-btn weui-btn_primary" href="javascript:" id="showTooltips">确认交车</a>
    </div>
</div>

<script>
//    var order_id = "<?php //echo $_POST['order_id'];?>//";
//    var r = '<?php //echo $_POST['r'];?>//';

    var fileUrl = null;
    var url = '/sales/pen-ding-order/info';
    $.post(url, {'r': r, 'order_id': order_id}, function (data) {
        var res = data.res;
        //console.log(res);
        if (data.code == 1) {

            if(res.isCheck) {
                $('#isCheck').show()
            } else {
                $('#files').hide();
            }

            fileUrl = res.urlFile;
            for (i = 1; i <= 3; i++) {
                $('#f' + i).attr('action', fileUrl + 'api/file/upload');
            }
            $('#saleNo').text(res.saleNo);
            $('#saleContractNo').text(res.saleContractNo);
            $('#totalPrice').text(cuter(res.totalPrice.toString()));
            var expectedDate = null;
            if (res.expectedDate == 1) expectedDate = '7~10天';
            $('#expectedDate').text();
            $('#cusName').text(res.cusName);
            $('#cusMobile').text(res.cusMobile);
            $('#cusIdNo').text(res.cusIdNo);
            $('#modelName').text(res.brandName + '-' + res.seriesName + '-' + res.modelName);
            $('#outColor').text(res.outColor);
            $('#inColor').text(res.inColor);
            $('#nakedPrice').text(cuter(res.nakedPrice != null ? res.nakedPrice.toString() : ''));
            $('#saleName').text(res.saleName);
            $('#loanTypeName').text(res.loanTypeName);
            $('#loanQuota').text(cuter(res.loanQuota != null ? res.loanQuota.toString() : ''));
            $('#loanPeriod').text(res.loanPeriod);
            $('#downpayment').text(cuter(res.downpayment != null ? res.downpayment.toString() : ''));
            $('#finalpayment').text(cuter(res.finalpayment != null ? res.finalpayment.toString() : ''));
            $('#insuranceFee').text(cuter(res.insuranceFee != null ? res.insuranceFee.toString() : ''));
            $('#fineFee').text(cuter(res.fineFee != null ? res.fineFee.toString() : ''));
            $('#saleContractUrl a').text('查看合同');
            $('#saleContractUrl a').attr('href', res.url + 'api/sale/contract?clueNo=' + res.order_id + '&sign=' + res.sign);
            $('#payType').text(res.payType == 1 ? '全款' : '贷款');
        } else {
            $.toast(data.message);
        }
    }, 'json')

    var arr = new Array();
    //点击上传
    var tmpl = '<li class="weui-uploader__file" style="background-image:url(#url#)"></li>',
        $gallery = $("#gallery"), $galleryImg = $("#galleryImg"),
        $uploaderInput = $(".weui-uploader__input");
    $uploaderInput.on("change", function (e) {
        var val = $(this).attr('val');
        var urls = $('#f' + val).attr('action');
        var formData = new FormData($("#f" + val)[0]);
        var _this = $(this);
        $.ajax({
            url: urls,
            type: 'POST',
            data: formData,
            async: false,
            cache: false,
            contentType: false,
            processData: false,
            success: function (data) {
                if (data.statusCode == 1) {
                    var attachmentType = 0;
                    if (val == 1) {
                        attachmentType = 35;
                    } else if (val == 2) {
                        attachmentType = 36;
                    } else if (val == 3) {
                        attachmentType = 37;
                    }
                    data.content.attachmentType = attachmentType;
                    arr.push(data.content);
                    var src, url = window.URL || window.webkitURL || window.mozURL, files = e.target.files;
                    for (var i = 0, len = files.length; i < len; ++i) {
                        var file = files[i];
                        if (url) {
                            src = url.createObjectURL(file);
                        } else {
                            src = e.target.result;
                        }
                        _this.parent().siblings().append($(tmpl.replace('#url#', src)));
                    }
//                    console.log(arr)
                }
            },
            error: function (data) {
                console.log(data)
            }
        }, 'json');
    });

    function cuter(n) {
        var re = /\d{1,3}(?=(\d{3})+$)/g;
        var n1 = n.replace(/^(\d+)((\.\d+)?)$/, function (s, s1, s2) {
            return s1.replace(re, "$&,") + s2;
        });
        return n1;
    }
    $("#showTooltips").click(function () {
        var url = "/sales/pen-ding-order/apply";
        $.post(url, {'r': r, 'order_id': order_id, 'type': 3, 'arr': arr}, function (data) {
            $.toast(data.message);
            if (data.code == 1) {
                setTimeout(function () {
                    window.CarStatus.onBackActivity()
                }, 2000);
            }
        }, 'json')

    })

</script>
</body>
</html>
