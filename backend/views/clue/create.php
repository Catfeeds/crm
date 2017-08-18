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

    <form method="post" action="save" id="form1" v-model="formInline">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" class="reset_but">×</span></button>
                <h4 class="modal-title" id="myModalLabel">基本信息</h4>

            </div>
            <div class="modal-body">
                <div class="panel boxshadow-none mb-0 clearfix">
                   <div class="row">
                        <div class="form-group col-md-6 col-sm-6">
                            <label for="" class="control-label col-sm-4 t-r"><span class="c-red pdr-5">*</span>姓名：</label>
                            <div class="col-md-8"><input class="form-control required" id="name" name="name" type="text"  placeholder="姓名"></div>
                        </div>
                        <div class="form-group col-md-6 col-sm-6">
                            <label for="" class="control-label col-sm-4 t-r">姓别：</label>
                            <div class="col-md-8" style="line-height:34px;">
                                <label class="mr-5">
                                    <input name="sex" value="1" checked="checked" type="radio"  style="margin-top:10px;">男
                                </label>
                                <label>
                                    <input name="sex" value="2" type="radio" style="margin-top:10px;">女
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6 col-sm-6">
                            <label for="" class="control-label col-sm-4 t-r"><span class="c-red pdr-5">*</span>手机号码：</label>
                            <div class="col-md-8">
                                <input class="form-control required" id="phone" name="phone" maxlength="11" type="text" placeholder="手机号码">
                            </div>
                        </div>
                        <div class="form-group col-md-6 col-sm-6" >
                            <label for="" class="control-label col-sm-4 t-r">备用电话：</label>
                            <div class="col-md-8"><input class="form-control " id="spare_phone" name="spare_phone" type="text" placeholder="备用电话"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6 col-sm-6">
                            <label for="" class="control-label col-sm-4 t-r"><span class="c-red pdr-5">*</span>地址：</label>
                            <div class="col-md-8" id="xfarea">
                                <el-cascader
                                    placeholder="请选择"
                                    size="small"
                                    :options="options2"
                                    @change="handlechange_xfarea"
                                    filterable
                                ></el-cascader>
                                <input id="area" class="required" name="area" type="hidden" placeholder="地址" value="">
<!--                                <div class="cascader" id="area">-->
<!--                                    <div class="cascader-inputbox">-->
<!--                                        <input class="cascader-input" type="text" autocomplete="off" value=""  readonly placeholder="请选择"><i class="fa fa-set"></i>-->
<!--                                        <input class="sid" type="hidden" name="area"  value="">-->
<!--                                    </div>-->
<!--                                    <div class="cascader-list none">-->
<!--                                        <ul class="cascader-menu">-->
<!--                                        </ul>-->
<!--                                    </div>-->
<!--                                </div>-->
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6 col-sm-6">
                            <label for="" class="control-label col-sm-4 t-r"><span class="c-red pdr-5">*</span>渠道来源：</label>
                            <div class="col-md-8">
                                <select class="form-control required" name="clue_input_type" id="clue_input_type" placeholder="渠道来源">
                                    <option value="" selected="selected">请选择</option>
                                    <?php if (!empty($model['input_type'])) {
                                        foreach ($model['input_type'] as $v) { ?>
                                            <option value="<?php echo $v['id']; ?>"><?php echo $v['name']; ?></option>
                                        <?php }
                                    } ?>
                                </select>
                                <input type="hidden" id="clue_input_type_name" name="clue_input_type_name">
                            </div>
                        </div>
                        <div class="form-group col-md-6 col-sm-6">
                            <label for="" class="control-label col-sm-4 t-r">微信账号：</label>
                            <div class="col-md-8"><input class="form-control" name="weixin" type="text"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6 col-sm-6">
                            <label for="" class="control-label col-sm-4 t-r"><span class="c-red pdr-5">*</span>信息来源：</label>
                            <div class="col-md-8">
                                <select class="form-control required" name="clue_source"  placeholder="信息来源">
                                    <option value="" selected="selected">请选择</option>
                                    <?php if (!empty($model['source'])) {
                                        foreach ($model['source'] as $v) { ?>
                                            <option value="<?php echo $v['id']; ?>"><?php echo $v['name']; ?></option>
                                        <?php }
                                    } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-md-6 col-sm-6">
                            <label for="" class="control-label col-sm-4 t-r">说明：</label>
                            <div class="col-md-8"><input class="form-control" name="des" type="text"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6 col-sm-6">
                            <label for="" class="control-label col-sm-4 t-r"><span class="c-red pdr-5">*</span>下发门店：</label>
                           <div class="col-md-8"  id="xfmd">
                                <el-cascader
                                    placeholder="请选择"
                                    size="small"
                                    :options="options1"
                                    @change="handlechange_xfmd"
                                    filterable
                                ></el-cascader>
                                <input class="required" placeholder="下发门店" id="shop_id" name="shop_id" type="hidden" value="">
                               <!-- <div class="cascader" id="shop">
                                   <div class="cascader-inputbox">
                                       <input class="cascader-input required" type="text" autocomplete="off" name="shop_name" value=""  readonly placeholder="请选择"><i class="fa fa-set"></i>
                                       <input class="sid" type="hidden" name="shop"  value="">
                                   </div>
                                   <div class="cascader-list none">
                                       <ul class="cascader-menu">
                                       </ul>
                                   </div>
                               </div> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">购车信息</h4>
            </div>
            <div class="modal-body">
                <div class="panel boxshadow-none mb-0">
                    <div class="row">
                        <div class="form-group col-md-6 col-sm-6">
                            <label for="" class="control-label col-sm-4 t-r">意向车型：</label>
                            <div class="col-md-8" id="yxcx">
                                <!-- <div class="cascader" id="intention_des">
                                    <div class="cascader-inputbox">
                                        <input class="cascader-input" type="text" autocomplete="off" value="" name="intention_des" readonly placeholder="请选择"><i class="fa fa-set"></i>
                                        <input class="sid" type="hidden" name="intention_id"  value="0">
                                    </div>
                                    <div class="cascader-list none">
                                        <ul class="cascader-menu">
                                        </ul>
                                    </div>
                                </div> -->
                                <el-cascader
                                    placeholder="请选择"
                                    size="small"
                                    :options="options"
                                    @change="handlechange_yxcx"
                                    filterable
                                ></el-cascader>
                                <input id="intention_id" name="intention_id" type="hidden" value="0">
                            </div>
                        </div>
                        <div class="form-group col-md-6 col-sm-6">
                            <label for="" class="control-label col-sm-4 t-r">拟购时间：</label>
                            <div class="col-md-8">
                                <select class="form-control col-md-3 mr-10" name="planned_purchase_time_id">
                                    <option value="0" selected="selected">请选择</option>
                                    <?php if (!empty($model['planned_purchase_time'])) {
                                        foreach ($model['planned_purchase_time'] as $v) { ?>
                                            <option value="<?php echo $v['id']; ?>"><?php echo $v['name']; ?></option>
                                        <?php }
                                    } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6 col-sm-6">
                            <label for="" class="control-label col-sm-4 t-r">购买方式：</label>
                            <div class="col-md-8">
                                <select class="form-control" name="buy_type">
                                    <option value="0" selected="selected">请选择</option>
                                    <?php if (!empty($model['buy_type'])) {
                                        foreach ($model['buy_type'] as $v) { ?>
                                            <option value="<?php echo $v['id']; ?>"><?php echo $v['name']; ?></option>
                                        <?php }
                                    } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-sm reset_but" >取消</button>
                <button type="button" id="submit"  class="btn btn-sm btn-primary">保存</button>
            </div>
        </div>



    </form>
</div>

<script type="text/javascript">

    $(function () {
        //地区
//        $("#area").Cascader({
//            dataurl:'/get-json-data-for-select/index?type=getShengShiQu',
//            islevel:true,
//            onchange:function(a){
//            }
//
//        });
        $.post("/get-json-data-for-select/index?type=getShengShiQu",{},function(response){
            new Vue({
                el: '#xfarea',
                data:function(){
                    return {
                        formInline:{
                            desc:[]
                        },
                        options2:response

                    }
                },
                methods: {
                    handlechange_xfarea:function(value){
                        $("#area").val(value);
                    }
                }
            })


        },'json');

        /*//车型
        $("#intention_des").Cascader({
            dataurl:'/get-json-data-for-select/index?type=getCar',
            islevel:true,
            onchange:function(a){

            }

        });*/

        $.post("/get-json-data-for-select/index?type=getCar",{},function(response){
            new Vue({
                el: '#yxcx',
                  data:function(){
                    return {
                        formInline:{
                            desc:[]
                        },
                        options:response

                    }
                },
                methods: {
                    handlechange_yxcx:function(value){
                        $("#intention_id").val(value);
                    }
                }
            })


        },'json');





        //门店
        /*$("#shop").Cascader({
            dataurl:'/get-json-data-for-select/index?type=getOrgInfo',
            islevel:true,
            onchange:function(a){

            }

        });*/
        $.post("/get-json-data-for-select/index?type=getOrgInfos&showAll=1",{},function(response){
            new Vue({
                     el: '#xfmd',
                     data:function() {
                        return {
                            formInline:{
                                desc:[]
                            },
                            options1: response
                        }
                    },
                    methods: {
                      handlechange_xfmd:function(value){
                        $("#shop_id").val(value);
                      }
                    }
                })
            },'json')
        });


     $('#clue_input_type').change(function () {
         $('#clue_input_type_name').val(($('#clue_input_type option:selected').text()));
     })

</script>
