<?php
use common\logic\LinkPager;
$this->registerJsFile('/dist/plugins/daterangepicker/moment.min.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/daterangepicker/daterangepicker.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
//$this->registerJsFile('/dist/plugins/echarts.min.js', [
//    'depends' => ['backend\assets\AdminLteAsset']
//]);
//$this->registerJsFile('/dist/js/appSelfUpdate.js', [
//    'depends'=> ['backend\assets\AdminLteAsset']
//]);
//$this->registerJsFile('/dist/plugins/daterangepicker/bootstrap-datepicker.js', [
//    'depends' => ['backend\assets\AdminLteAsset']
//]);
//$this->registerJsFile('/dist/plugins/daterangepicker/bootstrap-datepicker_002.js', [
//    'depends' => ['backend\assets\AdminLteAsset']
//]);
//$this->registerCssFile('/dist/css/datepicker3.css', [
//    'depends' => ['backend\assets\AdminLteAsset']
//]);


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
$this->registerJsFile('/dist/js/appSelfUpdate.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
?>
    <section class="content-header">
        <h1 class="page-title">APP版本管理</h1>
    </section>

    <!-- Main content -->
    <section class="content-body">
        <div class="box advanced-search-form mb-lg">
            <form class="form-horizontal" action="/self-update" id="search" method="post">
                <input name="_csrf-backend" type="hidden" id="_csrf" value="<?php echo Yii::$app->request->csrfToken ?>">
                <div class="row">
                    <div class="form-group col-md-4">
                        <label for="" class="control-label col-sm-3">关键字：</label>
                        <div class="col-sm-9">
                            <input id="so" class="form-control" name="search_key" value="<?php echo $search_data['search_key']?>" placeholder="请输入更新内容/备注" type="text">
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="" class="control-label col-sm-3">上传时间：</label>
                        <div class="col-sm-9">
                            <div class="calender-picker">
                                <input class="form-control" id="search_time" name ="search_time" value="<?php echo $search_data['search_time']?>" type="text" >
                                <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                            </div>
                        </div>
<!--                        <div class="col-sm-9">-->
<!--                            <input id="startcreateTime" name ="startcreateTime" value="" placeholder="2017-04-01" type="text" class="form-control" style="width:48%;float:left;">-->
<!--                            <span style="line-height:30px;width:4%;text-align:center;float:left;">~</span>-->
<!--                            <input id="endcreateTime" name ="endcreateTime" value="" placeholder="2017-04-02" type="text" class="form-control" style="width:48%;float:left;">-->
<!--                        </div>-->
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="pull-right mr-15">
                            <input class="btn btn-primary btn-sm pull-left mr-15" value="搜索" type="submit">
                            <input class="btn btn-default btn-sm pull-left" onclick="appSelfUpdate.clearSeachCondition();" value="清除" type="submit">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="mb-md clearfix">
            <input class="btn btn-primary btn-sm mr-15 pull-left" onclick="appSelfUpdate.createLayerandroid()" value="新增Android版本" type="button">
            <input class="btn btn-primary btn-sm mr-15 pull-left" onclick="appSelfUpdate.createLayerios()" value="新增iOS版本" type="button">
            <a href="/self-update/update-history?app_name=管理速报"><input class="btn btn-primary btn-sm mr-15 pull-left"  value="更新历史-管理速报" type="button"></a>
            <a href="/self-update/update-history?app_name=销售助手"><input class="btn btn-primary btn-sm mr-15 pull-left"  value="更新历史-销售助手" type="button"></a>
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
                                            <th width="60">序号</th>
                                            <th>上传时间</th>
                                            <th>客户端</th>
                                            <th>名称</th>
                                            <th>版本号</th>
                                            <th>更新编号</th>
                                            <th>强制更新</th>
                                            <th>更新内容</th>
                                            <th>备注</th>
                                            <th class="t-c">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 0;
                                        $page = empty($_GET['page']) ? 1 : $_GET['page'];
                                        foreach($list as $val)
                                        {
                                            $i++
                                        ?>
                                        <tr>
                                            <td><?php echo (($page - 1) * 10) + ($i);?></td>
                                            <td><?php echo date('Y-m-d H:i',$val['create_time']);?></td>
                                            <td><?php echo $val['ios_or_android'];?></td>
                                            <td><?php echo $val['app_name'];?></td>
                                            <td><?php echo $val['versionName'];?></td>
                                            <td><?php echo $val['versionCode'];?></td>
                                            <td><?php
                                                if($val['is_forced_update'] == 1){
                                                echo '是';
                                                }else{
                                                    echo '否';
                                                }
                                                ?></td>
                                            <td><?php echo str_replace("\n", "<br>", $val['content']);?></td>
                                            <td><?php echo str_replace("\n", "<br>", $val['tips']);?></td>
                                            <td class="t-c va-m">
                                                <div class="operation">
                                                    <span class="thisDataSpan" style="display:none">
                                                        <?php echo json_encode($val);
                                                        if($val['app_id'] == 1 || $val['app_id'] == 2){
                                                            $os = 'android';
                                                        }else{
                                                            $os = 'ios';
                                                        }
                                                        ?>
                                                    </span>
                                                    <?php if($val['can_modify'] == 1){?>
                                                        <a onclick="appSelfUpdate.updateLayer<?php echo $os?>($(this));" href="javascript:;" intentionId="<?php echo $val['id'];?>">编辑</a>
                                                    <?php }?>

                                                </div>
                                            </td>
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
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="appSelfUpdate.cancelLayer();"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">新增安卓版本</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" action="/self-update/update-or-create" id="form1" enctype="multipart/form-data" method="post">
                    <input name="_csrf-backend" type="hidden" id="_csrf" value="<?php echo Yii::$app->request->csrfToken ?>">
                    <input class="form-control" id="input_id" name="id" placeholder="版本的id，编辑数据用到" type="hidden">
                    <div class="row">
                        <div class="col-sm-12">

                            <div class="form-group">
                                <label for="inputname" class="control-label col-sm-2"><span class="c-red mr-5">*</span>名称：</label>
                                <div class="col-sm-10">
                                    <select class="form-control" name="app_id">
                                        <option value="1" id="app_type_1">安卓销售助手</option>
                                        <option value="2" id="app_type_2">安卓管理速报</option>
                                        <option value="3" id="app_type_3">ios销售助手</option>
                                        <option value="4" id="app_type_4">ios管理速报</option>
                                    </select>
<!--                                    <input class="form-control" id="input_versionCode" placeholder="请输入更新编号" type="text">-->
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="inputname" class="control-label col-sm-2"><span class="c-red mr-5">*</span>版本号：</label>
                                <div class="col-sm-10">
                                    <input class="form-control" id="versionName" name="versionName" placeholder="请输入版本号" type="text">
                                    <span class="c-red" id="versionName-error"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputname" class="control-label col-sm-2"><span class="c-red mr-5">*</span>更新编号：</label>
                                <div class="col-sm-10">
                                    <input class="form-control" id="versionCode" name="versionCode" placeholder="请输入更新编号" type="text">
                                    <span class="c-red" id="versionCode-error"></span>
                                </div>
                            </div>
                            <div class="form-group" id="upfile">
                                <label for="inputname" class="control-label col-sm-2"><span class="c-red mr-5">*</span>上传安装包：</label>
                                <div class="col-sm-10" id="filebox">
                                    <input class="" id="file" name="file" placeholder="请选择安装包" type="file">
                                    <input class="" id="file_url" name="file_url" placeholder="请选择安装包" type="text">
                                    <span class="c-red" id="file-error"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputname" class="control-label col-sm-2"><span class="c-red mr-5">*</span>更新内容：</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" id="content" name="content" placeholder="请输入更新内容"></textarea>
<!--                                    <input class="form-control" id="content" name="content" placeholder="请输入更新内容" type="text">-->
                                    <span class="c-red" id="content-error"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputname" class="control-label col-sm-2"><span class="c-red mr-5">*</span>备注：</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" id="tips" name="tips" placeholder="请输入备注"></textarea>
<!--                                    <input class="form-control" id="tips" name="tips" placeholder="请输入备注" type="text">-->
                                    <span class="c-red" id="tips-error"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputname" class="control-label col-sm-2"><span class="c-red mr-5">*</span>强制更新：</label>
                                <div class="col-sm-10">
                                    <select class="form-control" name="is_forced_update" id="is_forced_update">
                                        <option value="1" id="forced_update">强制更新</option>
                                        <option value="0" selected id="noforced_update">不强制更新</option>
                                    </select>
                                    <span class="c-red" id="is_forced_update_error"></span>
<!--                                    <input class="form-control" id="input_is_forced_update" name="input_is_forced_update" placeholder="是否强制更新" type="text">-->
                                </div>
                            </div>
                       </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button onclick="appSelfUpdate.cancelLayer();" type="button" class="btn btn-default btn-sm" data-dismiss="modal">取消</button>
                <button onclick="appSelfUpdate.submitForm();" type="button" class="btn btn-primary btn-sm">确认</button>
            </div>
        </div>
    </div>
</div>
<!--新建编辑弹出层 start-->

<script>

</script>
