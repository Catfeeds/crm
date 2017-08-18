<?php
$this->render('../w_top.php');
?>
    <section class="content-header">
        <h1 class="page-title">意向等级设置</h1>
    </section>
    <!-- Main content -->
    <section class="content-body">
        <div class="row mb-md">
            <div class="col-sm-8 col-md-8">
                <input class="btn btn-primary btn-sm mr-10" onclick="intention.createLayer();" value="新增意向等级" type="button">
                <input class="btn btn-primary btn-sm" value="示例" data-toggle="modal" data-target="#myModal-sl" type="button">
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
                                            <th>意向等级</th>
                                            <th>描述</th>
                                            <th>规则</th>
                                            <th>当天推送任务</th>
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
                                            <td><?php echo $val['id']; ?></td>
                                            <td><?php echo $val['name'];?></td>
                                            <td><?php echo $val['des'];?></td>
                                            <td>每<?php echo $val['frequency_day'];?>天推送一次，共<?php echo $val['total_times'];?>次<?php if( $val['has_today_task'] ){ ?>，当天推送<?php } ?></td>
                                            <td><?php echo ($val['has_today_task'] ? '是' : '否' );?></td>
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
                                                    <a onclick="intention.updateLayer($(this));" href="javascript:;" intentionId="<?php echo $val['id'];?>">编辑</a>
                                                    <?php
                                                    if($val['is_special'] == 0){
                                                    ?>
                                                    <span>|</span>
                                                    <a href="/intention/update-status?id=<?php echo $val['id'];?>&status=<?php echo intval(!$val['status']) ?>"><?php echo $btnName; ?></a>
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
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="intention.cancelLayer();"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">新增意向等级</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="icon fa fa-warning"></i> 如果某等级不需要推送任务，则将回访规则设置为0即可
                </div>
                <form class="form-horizontal">
                    <input class="form-control" id="input_id" placeholder="意向等级的id，编辑数据用到" type="hidden">
                    <div class="row">
                        <div class="form-group clearfix">
                            <label for="inputname" class="control-label col-sm-2"><span class="c-red mr-5">*</span>名称：</label>
                            <div class="col-sm-9">
                                <input class="form-control required" id="input_name" placeholder="名称" type="text">
                            </div>
                        </div>
                        <div class="form-group clearfix">
                            <label for="inputname" class="control-label col-sm-2"><span class="c-red mr-5">*</span>描述：</label>
                            <div class="col-sm-9">
                                <input class="form-control required" id="input_des" placeholder="描述" type="text">
                            </div>
                        </div>
                        <div class="form-group clearfix">
                            <label for="inputname" class="control-label col-sm-2"><span class="c-red mr-5">*</span>回访规则：</label>
                            <div class="col-sm-10 clearfix">
                                <span class="pull-left mr-sm">每</span>
                                <input id="input_frequency_day" value="11" placeholder="" class="col-sm-2 col-xs-2 mr-sm bd-color-gray required" type="text" style="height:30px;">
                                <span class="pull-left mr-sm">天推送一次任务，共推送</span>
                                <input id="input_total_times" value="0" placeholder="" class="col-sm-2 col-xs-2 mr-sm bd-color-gray required" type="text" style="height:30px;">
                                <span class="pull-left">次</span>
                            </div>
                        </div>
                        <div class="form-group clearfix">
                            <label for="inputname" class="control-label col-sm-2"></label>
                            <label class="col-sm-5" style="font-weight: 500;">
                                <input id="input_has_today_task" class="pull-left mt-0" style="margin: 2px 3px 0 0 !important;" type="checkbox">客户创建当天推送任务
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button onclick="intention.cancelLayer();" type="button" class="btn btn-default btn-sm" data-dismiss="modal">取消</button>
                <button onclick="intention.submitForm();" type="button" class="btn btn-primary btn-sm">确认</button>
            </div>
        </div>
    </div>
</div>
<!--新建编辑弹出层 start-->
<div class="modal fade" id="myModal-sl" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;">
  <div class="modal-dialog yxsl-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
      </div>
      <div class="modal-body">
         <form class="form-horizontal">
              <div class="row">
                   <div class="col-sm-12">
                        <p>例：A级推送规则设置为：每<input type="text" value="3">天推送一次任务，推送<input type="text" value="2">次</p>
                        <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;销售顾问在2016年10月8日创建了1条A级客户</p>
                        <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;销售顾问未自己改变联系时间，则改任务推送时间如图：</p>
                        <dl class="ts-list mb-15">
                            <dt><input type="checkbox">勾选当天推送回访任务</dt>
                            <dd>
                                <span class="">2016年10月</span>
                                <ul class="time-line">
                                    <li class="active"><a href="#">8</a></li>
                                    <li><a>9</a></li>
                                    <li><a>10</a></li>
                                    <li class="active"><a href="#">11</a></li>
                                    <li><a>12</a></li>
                                    <li><a>13</a></li>
                                    <li class="active"><a href="#">14</a></li>
                                    <li><a>15</a></li>
                                    <li class="p1">建卡当天</li>
                                    <li class="p2">每3天推送一次任务</li>
                                    <li class="p3">每3天推送二次任务</li>
                                </ul>
                            </dd>
                        </dl>
                        <dl class="ts-list">
                            <dt><input type="checkbox">不勾选当天推送回访任务</dt>
                            <dd>
                                <span class="">2016年10月</span>
                                <ul class="time-line">
                                    <li><a href="#">8</a></li>
                                    <li><a>9</a></li>
                                    <li class="active"><a>10</a></li>
                                    <li><a href="#">11</a></li>
                                    <li><a>12</a></li>
                                    <li class="active"><a>13</a></li>
                                    <li><a href="#">14</a></li>
                                    <li><a>15</a></li>
                                    <li class="p4">每3天推送一次任务</li>
                                    <li class="p5">每3天推送二次任务</li>
                                </ul>
                            </dd>
                        </dl>
                   </div>
              </div>
         </form>
      </div>
      <!-- <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
        <button type="button" class="btn btn-primary">确认</button>
      </div> -->
    </div>
  </div>
</div>