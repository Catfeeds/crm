<?php
    use common\logic\LinkPager;
    $this->title = '操作记录';
    $this->registerJsFile('/dist/plugins/daterangepicker/moment.min.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
        ]);
    $this->registerJsFile('/dist/plugins/daterangepicker/daterangepicker.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
        ]);
    $this->registerJsFile('/dist/js/log.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);
?>
    <section class="content-header">
        <h1 class="page-title">操作记录</h1>
    </section>
    <!-- Main content -->
    <section class="content-body">
        <div class="box advanced-search-form mb-lg">
        <!--查询条件-->
            <form class="form-horizontal" action="/logs/show-logs">
                <div class="row">
                    <div class="form-group col-md-4">
                        <label for="" class="control-label col-sm-3">关键字：</label>
                        <div class="col-sm-9"><input id="so" class="form-control" name="so" value="<?php echo $so;?>" placeholder="操作人/手机号" type="text"></div>
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
                            <input class="btn btn-default btn-sm pull-left" onclick="showLogs.clearSearch()" value="清除" type="button">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="mb-md">
           <!--交车日期-->
            <input id="search_time_start" value="<?php echo $strStartDate;?>" type="hidden">
            <input id="search_time_end" value="<?php echo $strEndDate;?>" type="hidden">
            <!--导出按钮-->
            <input class="btn btn-primary btn-sm mr-10" onclick="showLogs.downloadLogs();" value="导出列表" type="button">
        </div>
        <div class="row">
          <div class="box box-none-border col-sm-12 col-xs-12">
                <div class="box-body no-padding">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-list-check">
                            <thead>
                                <tr>
                                    <th width="60">序号</th>
                                    <th>操作时间</th>
                                    <th>操作类型</th>
                                    <th>操作人</th>
                                    <th>手机号</th>
                                    <th>归属</th>
                                    <th>IP地址/IMEI</th>
                                    <th class="t-c">备注</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $page = empty($_GET['page']) ? 1 : $_GET['page'];
                                foreach($list as $k => $val)
                                {
                                ?>
                                <tr>
                                    <td><?php echo (($page-1)*20) + ($k + 1); ?></td>
                                    <td><?php echo date('Y-m-d H:i:s', $val['create_time']); ?></td>
                                    <td><?php echo $val['type_name']; ?></td>
                                    <td><?php echo $val['user']; ?></td>
                                    <td><?php echo $val['phone']; ?></td>
                                    <td><?php echo $val['org_name']; ?></td>
                                    <td><?php echo $val['ip']; ?></td>
                                    <td><?php echo $val['content']; ?></td>
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


