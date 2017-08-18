<?php


$this->registerJsFile('/dist/plugins/echarts.min.js', [
    'depends' => ['backend\assets\AdminLteAsset']
]);

$this->registerJsFile('/dist/plugins/daterangepicker/bootstrap-datepicker.js', [
    'depends' => ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/daterangepicker/bootstrap-datepicker_002.js', [
    'depends' => ['backend\assets\AdminLteAsset']
]);
$this->registerCssFile('/dist/css/datepicker3.css', [
    'depends' => ['backend\assets\AdminLteAsset']
]);

$this->registerJsFile('/dist/js/user/ranking.js', [
    'depends' => ['backend\assets\AdminLteAsset']
]);
?>
<section class="content-header">
    <h1 class="page-title">排行榜 <span class="c-red font14 ml-10">最近更新时间：<?php echo $data_common['data_update_time']?></span></h1>
</section>

<section class="content-body">
    <div class="alert alert-warning alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <div>
            <i class="icon fa fa-warning"></i>
            订车排行榜：按大区、门店、顾问三个维度展示月度的订单成交数。包含订车战败。<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;成交率排行榜：按大区、门店、顾问三个维度展示月度的订单成交率。成交率 = 订车数 / （当月新增意向客户+之前结余意向客户） 。
       </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="box border1">
                <div class="box-header pdb-0 font15"><strong>订车排行榜</strong></div>
                <div class="box-body">
                    <form class="form-horizontal">
                        <div class="btn-group custom-btn-group col-md-6 mb-md pdl-0" data-toggle="buttons">
                            <label class="btn btn-default btn-sm active dc" val="1">
                                <input type="radio" name="options" id="option1" autocomplete="off" checked="">按区域
                            </label>
                            <label class="btn btn-default btn-sm dc" val="2">
                                <input type="radio" name="options" id="option2" autocomplete="off">按门店
                            </label>
                            <label class="btn btn-default btn-sm dc" val="3">
                                <input type="radio" name="options" id="option3" autocomplete="off">按顾问
                            </label>
                            <input type="hidden" id="d1" value="1">
                        </div>
                        <div class="form-group col-md-6 mb-md pdl-0 pdr-0">
                            <label class="control-label col-sm-3 t-r pdl-0">月份：</label>
                            <div class="col-md-9 pdl-0 pdr-0">
                                <div class="calender-picker">
                                    <input class="form-control" type="text" value="<?php echo date('Y-m') ?>"
                                           name="dingche_month" id="datetimepicker">
                                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i></div>
                            </div>
                        </div>

                    </form>
                    <div id="dingche">

                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="box border1">
                <div class="box-header pdb-0 font15"><strong>成交率排行榜</strong></div>
                <div class="box-body">
                    <form class="form-horizontal">
                        <div class="btn-group custom-btn-group col-md-6 mb-md pdl-0" data-toggle="buttons">
                            <label class="btn btn-default btn-sm active cj" val="1">
                                <input type="radio" name="options" id="option1" autocomplete="off" checked="">按区域
                            </label>
                            <label class="btn btn-default btn-sm cj" val="2">
                                <input type="radio" name="options" id="option2" autocomplete="off">按门店
                            </label>
                            <label class="btn btn-default btn-sm cj" val="3">
                                <input type="radio" name="options" id="option3" autocomplete="off">按顾问
                            </label>
                            <input type="hidden" id="d2" value="1">
                        </div>
                        <div class="form-group col-md-6 mb-md pdl-0 pdr-0">
                            <label class="control-label col-sm-3 t-r pdl-0">月份：</label>
                            <div class="col-md-9 pdl-0 pdr-0">
                                <div class="calender-picker">
                                    <input class="form-control" type="text" value="<?php echo date('Y-m') ?>"
                                           name="jiaoche_month" id="datetimepicker1">
                                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </form>
                    <div id="chengjiao">

                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- /.row -->
</section>