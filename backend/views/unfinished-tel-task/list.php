<?php
    use common\logic\LinkPager;
    $this->title = '未完成电话任务';
    $this->registerJsFile('/dist/plugins/daterangepicker/moment.min.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
        ]);
    $this->registerJsFile('/dist/plugins/daterangepicker/daterangepicker.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
        ]);
    $this->registerJsFile('/dist/js/unfinished-tel-task.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);
?>
    <section class="content-header">
        <h1 class="page-title">未完成电话任务</h1>
    </section>
    <!-- Main content -->
<form class="form-horizontal" action="/unfinished-tel-task/list" id="form" method="post">
    <section class="content-body">
        <div class="box advanced-search-form mb-lg">
        <!--查询条件-->

                <div class="row">
                    <div class="form-group col-md-4">
                        <label for="" class="control-label col-sm-3">关键字：</label>
                        <div class="col-sm-9"><input id="so" class="form-control" name="so" value="<?php echo $so;?>" placeholder="姓名/手机/顾问/车型/门店" type="text"></div>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="" class="control-label col-sm-3">操作时间：</label>
                        <div class="col-sm-9"><input class="form-control" id="search_time" name="search_time" value="" type="text"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="pull-right mr-15">
                            <input class="btn btn-primary btn-sm pull-left mr-15" value="搜索" type="submit">
                            <input class="btn btn-default btn-sm pull-left" onclick="activeClue.clearSeachCondition()" value="清除" type="button">
                        </div>
                    </div>
                </div>

        </div>
        <div class="mb-md">
           <!--交车日期-->
            <input id="search_time_start" value="<?php echo $strStartDate;?>" type="hidden">
            <input id="search_time_end" value="<?php echo $strEndDate;?>" type="hidden">
            <!--导出按钮-->
        </div>
        <div class="row">
          <div class="box box-none-border col-sm-12 col-xs-12">
                <div class="box-body no-padding">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-list-check">
                            <thead>
                                <tr>
                                    <th width="60">序号</th>
                                    <th>任务日期</th>
                                    <th>客户姓名</th>
                                    <th>手机号码</th>
                                    <th>意向等级
                                        <div class="lte-filterbox">
                                            <i title="Filter Menu" class="fa fa-filter lte-dropdown-trigger ml-0"></i>
                                            <div class="lte-table-filter-dropdown none">
                                                <ul class="lte-dropdown-menu lte-dropdown-menu-vertical  lte-dropdown-menu-root" role="menu" aria-activedescendant="" tabindex="0">
                                                    <?php if(!empty($intention_list)){foreach ($intention_list as $v){?>
                                                        <li class="lte-dropdown-menu-item" role="menuitem" aria-selected="false">
                                                            <label class="lte-checkbox-wrapper">
                                                                <input type="checkbox" class="lte-checkbox-input"  name="intention[]" value="<?php echo $v['name'];?>" <?php echo $v['is_check'];?>>
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
                                    <th>顾问</th>
                                    <th>归属门店
                                        <div class="lte-filterbox">
                                            <i title="Filter Menu" class="fa fa-filter lte-dropdown-trigger ml-0"></i>
                                            <div class="lte-table-filter-dropdown none">
                                                <ul class="lte-dropdown-menu lte-dropdown-menu-vertical  lte-dropdown-menu-root" role="menu" aria-activedescendant="" tabindex="0">
                                                    <?php if(!empty($shop_info_check)){foreach ($shop_info_check as $k=>$v){?>
                                                        <li class="lte-dropdown-menu-item" role="menuitem" aria-selected="false">
                                                            <label class="lte-checkbox-wrapper">
                                                                <input type="checkbox" class="lte-checkbox-input"  name="shop_id[]" value="<?php echo $v['id'];?>" <?php echo $v['is_check'];?>>
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
<!--                                    <th class="t-c">备注</th>-->
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $page = empty($_GET['page']) ? 1 : $_GET['page'];
                                foreach($list as $k => $val)
                                {
                                ?>
                                <tr>
                                    <td><?php echo (($page-1)*20) +($k + 1);?></td>
                                    <td><?php echo $val['task_date'] ?></td>
                                    <td><a href="/customer/customer-detail?id=<?php echo $val['clue_id']?>"><?php echo $val['customer_name'];?></a></td>
                                    <td><?php echo $val['customer_phone']; ?></td>
                                    <td><?php echo $val['intention_level_des']; ?></td>
                                    <td><?php echo $val['intention_des']; ?></td>
                                    <td><?php echo $val['salesman_name']; ?></td>
                                    <td><?php echo $val['shop_name_show']; ?></td>
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
        </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
</form>

