<?php
    use common\logic\LinkPager;
    $this->title = '订车客户列表';
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
    $this->registerJsFile('/dist/js/customer/orderList.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);
?>
<form class="form-horizontal" action="/customer/get-order-customer" id="form1">
<!--订车客户列表-->
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="page-title">订车客户</h1>
    </section>
    <!-- Main content -->
    <section class="content-body">
        <div class="tab-content dealthread">
            <div class="tab-pane active" id="waitdeal">
                <div class="box advanced-search-form mb-lg">

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
                                <div class="col-sm-9"><input id="so" class="form-control" name="so" value="<?php echo $so;?>" placeholder="姓名/手机/车型/顾问" type="text"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label for="" class="control-label col-sm-3">建卡日期：</label>
                                <div class="col-sm-9">
                                    <div class="calender-picker">
                                        <input class="form-control" id="createCardTime" name ="createCardTime" value="" type="text">
                                        <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="" class="control-label col-sm-3">订车日期：</label>
                                <div class="col-sm-9">
                                    <div class="calender-picker">
                                        <input class="form-control" id="orderTime" name ="orderTime" value="" type="text">
                                        <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="pull-right mr-15">
                                    <input class="btn btn-primary btn-sm pull-left mr-15" value="搜索" type="submit">
                                    <input class="btn btn-default btn-sm pull-left" onclick="customer_list.clearSeachCondition();" value="清除" type="button">
                                </div>
                            </div>
                        </div>

                    <!--建卡日期-->
                    <input type="hidden" id="startCreateCardDate" value="<?php echo $startCreateCardDate; ?>" />
                    <input type="hidden" id="endCreateCardDate" value="<?php echo $endCreateCardDate; ?>" />
                    <!--订车日期-->
                    <input type="hidden" id="startOrderDate" value="<?php echo $startOrderDate; ?>" />
                    <input type="hidden" id="endOrderDate" value="<?php echo $endOrderDate; ?>" />
                </div>
                <div class="mb-md">
                    <input class="btn btn-primary btn-sm mr-10" onclick="customer_list.downloadOrderList();" value="导出列表" type="button">
                </div>
                <!--列表 start -->
                <div class="box box-none-border">
                    <div class="box-body no-padding">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered table-list-check">
                                <thead>
                                    <tr>
                                        <th width="50">序号</th>
                                        <th>姓名</th>
                                        <th>手机号码</th>
                                        <th width="100">建卡日期
                                            <div class="lte-table-column-sorter" onclick="create_card_times()">
                                              <span class="lte-table-column-sorter-up off" id="ccdesc"   title="↑" > <i class="fa fa-caret-up"></i>
                                              </span>
                                                <span class="lte-table-column-sorter-down off" id="ccasc"   title="↓"> <i class="fa fa-caret-down"></i>
                                                    <input type="hidden" name="create_card_time" id="create_card_time" />
                                             </span>
                                            </div>
                                        </th>
                                        <th width="100">订车日期
                                            <div class="lte-table-column-sorter" onclick="create_times()">
                                              <span class="lte-table-column-sorter-up off" id="cdesc"   title="↑" > <i class="fa fa-caret-up"></i>
                                              </span>
                                                <span class="lte-table-column-sorter-down off" id="casc"   title="↓"> <i class="fa fa-caret-down"></i>
                                                    <input type="hidden" name="create_time" id="create_time" />
                                             </span>
                                            </div>
                                        </th>
                                        <th width="150">预计交车日期
                                            <div class="lte-table-column-sorter" onclick="predict_car_delivery_times()">
                                              <span class="lte-table-column-sorter-up off" id="pdesc"   title="↑" > <i class="fa fa-caret-up"></i>
                                              </span>
                                                <span class="lte-table-column-sorter-down off" id="pasc"   title="↓"> <i class="fa fa-caret-down"></i>
                                                    <input type="hidden" name="predict_car_delivery_time" id="predict_car_delivery_time" />
                                             </span>
                                            </div>
                                        </th>
                                        <th width="170">车型（车系）</th>
                                        <th>颜色</th>
                                        <th>订金</th>
                                        <th width="100">购买方式
                                            <div class="lte-filterbox">
                                                <i title="Filter Menu" class="fa fa-filter lte-dropdown-trigger ml-0"></i>
                                                <div class="lte-table-filter-dropdown none">
                                                    <ul class="lte-dropdown-menu lte-dropdown-menu-vertical  lte-dropdown-menu-root" role="menu" aria-activedescendant="" tabindex="0">
                                                        <?php foreach ($buy_type as $v){?>
                                                        <li class="lte-dropdown-menu-item" role="menuitem" aria-selected="false">
                                                            <label class="lte-checkbox-wrapper">
                                                                <input type="checkbox" class="lte-checkbox-input" val="b<?php echo $v['id'];?>"  name="buy_type[]" value="<?php echo $v['id'];?>">
                                                            </label>
                                                            <span><?php echo $v['name'];?></span>
                                                        </li>
                                                       <?php }?>
                                                    </ul>
                                                    <div class="lte-table-filter-dropdown-btns">
                                                        <a class="lte-table-filter-dropdown-link confirm sub">确定</a>
                                                        <a class="lte-table-filter-dropdown-link clean">重置</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </th>
                                        <th width="100">本店投保
                                            <div class="lte-filterbox">
                                                <i title="Filter Menu" class="fa fa-filter lte-dropdown-trigger ml-0"></i>
                                                <div class="lte-table-filter-dropdown none">
                                                    <ul class="lte-dropdown-menu lte-dropdown-menu-vertical  lte-dropdown-menu-root" role="menu" aria-activedescendant="" tabindex="0">

                                                            <li class="lte-dropdown-menu-item" role="menuitem" aria-selected="false">
                                                                <label class="lte-checkbox-wrapper">
                                                                    <input type="checkbox" class="lte-checkbox-input" val="i1"  name="is_insurance[]" value="1">
                                                                </label>
                                                                <span>是</span>
                                                            </li>
                                                            <li class="lte-dropdown-menu-item" role="menuitem" aria-selected="false">
                                                                <label class="lte-checkbox-wrapper">
                                                                    <input type="checkbox" class="lte-checkbox-input" val="i0" name="is_insurance[]" value="0">
                                                                </label>
                                                                <span>否</span>
                                                            </li>
                                                    </ul>
                                                    <div class="lte-table-filter-dropdown-btns">
                                                        <a class="lte-table-filter-dropdown-link confirm sub">确定</a>
                                                        <a class="lte-table-filter-dropdown-link clean">重置</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </th>
                                        <th width="90">状态
                                            <div class="lte-filterbox">
                                                <i title="Filter Menu" class="fa fa-filter lte-dropdown-trigger ml-0"></i>
                                                <div class="lte-table-filter-dropdown none">
                                                    <ul class="lte-dropdown-menu lte-dropdown-menu-vertical  lte-dropdown-menu-root" role="menu" aria-activedescendant="" tabindex="0">

                                                        <li class="lte-dropdown-menu-item" role="menuitem" aria-selected="false">
                                                            <label class="lte-checkbox-wrapper">
                                                                <input type="checkbox" class="lte-checkbox-input" val="o1"  name="status[]" value="1">
                                                            </label>
                                                            <span>处理中</span>
                                                        </li>
                                                        <li class="lte-dropdown-menu-item" role="menuitem" aria-selected="false">
                                                            <label class="lte-checkbox-wrapper">
                                                                <input type="checkbox" class="lte-checkbox-input" val="o2"  name="status[]" value="2">
                                                            </label>
                                                            <span>客户未支付</span>
                                                        </li>
                                                        <li class="lte-dropdown-menu-item" role="menuitem" aria-selected="false">
                                                            <label class="lte-checkbox-wrapper">
                                                                <input type="checkbox" class="lte-checkbox-input" val="o3" name="status[]" value="3">
                                                            </label>
                                                            <span>财务到账</span>
                                                        </li>
                                                        <li class="lte-dropdown-menu-item" role="menuitem" aria-selected="false">
                                                            <label class="lte-checkbox-wrapper">
                                                                <input type="checkbox" class="lte-checkbox-input" val="o4" name="status[]" value="4">
                                                            </label>
                                                            <span>战败</span>
                                                        </li>
                                                        <li class="lte-dropdown-menu-item" role="menuitem" aria-selected="false">
                                                            <label class="lte-checkbox-wrapper">
                                                                <input type="checkbox" class="lte-checkbox-input" val="o5" name="status[]" value="5">
                                                            </label>
                                                            <span>客户已支付</span>
                                                        </li>
                                                    </ul>
                                                    <div class="lte-table-filter-dropdown-btns">
                                                        <a class="lte-table-filter-dropdown-link confirm sub">确定</a>
                                                        <a class="lte-table-filter-dropdown-link clean">重置</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php  foreach($list as $k => $val){
                                        $page = empty($_GET['page']) ? 1 : $_GET['page'];?>
                                    <tr>
                                        <td><?php echo (($page - 1) * 20) + ($k + 1);?></td>
                                        <td>
                                            <a href="/customer/customer-detail?id=<?php echo  $val['id'];?>">
                                            <?php echo $val['customer_name']; ?>
                                            </a>
                                        </td>
                                        <td><?php echo empty($val['customer_phone']) ? '--' : $val['customer_phone']; ?></td>
                                        <td><?php echo empty($val['create_card_time']) ? '--' :date('Y-m-d', $val['create_card_time']); ?></td>
                                        <td><?php echo empty($val['create_time']) ? '--' : date('Y-m-d', $val['create_time']); ?></td>
                                        <td><?php echo empty($val['predict_car_delivery_time']) ? '--' : date('Y-m-d', $val['predict_car_delivery_time']); ?></td>
                                        <td><?php echo empty($val['car_type_name']) ? '--' :$val['car_type_name'];?></td>
                                        <td><?php echo empty($val['color_configure']) ? '--' : $val['color_configure']; ?></td>
                                        <td><?php echo empty($val['deposit']) ? '--' : $val['deposit']; ?></td>
                                        <td><?php echo empty($val['buy_type']) ? '--' : $objDataDic->getBuyTypeName($val['buy_type']); ?></td>
                                        <td><?php echo ($val['is_insurance'] == 1 ? '是' : '否'); ?></td>
                                        <td><?php
                                                if ($val['status'] == 1) echo '处理中';
                                                else if ($val['status'] == 2) echo '客户未支付';
                                                else if ($val['status'] == 3) echo '财务到账';
                                                else if ($val['status'] == 4) echo '战败';
                                                else if ($val['status'] == 5) echo '客户已支付';

                                            ?></td>
                                    </tr>
                                    <?php }?>
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
</form>
<script type="text/javascript">
    var create_time = '<?php echo $create_time;?>';//订车日期
    var create_card_time = '<?php echo $create_card_time;?>';//建卡日期
    var predict_car_delivery_time = '<?php echo $predict_car_delivery_time;?>';//预计交车日期
    var is_insurance = '<?php echo $is_insurance;?>';//本店投保
    var buy_types = '<?php echo $buy_types;?>';//购买方式
    var status = '<?php echo $status;?>';//状态
    
    var selectOrgJson = eval('<?php echo $selectOrgJson; ?>');
    var defaultSelectString = '<?php echo (isset($get['shop_id']) ? $get['shop_id'] : '') ?>';
    var defaultSelectArray = defaultSelectString.split(",");

</script>