<?php
$this->render('../w_top.php');
?>
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="page-title">短信模板设置</h1>
    </section>

    <!-- Main content -->
    <section class="content-body">
        <div class="row">
            <div class="col-sm-12 col-xs-12">
                <ul class="nav nav-tabs custom-tab mb-md">
                    <li class="<?php echo ($selectType == 2 ? 'active' : ''); ?>"><a href="/phone-letter-tmp/index?type=2">APP短信模板</a></li>
                    <li class="<?php echo ($selectType == 1 ? 'active' : ''); ?>"><a href="/phone-letter-tmp/index?type=1">文字短信模板</a></li>
                    <li class="<?php echo ($selectType == 3 ? 'active' : ''); ?>"><a href="/phone-letter-tmp/index?type=3">语言短信模板</a></li>
                </ul>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12 tab-content">
                <div class="row tab-pane active" id="appuse">
                    <?php if($selectType == 2){ ?>
                    <div class="col-sm-3 col-xs-4 mb-md">
                        <input class="btn btn-primary btn-sm mr-10" value="新增模板" onclick="phoneLetterTmp.createLayer($(this));" type="button">
                    </div>
                    <?php }?>
                    <div class="box box-none-border col-sm-12 col-xs-12">
                        <div class="box-body no-padding">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-list-check">
                                    <thead>
                                        <tr>
                                            <th width="60">序号</th>
                                            <th>名称</th>
                                            <?php if($selectType == 1){ ?>
                                            <th>使用场景</th>
                                            <?php } ?>
                                            <th>模板内容</th>
                                            <th>状态</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($list as $k => $val){ ?>
                                        <tr>
                                            <td><?php echo ($k + 1); ?></td>
                                            <td><?php echo $val['title']; ?></td>
                                            <?php if($selectType == 1){ ?>
                                            <td><?php echo $val['use_scene']; ?></td>
                                            <?php } ?>
                                            <td><?php echo $val['content']; ?></td>
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
                                                    <?php if($selectType != 1){ ?>
                                                    <a onclick="phoneLetterTmp.updateLayer($(this));" href="javascript:;" intentionId="<?php echo $val['id'];?>">编辑</a>
                                                    <?php
                                                    if($val['is_special'] == 0){
                                                    ?>
                                                    <span>|</span>
                                                    <a onclick="phoneLetterTmp.updateStatus($(this));" href="javascript:;"><?php echo $btnName; ?></a>
                                                    <span>|</span>
                                                    <a onclick="phoneLetterTmp.delete($(this));" href="javascript:;">删除</a>
                                                    <?php
                                                    }
                                                    ?>
                                                    <?php }else{ ?>
                                                    <a onclick="phoneLetterTmp.updateStatus($(this));" href="javascript:;"><?php echo $btnName; ?></a>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php }?>
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
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="phoneLetterTmp.cancelLayer();"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">新增</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <input class="form-control" id="input_id" placeholder="短信模板的id，编辑数据用到" type="hidden">
                    <input class="form-control" id="input_type" value="<?php echo $selectType;?>" type="hidden">
                    <div class="row">
                            <!--名称-->
                            <div class="form-group col-md-12 col-sm-12">
                                <label for="inputname" class="control-label col-sm-2"><span class="c-red mr-5">*</span>名称：</label>
                                <div class="col-sm-9">
                                    <input class="form-control required" id="input_title" placeholder="名称" type="text">
                                </div>
                            </div>
                            <!--使用场景-->
                            <?php if($selectType == 1){ ?>
                            <div class="form-group col-md-12 col-sm-12">
                                <label for="inputname" class="control-label col-sm-2"><span class="c-red mr-5">*</span>使用场景：</label>
                                <div class="col-sm-9">
                                    <input class="form-control required" id="input_use_scene" placeholder="使用场景" type="text">
                                </div>
                            </div>
                            <?php } ?>
                            <!--模板正文-->
                            <div class="form-group col-md-12 col-sm-12">
                                <label for="inputname" class="control-label col-sm-2"><span class="c-red mr-5">*</span>正文：</label>
                                <div class="col-sm-9">
                                    <input class="form-control required" id="input_content" placeholder="正文" type="text">
                                </div>
                            </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button onclick="phoneLetterTmp.cancelLayer();" type="button" class="btn btn-default btn-sm" data-dismiss="modal">取消</button>
                <button onclick="phoneLetterTmp.submitForm();" type="button" class="btn btn-primary btn-sm">确认</button>
            </div>
        </div>
    </div>
</div>
<!--新建编辑弹出层 start-->    

  