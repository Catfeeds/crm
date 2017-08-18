<?php

use common\logic\LinkPager;

	$this->registerJsFile('/assets/js/dist.js', [
		'depends'=> ['backend\assets\AdminLteAsset']
		]);
	$this->registerJsFile('/dist/js/user/updateXlsxLog.js', [
		'depends'=> ['backend\assets\AdminLteAsset']
		]);
	$this->registerJsFile('/assets/js/checkform.js', [
		'depends'=> ['backend\assets\AdminLteAsset']
	]);
	$this->registerJsFile('/dist/js/form.js', [
		'depends'=> ['backend\assets\AdminLteAsset']
	]);

?>
<section class="content-header">
    <h1 class="page-title">导入线索</h1>
</section>

<section class="content-body">
    <div class="box advanced-search-form pdt-lg pdb-lg mb-lg">
        <div class="row">
            <form  class="form-horizontal col-sm-12" action="add_file" method="post" enctype="multipart/form-data" name="form"  id="J_myUploadForm">
                <div class="row">
                    <div class="col-sm-12">
                    	<div class="form-group mb-md">
                    		<label for="" class="control-label col-lg-2 col-md-3 col-sm-5">请选择需要导入的Excel文件：</label>
                            <div class="col-lg-5 col-md-5 col-sm-7"><input type="text" id="fileval" class="form-control"></div>
                    	</div>
                    </div>
                	<div class="col-md-12">
                        <div class="pull-right mr-15">
                		    <a class=" filebtn btn btn-primary btn-sm mr-15" style="height:30px;line-height:30px;">
                			    <span>浏览...</span>
                			    <input type="file" name="file" id="file" style="height:30px;" />
                		    </a>
                    		<input type="button" id="submit_upload"  class="btn btn-primary btn-sm mr-md" value="导入信息">
                            <a class="model-download" href="../muban/线索导入模板.xls"><input type="button" class="btn btn-danger btn-sm" value="模板下载"></a>
                        </div>
                        <div class="overlay" id="ceng" style=" display: none; width: 100%; height: 100%; position: fixed; left: 0; top:0; background-color: (0,0,0,0.25)">
                            <i class="fa fa-refresh fa-spin"></i>
                        </div>
                	</div>
                </div>
                <p class="tips c-gray clear col-sm-12 mb-0">提示：一次最多导入800条数据</p>
            </form>
        </div>
    </div>
	<div class="box box-none-border">
		<div class="box-body no-padding">
			<div class="table-responsive">
				<form method="get" id="form1" action="index">
				<table class="table table-hover table-bordered table-list-check">
					<thead>
					<tr>
						<th width="60">序号</th>
						<th>导入时间
							<div class="lte-table-column-sorter" onclick="update_times()">
								  <span class="lte-table-column-sorter-up off" id="desc"   title="↑" > <i class="fa fa-caret-up"></i>
								  </span>
								<span class="lte-table-column-sorter-down off" id="asc"   title="↓"> <i class="fa fa-caret-down"></i>
										<input type="hidden" name="update_time" id="update_time" />
								 </span>
							</div>
						</th>
						<th>文件名称</th>
						<th>操作人</th>
						<th>成功导入数量</th>
						<th>失败数量</th>
						<th>操作</th>
					</tr>
					</thead>
					<tbody>
					<?php if (!empty($list)) {
						$page = empty($_GET['page']) ? 1 : $_GET['page'];
						foreach ($list as $k => $v) { ?>
							<tr>
								<td><?php echo (($page - 1) * 20) + ($k + 1);?></td>
								<td><?php echo date('Y-m-d H:i:s',$v['update_time']);?></td>
								<td><?php echo $v['update_file'];?></td>
								<td><?php echo $v['update_person_name'];?></td>
								<td><?php echo $v['success_num'];?></td>
								<td><?php echo $v['error_num'];?></td>
								<td>
									<?php if ($v['error_num'] > 0){?>
										<a href="../uploads/errorclue/<?php echo $v['error_file']?>">下载失败数据</a>
									<?php }?>
								</td>

							</tr>
						<?php }
					} ?>
					</tbody>
				</table>
				</form>
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

<script type="text/javascript">
	update_time = "<?php echo $get['update_time']?>";
</script>