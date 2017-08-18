<?php
    use common\logic\LinkPager;
    $this->title = '意向客户列表';
    $this->registerJsFile('/dist/plugins/daterangepicker/moment.min.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
        ]);
    $this->registerJsFile('/dist/plugins/daterangepicker/daterangepicker.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
        ]);
    $this->registerJsFile('/dist/js/customer_list.js', [
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
    $this->registerJsFile('/dist/js/customer/intentionList.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);
?>
<!--意向客户列表-->
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="page-title">意向客户</h1>
    </section>
<form class="form-horizontal" action="/customer/get-intention-customer" id="form1" method="get">
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
                                <div class="col-sm-9">
                                    <input id="so" class="form-control" name="so" value="<?php echo $so;?>" placeholder="姓名/手机/车型/顾问" type="text">
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="" class="control-label col-sm-3">建卡日期：</label>
                                <div class="col-sm-9">
                                    <div class="calender-picker">
                                        <input class="form-control" id="createCardTime" name ="createCardTime" value="" type="text">
                                        <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="pull-right mr-15">
                                    <input class="btn btn-primary btn-sm  pull-left mr-15" value="搜索" type="submit">
                                    <input class="btn btn-default pull-left btn-sm" onclick="customer_list.clearSeachCondition();" value="清除" type="button">
                                </div>
                            </div>
                        </div>

                    <!--建卡日期-->
                    <input type="hidden" id="startCreateCardDate" value="<?php echo $startCreateCardDate; ?>" />
                    <input type="hidden" id="endCreateCardDate" value="<?php echo $endCreateCardDate; ?>" />
                </div>
                <div class="mb-md font0">
                    <input class="btn btn-primary btn-sm mr-md" onclick="customer_list.addTask()" value="推送任务" type="button">
                    <input class="btn btn-primary btn-sm" onclick="customer_list.downloadIntentionList();" value="导出列表" type="button">
                </div>
                <!--列表 start -->
                <div class="box box-none-border">
                        <div class="box-body no-padding">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-list-check">
                                    <thead>
                                        <tr>
                                            <th width="60"><input type="checkbox"></th>
                                            <th>序号</th>
                                            <th>姓名</th>
                                            <th>手机号码</th>
                                            <th>信息来源
                                                <div class="lte-filterbox">
                                                    <i title="Filter Menu" class="fa fa-filter lte-dropdown-trigger ml-0"></i>
                                                    <div class="lte-table-filter-dropdown none">
                                                        <ul class="lte-dropdown-menu lte-dropdown-menu-vertical  lte-dropdown-menu-root" role="menu" aria-activedescendant="" tabindex="0">
                                                            <?php if(!empty($source)){foreach ($source as $v){?>
                                                                <li class="lte-dropdown-menu-item" role="menuitem" aria-selected="false">
                                                                    <label class="lte-checkbox-wrapper">
                                                                        <input type="checkbox" class="lte-checkbox-input"  name="sourve[]" value="<?php echo $v['id'];?>">
                                                                    </label>
                                                                    <span><?php echo $v['name'];?></span>
                                                                </li>
                                                            <?php }}?>

                                                        </ul>
                                                        <div class="lte-table-filter-dropdown-btns">
                                                            <a class="lte-table-filter-dropdown-link confirm sub">确定</a>
                                                            <a class="lte-table-filter-dropdown-link clean">重置</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </th>
                                            <th>意向等级
                                                <div class="lte-filterbox">
                                                    <i title="Filter Menu" class="fa fa-filter lte-dropdown-trigger ml-0"></i>
                                                    <div class="lte-table-filter-dropdown none">
                                                        <ul class="lte-dropdown-menu lte-dropdown-menu-vertical  lte-dropdown-menu-root" role="menu" aria-activedescendant="" tabindex="0">
                                                            <?php if(!empty($intention)){foreach ($intention as $v){?>
                                                                <li class="lte-dropdown-menu-item" role="menuitem" aria-selected="false">
                                                                    <label class="lte-checkbox-wrapper">
                                                                        <input type="checkbox" class="lte-checkbox-input " val="<?php echo $v['id'];?>"  name="intention[]" value="<?php echo $v['id'];?>">
                                                                    </label>
                                                                    <span><?php echo $v['name'];?></span>
                                                                </li>
                                                            <?php }}?>

                                                        </ul>
                                                        <div class="lte-table-filter-dropdown-btns">
                                                            <a class="lte-table-filter-dropdown-link confirm sub">确定</a>
                                                            <a class="lte-table-filter-dropdown-link clean">重置</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </th>
                                            <th>意向车型</th>
                                            <th>建卡日期
                                                <div class="lte-table-column-sorter" onclick="create_card_time()">
                                              <span class="lte-table-column-sorter-up off" id="cdesc"   title="↑" > <i class="fa fa-caret-up"></i>
                                              </span>
                                                    <span class="lte-table-column-sorter-down off" id="casc"  title="↓"> <i class="fa fa-caret-down"></i>
                                                    <input type="hidden" name="create_card_time" id="create_card_time" />
                                             </span>
                                                </div>
                                            </th>
                                            <th>最近联系
                                                <div class="lte-table-column-sorter" onclick="last_view_time()">
                                              <span class="lte-table-column-sorter-up off" id="ldesc"   title="↑" > <i class="fa fa-caret-up"></i>
                                              </span>
                                                    <span class="lte-table-column-sorter-down off" id="lasc"   title="↓"> <i class="fa fa-caret-down"></i>
                                                    <input type="hidden" name="last_view_time" id="last_view_time" />
                                             </span>
                                                </div>
                                            </th>
                                            <th>归属顾问</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($list as $k => $val){$page = empty($_GET['page']) ? 1 : $_GET['page'];?>
                                        <tr>
                                            <td><input name="checkbox" type="checkbox" data-id="<?php echo $val['id']; ?>"></td>
                                            <td><?php echo (($page - 1) * 20) + ($k + 1);?></td>
                                            <td>
                                                <a href="/customer/customer-detail?id=<?php echo $val['id']; ?>">
                                                <?php echo empty($val['customer_name']) ? '--' :$val['customer_name']; ?>
                                                </a>
                                            </td>
                                            <td><?php echo empty($val['customer_phone']) ? '--' :$val['customer_phone']; ?></td>
                                            <td><?php echo $objDataDic->getSourceName($val['clue_source']); ?></td>
                                            <td><?php echo empty($val['intention_level_des']) ? '--' :$val['intention_level_des']; ?></td>
                                            <td><?php echo empty($val['intention_des']) ? '--' :$val['intention_des']; ?></td>
                                            <td><?php echo empty($val['create_card_time']) ? '--' : date('Y-m-d', $val['create_card_time']); ?></td>
                                            <td><?php echo empty($val['last_view_time']) ? '--' : date('Y-m-d H:i:s', $val['last_view_time']); ?></td>
                                            <td><?php echo empty($val['salesman_name']) ? '--' :$val['salesman_name']; ?></td>
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
    var create_card_times = '<?php echo $create_card_time;?>';//创建时间排序
    var last_view_times = '<?php echo $last_view_time;?>';//最近联系时间排序
    var sourve = '<?php echo $sourve;?>';//信息来源
    var intention = '<?php echo $intentions;?>';//意向等级
    
    var selectOrgJson = eval('<?php echo $selectOrgJson; ?>');
    var defaultSelectString = '<?php echo (isset($get['shop_id']) ? $get['shop_id'] : '') ?>';
    var defaultSelectArray = defaultSelectString.split(",");

</script>