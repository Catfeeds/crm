<?php
use common\logic\LinkPager;

$this->title = '新增线索';
?>
<?php
    $this->registerJsFile('/dist/plugins/daterangepicker/moment.min.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
        ]);
    $this->registerJsFile('/dist/plugins/daterangepicker/daterangepicker.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
        ]);

    $this->registerJsFile('/dist/js/user/date.js', [
        'depends' => ['backend\assets\AdminLteAsset']
    ]);
	$this->registerJsFile('/assets/js/dist.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
        ]);
    $this->registerJsFile('/dist/js/user/clue.js', [
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
    <h1 class="page-title">新增线索</h1>
</section>
<form class="form-horizontal o-f" action="index" method="get" id="form2">
<section class="content-body">
    <div class="box advanced-search-form mb-lg">

            <div class="row">
                <div class="form-group col-lg-4 col-md-6">
                    <label for="" class="control-label col-sm-3 t-r">创建日期：</label>
                    <div class="col-sm-9 col-md-9">
                        <div class="calender-picker double-time" id="datetime" style="height:34px;padding:5px 15px;">
                            <div class="timeinputbox">
                                <input type="text" id="addtime" name="addtime" value="<?php echo $addtime;?>" placeholder="请输入时间" style="width:100%;padding-left:0;">
                                <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group col-lg-4 col-md-6">
                    <label for="" class="control-label col-sm-3 t-r">关 键 字：</label>
                    <div class="col-sm-9 col-md-9"><input class="form-control" type="text" id="keyword" name="keyword" value="<?php echo $keyword;?>" placeholder="姓名/手机/车型/创建人/门店"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 t-r">
                    <div class="pull-right mr-15">
                        <input class="btn btn-primary btn-sm pull-left mr-15" value="查询" type="submit">
                        <input class="btn btn-default btn-sm pull-left" value="清除" id="clear" type="button">
                    </div>
                </div>
            </div>

    </div>
    <div class="mb-md">
        <input class="btn btn-primary btn-sm mr-15" value="新增线索" id="craete" data-toggle="modal" data-target="#myModal" type="button">
    </div>
    <div class="box box-none-border">
        <div class="box-body no-padding">
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-list-check">
                    <thead>
                    <tr>
                        <th width="60">序号</th>
                        <th>姓名</th>
                        <th>电话</th>
                        <th>意向车型</th>
                        <th>拟购时间</th>
                        <th>渠道来源
                            <div class="lte-filterbox">
                                <i title="Filter Menu" class="fa fa-filter lte-dropdown-trigger ml-0"></i>
                                <div class="lte-table-filter-dropdown none">
                                    <ul class="lte-dropdown-menu lte-dropdown-menu-vertical  lte-dropdown-menu-root" role="menu" aria-activedescendant="" tabindex="0">
                                        <?php if(!empty($arr['input_type'])){foreach ($arr['input_type'] as $v){?>
                                            <li class="lte-dropdown-menu-item" role="menuitem" aria-selected="false">
                                                <label class="lte-checkbox-wrapper">
                                                    <input type="checkbox" class="lte-checkbox-input" val="i<?php echo $v['id'];?>"  name="input_type[]" value="<?php echo $v['id'];?>">
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
    					<th>信息来源
                            <div class="lte-filterbox">
                                <i title="Filter Menu" class="fa fa-filter lte-dropdown-trigger ml-0"></i>
                                <div class="lte-table-filter-dropdown none">
                                    <ul class="lte-dropdown-menu lte-dropdown-menu-vertical  lte-dropdown-menu-root" role="menu" aria-activedescendant="" tabindex="0">
                                        <?php if(!empty($arr['source'])){foreach ($arr['source'] as $v){?>
                                            <li class="lte-dropdown-menu-item" role="menuitem" aria-selected="false">
                                                <label class="lte-checkbox-wrapper">
                                                    <input type="checkbox" class="lte-checkbox-input" val="s<?php echo $v['id'];?>" name="source[]" value="<?php echo $v['id'];?>">
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
                        <th>创建时间</th>
                        <th>创建人</th>
    					<th>创建方式
                            <div class="lte-filterbox">
                                <i title="Filter Menu" class="fa fa-filter lte-dropdown-trigger ml-0"></i>
                                <div class="lte-table-filter-dropdown none">
                                    <ul class="lte-dropdown-menu lte-dropdown-menu-vertical  lte-dropdown-menu-root" role="menu" aria-activedescendant="" tabindex="0">
                                        <li class="lte-dropdown-menu-item" role="menuitem" aria-selected="false">
                                            <label class="lte-checkbox-wrapper">
                                                <input type="checkbox" class="lte-checkbox-input" val="c1"  name="create_type[]" value="1">
                                            </label>
                                            <span>导入</span>
                                        </li>
                                        <li class="lte-dropdown-menu-item" role="menuitem" aria-selected="false">
                                            <label class="lte-checkbox-wrapper">
                                                <input type="checkbox" class="lte-checkbox-input"  val="c2" name="create_type[]" value="2">
                                            </label>
                                            <span>手动录入</span>
                                        </li>

                                    </ul>
                                    <div class="lte-table-filter-dropdown-btns">
                                        <a class="lte-table-filter-dropdown-link confirm sub">确定</a>
                                        <a class="lte-table-filter-dropdown-link clean">重置</a>
                                    </div>
                                </div>
                            </div>
                        </th>
                        <th>下发到</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($list)) {
                        $page = empty($_GET['page']) ? 1 : $_GET['page'];
                        foreach ($list as $k => $v) { ?>
                            <tr>
                                <td><?php echo (($page - 1) * 20) + ($k + 1);?></td>
                                <td><?php echo empty($v['customer_name']) ? '--' : $v['customer_name']; ?></td>
                                <td><?php echo empty($v['customer_phone']) ? '--' : $v['customer_phone']; ?></td>
                                <td><?php echo empty($v['intention_id_name']) ? '--' : $v['intention_id_name']; ?></td>
                                <td><?php echo empty($v['planned_purchase_time_name']) ? '--' : $v['planned_purchase_time_name']; ?></td>
                                <td><?php echo empty($v['clue_input_type_name']) ? '--' : $v['clue_input_type_name']; ?></td>
                                <td><?php echo empty($v['source_name']) ? '--' : $v['source_name']; ?></td>
                                <td><?php echo date('Y-m-d H:i', $v['create_time']); ?></td>
                                <td><?php echo empty($v['create_person_name']) ? '--' : $v['create_person_name']; ?></td>
    							<td><?php
    								if($v['create_type'] == 1) echo '导入';
    								else if($v['create_type'] == 2) echo '手动录入';
    								else echo '--';
    								?>
    							</td>
                                <td><span><?php echo empty($v['shop_name']) ? '--' : $v['shop_name']; ?></span></td>
                            </tr>
                        <?php }
                    } ?>
                    </tbody>
                </table>
            </div>
            <div class="box-footer pdt-md pull-right bd-t0">
                <?php

                // 显示分页
                echo LinkPager::widget([
                    'pagination' => $pagination,
                    'firstPageLabel' => "首页",
                    'prevPageLabel' => '上一页',
                    'nextPageLabel' => '下一页',
                    'lastPageLabel' => '末页',
                ]);
                ?>
            </div>
        </div>
    </div>
</section>
</form>
<div class="modal fade" id="myModal" tabindex="-1" data-backdrop='static'   aria-labelledby="myModalLabel">

</div>
<script type="text/javascript">
    var sourves = '<?php echo $arr['sources'];?>';//信息来源
    var input_types = '<?php echo $arr['input_types'];?>';//渠道来源
    var create_types = '<?php echo $arr['create_types'];?>';//创建方式
</script>