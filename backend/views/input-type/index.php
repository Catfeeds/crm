<?php
$this->render('../w_top.php');
?>

    <section class="content-header">
        <h1 class="page-title ">渠道来源设置</h1>
    </section>

    <!-- Main content -->
    <section class="content-body">
        <div class="row mb-md">
            <div class="col-sm-12 col-xs-12">
                <ul class="nav nav-tabs custom-tab mb-md">
                    <li><a href="/profession/index">职业</a></li>
                    <li class="active"><a href="/input-type/index" title='后台导入、后台新建...'>渠道来源</a></li>
                    <li><a href="/source/index" title='广播、户外广告...'>信息来源</a></li>
                    <li><a href="/age-group/index">年龄段</a> </li>
                    <li><a href="/planned-purchase-time/index">拟购时间</a></li>
                </ul>
            </div>
            <div class="col-sm-3 col-xs-4">
                <input class="btn btn-primary btn-sm mr-10" onclick="inputType.createLayer()" value="新增渠道来源" type="button">
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
                                            <th>描述</th>
                                            <th>逾期时长设定</th>
                                            <th>状态</th>
                                            <th class="t-c">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach($list as $val)
                                        {
                                        ?>
                                        <tr>
                                            <td><?php echo $val['id'];?></td>
                                            <td><?php echo $val['name'];?></td>
                                            <td><?php echo $val['des'];?></td>
                                            <td><?php echo empty($val['yuqi_time']) ? '无' : $val['yuqi_time'].'小时' ;?></td>
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
                                                    <a onclick="inputType.updateLayer($(this));" href="javascript:;" intentionId="<?php echo $val['id'];?>">编辑</a>
                                                    <?php
                                                    if($val['is_special'] == 0){
                                                    ?>
                                                    <span>|</span>
                                                    <a href="/input-type/update-status?id=<?php echo $val['id'];?>&status=<?php echo intval(!$val['status']) ?>"><?php echo $btnName; ?></a>
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
                <button type="button" class="close" onclick="window.location.reload()" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">新增渠道来源</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <input type="hidden" name="is_yuqi" id="is_yuqi" value="0">
                    <input class="form-control" id="input_id" placeholder="渠道来源的的id，编辑数据用到" type="hidden">
                    <div class="row">
                            <div class="form-group col-md-12 col-sm-12">
                                <label for="inputname" class="control-label col-sm-2"><span class="c-red mr-5">*</span>名称：</label>
                                <div class="col-sm-9">
                                    <input class="form-control required" id="input_name" placeholder="名称" type="text">
                                </div>
                            </div>
                            <div class="form-group col-md-12 col-sm-12">
                                <label for="inputname" class="control-label col-sm-2">描述：</label>
                                <div class="col-sm-9">
                                    <input class="form-control" id="input_des" placeholder="请输入描述" type="text">
                                </div>
                            </div>
                        <div class="form-group col-md-12 col-sm-12">
                            <label for="inputname" class="control-label col-sm-2"><span class="c-red mr-5">*</span> 逾期限制：</label>
                            <div class="col-sm-9">
                                <div class="row mb-md">
                                    <div class="col-sm-12 col-xs-12">
                                        <ul class="nav nav-tabs custom-tab mb-md">
                                            <li class="active" id="s0"><a  href="#" onclick="inputType.is_yuqi(0)">不限制</a></li>
                                            <li id="s1" ><a href="#" onclick="inputType.is_yuqi(1)">限制</a></li>
                                        </ul>
                                        <div id="d1" style="display: none;">
                                            <input class="form-control" value="0" name="yuqi_time" id="yuqi_time" placeholder="小时" type="text">小时
                                            
                                            <div style="color: red;">小数点后1位，范围：0.1 - 48</div>
											<span id="errors" style="color: red"></span>
                                        </div>

                                    </div>

                                </div>

                            </div>
                        </div>
                       </div>

                </form>
            </div>
            <div class="modal-footer">
                <button onclick="inputType.cancelLayer();" type="button" class="btn btn-default btn-sm" data-dismiss="modal">取消</button>
                <button onclick="inputType.submitForm();" type="button" class="btn btn-primary btn-sm">确认</button>
            </div>
        </div>
    </div>
</div>
<!--新建编辑弹出层 start-->    
