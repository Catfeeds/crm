<?php
    include_once '../views/top.php'
?>
<style>
/*    .custom-dialog .control-label{width: 25%;text-align: right;}
*/
.el-cascader{
       width: 100%;
    }
.el-input__inner,.el-input__inner::placeholder{
  border-radius: 0;
  color: #333;
}

@media (min-width: 768px){
    .custom-dialog .control-label{width: 25%;text-align: right;}
}
@media (max-width:768px){
    .custom-dialog .control-label{width: inherit;text-align: right;}
}

</style>
<div class="modal-dialog custom-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" class="reset_but">×</span></button>
            <h4 class="modal-title" id="myModalLabel">门店编辑</h4>

        </div>
        <div class="modal-body">
            <div class="panel boxshadow-none mb-0 clearfix">
                <div class="row">
                    <div class="form-group col-md-12 col-sm-12">
                        <label for="" class="control-label col-sm-4 t-r">Banner：</label>
                        <div class="col-md-8"><input class="form-control required" id="name" name="name" type="text"  placeholder="姓名"></div>
                    </div>
                    <div class="form-group col-md-12 col-sm-12">
                        <label for="" class="control-label col-sm-4 t-r">主推车型：</label>
                        <div class="col-md-8"><input class="form-control required" id="name" name="name" type="text"  placeholder="姓名"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default btn-sm reset_but" >取消</button>
            <button type="button" id="submit"  class="btn btn-sm btn-primary">保存</button>
        </div>
    </div>
</div>

<script type="text/javascript">

</script>
