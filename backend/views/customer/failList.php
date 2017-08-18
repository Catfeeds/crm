<?php
    use common\logic\LinkPager;
    $this->title = '战败客户列表';
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
        <h1 class="page-title">战败客户</h1>
    </section>
    <!-- Main content -->
    <section class="content-body">
        <div class="tab-content dealthread">
            <div class="tab-pane active" id="waitdeal">
                <div class="box advanced-search-form mb-lg">
                    <form class="form-horizontal" action="/customer/get-fail-customer">
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
                                <div class="col-sm-9"><input id="so" class="form-control" name="so" value="<?php echo $so;?>" placeholder="姓名/手机/车型/说明/创建人/顾问" type="text"></div>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="" class="control-label col-sm-3">创建日期：</label>
                                <div class="col-sm-9">
                                    <div class="calender-picker">
                                        <input class="form-control" id="addtime" name ="searchTime" value="" type="text">
                                        <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="pull-right mr-15">
                                    <input class="btn btn-primary btn-sm pull-left mr-15" value="搜索" type="submit">
                                    <input class="btn btn-default btn-sm pull-left btn-sm" onclick="customer_list.clearSeachCondition();" value="清除" type="button">
                                </div>
                            </div>
                        </div>
                    </form>
                    <!--建卡日期-->
                    <input type="hidden" id="startDateSelect" value="<?php echo $startDate; ?>" />
                    <input type="hidden" id="endDateSelect" value="<?php echo $endDate; ?>" />
                </div>
                <div class="mb-md">
                    <input class="btn btn-primary btn-sm mr-10"  value="战败激活" type="button" onclick="customer_list.checkactiveForm()" >
                    <input class="btn btn-primary btn-sm mr-10" onclick="customer_list.downloadFailList();" value="导出列表" type="button">
                </div>
                <!--列表 start -->
                <div class="box box-none-border">
                    <div class="box-body no-padding">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered table-list-check">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox"></th>
                                        <th>序号</th>
                                        <th>姓名</th>
                                        <th>手机号码</th>
                                        <th>战败来源</th>
                                        <th>建卡日期</th>
                                        <th>战败日期</th>
                                        <th>战败原因</th>
                                        <th>说明</th>
                                        <th>战败顾问</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($list as $k => $val){$page = empty($_GET['page']) ? 1 : $_GET['page'];?>
                                    <tr>
                                        <td><input type="checkbox"  class="input_id" value="<?php echo $val['id'];?>"></td>
                                        <td><?php echo (($page - 1) * 20) + ($k + 1);?></td>
                                        <td>
                                            <a href="/customer/customer-detail?id=<?php echo $val['id']; ?>">
                                            <?php echo $val['customer_name']; ?>
                                            </a>
                                        </td>
                                        <td><?php echo $val['customer_phone']; ?></td>
                                        <td>
                                            <?php
                                            switch($val['status'])
                                            {
                                                case 0 : $failName = '线索战败'; break;
                                                case 1 : $failName = '意向战败'; break;
                                                case 2 : $failName = '订车战败'; break;
                                                default :$failName = '线索战败'; break;
                                            }
                                            echo $failName;
                                            ?>
                                        </td>
                                        <td><?php echo empty($val['create_card_time']) ? '--' : date('Y-m-d', $val['create_card_time']); ?></td>
                                        <td><?php echo empty($val['last_fail_time']) ? '--' : date('Y-m-d', $val['last_fail_time']); ?></td>
                                        <td><?php echo empty($val['fail_reason']) ? '--' : $val['fail_reason']; ?></td>
                                        <td><?php echo empty($val['des']) ? '--' :$val['des'];?></td>
                                        <td><?php echo empty($val['salesman_name']) ? '--' :$val['salesman_name'];?></td>
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

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">战败激活</h4>
      </div>
      <div class="modal-body">
         <div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <i class="icon fa fa-warning"></i> 该客户将作为意向客户被激活给原顾问
         </div>
         <form class="form-horizontal">
              <div class="row">
                   <div class="col-sm-11">
                      <div class="form-group">
                        <label for="inputname" class="col-sm-3 control-label"><span class="c-red mr-5">*</span>意向等级：</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="intention_level" id="intention_level">
                                <option value="">请选择</option>
                                <?php foreach ($intention_list as $item){?>
                                    <option value="<?php echo $item['id']?>"><?php echo $item['name'].'级 '.$item['content']?></option>
                                <?php }?>
                            </select>
                        </div>
                      </div>
                   </div>
              </div>
         </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
        <button type="button" class="btn btn-primary" onclick="customer_list.submitactiveForm();">激活</button>
      </div>
    </div>
  </div>
</div>
<script>
    var selectOrgJson = eval('<?php echo $selectOrgJson; ?>');
    var defaultSelectString = '<?php echo (isset($get['shop_id']) ? $get['shop_id'] : '') ?>';
    var defaultSelectArray = defaultSelectString.split(",");
</script>