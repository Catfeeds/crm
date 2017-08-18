<?php
use common\logic\LinkPager;
$this->registerJsFile('/dist/js/activeClue.js', [
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
        <h1 class="page-title">重新分配客户</h1>
    </section>

    <!-- Main content -->
    <section class="content-body">
        <div class="row">
            <div class="col-sm-12 col-xs-12">
                <div class="alert alert-warning alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <div>
                        <i class="icon fa fa-warning pull-left mb-md" style="color:#FAC450;"></i>
                        <p class="pull-left mb-0">*造成客户无人跟进目前有两种情况: <br>
                    1、原销售顾问离职，用户被注销<br>
                    2、原销售顾问的身份在编辑用户时被勾掉<br></p>
                    </div>
                    <div class="clear"></div>
                </div>
                 <div class="box advanced-search-form mb-lg clear">
                    <form class="form-horizontal" action="" method="get" id="form">
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label class="control-label col-sm-3">门店：</label>
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
                                <label class="control-label col-sm-3">关键字：</label>
                                <div class="col-sm-9"><input type="text" class="form-control" name="search_key" value="<?php echo $search_data['search_key']?>" placeholder="客户姓名/手机号码"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="pull-right mr-15">

                                    <button type="submit" class="btn btn-primary btn-sm pull-left mr-15">搜索</button>
                                    <button type="reset" class="btn btn-default btn-sm pull-left" onclick="activeClue.clearSeachCondition();">清除</button>
                            </div>
                        </div>
                            <input type="hidden" name="export_data" id="exportData" value="0">
                    </form>
                </div>
            </div>
            <div class="mb-md clearfix">

                <button type="button" class="btn btn-primary btn-sm mr-md pull-left" onclick="activeClue.exportData()">导出列表</button>
                <button onclick="activeClue.createLayer();" type="button" class="btn btn-primary btn-sm pull-left">重新分配</button>

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
                                            <th width="60" onclick="activeClue.checkall();"><input type="checkbox" id="checkall"></th>
                                            <th>序号</th>
                                            <th>客户姓名</th>
                                            <th>电话</th>
                                            <th>意向等级</th>
                                            <th>意向车型</th>
                                            <th>信息来源</th>
                                            <th>最近联系时间</th>
                                            <th>联系次数</th>
<!--                                            <th class="t-c">操作</th>-->
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <form action="">
                                        <?php
                                        foreach($list as $k => $val)
                                        {$page = empty($_GET['page']) ? 1 : $_GET['page'];
                                        ?>
                                        <tr>

                                            <td><input type="checkbox"  class="input_id" value="<?php echo $val['id'];?>"></td>
                                            <td><?php echo (($page - 1) * 20) + ($k + 1);?></td>
                                            <td><a href="/customer/customer-detail?id=<?php echo $val['id']?>"><?php echo empty($val['customer_name']) ? '--' : $val['customer_name'];?></a></td>
                                            <td><?php echo empty($val['customer_phone']) ? '--' : $val['customer_phone'];?></td>
                                            <td><?php echo empty($val['intention_level_des']) ? '--' : $val['intention_level_des'];?></td>
                                            <td><?php echo empty($val['intention_des']) ? '--' : $val['intention_des'];?></td>
                                            <td><?php echo empty($val['clue_source_name']) ? '--' : $val['clue_source_name'];?></td>
                                            <td><?php echo empty($val['last_view_time']) ? '--' : $val['last_view_time'];?></td>
                                            <td><?php echo intval($val['count']);?></td>

                                        </tr>
                                        <?php
                                        }
                                        ?>
                                    </form>
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
      <!-- /.row -->
    </section>
    <!-- /.content -->


<!--新建编辑弹出层 start-->
<div class="modal fade in" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">选择人员</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">

                    <div class="row">
                        <div class="col-sm-11">
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
                <button onclick="activeClue.cancelLayer();" type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button onclick="activeClue.submitreassignFormnofollow();" type="button" class="btn btn-primary">确认</button>
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

