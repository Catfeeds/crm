<?php
$this->title = '意向等级分析';
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
$this->registerJsFile('/dist/js/intentionlevel.js', [
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
    <h1 class="page-title">客户分析-意向客户 <span class="c-red font14 ml-10">最近更新时间：<?php echo $data_common['data_update_time']?></span></h1>
</section>

<!-- Main content -->
<section class="content-body turnover transformtion">
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <div><i class="icon fa fa-warning"></i>意向等级分析：当前正在跟进中的意向客户意向等级的分布。</div>
        </div>
        <div class="panel advanced-search-form pdt-sm pdb-sm">
            <div class="panel-heading pdb-0"><strong>意向等级分析</strong></div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-12">
                        <form class="form-horizontal" action="" method="post" id="form">
                            <div class="form-group col-md-4">
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
                            <div class="form-group col-md-4" style="display:<?php echo $data_common['info_owner_display']?>">
                                <label class="control-label col-sm-3">区域&amp;门店：</label>
                                <div class="col-md-8"  id="orgSelect">
                                    <el-cascader
                                            placeholder="请选择"
                                            size="small"
                                            :options="options1"
                                            v-model="selectedOptions3"
                                            @change="handlechange_shopid"
                                            filterable
                                        ></el-cascader>
                                    <input id="shopid" name="shop_id" type="hidden" value="<?php echo (isset($post['shop_id']) ? $post['shop_id'] : '') ?>">
                                </div>
                            </div>
                            <input name="_csrf-backend" type="hidden" id="_csrf" value="<?php echo Yii::$app->request->csrfToken ?>">
                        </form>
                    </div>
                </div>

                <div class="row">
                    <div class="mt-sm mb-lg">
                        <div id="chart" style="min-width: 200px; height: 300px; -webkit-tap-highlight-color: transparent; user-select: none; position: relative; background: transparent;" _echarts_instance_="ec_1491894792252"><div style="position: relative; overflow: hidden; width: 641px; height: 300px; padding: 0px; margin: 0px; border-width: 0px; cursor: pointer;"><canvas width="641" height="300" data-zr-dom-id="zr_0" style="position: absolute; left: 0px; top: 0px; width: 641px; height: 300px; user-select: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); padding: 0px; margin: 0px; border-width: 0px;"></canvas></div><div style="position: absolute; display: none; border-style: solid; white-space: nowrap; z-index: 9999999; transition: left 0.4s cubic-bezier(0.23, 1, 0.32, 1), top 0.4s cubic-bezier(0.23, 1, 0.32, 1); background-color: rgba(0, 0, 0, 0.6); border-width: 0px; border-color: rgb(51, 51, 51); border-radius: 4px; color: rgb(255, 255, 255); font-style: normal; font-variant: normal; font-weight: normal; font-stretch: normal; font-size: 14px; font-family: &quot;Microsoft YaHei&quot;; line-height: 21px; padding: 5px; left: 332px; top: 243px;">访问来源 <br>搜索引擎 : 1548 (60.42%)</div></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 mt-sm">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered table-list-check">
                                <thead class="lte-table-thead">
                                    <tr>
                                        <th width="50">序号</th>
                                        <th>名称</th>
                                        <th>客户总数</th>
                                        <?php foreach ($intention_list_table as $value){?>
                                            <th><?php echo $value['name']?>级</th>
                                        <?php }?>
                                    </tr>
                                </thead>
                                <tbody id="tbody">
                                    <?php foreach ($one_info_owner_new_list as $key=>$one){?>
                                    <tr>

                                        <td><?php echo  $key+1;?></td>
                                        <td><?php echo $one['info_owner_name']?></td>
                                        <td><?php echo $one['sum_all']?></td>
                                        <?php foreach ($intention_list_table as $value){?>
                                            <td><?php echo empty($one[$value['name']])? 0 :$one[$value['name']];?></td>
                                        <?php }?>
                                    </tr>
                                    <?php }?>
                                    <tr>
                                        <td></td>
                                        <td>总计</td>
                                        <td><?php echo $info_sum['sum_all']?></td>
                                        <?php foreach ($intention_list_table as $value){?>
                                            <td><?php echo empty($info_sum[$value['name']]['sum_num'])? 0 :$info_sum[$value['name']]['sum_num'];?></td>
                                        <?php }?>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</section>
<script>
    var intention_info_js = <?php echo  json_encode($intention_info)?>;
    var selectOrgJson = eval('<?php echo $selectOrgJson; ?>');
    var defaultSelectString = '<?php echo (isset($post['shop_id']) ? $post['shop_id'] : '') ?>';
    var defaultSelectArray = defaultSelectString.split(",");

</script>
