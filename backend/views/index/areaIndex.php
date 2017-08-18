<?php

	$this->registerJsFile('/assets/js/area/index.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);
	$this->registerJsFile('/dist/plugins/echarts.min.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);
	$this->registerJsFile('/dist/js/baobiao.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);
	$this->registerJsFile('/dist/plugins/daterangepicker/bootstrap-datepicker.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);
	$this->registerJsFile('/dist/plugins/daterangepicker/bootstrap-datepicker_002.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);
	$this->registerCssFile('/dist/css/datepicker3.css', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);

?>

<section class="content-header">
  <h1 class="page-title">销售指标-大区</h1>
</section>
<!-- Main content -->
<section class="content form-horizontal">
  <div class="panel border1">
    <div class="panel-heading pdb-0"><strong>年销售指标设定（<span><?php echo $year;?>年</span>）</strong></div>
    <div class="panel-body">
      <div class="row">
        <div class="col-sm-12">
          <div class="form-group mb-md col-md-4">
            <label class="control-label col-sm-3">年份：</label>
              <form class="col-sm-9" action="target" method="post" id="areaForm">
                <select class="form-control" id="year" name="year">
                  <option value="2017">2017年</option>
                  <option value="2018">2018年</option>
                  <option value="2019">2019年</option>
                </select>
              </form>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <div class="table-responsive">
            <table class="table table-hover table-bordered table-list-check">
              <thead>
                <tr>
                  <th width="60">序号</th>
                  <th>名称</th>
                  <th>年销售目标</th>
                  <th>实际完成台数</th>
                  <th>完成率</th>
                  <th>操作</th>
                </tr>
              </thead>
              <tbody>
                <?php $i = 1;
					foreach ($dataList as $v){
				?>
                <tr>
                  <td><?php echo $i++;?></td>
                  <td><?php echo $v['name'];?></td>
                  <td><?php echo $v['target_num'];?></td>
                  <td><?php echo $v['finish_num'];?></td>
                  <td><?php echo $v['percentage'];?></td>
                  <td><a class="edit" href="#" data-toggle="modal" data-target="#myModal" url="<?php echo '/index/edit?shop_id='.$v['id'].'&year='.$year;?>">编辑</a>
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
  <div class="panel border1">
    <div class="panel-heading pdb-0"><strong>月度销售指标及完成情况（<span id="month_title"><?php echo date('Y-n')?></span>）</strong></div>
    <div class="panel-body">
      <div class="row">
        <div class="col-sm-12">
          <div class="col-md-4 mb-md">
            <label class="form-label col-sm-3">月份：</label>
            <div class="timebox col-sm-9">
              <input type="text" value="<?php echo date('Y-n')?>"  name="year_and_month" id="datetimepicker">
              <i class="glyphicon glyphicon-calendar fa fa-calendar"></i> </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12 mt-15">
          <div id="chart" style="min-width: 200px; height: 300px;"></div>
        </div>
        <div class="col-md-12 mt-15">
          <div class="table-responsive" id="ajaxitem">

          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- /.row -->
</section>
<div class="modal fade" id="myModal" tabindex="-1" data-backdrop='static'   aria-labelledby="myModalLabel">

</div>
<script type="text/javascript">
var year = "<?php echo $year;?>";
</script>