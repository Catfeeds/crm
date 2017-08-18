<?php
use common\logic\LinkPager;
$this->registerJsFile('/dist/plugins/daterangepicker/moment.min.js', [
    'depends' => ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/daterangepicker/daterangepicker.js', [
    'depends' => ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/js/pendingClue.js', [
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
?>
<section class="content-header">
    <h1 class="page-title">未下发线索</h1>
</section>
<!-- Main content -->
<section class="content-body">
    <div class="row">
        <div class="col-sm-12 col-xs-12 mb-lg">
            <div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <div class="clear"></div>
            </div>
            <div class="box advanced-search-form mb-0 clear">
                <form class="form-horizontal" action="" method="get" id="form">
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label class="control-label col-sm-3">关键字：</label>
                            <div class="col-sm-9"><input type="text" class="form-control" name="search_key" value="<?php echo $search_data['search_key']?>" placeholder="姓名/手机/车型"></div>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="" class="control-label col-sm-3">日期：</label>
                            <div class="col-sm-9">
                                <input class="form-control" id="addtime" name="addtime" value="<?php echo $search_data['addtime']; ?>"
                                       type="text">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="pull-right mr-15">
                                <button type="submit" class="btn btn-primary btn-sm pull-left mr-15">搜索</button>
                                <button type="reset" class="btn btn-default btn-sm pull-left" onclick="pendingClue.clearSeachCondition();">清除</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-12 col-sm-12 font0 mb-md">
<!--            <button  type="button" class="btn btn-primary btn-sm" onclick="pendingClue.exportData()">导出列表</button>-->
            <button onclick="pendingClue.createLayer();" type="button" class="btn btn-primary btn-sm mr-md pull-left">下发到门店</button>
            <button onclick="pendingClue.gonghai();" type="button" class="btn btn-primary btn-sm mr-md pull-left">投入公海</button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <div class="box box-none-border col-sm-12 col-xs-12">
                    <div class="box-body no-padding">
                        <div class="table-responsive">
                            <form action="/pending-clue/gong-hai" id="form1" method="post">
                                <input type="hidden" name="reason_id" value="5" >
                                <table class="table table-hover table-bordered table-list-check">
                                    <thead>
                                    <tr>
                                        <th width="60" onclick="pendingClue.checkall();"><input type="checkbox" id="checkall"></th>
                                        <th>序号</th>
                                        <th>线索时间</th>
                                        <th>姓名</th>
                                        <th>手机号码</th>
                                        <th>所在地</th>
                                        <th>车系</th>
                                        <th>意向车型</th>
                                        <th class="t-c">操作</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <!--                                    <form action="">-->
                                    <?php
                                    foreach($list as $k => $val)
                                    {
                                        $page = empty($_GET['page']) ? 1 : $_GET['page'];
                                        ?>
                                        <tr>
                                            <td><input type="checkbox" name="arrs[]" id="id_<?php echo $val['id']?>" class="input_id" value="<?php echo $val['id'];?>"></td>
                                            <td><?php echo (($page - 1) * 20) + ($k + 1);?></td>
                                            <td><?php echo empty($val['create_time_fomat']) ? '--' : $val['create_time_fomat'];?></td>
                                            <td><?php echo empty($val['customer_name']) ? '--' : $val['customer_name'];?></td>
                                            <td><?php echo empty($val['customer_phone']) ? '--' : $val['customer_phone'];?></td>
                                            <td><?php echo empty($val['location']) ? '--' : $val['location'];?></td>
                                            <td><?php echo empty($val['intention_des']) ? '--' : $val['intention_des'];?></td>
                                            <td><?php echo empty($val['car_brand_son_type_name']) ? '--' : $val['car_brand_son_type_name'];?></td>
                                            <td><a onclick="pendingClue.assign('<?php echo $val['id']?>')" >下发到门店</a></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    <!--                                    </form>-->
                                    </tbody>
                                </table>
                            </form>
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
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"  onclick="pendingClue.cancelLayer();">×</span></button>
                <h4 class="modal-title" id="myModalLabel">选择门店</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">


                    <div class="col-md-8"  id="xfmd">
                        <el-cascader
                            placeholder="请选择"
                            size="small"
                            :options="options1"
                            @change="handlechange_xfmd"
                            filterable
                        ></el-cascader>
                        <input id="shop_id" name="shop_id" type="hidden" value="">
                        <!-- <div class="cascader" id="shop">
                            <div class="cascader-inputbox">
                                <input class="cascader-input required" type="text" autocomplete="off" name="shop_name" value=""  readonly placeholder="请选择"><i class="fa fa-set"></i>
                                <input class="sid" type="hidden" name="shop"  value="">
                            </div>
                            <div class="cascader-list none">
                                <ul class="cascader-menu">
                                </ul>
                            </div>
                        </div> -->
                    </div>
                    <div class="clear">
                    <br>
                    <br>
                </form>
            </div>
            <div class="modal-footer">
                <button onclick="pendingClue.cancelLayer();" type="button" class="btn btn-default btn-sm" data-dismiss="modal">取消</button>
                <button onclick="pendingClue.submitassignForm();" type="button" class="btn btn-primary btn-sm">确认</button>
            </div>
        </div>
    </div>
</div>
<!--新建编辑弹出层 start-->
