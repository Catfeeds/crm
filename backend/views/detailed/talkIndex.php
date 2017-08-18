<?php
use common\logic\LinkPager;
?>
<link href="/dist/css/home/bootstrap.css" rel="stylesheet">
<link href="/dist/css/home/AdminLTE.min.css" rel="stylesheet">
<link href="/dist/css/style.css" rel="stylesheet">
<link href="/dist/css/home/font-awesome.min.css" rel="stylesheet">
<link href="/dist/css/home/ionicons.min.css" rel="stylesheet">
<div class="box box-none-border">
    <div class="box-body no-padding">
        <div class="table-responsive">
            <div>
                <h1 class="page-title pdb-0 mt-sm mb-md"><?php echo $title; ?></h1>
            </div>
            <div style="margin: 10px 10px 10px 0px;">
                <form id="form1" class="mb-md" action="/detailed/talk" method="get">
                    <input type="hidden" name="level" value="<?php echo $get['level']?>">
                    <input type="hidden" name="type" value="<?php echo $get['type']?>">
                    <input type="hidden" name="id" value="<?php echo $get['id']?>">
                    <input type="hidden" name="shop_id" value="<?php echo empty($get['shop_id']) ? 0 :$get['shop_id']?>">
                    <input type="hidden" name="addtime" value="<?php echo $get['addtime']?>">
                    <input type="hidden" name="ischeck" id="ischeck" value="<?php echo $get['ischeck'];?>">
                    <input class="btn btn-primary" value="导出列表" type="button" onclick="butCheck(1)">
                    <div class="row">
                        <div class="col-sm-12 t-r">
                            <div class="pull-right mr-15">
                                <div class="col-sm-9 col-md-9">
                                    <input class="form-control" type="text" style="width:200px;" id="keyword" name="keyword" value="<?php echo $get['keyword'];?>" placeholder="姓名/手机号码/顾问">
                                </div>
                                <input class="btn btn-primary btn-sm pull-left mr-15" value="查询" type="button" onclick="butCheck(0)" >
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <table class="table table-hover table-bordered table-list-check">
                <thead>
                <tr style="font-size: 14px;">
                    <th>序号</th>
                    <th>联系时间</th>
                    <th>姓名</th>
                    <th>手机号码</th>
                    <th>类型</th>
                    <th>标签</th>
                    <th style="max-width:400px;">商谈内容</th>
                    <th width="80">图片</th>
                    <th style="width:60px; max-width:60px;">录音</th>
                    <th>顾问</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($list) {
                    foreach ($list as $k => $v) {
                        $page = empty($_GET['page']) ? 1 : $_GET['page']; ?>
                        <tr style="font-size: 14px;">
                            <td><?php echo (($page - 1) * 20) + ($k + 1); ?></td>
<!--                            <td>--><?php //echo empty($v['talk_date']) ? '--' : $v['talk_date']; ?><!--</td>-->
                            <td><?php echo empty($v['talk_time']) ? '--' : $v['talk_time']; ?></td>
                            <td><?php echo empty($v['customer_name']) ? '--' : $v['customer_name'] ?></td>
                            <td><?php echo empty($v['customer_phone']) ? '--' : $v['customer_phone']; ?></td>
                            <td><?php echo $v['talk_typeDes']; ?></td>
                            <td><?php echo empty($v['select_tags_name']) ? '--' : $v['select_tags_name']; ?></td>
                            <td  style="max-width:400px;"><?php echo empty($v['content']) ? '--' : $v['content']; ?></td>
                            <td style="width:60px; max-width:60px;">
                                <?php
                                $img = [];
                                if (!empty($v['imgs'])) {
                                    $img = explode(',', $v['imgs']);
                                }

                                $imgArr = [];
                                foreach ($img as $val) {
                                    $imgArr[] = [
                                        'src' => $val,
                                    ];
                                }


                                ?>
                                    <p class="btn img_show border1" data='<?php echo json_encode([
                                        'data' => $imgArr
                                    ]); ?>'><i
                                            class="fa fa-photo mr-sm"></i><?php echo count($img); ?></p>

                            </td>
                            <td style="width:67px; max-width:67px;">
                                <?php if (!empty($v['voices'])) { ?>
                                    <button class="btn audio-btn" data-src="<?=$v['voices']?>">播放</button>
                                <?php } else echo '--'; ?>
                            </td>
                            <td><?php echo empty($v['salesman_name']) ? '--' : $v['salesman_name']; ?></td>
                        </tr>
                    <?php }
                } ?>
                </tbody>
            </table>
        </div>
        <div class="box-footer clearfix bd-t0" style="text-align:right;">
            <?php

            // 显示分页
            echo LinkPager::widget([
                'pagination' => $pagination,
                'firstPageLabel' => "首页",
                'prevPageLabel' => '上一页',
                'nextPageLabel' => '下一页',
                'lastPageLabel' => '末页',
            ]);
            ?>
        </div>
    </div>
</div>

<script src="/assets/js/1.9.1/jquery.min.js"></script>
<script src="/dist/plugins/layer/layer.js"></script>
<script type="text/javascript">
    $(".img_show").click(function(){
        var data = JSON.parse($(this).attr('data'));
        layer.photos({
            photos: data, //格式见API文档手册页
            anim: 10
        });
    });
    $(".audio-btn").click(function(){
        var src = $(this).data("src");
        var html = "";

        html =  '<div class="audio">'+
            '    <div class="">'+
            '        <audio src="'+ src +'" controls="controls">'+
            '            您的浏览器不支持 video 标签。'+
            '        </audio>'+
            '    </div>'+
            '</div>';

        layer.open({
            title:"语音播放",
            type: 1,
            skin: 'layui-layer-video',
            closeBtn: 1,
            anim: 2,
            shadeClose: true,
            content: html
        });
    });
    function butCheck(butNum) {
        $('#ischeck').val(butNum)
        $('#form1').submit();
    }
</script>