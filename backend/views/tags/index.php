<?php
$this->render('../w_top.php');
?>
    <section class="content-header">
        <h1 class="page-title">到店/上门商谈设置</h1>
    </section>

    <!-- Main content -->
    <section class="content-body">
        <div class="row mb-md">
            <div class="col-sm-12 col-xs-12">
                <ul class="nav nav-tabs custom-tab mb-md">
                    <?php
                    foreach($typeConfig as $k => $v){
                        $class = ($k == $selectType ? 'active' : '');
                    ?>
                    <li class="<?php echo $class; ?>"><a href="/tags/index?type=<?php echo $k; ?>"><?php echo $v; ?></a></li>
                    <?php
                    }
                    ?>
                </ul>
            </div>
            <div class="col-sm-3 col-xs-4">
                <input class="btn btn-primary btn-sm mr-10" onclick="tags.createLayer()" value="新增<?php echo $typeConfig[$selectType]; ?>" type="button">
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
                                            <th width="60">序号</th>
                                            <th>名称</th>
                                            <th>使用次数</th>
                                            <th>状态</th>
                                            <th class="t-c">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach($list as $k => $val)
                                        {
                                        ?>
                                        <tr>
                                            <td><?php echo ($k + 1);?></td>
                                            <td><?php echo $val['name'];?></td>
                                            <td><?php echo $val['used_times'];?></td>
                                            <td>
                                                <?php
                                                    if($val['status'] == 1) //使用中
                                                    {
                                                        $class = 'c-green mr-5';
                                                        $text = '使用中';
                                                        $btnName = '禁用';
                                                    }
                                                    else
                                                    {
                                                        $class = 'c-red mr-5';
                                                        $text = '禁用';
                                                        $btnName = '启用';
                                                    }
                                                ?>
                                                <span class="<?php echo $class; ?>">•</span><?php echo $text;?>
                                            </td>
                                            <td class="t-c va-m">
                                                <div class="operation">
                                                    <span class="thisDataSpan" style="display:none">
                                                        <?php echo json_encode($val); ?>
                                                    </span>
                                                    <a onclick="tags.updateLayer($(this));" href="javascript:;" intentionId="<?php echo $val['id'];?>">编辑</a>
                                                    <?php
                                                    if($val['is_special'] == 0){
                                                    ?>
                                                    <span>|</span>
                                                    <a onclick="tags.updateStatus($(this));" href="javascript:;"><?php echo $btnName; ?></a>
                                                    <?php
                                                    }
                                                    ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
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
                <button type="button" class="close" onclick="window.location.reload()"  data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">新增</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <input class="form-control" id="input_id" placeholder="标签的id，编辑数据用到" type="hidden">
                    <input class="form-control" id="input_type" value="<?php echo $selectType;?>" type="hidden">
                    <div class="row">
                        <div class="form-group col-md-12 col-sm-12">
                            <label for="inputname" class="control-label col-sm-2"><span class="c-red mr-5">*</span>名称：</label>
                            <div class="col-sm-9">
                                <input class="form-control required" id="input_name" placeholder="名称" type="text">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button onclick="tags.cancelLayer();" type="button" class="btn btn-default btn-sm" data-dismiss="modal">取消</button>
                <button onclick="tags.submitForm();" type="button" class="btn btn-primary btn-sm">确认</button>
            </div>
        </div>
    </div>
</div>
<!--新建编辑弹出层 start-->    
