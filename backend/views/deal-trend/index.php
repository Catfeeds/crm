<?php
$this->title = '成交趋势';
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
$this->registerJsFile('/dist/js/dealtrend.js', [
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
    <h1 class="page-title">成交趋势 <span class="c-red font14 ml-10">最近更新时间：<?php echo json_decode($data_common,true)['data_update_time']?></span></h1>
</section>

<!-- Main content -->
<section class="content-body turnover">
    <div class="alert alert-warning alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <div><i class="icon fa fa-warning"></i>成交量趋势分析：以年为单位统计订车数量，包含订车战败。<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;成交率趋势分析：以年为单位统计订车成交率，包含订车战败。订车率 = 订车数 / （订车数 + 意向客户数）</div>
    </div>
    <div class="panel advanced-search-form pdt-sm pdb-sm">
        <div class="panel-heading pdb-0">
            <h4 class="mt-0 mb-0">成交量趋势分析</h4>
        </div>
        <div class="panel-body">
            <div class="row">
                <form class="form-horizontal">
                <div class="col-sm-12">
                    <div class="form-group col-md-4 mb-md" >
                        <label class="control-label col-sm-3">渠道来源：</label>
                        <div class="cascader col-sm-9"  id="input_type">
                            <div class="cascader-inputbox">
                                <input class="cascader-input" type="text" autocomplete="off" id="input_type_name" readonly value=""><i class="fa fa-set"></i>
                                <input class="sid" name="input_type_id" id="input_type_id" type="hidden" value="">
                            </div>
                            <div class="cascader-list none">
                                <ul class="cascader-menu">
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-4 mb-md">
                        <label class="control-label col-sm-3">区域门店：</label>
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
                    <div class="form-group col-md-4">
                        <label class="control-label col-sm-3">年份：</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="search_year" onchange="submitData()">
                                <option value="2014" >请选择</option>
                                <option value="2015" >2015</option>
                                <option value="2016" >2016</option>
                                <option value="2017" selected>2017</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
            </div>
            <div class="row">
                <div class="col-sm-12" id="main1" style="width: 100%; height: 400px; -webkit-tap-highlight-color: transparent; user-select: none; position: relative; background: transparent;" _echarts_instance_="ec_1491998926030"><div style="position: relative; overflow: hidden; width: 1621px; height: 400px; padding: 0px; margin: 0px; border-width: 0px; cursor: default;"><canvas width="1621" height="400" data-zr-dom-id="zr_0" style="position: absolute; left: 0px; top: 0px; width: 1621px; height: 400px; user-select: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); padding: 0px; margin: 0px; border-width: 0px;"></canvas></div><div style="position: absolute; display: none; border-style: solid; white-space: nowrap; z-index: 9999999; transition: left 0.4s cubic-bezier(0.23, 1, 0.32, 1), top 0.4s cubic-bezier(0.23, 1, 0.32, 1); background-color: rgba(0, 0, 0, 0.6); border-width: 0px; border-color: rgb(51, 51, 51); border-radius: 4px; color: rgb(255, 255, 255); font-style: normal; font-variant: normal; font-weight: normal; font-stretch: normal; font-size: 14px; font-family: &quot;Microsoft YaHei&quot;; line-height: 21px; padding: 5px; left: 344.144px; top: 326px;">周二<br><span style="display:inline-block;margin-right:5px;border-radius:10px;width:9px;height:9px;background-color:#c12e34"></span>邮件营销 : 132<br><span style="display:inline-block;margin-right:5px;border-radius:10px;width:9px;height:9px;background-color:#e6b600"></span>联盟广告 : 182</div></div>
            </div>
        </div>
    </div>
    <div class="panel advanced-search-form pdt-sm pdb-sm">
        <div class="panel-heading pdb-0">
            <h4 class="mt-0 mb-0">成交率趋势分析</h4>
        </div>
        <div class="panel-body">
            <div class="row">
                <form class="form-horizontal">
                <div class="col-sm-12">
                    <div class="form-group col-md-4">
                        <label class="control-label col-sm-3">渠道来源：</label>
                        <div class="cascader col-sm-9"  id="input_type2">
                            <div class="cascader-inputbox">
                                <input class="cascader-input" type="text" id="input_type_name2" autocomplete="off" readonly value=""><i class="fa fa-set"></i>
                                <input class="sid" name="input_type_id" id="input_type_id2"  type="hidden" value="">
                            </div>
                            <div class="cascader-list none">
                                <ul class="cascader-menu">
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-4" >
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
                    <div class="form-group col-md-4">
                        <label class="control-label col-sm-3">年份：</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="search_year2" onchange="submitData2()">
                                <option value="2014" >请选择</option>
                                <option value="2015" >2015</option>
                                <option value="2016" >2016</option>
                                <option value="2017" selected >2017</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
            </div>
            <div class="row mt-sm">
                <div class="col-sm-12" id="main2" style="width: 100%; height: 400px; -webkit-tap-highlight-color: transparent; user-select: none; position: relative; background: transparent;" _echarts_instance_="ec_1491998926031"><div style="position: relative; overflow: hidden; width: 1621px; height: 400px; padding: 0px; margin: 0px; border-width: 0px; cursor: default;"><canvas width="1621" height="400" data-zr-dom-id="zr_0" style="position: absolute; left: 0px; top: 0px; width: 1621px; height: 400px; user-select: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); padding: 0px; margin: 0px; border-width: 0px;"></canvas></div><div style="position: absolute; display: none; border-style: solid; white-space: nowrap; z-index: 9999999; transition: left 0.4s cubic-bezier(0.23, 1, 0.32, 1), top 0.4s cubic-bezier(0.23, 1, 0.32, 1); background-color: rgba(0, 0, 0, 0.6); border-width: 0px; border-color: rgb(51, 51, 51); border-radius: 4px; color: rgb(255, 255, 255); font-style: normal; font-variant: normal; font-weight: normal; font-stretch: normal; font-size: 14px; font-family: &quot;Microsoft YaHei&quot;; line-height: 21px; padding: 5px; left: 344.144px; top: 275px;">周二<br><span style="display:inline-block;margin-right:5px;border-radius:10px;width:9px;height:9px;background-color:#c12e34"></span>邮件营销 : 132<br><span style="display:inline-block;margin-right:5px;border-radius:10px;width:9px;height:9px;background-color:#e6b600"></span>联盟广告 : 182</div></div>
            </div>
        </div>
    </div>

    <!-- /.row -->
</section>
<script>
    var data_common = <?php echo $data_common?>;
    var selectOrgJson = eval('<?php echo $selectOrgJson; ?>');
</script>

