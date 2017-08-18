<?php
use yii\widgets\LinkPager;
$this->title = '线索客户列表';
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
$this->registerJsFile('/dist/js/conversion-funnel.js', [
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
    <h1 class="page-title">转化漏斗 <span class="c-red font14 ml-10">最近更新时间：<?php echo $data_common['data_update_time']?></span></h1>
</section>

<!-- Main content -->
<section class="content-body turnover transformtion">
    <div class="alert alert-warning alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <div><i class="icon fa fa-warning"></i>如实地反映了一定时间内购车客户的转化情况，根据转化漏斗的过滤程度可以判断哪个环节客户流失率较高。</div>
    </div>
    <div class="panel advanced-search-form pdt-sm pdb-sm">
        <div class="panel-heading pdb-0"><strong>转化漏斗</strong></div>
        <div class="panel-body pdt-md pdb-0">
            <form class="form-horizontal" action="" method="post" id="form">
                <div class="row">
                            <div class="form-group col-md-4">
                                <label class="control-label col-sm-3">区域门店：</label>
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
                            <div class="form-group mb-15 col-md-4">
                                <label class="control-label col-sm-3">渠道来源：</label>
                                <div class="cascader col-sm-9"  id="input_type">
                                    <div class="cascader-inputbox">
                                        <input class="cascader-input" type="text" autocomplete="off" readonly value="<?php echo $data_common['input_type_name']?>"><i class="fa fa-set"></i>
                                        <input class="sid" name="input_type_id" id="input_type_id" type="hidden" value="<?php echo $data_common['input_type_id']?>">
                                    </div>
                                    <div class="cascader-list none">
                                        <ul class="cascader-menu">
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="" class="control-label col-sm-3">时间：</label>
                                <div class="col-sm-9">
                                    <div class="calender-picker">
                                        <input class="form-control" id="search_time" name ="search_time" value="<?php echo $data_common['search_time']?>" type="text" onchange="submitData()" style="height:28px;">
                                        <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                    </div>
                                </div>
                                    <input name="_csrf-backend" type="hidden" id="_csrf" value="<?php echo Yii::$app->request->csrfToken ?>">
                            </div>
                </div>
            </form>
        </div>
        <div class="row mr-0 ml-0 mt-sm">
            <div class="col-md-12">
                    <!-- <div class="col-md-5 mt-15" style="background:rgb(243, 243, 243);padding-left:0;">
                        <div id="funnel" style="min-width: 200px; height: 400px; -webkit-tap-highlight-color: transparent; user-select: none; background: transparent;" _echarts_instance_="ec_1492168564473">
                            <div style="position: relative; overflow: hidden; width: 641px; height: 400px; padding: 0px; margin: 0px; border-width: 0px; cursor: pointer;">
                                <canvas width="641" height="400" data-zr-dom-id="zr_0" style="position: absolute; left: 0px; top: 0px; width: 641px; height: 400px; user-select: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); padding: 0px; margin: 0px; border-width: 0px;"></canvas>
                            </div>
                        </div>
                    </div> -->
                    <div class="col-md-5 mb-md" style="background:rgb(243, 243, 243);padding-left:0;">
                        <div id="funnel" style="min-width: 200px; height: 400px;"></div>
                    </div>
                    <div class="col-md-7 mb-md" style="padding-right:0;">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered table-list-check">
                                <thead class="lte-table-thead">
                                <tr>
                                    <th width="50">序号</th>
                                    <th>名称</th>
                                    <th>线索数</th>
                                    <th>意向客户数</th>
                                    <th>到店数</th>
                                    <th>订车数</th>
                                    <th>成交率</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($table_data as $key=>$value){?>

                                    <tr>
                                        <td><?php echo $key+1;?></td>
                                        <td><?php echo $value['info_owner_name']?></td>
                                        <td><?php echo $value['new_clue_num']?></td>
                                        <td><?php echo $value['new_intention_num']?></td>
                                        <td><?php echo $value['to_shop_num']?></td>
                                        <td><?php echo $value['dingche_num']?></td>
                                        <td><?php
                                            $rate = @round($value['dingche_num']*100/$value['new_clue_num'],2);
                                            if($rate == 0){
                                                echo '0.00';
                                            }else{
                                                echo $rate;
                                            }
                                            ?>%</td>
                                    </tr>
                                <?php }?>
                                <?php $real_data = json_decode($data_funnel,true)['real_data'];?>
                                <tr>
                                    <td></td>
                                    <td>总计</td>
                                    <td><?php echo $real_data['new_clue_num']?></td>
                                    <td><?php echo $real_data['new_intention_num']?></td>
                                    <td><?php echo $real_data['to_shop_num']?></td>
                                    <td><?php echo $real_data['dingche_num']?></td>
                                    <td><?php
                                        $rate = @round($real_data['dingche_num']*100/$real_data['new_clue_num'],2);
                                        if($rate == 0){
                                            echo '0.00';
                                        }else{
                                            echo $rate;
                                        }
                                        ?>%</td>
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
    var funnel_data = <?php echo $data_funnel;?>;
    var selectOrgJson = eval('<?php echo $selectOrgJson; ?>');
    var defaultSelectString = '<?php echo (isset($post['shop_id']) ? $post['shop_id'] : '') ?>';
    var defaultSelectArray = defaultSelectString.split(",");
</script>
