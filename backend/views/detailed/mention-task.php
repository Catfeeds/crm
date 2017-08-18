<?php
use common\logic\LinkPager;
?>
<link href="/dist/css/home/bootstrap.css" rel="stylesheet">
<link href="/dist/css/home/AdminLTE.min.css" rel="stylesheet">
<link href="/dist/css/style.css" rel="stylesheet">
<script src="/dist/js/jquery-1.7.1.js"></script>
<div class="box box-none-border">
    <div class="box-body no-padding">
        <div class="table-responsive">
            <div>
                <h1 class="page-title pdb-0 mt-sm mb-md"><?php echo $title;?></h1>
            </div>
            <div style="margin: 10px 10px 10px 0px;">
                <form id="form1" class="mb-md" action="<?=\yii\helpers\Url::toRoute('mention-task')?>" method="get">
                    <input type="hidden" name="level" value="<?php echo $get['level']?>">
                    <input type="hidden" name="id" value="<?php echo $get['id']?>">
                    <input type="hidden" name="shop_id" value="<?php echo empty($get['shop_id']) ? 0 :$get['shop_id']?>">
                    <input type="hidden" name="addtime" value="<?php echo $get['addtime']?>">
                    <input type="hidden" name="type" value="<?php echo $get['type']?>">
                    <input type="hidden" name="ischeck" id="ischeck" value="<?php echo $get['ischeck'];?>">
                    <input class="btn btn-primary" value="导出列表" type="button" onclick="butCheck(1)">
                    <div class="row">
                        <div class="col-sm-12 t-r">
                            <div class="pull-right mr-15">
                                <div class="col-sm-9 col-md-9">
                                    <input class="form-control" type="text" style="width:200px;" id="keyword" name="keyword" value="<?php echo $get['keyword'];?>" placeholder="姓名/手机/顾问">
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
                    <th>购车时间</th>
                    <th>姓名</th>
                    <th>手机号码</th>
                    <th>订购车型</th>
                    <th>交车门店</th>
                    <th>交车顾问</th>
                    <th>订车门店</th>
                    <th>订车顾问</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($list) {
                    foreach ($list as $k => $v) {
                        $page = empty($_GET['page']) ? 1 : (int)$_GET['page']; ?>
                        <tr style="font-size: 14px;">
                            <td><?php echo (($page - 1) * 20) + ($k + 1); ?></td>
                            <td><?=date('Y-m-d H:i:s', $v['the_car_time'])?></td>
                            <td><?=empty($v['customer_name']) ? '--' : $v['customer_name']?></td>
                            <td><?=$v['customer_phone']?></td>
                            <td><?=$v['yu_ding_che_xing']?></td>
                            <td><?=$v['new_shop_name']?></td>
                            <td><?=$v['new_salesman_name'] ? $v['new_salesman_name'] : '--'?></td>
                            <td><?=$v['old_shop_name']?></td>
                            <td><?=empty($v['old_salesman_name']) ? '--' : $v['old_salesman_name']?></td>
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
<script type="text/javascript">
    function butCheck(butNum) {
        $('#ischeck').val(butNum)
        $('#form1').submit();
    }
</script>