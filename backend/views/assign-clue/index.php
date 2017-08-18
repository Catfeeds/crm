<?php
use common\logic\LinkPager;
$this->registerCssFile('/dist/plugins/tokenfield/jquery-ui.css', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerCssFile('/dist/plugins/tokenfield/bootstrap-tokenfield.css', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerCssFile('/dist/plugins/tokenfield/tokenfield-typeahead.css', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/tokenfield/jquery-ui.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/tokenfield/typeahead.bundle.min.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/tokenfield/bootstrap-tokenfield.js', [
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
$this->registerJsFile('/dist/js/assignClue.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
?>
    <section class="content-header">
        <h1 class="page-title">线索分配</h1>
    </section>

    <!-- Main content -->
    <section class="content-body">
        <div class="row">
            <div class="col-sm-12 col-xs-12 mb-lg">
                <div class="alert alert-warning alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <div>
                        <i class="icon fa fa-warning pull-left "></i>
                        <p class="pull-left mb-0">
                            线索是总部下发给到门店，门店店长分配给各个顾问进行跟进。</p>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="box advanced-search-form mb-lg">
                    <form method="get" name="searchForm" >
                        <div class="row">
                            <div class="form-group col-lg-4 col-md-6">
                                <label for="" class="control-label col-sm-3 t-r">门店：</label>
                                <div class="col-md-8"  id="orgSelect">
                                    <el-cascader
                                            placeholder="请选择"
                                            size="small"
                                            :options="options1"
                                            v-model="selectedOptions3"
                                            @change="handlechange_shopid"
                                            change-on-select
                                            filterable
                                        ></el-cascader>
                                    <input id="shopid" name="shop_id" type="hidden" value="<?php echo (isset($get['shop_id']) ? $get['shop_id'] : '') ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 t-r">
                                <div class="pull-right mr-15">
                                    <input id="searchBtn" class="btn btn-primary btn-sm pull-left mr-15" value="查询" type="submit">
                                    <input id="clearBtn" class="btn btn-default btn-sm pull-left" value="清除" id="clear" type="button">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-12 col-sm-12 font0 mb-md">
                    <button onclick="assignClue.createLayer();" type="button" class="btn btn-primary btn-sm mr-md pull-left">分配</button>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="row">
                    <div class="box box-none-border col-sm-12 col-xs-12">
                        <div class="box-body no-padding">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-list-check">
                                    <thead>
                                        <tr>
                                            <th width="60" onclick="assignClue.checkall();"><input type="checkbox" id="checkall"></th>
                                            <th>下发时间</th>
                                            <th>剩余跟进时间</th>
                                            <th>客户姓名</th>
                                            <th>手机号码</th>
                                            <th>渠道来源</th>
                                            <th>信息来源</th>
                                            <th>意向车型</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach($list as $k => $val)
                                        {
                                        ?>
                                        <tr>
                                            <td><input type="checkbox"  class="input_id" value="<?php echo $val['id'];?>"></td>
<!--                                            <td><a href="/customer/customer-detail?id=--><?php //echo $val['id']?><!--">--><?php //echo empty($val['customer_name']) ? '--' : $val['customer_name'];?><!--</a></td>-->
                                            <td><?php echo date('Y-m-d H:i',$val['create_time']);?></td>
                                            <td><?php echo empty($val['overdue']) ? '--' : $val['overdue'];?></td>
                                            <td><?php echo empty($val['customer_name']) ? '--' : $val['customer_name'];?></td>
                                            <td><?php echo empty($val['customer_phone']) ? '--' : $val['customer_phone'];?></td>
                                            <td><?php echo empty($val['clue_input_type_name']) ? '--' : $val['clue_input_type_name'];?></td>
                                            <td><?php echo empty($val['clue_source_name']) ? '--' : $val['clue_source_name'];?></td>
                                            <td><?php echo empty($val['intention_des']) ? '--' : $val['intention_des'];?></td>
                                        </tr>
                                        <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <!--// 显示分页 start-->
                            <div class="box-footer pdt-md pull-right bd-t0">
                                <?php
                                echo LinkPager::widget([
                                    'pagination' => $pagination,
                                    'firstPageLabel' => "首页",
                                    'prevPageLabel' => '上一页',
                                    'nextPageLabel' => '下一页',
                                    'lastPageLabel' => '末页',
                                ]);
                                ?>
                            </div>
                            <!--// 显示分页 end-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->


<!--新建编辑弹出层 start-->
<div class="modal fade in" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"  onclick="assignClue.cancelLayer();">×</span></button>
                <h4 class="modal-title" id="myModalLabel">选择人员</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">

                    <div class="row">
                        <div class="col-sm-12">
                            <select class="form-control" id="salesman_id">
                                <?php foreach ($user_list as $user){?>
                                    <option value="<?php echo $user['id']?>"><?php echo $user['name']?></option>
                                <?php }?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button onclick="assignClue.cancelLayer();" type="button" class="btn btn-default btn-sm" data-dismiss="modal">取消</button>
                <button onclick="assignClue.submitassignForm();" type="button" class="btn btn-primary btn-sm">确认</button>
            </div>
        </div>
    </div>
</div>
<!--新建编辑弹出层 start-->
<script>
    var selectOrgJson = eval('<?php echo $selectOrgJson; ?>');
    var defaultSelectString = '<?php echo (isset($get['shop_id']) ? $get['shop_id'] : '') ?>';
    var defaultSelectArray = defaultSelectString.split(",");
</script>

