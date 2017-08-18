<?php
    use common\logic\LinkPager;
    $this->title = '保有客户列表';
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
    $this->registerJsFile('/dist/js/customer_list.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
        ]);
?>
<!--订车客户列表-->
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="page-title">保有客户</h1>
    </section>
    <!-- Main content -->
    <section class="content-body">
        <div class="tab-content dealthread">
            <div class="tab-pane active" id="waitdeal">
                <div class="box advanced-search-form mb-lg">
                    <form class="form-horizontal" action="/customer/get-keep-customer">
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label for="" class="control-label col-sm-3">门店：</label>
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
                            <div class="form-group col-md-4">
                                <label for="" class="control-label col-sm-3">关键字：</label>
                                <div class="col-sm-9">
                                    <input id="so" class="form-control" name="so" value="<?php echo $so;?>" placeholder="姓名/手机/车型/说明/创建人/顾问" type="text">
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="" class="control-label col-sm-3">购车日期：</label>
                                <div class="col-sm-9">
                                    <div class="calender-picker">
                                        <input class="form-control" id="deliveryTime" name ="deliveryTime" value="" type="text">
                                        <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="pull-right mr-15">
                                    <input class="btn btn-primary btn-sm pull-left mr-15" value="搜索" type="submit">
                                    <input class="btn btn-default pull-left btn-sm" onclick="customer_list.clearSeachCondition();" value="清除" type="button">
                                </div>
                            </div>
                        </div>
                    </form>
                   <!--交车日期-->
                    <input type="hidden" id="startDeliveryDate" value="<?php echo $startDeliveryDate; ?>" />
                    <input type="hidden" id="endDeliveryDate" value="<?php echo $endDeliveryDate; ?>" />
                </div>
                <div class="mb-md font0">
                    <input class="btn btn-primary btn-sm mr-md" id="chongxinfenpei" value="重新分配" type="button">
                    <input class="btn btn-primary btn-sm" onclick="customer_list.downloadKeepList();" value="导出列表" type="button">
                </div>
                <!--列表 start -->
                <div class="box box-none-border">
                    <div class="box-body no-padding">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered table-list-check">
                                <thead>
                                    <tr>
                                        <th width="60"><input type="checkbox"></th>
                                        <th width="60">序号</th>
                                        <th>姓名</th>
                                        <th>手机号码</th>
                                        <th>车牌号</th>
                                        <th width="170">车架号</th>
                                        <th width="170">车型</th>
                                        <th width="100">购车日期</th>
                                        <th width="100">本店成交</th>
                                        <th width="100">归属顾问</th>
                                        <th width="100">购买方式</th>
                                        <th width="100">本店投保</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($list as $k => $val){$page = empty($_GET['page']) ? 1 : $_GET['page'];?>
                                    <tr>
                                        <td width="60"><input name="checkbox" data-id="<?php echo $val['id'];?>" type="checkbox"></td>
                                        <td><?php echo (($page - 1) * 20) + ($k + 1);?></td>
                                        <td>
                                            <a href="/customer/customer-detail-by-customer-id?id=<?php echo  $val['id'];?>">
                                            <?php echo $val['name'];?>
                                            </a>
                                        </td>
                                        <td><?php echo empty($val['phone']) ? '--' : $val['phone'];?></td>
                                        <td><?php echo empty($val['car_number']) ? '--' : $val['car_number'];?></td>
                                        <td><?php echo empty($val['frame_number']) ? '--' :$val['frame_number'];?></td>
                                        <td><?php echo empty($val['car_type_name']) ? '--' :$val['car_type_name'];?></td>
                                        <td><?php echo empty($val['car_delivery_time']) ? '--' :date('Y-m-d', $val['car_delivery_time']);?></td>
                                        <td>是</td>
                                        <td><?php echo empty($val['salesman_name']) ? '--' :$val['salesman_name'];?></td>
                                        <td><?php echo empty($val['buy_type_name']) ? '--' :$val['buy_type_name'];?></td>
                                        <td><?php echo ($val['is_insurance'] == '' ? '是' : '否'); ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                             </table>
                        </div>
                        <!--// 显示分页 start-->
                        <div class="box-footer pdt-md pull-right bd-t0">
                        <?php
                            echo LinkPager::widget([
                            'pagination' => $objPage,
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
                <!--列表 end -->
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
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="$('#myModal').hide();"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">选择销售人员</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="row">
                        <div class="col-sm-11">
                            <div class="form-group">
                                <label for="inputname" class="col-sm-4 control-label"><span class="c-red mr-5">*</span>选择销售：</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="saleman_id" >
                                        <?php
                                            foreach($shop_userlist as $val)
                                            {
                                                echo '<option value="' . $val['id'] . '">' . $val['name'] . '</option>';
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>
                       </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button onclick="$('#myModal').hide();" type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button onclick="customer_list.KeepCustomerReset();" type="button" class="btn btn-primary">确认</button>
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