<?php
include_once '../views/top.php'
?>

<div class="modal-dialog" role="document">
<form id="form1" action="save" class="form-inline" >
<input type="hidden" name="shop_id" value="<?php echo $get['shop_id'];?>" />
<input type="hidden" name="year" value="<?php echo $get['year'];?>" />
  <div class="modal-content">
	<div class="modal-header">
	  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" class="reset_but">×</span></button>
	  <h4 class="modal-title" id="myModalLabel">编辑销售指标</h4>
	</div>
	<div class="modal-body">
		<div class="row ml-15 mr-15">
			<div class="form-group col-md-12 col-sm-12 mb-15">
				<div class="col-sm-8 col-sm-offset-1 t-c">
				  目标台数
				</div>
				<div class="col-sm-3 t-c">
				  实际完成
				</div>
			</div>
			<?php
				$date = date('Yn');

				$arr = [
					'1' => '一月',
					'2' => '二月',
					'3' => '三月',
					'4' => '四月',
					'5' => '五月',
					'6' => '六月',
					'7' => '七月',
					'8' => '八月',
					'9' => '九月',
					'10' => '十月',
					'11' => '十一月',
					'12' => '十二月',
				];

				$ischeck = false;
				$newArr = [];
				foreach($arr as $k => $v){

					$date1 = $get['year'].$k;
					$ischeck = false;
					foreach($list as $val) {
						if($k == $val['months']) {
							$ischeck = true;
							$newArr = $val;
						}
					}

					if($ischeck){

			?>
			<div class="form-group col-sm-12 mb-15">
				<div class="col-sm-8 col-sm-offset-1">
				  <label class="form-label t-r w60"><?php echo $v;?>：</label>
				  <input class="form-control t-c" name="<?php echo $k;?>"  <?php if ($date1 < $date) echo 'readonly';?> value="<?php echo $newArr['target_num'];?>"  type="text">
				  <label class="control-label t-l">台</label>
				</div>
				<div class="col-sm-3 t-c">
				  <label for="" class="form-label"><?php echo $newArr['finish_num'];?>台</label>
				</div>
			</div>
			<?php }else{?>
				<div class="form-group col-sm-12 mb-15">
				<div class="col-sm-8 col-sm-offset-1">
				  <label class="form-label t-r w60"><?php echo $v;?>：</label>
				  <input class="form-control t-c" <?php if ($date1 < $date) echo 'readonly';?> name="<?php echo $k;?>" value="0"  type="text">
				  <label class="form-label t-r">台</label>
				</div>
				<div class="col-sm-3 t-c">
				  <label for="" class="form-label mt-8">0台</label>
				</div>
			</div>
			<?php }}?>
		</div>
	</div>
	<div class="modal-footer t-c">
	  <button type="button" class="btn btn-default reset_but" >取消</button>
	  <button type="button" class="btn btn-primary" id="submit">保存</button>
	</div>
  </div>
</form>
</div>

