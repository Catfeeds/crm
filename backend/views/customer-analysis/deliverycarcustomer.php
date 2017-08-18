<?php
$this->title = '客户分析-交车客户';
$this->registerJsFile('/dist/plugins/daterangepicker/moment.min.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/daterangepicker/daterangepicker.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);

$this->registerJsFile('/dist/plugins/vue-element/vue.min.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/vue-element/index.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerCssFile('/dist/plugins/vue-element/index.css', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);


$this->registerJsFile('/dist/plugins/echarts.min.js', [
    'depends' => ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/js/deliverycarcustomer.js', [
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

?>
<section class="content-header">
    <h1 class="page-title">客户分析-订车客户 <span class="c-red font14 ml-10">最近更新时间：<?php echo $data_common['data_update_time']?></span></h1>
</section>

<!-- Main content -->
<section class="content-body turnover transformtion">
    <div class="alert alert-warning alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <div>
            <i class="icon fa fa-warning"></i>成交周期分析：从建卡开始到订车结束。<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;成交客户渠道来源分析：展示成交客户的渠道来源，渠道来源最多的前五名。
        </div>
    </div>
    <div class="panel advanced-search-form pdt-sm pdb-sm">
        <div class="panel-heading pdb-0"><strong>成交周期分析</strong></div>
        <div class="panel-body pdb-0">
            <div class="row">
                <div class="col-sm-12">
                    <form class="form-horizontal">
                        <div class="form-group col-md-4 mb-md" >
                            <label class="control-label col-sm-3 t-r">区域/门店：</label>
                            <div class="col-md-8"  id="orgSelect">
                                <el-cascader
                                        placeholder="请选择"
                                        size="small"
                                        :options="options1"
                                        @change="handlechange_shopid"
                                        filterable
                                    ></el-cascader>
                                <input id="shopid" name="shop_id" type="hidden" value="">
                            </div>
                        </div>
                        <div class="form-group col-md-4 mb-md">
                            <label class="control-label col-sm-3">时间：</label>
                            <div class="col-sm-9">
                                <div class="calender-picker">
                                    <input class="form-control" id="search_time" name ="search_time" value="<?php echo $data_common['search_time']?>" type="text" onchange="submitData()">
                                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                </div>
                            </div>
                        </div>
<!--                        <input type="button" onclick="submitData()">-->
                    </form>
                </div>
            </div>
            <div class="row mt-sm">
                <div class="mt-sm mb-lg">
                    <div id="chart" style="min-width: 200px; height: 300px; -webkit-tap-highlight-color: transparent; user-select: none; position: relative; background: transparent;" _echarts_instance_="ec_1492054612553"><div style="position: relative; overflow: hidden; width: 654px; height: 300px; padding: 0px; margin: 0px; border-width: 0px; cursor: pointer;"><canvas width="654" height="300" data-zr-dom-id="zr_0" style="position: absolute; left: 0px; top: 0px; width: 654px; height: 300px; user-select: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); padding: 0px; margin: 0px; border-width: 0px;"></canvas></div><div style="position: absolute; display: none; border-style: solid; white-space: nowrap; z-index: 9999999; transition: left 0.4s cubic-bezier(0.23, 1, 0.32, 1), top 0.4s cubic-bezier(0.23, 1, 0.32, 1); background-color: rgba(0, 0, 0, 0.6); border-width: 0px; border-color: rgb(51, 51, 51); border-radius: 4px; color: rgb(255, 255, 255); font-style: normal; font-variant: normal; font-weight: normal; font-stretch: normal; font-size: 14px; font-family: &quot;Microsoft YaHei&quot;; line-height: 21px; padding: 5px; left: 287px; top: 218px;">访问来源 <br>搜索引擎 : 1548 (60.42%)</div></div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 mb-lg">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-list-check">
                            <thead class="lte-table-thead">
                            <tr>
                                <th width="50">序号</th>
                                <th>名称</th>
                                <th>1-7天</th>
                                <th>7-14天</th>
                                <th>1个月内</th>
                                <th>2个月内</th>
                                <th>2个月以上</th>
                            </tr>
                            </thead>
                            <tbody id="tbody">

                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel advanced-search-form pdt-sm pdb-sm">
        <div class="panel-heading pdb-0"><strong>成交客户渠道来源分析</strong></div>
        <div class="panel-body pdb-0">
            <div class="row mt-sm">
                <div class="col-sm-12">
                    <form class="form-horizontal">
                        <div class="form-group col-md-4 mb-md">
                            <label class="control-label col-sm-3">区域门店：</label>
                            <div class="col-md-8"  id="orgSelect2">
                                <el-cascader
                                        placeholder="请选择"
                                        size="small"
                                        :options="options1"
                                        @change="handlechange_shopid"
                                        filterable
                                    ></el-cascader>
                                <input id="shopid2" name="shop_id" type="hidden" value="">
                            </div>
                        </div>
                        <div class="form-group col-md-4 mb-md">
                            <label class="control-label col-sm-3">时间：</label>
                            <div class=" col-sm-9">
                                <div class="calender-picker">
                                    <input class="form-control" id="search_time2" name ="search_time" value="<?php echo $data_common['search_time']?>" type="text" onchange="submitData()">
                                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="row mt-sm mb-lg">
                <div class="col-sm-12">
                    <div id="chart1" style="min-width: 200px; height: 300px; -webkit-tap-highlight-color: transparent; user-select: none; position: relative; background: transparent;" _echarts_instance_="ec_1492054612554"><div style="position: relative; overflow: hidden; width: 1611px; height: 300px; padding: 0px; margin: 0px; border-width: 0px; cursor: default;"><canvas width="1611" height="300" data-zr-dom-id="zr_0" style="position: absolute; left: 0px; top: 0px; width: 1611px; height: 300px; user-select: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); padding: 0px; margin: 0px; border-width: 0px;"></canvas></div><div></div></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-list-check">
                            <thead class="lte-table-thead">
                            <tr id="column">
                                <th width="50">序号</th>
                                <th>名称</th>
                                <th class="change">战败时间</th>
                                <th class="change">战败顾问</th>
                                <th class="change">战败来源</th>
                                <th class="change">战败原因</th>
                                <th class="change">战败原因</th>
                            </tr>
                            </thead>
                            <tbody id="tbody1">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-footer clearfix border-none pdr-md">
        </div>
    </div>
    <!-- /.row -->
</section>

<script>
    var data_common = <?php echo json_encode($data_common)?>;
    var selectOrgJson = eval('<?php echo $selectOrgJson; ?>');
</script>
