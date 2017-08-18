<?php
use common\logic\LinkPager;

?>
<section class="content-header">
    <h1 class="page-title"><a href="/index/index">返回</a><span>未交车订单</span></h1>
</section>

<section class="content-body">
    <div class="box advanced-search-form mb-lg">
        <form class="form-horizontal" action="no-car" method="get">
            <div class="row">
                <div class="form-group col-md-4">
                    <label for="" class="control-label col-sm-3">关 键 字：</label>
                    <div class="col-sm-9"><input class="form-control" type="text" id="keyword" name="keyword" value="<?php echo $keyword;?>" placeholder="姓名/手机号码/车型/颜色"></div>
                </div>
                <div class="col-md-4">
                        <input class="btn btn-primary btn-sm ml-15" value="搜索" type="submit">
                </div>
            </div>
        </form>
    </div>

    <div class="box box-none-border">
        <div class="box-body no-padding">
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-list-check">
                    <thead>
                    <tr>
                        <th width="60">序号</th>
                        <th>姓名</th>
                        <th>手机号码</th>
                        <th>建卡日期</th>
                        <th>订车日期</th>
                        <th>预计交车日期</th>
    					<th>车型</th>
                        <th>颜色</th>
                        <th>订金</th>
    					<th>购买方式</th>
                        <th>本店投保</th>
                        <th>状态</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($list)) {
                        $page = empty($_GET['page']) ? 1 : $_GET['page'];
                        foreach ($list as $k => $v) { ?>
                            <tr>
                                <td><?php echo (($page - 1) * 20) + ($k + 1);?></td>
                                <td>
                                    <a href="/customer/customer-detail?id=<?php echo $v['id'];?>">
                                        <?php echo empty($v['customer_name']) ? '--' : $v['customer_name']; ?>
                                    </a>
                                </td>
                                <td><?php echo empty($v['customer_phone']) ? '--' : $v['customer_phone']; ?></td>
                                <td><?php echo  empty($v['create_card_time']) ? '--' :  date('Y-m-d', $v['create_card_time']); ?></td>
                                <td><?php echo empty($v['create_time']) ? '--' :  date('Y-m-d', $v['create_time']); ?></td>
                                <td><?php echo empty($v['predict_car_delivery_time']) ? '--' :  date('Y-m-d', $v['predict_car_delivery_time']); ?></td>
                                <td><?php echo empty($v['car_type_name']) ? '--' : $v['car_type_name']; ?></td>

                                <td><?php echo empty($v['color_configure']) ? '--' : $v['color_configure']; ?></td>
    							<td><?php echo empty($v['deposit']) ? '--' : $v['deposit']; ?></td>
                                <td><?php echo $v['buy_name']; ?></td>

                                <td><?php
                                    echo $v['is_insurance'] == 0 ? '否' : '是';
                                    ?></td>
    							<td><?php
    								if($v['status'] == 1) echo '处理中';
    								else if($v['status'] == 2) echo '客户未支付';
    								else if($v['status'] == 3) echo '财务到账';
    								else if($v['status'] == 4) echo '失败';
    								else if($v['status'] == 5) echo '客户已支付';

    								?>
    							</td>
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
</section>
<div class="modal fade" id="myModal" tabindex="-1" data-backdrop='static'   aria-labelledby="myModalLabel">

</div>