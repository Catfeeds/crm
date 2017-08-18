<?php
use common\logic\LinkPager;
$this->registerJsFile('/dist/plugins/daterangepicker/moment.min.js', [
    'depends' => ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/daterangepicker/daterangepicker.js', [
    'depends' => ['backend\assets\AdminLteAsset']
]);

$this->registerJsFile('/dist/js/user/reportForm.js', [
    'depends' => ['backend\assets\AdminLteAsset']
]);
?>
<style type="text/css">
    .error{style="border-color: red "}
</style>
    <section class="content-header clearfix">
        <h1 class="page-title">报表查询 <span class="c-red font14 ml-10">最近更新时间：<?php echo $get['upTime'];?></span></h1>
    </section>

    <section class="content-body">
        <div class="box advanced-search-form mb-lg">
            <form class="form-horizontal" action="index" method="get" id="form1">
                <input type="hidden" name="upTime" value="<?php echo $get['upTime'];?>">
                <div class="row">
                    <div class="form-group col-md-4">
                            <label class="control-label col-sm-3">报表类型：</label>
                            <div class="col-sm-9">
                                <select class="form-control" id="status" name="status">
                                    <option value="0">请选择</option>
                                    <option value="1">线索</option>
                                    <option value="2">无效线索</option>
                                    <option value="3">意向</option>
                                    <option value="4">战败</option>
                                    <option value="5">已到店</option>
                                    <option value="6">订车</option>
                                </select>
                            </div>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="" class="control-label col-sm-3">时间：</label>
                        <div class="col-sm-9">
                            <div class="calender-picker" >
                                <input class="form-control"  id="addtime" name="addtime" value="<?php echo $get['addtime'];?>" type="text">
                                <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="pull-right mr-15">
                            <input class="btn btn-primary btn-sm mr-15 pull-left" value="查询" type="button" id="check" >
                            <a href="index" class="pull-left"><input class="btn btn-default btn-sm" value="清除" id="clear" type="button"></a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    <?php if(!empty($list)){?>
        <div class="box box-none-border">
            <div class="box-body no-padding">

                <div class="table-responsive">
                    <div style="margin: 10px 10px 10px 0px;">
                        <a target="_blank" href="excel<?php echo $get['status'];?>?status=<?php echo $get['status'];?>&addtime=<?php echo $get['addtime'];?>">
                            <input class="btn btn-blue" value="导出列表" type="submit" >
                        </a>
                    </div>
                    <table class="table table-hover table-bordered table-list-check">
                        <thead>
                        <tr>
                            <th>序号</th>
                            <th>姓名</th>
                            <th>手机号码</th>
                            <th>性别</th>
                            <th>渠道来源</th>
                            <th>信息来源</th>
                            <th>品牌</th>
                            <th>厂商</th>
                            <th>意向车系</th>
                            <th>意向车型</th>
                            <th>提车门店</th>
                            <th>拟购时间</th>
                            <th>
                                <?php

                                    $start = $get['status'];
                                    if ($start == 1) echo '线索创建时间';
                                    else if ($start == 2) echo '无效判定时间';
                                    else if ($start == 3) echo '意向首次评级时间';
                                    else if ($start == 4) echo '战败时间';
                                    else if ($start == 5) echo '实际到店时间';
                                    else if ($start == 6) echo '签单时间';

                                ?>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($list as $k => $v){
                            $page = empty($_GET['page']) ? 1 : $_GET['page'];
                            ?>
                            <tr>
                                <td><?php echo (($page - 1) * 20) + ($k + 1);?></td>
                                <td><?php echo empty($v['customer_name']) ? '--' : $v['customer_name'];?></td>
                                <td><?php echo $v['customer_phone'];?></td>
                                <td><?php echo $v['sex'] == 1 ? '男' : '女';?></td>
                                <td><?php echo $v['clue_input_type_name'];?></td>
                                <td><?php echo $v['source_name'];?></td>
                                <td><?php echo $v['brand_name'];?></td>
                                <td><?php echo $v['factory_name'];?></td>
                                <td><?php echo $v['car_brand_type_name'];?></td>
                                <td><?php echo empty($v['intention_des']) ? '--' : $v['intention_des'];?></td>
                                <td><?php echo $v['shop_name'];?></td>
                                <td><?php echo $v['planned_purchase_time_name'];?></td>
                                <td>
                                    <?php

                                        $start = $get['status'];
                                        if ($start == 1) echo date('Y-m-d H:i:s',$v['create_time']);
                                        else if ($start == 2) echo date('Y-m-d H:i:s',$v['last_fail_time']);
                                        else if ($start == 3) echo date('Y-m-d H:i:s',$v['create_card_time']);
                                        else if ($start == 4) echo date('Y-m-d H:i:s',$v['last_fail_time']);
                                        else if ($start == 5) echo date('Y-m-d H:i:s',$v['create_time']);
                                        else if ($start == 6) echo date('Y-m-d H:i:s',$v['create_time']);
                                    ?>
                                </td>

                            </tr>
                        <?php }?>
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
<?php }?>

<script type="text/javascript">
    var status = '<?php echo empty($get['status']) ? 0 : $get['status'];?>';



</script>