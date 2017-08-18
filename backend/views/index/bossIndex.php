<?php

$session = Yii::$app->getSession();
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
$this->registerJsFile('/dist/plugins/vue-element/vue.min.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/vue-element/index.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerCssFile('/dist/plugins/vue-element/index.css', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);

$this->registerJsFile('/dist/js/bossbaobiao.js', [
    'depends' => ['backend\assets\AdminLteAsset']
]);

?>

<section class="content-header">
    <h1 class="page-title">销售指标-<?php echo $title;?></h1>
</section>
<!-- Main content -->
<section class="content-body">
    <div class="panel advanced-search-form pdt-sm pdb-s">
        <div class="panel-heading pdb-0"><strong>月度销售指标及完成情况（<span
                    id="month_title"><?php echo date('Y-n') ?></span>）</strong></div>
        <div class="panel-body">
            <div class="row">
                <div class="form-group col-md-4">
                    <label class="control-label col-sm-3">月份：</label>
                    <div class="col-sm-9">
                        <div class="calender-picker">
                        <input class="form-control" type="text" value="<?php echo date('Y-n') ?>"
                               name="year_and_month" id="datetimepicker">
                        <i class="glyphicon glyphicon-calendar fa fa-calendar"></i></div></div>
                </div>
                <div class="form-group col-md-4">
                    <label class="control-label col-sm-3">区域&门店：</label>
                    <div class="col-md-8"  id="orgSelect">
                        <el-cascader
                                placeholder="请选择"
                                size="small"
                                :options="options1"
                                v-model="selectedOptions3"
                                @change="handlechange_shopid"
                                filterable
                            ></el-cascader>
                        <input id="shopid" name="shop_id" type="hidden" value="">
                    </div>                    
                </div>
            </div>
        </div>
        <div class="form-group clearfix">
            <div class="col-md-12 mt-15">
                <div id="chart" style="min-width: 200px; height: 300px;"></div>
            </div>
            <div class="col-md-12 col-sm-12 mt-md">
                <div class="table-responsive" id="ajaxitem">

                </div>
            </div>
        </div>
    </div>
    <!-- /.row -->

</section>
<script type="text/javascript">
    var selectOrgJson = eval('<?php echo $selectOrgJson; ?>');
</script>