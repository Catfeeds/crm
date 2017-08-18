<?php
use common\logic\LinkPager;

$this->title = '线索处理';
?>
<?php
    $this->registerJsFile('/dist/plugins/daterangepicker/moment.min.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
        ]);
    $this->registerJsFile('/dist/plugins/daterangepicker/daterangepicker.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
        ]);
    $this->registerJsFile('/dist/js/customer_list.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
        ]);
	$this->registerJsFile('/dist/js/yuqi/yuqi.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
        ]);


?>
<section class="content-header">
    <h1 class="page-title">逾期线索</h1>
</section>
<form class="form-horizontal o-f" action="index" method="get" id="form1">
<section class="content-body">
    <div class="box advanced-search-form mb-lg">
        
            <div class="row">
                <div class="form-group col-lg-4 col-md-6">
                    <label for="" class="control-label col-sm-3 t-r">下发日期：</label>
                    <div class="col-sm-9 col-md-9">
                        <div class="calender-picker double-time" id="datetime" style="height:34px;padding:5px 15px;">
                            <div class="timeinputbox">
                                <input type="text" id="addtime" name="addtime" value="<?php echo $get['addtime'];?>" placeholder="请输入时间" style="width:100%;padding-left:0;">
                                <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group col-lg-4 col-md-6">
                    <label for="" class="control-label col-sm-3 t-r">关 键 字：</label>
                    <div class="col-sm-9 col-md-9"><input class="form-control" type="text" id="keyword" name="keyword" value="<?php echo $get['keyword'];?>" placeholder="姓名/手机/顾问/车型"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 t-r">
                    <div class="pull-right mr-15">
                        <input class="btn btn-primary btn-sm pull-left mr-15" value="查询" type="submit">
                        <a href="index"><input class="btn btn-default btn-sm pull-left" value="清除" id="clear" type="button"></a>
                    </div>
                </div>
            </div>
        
    </div>

    <div class="box box-none-border">
        <div class="box-body no-padding">
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-list-check">
                    <thead>
                    <tr>
                        <th>序号</th>
                        <th>下发时间
						 <div class="lte-table-column-sorter" onclick="start_time()">
						  <span class="lte-table-column-sorter-up off" id="desc"   title="↑" > <i class="fa fa-caret-up"></i>
						  </span>
						  <span class="lte-table-column-sorter-down off" id="asc"   title="↓"> <i class="fa fa-caret-down"></i>
								<input type="hidden" name="start_time" id="start_time" />
						 </span>
						</div>
						</th>
                        <th>逾期</th>
                        <th>客户姓名</th>
                        <th>手机号码</th>
                        <th>意向等级
						 <div class="lte-filterbox">
							<i title="Filter Menu" class="fa fa-filter lte-dropdown-trigger ml-0"></i>
							<div class="lte-table-filter-dropdown none">
								<ul class="lte-dropdown-menu lte-dropdown-menu-vertical  lte-dropdown-menu-root" role="menu" aria-activedescendant="" tabindex="0">
									<?php if(!empty($intention)){foreach ($intention as $v){?>
										<li class="lte-dropdown-menu-item" role="menuitem" aria-selected="false">
											<label class="lte-checkbox-wrapper">
												<input type="checkbox" class="lte-checkbox-input " val="y<?php echo $v['id'];?>"  name="intention[]" value="<?php echo $v['id'];?>">
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
									<?php if(!empty($arrOutPut['shop'])){foreach ($arrOutPut['shop'] as $v){?>
										<li class="lte-dropdown-menu-item" role="menuitem" aria-selected="false">
											<label class="lte-checkbox-wrapper">
												<input type="checkbox" class="lte-checkbox-input " val="s<?php echo $v['id'];?>"  name="shop[]" value="<?php echo $v['id'];?>">
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
                        <th>状态
						 <div class="lte-filterbox">
							<i title="Filter Menu" class="fa fa-filter lte-dropdown-trigger ml-0"></i>
							<div class="lte-table-filter-dropdown none">
								<ul class="lte-dropdown-menu lte-dropdown-menu-vertical  lte-dropdown-menu-root" role="menu" aria-activedescendant="" tabindex="0">
									<li class="lte-dropdown-menu-item" role="menuitem" aria-selected="false">
										<label class="lte-checkbox-wrapper">
											<input type="checkbox" val="0" class="lte-checkbox-input" name="is_lianxi[]" value="0">
										</label>
										<span>未跟进</span>
									</li>
									<li class="lte-dropdown-menu-item"  val="0" role="menuitem" aria-selected="false">
										<label class="lte-checkbox-wrapper">
											<input type="checkbox" val="1" class="lte-checkbox-input" name="is_lianxi[]" value="1">
										</label>
										<span>已跟进</span>
									</li>
								</ul>
								<div class="lte-table-filter-dropdown-btns">
									<a class="lte-table-filter-dropdown-link confirm sub">确定</a>
									<a class="lte-table-filter-dropdown-link clean">重置</a>
								</div>
							</div>
						</div>
						</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($list)) {
                        $page = empty($_GET['page']) ? 1 : $_GET['page'];
                        foreach ($list as $k => $v) { ?>
                            <tr>
                                <td><?php echo (($page - 1) * 20) + ($k + 1);?></td>
                                <td><?php echo $v['start_time']; ?></td>
                                <td><?php

                                    $date = date('Y-m-d H:i:s');
                                    if ($v['is_lianxi'] == 0 && $date > $v['end_time']) {
                                        $date1 = $v['end_time'];
                                        $date2 = $date;

                                    }else if($v['is_lianxi'] == 1 && $v['lianxi_time'] > $v['end_time']) {
                                        $date1 = $v['end_time'];
                                        $date2 = $v['lianxi_time'];

                                    }
                                    //将时间转换为时间戳
                                    $str1=strtotime($date1);
                                    $str2=strtotime($date2);

                                    //求时间差
                                    $cle= $str2 - $str1;

                                    $d = floor($cle/3600/24);
                                    $h = floor(($cle%(3600*24))/3600);  //%取余
                                    $m = floor(($cle%(3600*24))%3600/60);

                                    echo "$d 天 $h 小时 $m 分";

                                    ?></td>
                                <td>
								<?php if(!empty($v['customer_name'])){?>
									<a href="/customer/customer-detail?ischeck=1&id=<?php echo $v['clue_id'];?>"><?php echo $v['customer_name'];?></a>
								<?php }else{echo '--';}?>
								</td>
                                <td><?php echo empty($v['customer_phone']) ? '--' : $v['customer_phone']; ?></td>
                                <td><?php echo empty($v['intention_level_des']) ? '--' : $v['intention_level_des']; ?></td>
                                <td><?php echo empty($v['intention_des']) ? '--' : $v['intention_des']; ?></td>
                                <td><?php echo empty($v['salesman_name']) ? '--' : $v['salesman_name']; ?></td>
                                <td><?php echo empty($v['shop_name']) ? '--' : $v['shop_name']; ?></td>
                                <td>
                                    <?php echo $v['lianxi_time'] > 0 ? '已跟进' : '未跟进';?>
                                </td>
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
<script type="text/javascript">
var start_times = "<?php echo $get['start_time'];?>";
var intention = '<?php echo $arrOutPut['intentions'];?>';//意向等级
var is_lianxi = '<?php echo $arrOutPut['is_lianxis'];?>';//状态
var shop = '<?php echo $arrOutPut['shops'];?>';//门店
</script>