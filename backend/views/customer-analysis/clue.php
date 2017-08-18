<?php
$this->title = '线索分析';
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
$this->registerJsFile('/dist/js/clue.js', [
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
    <h1 class="page-title">客户分析-线索 <span class="c-red font14 ml-10">最近更新时间：<?php echo $data_common['data_update_time']?></span></h1>
</section>

<!-- Main content -->
<section class="content-body">
    <div class="alert alert-warning alert-dismissible mb-lg">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <div><i class="icon fa fa-warning"></i>渠道来源分析：一段时间内新增客户的转化情况。<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;不同渠道线索有效率对比：不同渠道来源的线索转化率对比，可以分析出不同渠道的客户质量。</div>
    </div>
    <div class="panel advanced-search-form mb-lg pdt-sm pdb-0">
        <div class="panel-heading pdb-0"><strong>渠道来源分析</strong></div>
        <div class="panel-body pdb-lg">

            <div class="form-horizontal row mb-sm">
                <div class="form-group col-md-4">
                    <label class="control-label col-md-3">区域门店：</label>
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
                <div class="form-group col-md-4 clearfix">
                    <label class="control-label col-sm-3">时间：</label>
                    <div class="col-sm-9">
                        <div class="calender-picker">
                            <input class="form-control" id="search_time" name ="search_time" value="<?php echo $data_common['search_time']?>" type="text" >
                            <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                        </div>
                    </div>
                </div>
            </div>
<!--            <input type="button" value="提交" onclick="submitData()">-->
            <div class="row">
                <div class="col-sm-12">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="mb-md" id="chart1" style="min-width: 200px; height: 300px; -webkit-tap-highlight-color: transparent; user-select: none; position: relative; background: rgb(243, 243, 243);" _echarts_instance_="ec_1491803150698"><div style="position: relative; overflow: hidden; width: 517px; height: 300px; padding: 0px; margin: 0px; border-width: 0px; cursor: pointer;"><canvas width="517" height="300" data-zr-dom-id="zr_0" style="position: absolute; left: 0px; top: 0px; width: 517px; height: 300px; user-select: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); padding: 0px; margin: 0px; border-width: 0px;"></canvas></div><div style="position: absolute; display: none; border-style: solid; white-space: nowrap; z-index: 9999999; transition: left 0.4s cubic-bezier(0.23, 1, 0.32, 1), top 0.4s cubic-bezier(0.23, 1, 0.32, 1); background-color: rgba(0, 0, 0, 0.6); border-width: 0px; border-color: rgb(51, 51, 51); border-radius: 4px; color: rgb(255, 255, 255); font-style: normal; font-variant: normal; font-weight: normal; font-stretch: normal; font-size: 14px; font-family: &quot;Microsoft YaHei&quot;; line-height: 21px; padding: 5px; left: 300px; top: 155px;">访问来源 <br>直接访问 : 335 (13.08%)</div></div>
                        </div>
                        <div class="col-sm-4">
                            <div class="mb-md" id="chart3" style="min-width: 200px; height: 300px; -webkit-tap-highlight-color: transparent; user-select: none; position: relative; background: rgb(243, 243, 243);" _echarts_instance_="ec_1491803150700"><div style="position: relative; overflow: hidden; width: 517px; height: 300px; padding: 0px; margin: 0px; border-width: 0px;"><canvas width="517" height="300" data-zr-dom-id="zr_0" style="position: absolute; left: 0px; top: 0px; width: 517px; height: 300px; user-select: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); padding: 0px; margin: 0px; border-width: 0px;"></canvas></div><div></div></div>
                        </div>
                        <div class="col-sm-4">
                            <div class="mb-md" id="chart2" style="min-width: 200px; height: 300px; -webkit-tap-highlight-color: transparent; user-select: none; position: relative; background: rgb(243, 243, 243);" _echarts_instance_="ec_1491803150699"><div style="position: relative; overflow: hidden; width: 517px; height: 300px; padding: 0px; margin: 0px; border-width: 0px; cursor: default;"><canvas width="517" height="300" data-zr-dom-id="zr_0" style="position: absolute; left: 0px; top: 0px; width: 517px; height: 300px; user-select: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); padding: 0px; margin: 0px; border-width: 0px;"></canvas></div><div></div></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-list-check">
                            <thead class="lte-table-thead">
                            <tr>
                                <th width="50">序号</th>
                                <th>名称</th>
                                <th>新增线索</th>
                                <th>未分配线索</th>
                                <th>跟进中线索</th>
                                <th>已转化线索</th>
                                <th>无效线索</th>
                                <th>有效率</th>
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
    <div class="panel advanced-search-form mb-lg pdt-sm pdb-0">
        <div class="panel-heading pdb-0"><strong>不同渠道线索有效率对比</strong></div>
        <div class="panel-body pdb-lg">
            <div class="form-horizontal row mb-sm">
                <div class="form-group col-md-4 mb-md">
                    <label class="control-label col-sm-3">区域&amp;门店：</label>
                    <div class="col-md-8"  id="orgSelect2">
                        <el-cascader
                                placeholder="请选择"
                                size="small"
                                :options="options1"
                                @change="handlechange_shopid2"
                                filterable
                            ></el-cascader>
                        <input id="shopid2" name="shop_id" type="hidden" value="">
                    </div>
                </div>
                <div class="form-group col-md-4 mb-md">
                    <label class="control-label col-sm-3 t-r">时间：</label>
                    <div class="col-sm-9">
                        <div class="calender-picker">
                            <input class="form-control" id="search_time2" name ="search_time" value="<?php echo $data_common['search_time']?>" type="text" >
                            <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row pdt-15">
                <div id="chart4" style="min-width: 200px; height: 400px; -webkit-tap-highlight-color: transparent; user-select: none; position: relative; background: transparent;" _echarts_instance_="ec_1491803150701"><div style="position: relative; overflow: hidden; width: 1641px; height: 400px; padding: 0px; margin: 0px; border-width: 0px; cursor: default;"><canvas width="1641" height="400" data-zr-dom-id="zr_0" style="position: absolute; left: 0px; top: 0px; width: 1641px; height: 400px; user-select: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); padding: 0px; margin: 0px; border-width: 0px;"></canvas></div><div style="position: absolute; display: block; border-style: solid; white-space: nowrap; z-index: 9999999; transition: left 0.4s cubic-bezier(0.23, 1, 0.32, 1), top 0.4s cubic-bezier(0.23, 1, 0.32, 1); background-color: rgba(0, 0, 0, 0.6); border-width: 0px; border-color: rgb(51, 51, 51); border-radius: 4px; color: rgb(255, 255, 255); font-style: normal; font-variant: normal; font-weight: normal; font-stretch: normal; font-size: 14px; font-family: &quot;Microsoft YaHei&quot;; line-height: 21px; padding: 5px; left: 205.271px; top: 329px;">Mon<br><span style="display:inline-block;margin-right:5px;border-radius:10px;width:9px;height:9px;background-color:#3398DB"></span>直接访问 : 10</div></div>
            </div>
        </div>
    </div>
    <!-- /.row -->
</section>

<script>
    var data_common = <?php echo json_encode($data_common)?>;
    var selectOrgJson = eval('<?php echo $selectOrgJson; ?>');
</script>
