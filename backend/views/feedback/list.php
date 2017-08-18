<?php
    use common\logic\LinkPager;
    $this->title = '意见反馈';
    $this->registerJsFile('/dist/plugins/daterangepicker/moment.min.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
        ]);
    $this->registerJsFile('/dist/plugins/daterangepicker/daterangepicker.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
        ]);
    $this->registerJsFile('/dist/js/feedback.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);
?>
    <section class="content-header">
        <h1 class="page-title">意见反馈</h1>
    </section>
    <!-- Main content -->
    <section class="content-body">
        <div class="box advanced-search-form mb-lg">
        <!--查询条件-->
            <form class="form-horizontal" action="/feedback/list">
                <div class="row">
                    <div class="form-group col-md-4">
                        <label for="" class="control-label col-sm-3">关键字：</label>
                        <div class="col-sm-9"><input id="so" class="form-control" name="so" value="<?php echo $so;?>" placeholder="提出人/手机号码" type="text"></div>
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
                                    <th>反馈时间</th>
                                    <th>反馈内容</th>
                                    <th class="t-c">截图</th>
                                    <th>提出人</th>
                                    <th>手机号码</th>
                                    <th>归属</th>
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
                                    <td>
                                        <a onclick="showLogs.lookLayer('<?php echo htmlspecialchars($val['content']); ?>');">
                                            <?php
                                            //如果长度大于10只显示10个字符
                                            if(mb_strlen($val['content']) > 10){
                                                echo mb_substr($val['content'],0,10).'...';
                                            }else{
                                                echo $val['content'];
                                            }
                                            ?></a>
                                    </td>
                                    <td class="t-c">
                                        <p class="btn img_show border1" data='<?=$val['imgs_json']?>'><i class="fa fa-photo mr-sm"></i><?=$val['imgs_count']?></p>
                                    </td>
                                    <td><?php echo $val['user_name']; ?></td>
                                    <td><?php echo $val['user_phone']; ?></td>
                                    <td><?php echo $val['org_name']; ?></td>
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

<!--查看弹出层 start-->
<div class="modal fade in" id="look_myModal" tabindex="-1" role="dialog" aria-labelledby="look_myModalLabel" style="display: none;">
    <div class="modal-dialog notice-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button onclick="showLogs.look_cancelLayer();" type="button" class="close" data-dismiss="modal" aria-label="Close" ><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">反馈内容</h4>
            </div>
            <div class="modal-body clearfix">
                <form class="form-horizontal">
                    <div class="row">
                        <div class="form-group border-none  col-md-12 col-sm-12" >
                            <div class="col-md-12 font-md c-gray" id="look_content" style="line-height: 1.5;">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button onclick="showLogs.look_cancelLayer();" type="button" class="btn btn-primary btn-sm" data-dismiss="modal">确定</button>
            </div>
        </div>
    </div>
</div>


