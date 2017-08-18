<?php
$this->title = '战败客户分析';
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
$this->registerJsFile('/dist/js/failcustomer.js', [
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
    <h1 class="page-title">客户分析-战败客户 <span class="c-red font14 ml-10">最近更新时间：<?php echo $data_common['data_update_time']?></span></h1>
</section>

<!-- Main content -->
<section class="content-body turnover transformtion">
    <div class="alert alert-warning alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <div>
            <i class="icon fa fa-warning"></i>
            意向战败原因分析：展示在意向客户阶段战败，战败原因最多的前五名。<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;订车战败列表：展示所有在订车客户阶段战败的客户。
        </div>
    </div>
    <div class="panel advanced-search-form pdt-sm pdb-sm">
        <div class="panel-heading pdb-0"><strong>意向战败原因分析</strong></div>
        <div class="panel-body">
            <div class="row">
                <form class="form-horizontal">
                        <div class="form-group col-md-4 mb-md">
                            <label class="control-label col-sm-3">渠道来源：</label>
                            <div class="cascader col-sm-9"  id="input_type">
                                <div class="cascader-inputbox">
                                    <input class="cascader-input" type="text" autocomplete="off" readonly value="<?php echo $data_common['input_type_name']?>"><i class="fa fa-set"></i>
                                    <input class="sid" name="input_type_id" id="input_type_id" value="<?php echo $data_common['input_type_id']?>" type="hidden">
                                </div>
                                <div class="cascader-list none">
                                    <ul class="cascader-menu">
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-4 mb-md">
                            <label class="control-label col-sm-3">区域&amp;门店：</label>
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
                        <div class="form-group col-md-4 mb-15">
                            <label class="control-label col-sm-4 t-r">时&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;间：</label>
                            <div class="col-md-8">
                                <div class="calender-picker">
                                    <input class="form-control" id="search_time" name ="search_time" value="<?php echo $data_common['search_time']?>" type="text" onchange="submitData()">
                                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                </div>
                            </div>
                        </div>
                </form>
            </div>
            <div class="row">
                <div class="mt-sm mb-lg">
                    <div id="chart" style="min-width: 200px; height: 300px; -webkit-tap-highlight-color: transparent; user-select: none; position: relative; background: transparent;" _echarts_instance_="ec_1491976235384"><div style="position: relative; overflow: hidden; width: 654px; height: 300px; padding: 0px; margin: 0px; border-width: 0px; cursor: default;"><canvas width="654" height="300" data-zr-dom-id="zr_0" style="position: absolute; left: 0px; top: 0px; width: 654px; height: 300px; user-select: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); padding: 0px; margin: 0px; border-width: 0px;"></canvas></div><div style="position: absolute; display: none; border-style: solid; white-space: nowrap; z-index: 9999999; transition: left 0.4s cubic-bezier(0.23, 1, 0.32, 1), top 0.4s cubic-bezier(0.23, 1, 0.32, 1); background-color: rgba(0, 0, 0, 0.6); border-width: 0px; border-color: rgb(51, 51, 51); border-radius: 4px; color: rgb(255, 255, 255); font-style: normal; font-variant: normal; font-weight: normal; font-stretch: normal; font-size: 14px; font-family: &quot;Microsoft YaHei&quot;; line-height: 21px; padding: 5px; left: 297px; top: 108px;">访问来源 <br>搜索引擎 : 1548 (60.42%)</div></div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 mt-lg">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-list-check">
                            <thead class="lte-table-thead">
                                <tr id="column">
                                    <th width="50">序号</th>
                                    <th>名称</th>
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
        <div class="panel-heading pdb-0"><strong>订车战败列表</strong></div>
        <div class="panel-body">
            <div class="row">
                    <form class="form-horizontal">
                        <div class="form-group col-md-4 mb-lg">
                            <label class="control-label col-sm-3">时间：</label>
                            <div class="col-sm-9">
                                <div class="calender-picker">
                                    <input class="form-control" id="search_time2" name ="search_time2" value="<?php echo $data_common['search_time']?>" type="text" onchange="submitData2()">
                                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                </div>
                            </div>
                        </div>
                    </form>
            </div>


            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-list-check">
                            <thead class="lte-table-thead">
                            <tr>
                                <th width="50">序号</th>
                                <th>姓名</th>
                                <th>订车车型</th>
                                <th>战败时间</th>
                                <th>战败顾问</th>
                                <th>战败原因</th>
                            </tr>
                            </thead>
                            <tbody id="tbody2">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-footer clearfix border-none pdt-0">
            <div class="paginationbox pdr-md">
                <div class="jump pull-right">跳转<input type="text" id="input_page" class="form-control" onblur="submitData2()">页</div>
                <div class="display-page pull-right">
                    <select id="select_page" class="form-control" onchange="submitData3()">
                        <option value="5">5条/页</option>
                        <option value="20">20条/页</option>
                        <option value="30">30条/页</option>
                    </select>
                </div>

                <ul class="pagination pagination-sm no-margin pull-right" id="page_num">
                    <li><a href="#">«</a></li>
                    <li><a href="#">1</a></li>
                    <li><a href="#">2</a></li>
                    <li><a href="#">3</a></li>
                    <li><a href="#">»</a></li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /.row -->
</section>

<script>
    var data_common = <?php echo json_encode($data_common)?>;
    var selectOrgJson = eval('<?php echo $selectOrgJson; ?>');

</script>

