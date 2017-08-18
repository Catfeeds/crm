<?php
$this->registerCssFile('/dist/css/home/AdminLTE.min.css', [
    'depends' => ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/js/user/home.js', [
    'depends' => ['backend\assets\AdminLteAsset']
]);

$this->registerJsFile('/dist/plugins/daterangepicker/moment.min.js', [
    'depends' => ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/daterangepicker/daterangepicker.js', [
    'depends' => ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/knob/jquery.knob.js', [
    'depends' => ['backend\assets\AdminLteAsset']
]);

?>
<!-- Main content -->
<section class="content-body home">
    <div class="row pdt-md pdb-sm">
        <div class="col-sm-4 col-xs-12 itembox mb-md">
            <div class="bg-green border-radius4 project o-f">
                <a href="/detailed/no-car">
                    <span class="icon home-icon b-qblack"><img src="/dist/img/undelivered.png"></span>
                    <div class="data">
                        <span class="c-white">未交车</span>
                        <strong class="c-white"><?php echo $data['weijiaoche']['num']; ?></strong>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-sm-8 col-xs-12 itembox mb-md">
            <div class="bg-blue border-radius4 indication o-f">
                <?php if ($data['month'] === 0 && $level == 10) { ?>
                    <a href="#">
                        <div class="data">
                      <span class="c-white lh-56">还未设定本月销售目标</span>
                        </div>
                    </a>
                <?php } else if ($data['month'] !== 0 && $level == 10) { ?>
                    <a href="target">
                        <div class="chart icon">
                            <div class="ring">
                                <div class="ring-left">
                                    <div class="ringside-left"></div>
                                </div>
                                <div class="ring-right">
                                    <div class="ringside-right"></div>
                                </div>
                                <div class="ring-mask"><span><?php echo ceil($data['month'] * 100); ?></span>%</div>
                            </div>
                        </div>
                        <div class="data">
                            <span class="c-white lh-56">本月指标完成度</span>
                        </div>
                    </a>
                    <?php
                } else if ($data['month'] === 0 && $level == 15) { //公司 当前所有区域都没有设置任务
                    ?>
                    <a href="#">
                        <div class="data">
                      <span class="c-white lh-56">还未设定本月销售目标
					  请让区长到后台设定</span>
                        </div>
                    </a>
                <?php } else if ($data['month'] !== 0 && $level == 15) { //公司 已有区域设置了任务?>
                    <a href="target">
                        <div class="chart icon">
                            <div class="ring">
                                <div class="ring-left">
                                    <div class="ringside-left"></div>
                                </div>
                                <div class="ring-right">
                                    <div class="ringside-right"></div>
                                </div>
                                <div class="ring-mask"><span><?php echo ceil($data['month'] * 100); ?></span>%</div>
                            </div>
                        </div>
                        <div class="data">
                            <span class="c-white lh-56">本月指标完成度</span>
                        </div>
                    </a>
                <?php } else if ($data['month'] === 0 && $level == 20) {//某大区 当前区域没有设置任务?>
                    <a href="target">
                        <div class="data">
                            <span class="c-white lh-56">点击设置销售指标</span>
                        </div>
                    </a>
                <?php } else if ($data['month'] !== 0 && $level == 20) { ?>
                    <a href="target">
                        <div class="chart icon">
                            <div class="ring">
                                <div class="ring-left">
                                    <div class="ringside-left"></div>
                                </div>
                                <div class="ring-right">
                                    <div class="ringside-right"></div>
                                </div>
                                <div class="ring-mask"><span><?php echo ceil($data['month'] * 100); ?></span>%</div>
                            </div>
                        </div>
                        <div class="data">
                            <span class="c-white lh-56">本月指标完成度</span>
                        </div>
                    </a>
                <?php } else if ($data['month'] === 0 && $level == 30) {//某门店 当前门店没有设置任务?>
                    <a href="">
                        <div class="data">
                            <span class="c-white lh-56">区长还未设定本月销售目标</span>
                        </div>
                    </a>
                <?php } else if ($data['month'] !== 0 && $level == 30) { ?>
                    <a href="#">
                        <div class="chart icon">
                            <div class="ring">
                                <div class="ring-left">
                                    <div class="ringside-left"></div>
                                </div>
                                <div class="ring-right">
                                    <div class="ringside-right"></div>
                                </div>
                                <div class="ring-mask"><span><?php echo ceil($data['month'] * 100); ?></span>%</div>
                            </div>
                        </div>
                        <div class="data col-sm-4 t-c pull-left">
                            <span class="c-white lh-56">本月指标完成进度</span>
                        </div>
                        <div class="data col-sm-3 col-xs-6 t-c">
                            <span class="c-white">任务台数</span>
                            <strong class="c-white"><?php echo $data['month_list']['target_num'] ?></strong>
                        </div>
                        <div class="data col-sm-2 col-xs-6 t-c">
                            <span class="c-white">已完成</span>
                            <strong class="c-white"><?php echo $data['month_list']['finish_num'] ?></strong>
                        </div>
                    </a>
                <?php } ?>
            </div>
        </div>

    </div>
    <div class="panel border-none boxshadow-none mb-0">
        <div class="panel-heading pd-0">
            <h3 class="mt-0 o-f"><strong class="pull-left mb-md pdr-md"><?php
                    if ($level == 10) echo '所有公司销售数据一览';
                    else if ($level == 15) echo '所有大区销售数据一览';
                    else if ($level == 20) echo $data['name'] . '销售数据一览';
                    else if ($level == 30) echo $data['name'] . '销售数据一览';
                    ?></strong><span class=" col-md-6 col-xs-12 mb-lg pdl-0">(<b id="title_time" class="font-w500"></b>)</span>
            </h3>
            <form method="post" action="index" id="form1">
                <input type="hidden" name="time" id="time"/>
                <div class="form-horizontal">
                    <div class="mb-md clearfix">
                        <div class="form-group pull-left" style="padding:0;">
                            <div class="btn-group custom-btn-group mr-15" data-toggle="buttons">
                                <label class="btn btn-default" value="1" id="t1"><input type="radio" autocomplete="off"
                                                                                        checked="">今天</label>
                                <label class="btn btn-default" value="2" id="t2"><input type="radio" autocomplete="off">昨天</label>
                                <label class="btn btn-default" value="3" id="t3"><input type="radio" autocomplete="off">本月</label>
                            </div>
                        </div>
                        <div class="form-group col-md-4 col-xs-12" style="padding:0;">
                            <div class="row">
                                <label class="control-label col-sm-2">时间：</label>
                                <div class="col-sm-10">
                                    <div class="calender-picker">
                                        <input type="text" class="form-control" name="addtime" value="" id="addtime"
                                               style="width:100%;">
                                        <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <div class="row">
                <div class="col-md-4 col-sm-6 col-xs-12 itembox mb-md">
                    <div class="border-radius4t border1">
                        <a href="/detailed/index?addtime=<?php echo $data['addtime']; ?>&selectArea=<?php echo $data['name']; ?>">
                            <span class="icon home-icon b-yellow"><img src="/dist/img/undeliverycustomer.png"></span>
                            <div class="data">
                                <span>交车客户 ( 位 )</span>
                                <strong><?php echo $data['list']['chengjiao_num'] ?></strong>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12 itembox mb-md">
                    <div class="border-radius4 border1">
                        <a href="/detailed/index?addtime=<?php echo $data['addtime']; ?>&selectArea=<?php echo $data['name']; ?>">
                            <span class="icon home-icon b-yellow"><img src="/dist/img/defeatcustomer.png"></span>
                            <div class="data">
                                <span>战败客户 ( 位 )</span>
                                <strong><?php echo $data['list']['fail_num'] ?></strong>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12 itembox mb-md">
                    <div class="border-radius4t border1">
                        <a href="/detailed/index?addtime=<?php echo $data['addtime']; ?>&selectArea=<?php echo $data['name']; ?>">
                            <span class="icon home-icon b-yellow"><img src="/dist/img/undeliverycustomer.png"></span>
                            <div class="data">
                                <span>交车任务 ( 个 )</span>
                                <strong><?php echo $data['mention_task_num'] ?></strong>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 col-sm-6 col-xs-12 itembox">
                    <div class="box border-radius4 border1 mb-md">
                        <div class="box-header border-btm1">线索</div>
                        <a href="/detailed/index?addtime=<?php echo $data['addtime']; ?>&selectArea=<?php echo $data['name']; ?>">
                            <div class="col-sm-6 col-xs-6 data t-c">
                                <span>新增线索（个）</span>
                                <strong><?php echo $data['list']['new_clue_num'] ?></strong>
                            </div>
                            <div class="col-sm-6 col-xs-6 data t-c">
                                <span>跟进中线索（个）</span>
                                <strong><?php echo $data['xiansuo_genjin']['num'] ?></strong>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12 itembox">
                    <div class="box border-radius4 border1 mb-md">
                        <div class="box-header border-btm1">电话任务</div>
                        <a href="/detailed/index?addtime=<?php echo $data['addtime']; ?>&selectArea=<?php echo $data['name']; ?>">
                            <div class="col-sm-6 col-xs-6 data t-c">
                                <span>电话任务（个）</span>
                                <strong><?php echo $data['list']['phone_task_num'] ?></strong>
                            </div>
                            <div class="col-sm-6 col-xs-6 data t-c">
                                <span>任务完成（个）</span>
                                <strong><?php echo $data['list']['finish_phone_task_num'] ?></strong>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12 itembox">
                    <div class="box border-radius4 border1 mb-md">
                        <div class="box-header border-btm1">意向客户</div>
                        <a class="row"
                           href="/detailed/index?addtime=<?php echo $data['addtime']; ?>&selectArea=<?php echo $data['name']; ?>">
                            <div class="col-sm-6 col-xs-6 data t-c">
                                <span>新增（个）</span>
                                <strong><?php echo $data['list']['new_intention_num'] ?></strong>
                            </div>
                            <div class="col-sm-6 col-xs-6 data t-c">
                                <span>跟进中（个）</span>
                                <strong><?php echo $data['yixiang_genjin']['num'] ?></strong>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-sm-12 col-xs-12 itembox discuss-item">
                    <div class="box border-radius4 border1 o-f mb-md">
                        <div class="box-header border-btm1">意向客户</div>
                        <a href="/detailed/index?addtime=<?php echo $data['addtime']; ?>&selectArea=<?php echo $data['name']; ?>"
                           class="col-sm-3 col-xs-12">
                            <span class="icon home-icon b-red"><img src="/dist/img/talk_record.png"></span>
                            <div class="data t-c">
                                <span>商谈记录</span>
                                <strong><?php echo $data['list']['talk_num'] ?></strong>
                            </div>
                        </a>
                        <div class="discuss-md col-sm-5 col-xs-12">
                            <div class="row">
                                <div class="col-sm-6 col-xs-6 data t-c">
                                    <span>来电（次）</span>
                                    <strong><?php echo $data['list']['lai_dian_num'] ?></strong>
                                </div>
                                <div class="col-sm-6 col-xs-6 data t-c">
                                    <span>去电（次）</span>
                                    <strong><?php echo $data['list']['qu_dian_num'] ?></strong>
                                </div>
                            </div>
                        </div>
                        <a href="/detailed/index?addtime=<?php echo $data['addtime']; ?>&selectArea=<?php echo $data['name']; ?>"
                           class="col-sm-4 col-xs-12">
                            <div class="row">
                                <div class="col-sm-6 col-xs-6 data t-c">
                                    <span>到店（次）</span>
                                    <strong><?php echo $data['list']['to_shop_num'] ?></strong>
                                </div>
                                <div class="col-sm-6 col-xs-6 data t-c">
                                    <span>上门（次）</span>
                                    <strong><?php echo $data['list']['to_home_num'] ?></strong>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="form-inline row"></div>
        </div>
        <div class="panel-body">

        </div>
    </div>
    <!-- /.row -->
</section>
<!-- /.content -->
<script type="text/javascript">
    var time = "<?php echo $data['time'];?>";
    var addtime = "<?php echo $data['addtime'];?>";


</script>
<?php
$js = <<<_SCRIPT
    /*$(".knob").knob({
            max: 100,
            min: 0,
            thickness: .2,
            fgColor: '#9CDD70',
            bgColor: '#2DA9E1',
            'cursor':false
    });*/
    $('.ring').each(function(index, el) {
                var num = $(this).find('span').text() * 3.6;
                if (num<=180) {
                    $(this).find('.ringside-right').css('transform', "rotate(" + num + "deg)");
                } else {
                    $(this).find('.ringside-right').css('transform', "rotate(180deg)");
                    $(this).find('.ringside-left').css('transform', "rotate(" + (num - 180) + "deg)");
                };
            });
_SCRIPT;
$this->registerJs($js);
?>
