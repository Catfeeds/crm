<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/4/10
 * Time: 9:50
 */

use yii\helpers\Url;

$page = Yii::$app->request->get('page') ? : 1;
?>

<section class="content-header">
    <h1 class="page-title">激励管理</h1>
</section>

<section class="content-body">
    <div class="row">
        <div class="col-md-12">
            <div class="mb-15">
                <input type="button" class="btn btn-primary btn-sm mr-15" value="新增激励" data-toggle="modal" data-target="#myModal">
                <input type="button" class="btn btn-primary btn-sm mr-15" value="正在进行激励的门店" data-toggle="modal" data-target="#myModal2">
            </div>
            <div class="box box-none-border">
                <div class="box-body no-padding">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-list-check">
                            <thead>
                            <tr>
                                <th width="60">序号</th>
                                <th>激励名称</th>
                                <th>创建人</th>
                                <th>结束人</th>
                                <th>激励金额</th>
                                <th>开始时间</th>
                                <th>结束时间</th>
                                <th>状态</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($models as $k => $model) :?>
                            <tr>
                                <td><?=($k+1) +  20 * ($page - 1)?></td>
                                <td><a href="<?=Url::to(['view', 'id' => $model->id])?>"><?=$model->name?></a></td>
                                <td><?=$model->create_person?></td>
                                <td><?=$model->end_person ? : '--'?></td>
                                <td><?=\backend\logic\ExcitationLogic::instance()->getTotalMoney($model->id)?>/<?=$model->money?></td>
                                <td><?=date('Y-m-d H:i', strtotime($model->start_time))?></td>
                                <td><?=($model->end_time == '0000-00-00 00:00:00') ? '--' : date('Y-m-d H:i', strtotime($model->end_time))?></td>
                                <td>
                                    <?php if($model->status == 0):?>
                                        <span class="c-green mr-5">•</span>激励中
                                    <?php else: ?>
                                        <span class="c-red mr-5">•</span>激励结束
                                    <?php endif;?>
                                </td>
                            </tr>
                            <?php endforeach;?>
                            </tbody>
                        </table>
                    </div>
                    <div class="box-footer pdt-md pull-right bd-t0">
                        <div class="paginationbox">
                            <div class="jump pull-right">跳转<input type="text" class="form-control">页</div>
                            <div class="display-page pull-right">
                                <select name="per-page" class="form-control">
                                    <option <?=(Yii::$app->request->get('per-page') == 10) ? 'selected ':'';?>value="10">10条/页</option>
                                    <option <?=(Yii::$app->request->get('per-page') == 20 || !Yii::$app->request->get('per-page')) ? 'selected ':'';?>value="20">20条/页</option>
                                    <option <?=(Yii::$app->request->get('per-page') == 30) ? 'selected ':'';?>value="30">30条/页</option>
                                </select>
                            </div>
                            <ul class="pagination no-margin">
                                <?php

                                // 显示分页
                                echo common\logic\LinkPager::widget([
                                    'pagination' => $pagination,
                                    'firstPageLabel' => "首页",
                                    'prevPageLabel' => '上一页',
                                    'nextPageLabel' => '下一页',
                                    'lastPageLabel' => '末页',
                                ]);
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /.row -->
</section>


<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;">
    <div class="modal-dialog" role="document">
<!--        <form action="" method="post">-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title" id="myModalLabel">新增激励</h4>
                </div>
                <div class="modal-body clearfix">
                    <div class="row">
                        <div class="col-md-12 col-sm-12 mb-15">
                            <label for="" class="control-label col-sm-3"><span class="c-red mr-5">*</span>激励对象：</label>
                            <?php if(empty($all['shopList'])):?>
                                <div class="col-sm-8"><span>无门店可选</span></div>
                            <?php else:?>
                            <div class="col-sm-8" id="shop_ids">
                                <div class="btn-group custom-btn-group" id="addressee" data-toggle="buttons">
                                    <label class="btn btn-default btn-sm active">
                                        <input type="radio" value="all" name="options" id="option1" autocomplete="off" checked="">全部
                                    </label>
                                    <label class="btn btn-default btn-sm">
                                        <input type="radio" value="company" name="options" id="option2" autocomplete="off">
                                        公司
                                    </label>
                                    <label class="btn btn-default btn-sm">
                                        <input type="radio" value="area" name="options" id="option2" autocomplete="off">大区</label>
                                    <label class="btn btn-default btn-sm">
                                        <input type="radio" value="shop" name="options" id="option3" autocomplete="off">门店</label>
                                </div>
                                <div class="addressee-tab">
                                    <div class="addressee-tab-panel">
                                    </div>
                                    <div class="addressee-tab-panel pd-sm none">
                                        <?php foreach ($all['companyList'] as $company):?>
                                            <label class="form-label">
                                                <input name="active_shop_ids" type="checkbox" value="<?=$company['id']?>">
                                                <?=$company['name']?>
                                            </label>
                                        <?php endforeach;?>
                                    </div>

                                    <div class="addressee-tab-panel mt-md none" style="margin-top:10px;">
                                        <select name="active_area_ids" class="form-control select2" multiple="multiple" data-placeholder="请选择" style="width: 100%;">
                                            <?php foreach ($all['areaList'] as $k =>$area):?>
                                                <optgroup label="<?=$k?>">
                                                    <?php foreach ($area as $v):?>
                                                        <option value="<?=$v['id']?>"><?=$v['name']?></option>
                                                    <?php endforeach;?>
                                                </optgroup>
                                            <?php endforeach;?>
                                        </select>
                                    </div>
                                    <div class="addressee-tab-panel mt-md none" style="margin-top:10px;">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <select name="active_shop_ids" class="form-control select2" multiple="multiple" data-placeholder="请选择" style="width: 100%;">
                                                    <?php foreach ($all['shopList'] as $k =>$shop):?>
                                                        <optgroup label="<?=$k?>">
                                                        <?php foreach ($shop as $v):?>
                                                            <option value="<?=$v['id']?>"><?=$v['name']?></option>
                                                        <?php endforeach;?>
                                                        </optgroup>
                                                    <?php endforeach;?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif?>
                        </div>
                        <div class="form-group col-md-12 col-sm-12">
                            <label for="" class="control-label col-sm-3"><span class="c-red mr-5">*</span>激励名称：</label>
                            <div class="col-sm-8"><input name="name" type="text" class="form-control"></div>
                        </div>
                        <div class="form-group col-md-12 col-sm-12">
                            <label for="" class="control-label col-sm-3"><span class="c-red mr-5">*</span>激励总额(元)：</label>
                            <div class="col-sm-8"><input name="money" type="text" class="form-control" value=""></div>
                        </div>
                        <div class="form-group col-md-12 col-sm-12">
                            <label for="" class="control-label col-sm-12" id="err" style="text-align:left;">
                                <span class="c-red mr-5">*</span>细分激励(至少设置一项)：
                            </label>
                        </div>
                        <div class="form-group col-md-12 col-sm-12 mb-sm">
                            <label class="control-label col-sm-3">新建线索</label>
                            <div class="col-sm-6"><input name="clue_price" type="text" class="form-control" value=""></div>
                            <label class="control-label money-label">元<span class="tit">（建议值0.5元）</span></label>
                        </div>
                        <div class="form-group col-md-12 col-sm-12 mb-sm">
                            <label class="control-label col-sm-3">线索转化</label>
                            <div class="col-sm-6"><input name="clue_to_intention_price" type="text" class="form-control" value=""></div>
                            <label class="control-label money-label">元<span class="tit">（建议值0.5元）</span></label>
                        </div>
                        <div class="form-group col-md-12 col-sm-12 mb-sm">
                            <label class="control-label col-sm-3">新建意向客户</label>
                            <div class="col-sm-6"><input name="new_intention_price" type="text" class="form-control" value=""></div>
                            <label class="control-label money-label">元<span class="tit">（建议值1元）</span></label>
                        </div>
                        <div class="form-group col-md-12 col-sm-12 mb-sm">
                            <label class="control-label col-sm-3">完成电话任务</label>
                            <div class="col-sm-6"><input name="finish_phone_task_price" type="text" class="form-control" value=""></div>
                            <label class="control-label money-label">元<span class="tit">（建议值0.5元）</span></label>
                        </div>
                        <div class="form-group col-md-12 col-sm-12 mb-sm">
                            <label class="control-label col-sm-3">客户到店</label>
                            <div class="col-sm-6"><input name="to_shop_price" type="text" class="form-control" value=""></div>
                            <label class="control-label money-label">元<span class="tit">（建议值1元）</span></label>
                        </div>
                        <div class="form-group col-md-12 col-sm-12 mb-sm">
                            <label class="control-label col-sm-3">客户试驾</label>
                            <div class="col-sm-6"><input name="to_home_price" type="text" class="form-control" value=""></div>
                            <label class="control-label money-label">元<span class="tit">（建议值5元）</span></label>
                        </div>
                        <div class="form-group col-md-12 col-sm-12 mb-sm">
                            <label class="control-label col-sm-3">客户订车</label>
                            <div class="col-sm-6"><input name="dingche_price" type="text" class="form-control" value=""></div>
                            <label class="control-label money-label">元<span class="tit">（建议值10元）</span></label>
                        </div>
                        <div class="form-group col-md-12 col-sm-12 mb-sm">
                            <label class="control-label col-sm-3">客户成交</label>
                            <div class="col-sm-6"><input name="jiaoche_price" type="text" class="form-control" value=""></div>
                            <label class="control-label money-label">元<span class="tit">（建议值20元）</span></label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">取消</button>
                    <button id="submit" type="submit" class="btn btn-primary btn-sm">保存</button>
                </div>
            </div>

<!--        </form>-->
    </div>
</div>

<div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">正在进行激励的门店</h4>
            </div>
            <div class="modal-body clearfix">
                <div class="alert alert-warning alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <div>
                        <i class="icon fa fa-warning pull-left mb-lg"></i>
                        括号内为该激励在列表中的序号
                    </div>
                </div>

                    <?php foreach ($tree as $item){
                        ?>
                    <div class="company">
                        <div class="title"><?php echo $item['name']?></div>
                            <dl class="dl-horizontal">
                            <?php 
                                if(isset($item['child']) && $item['child'])
                                {
                                    foreach ($item['child'] as $item2)
                                    {
                            ?>
                                        <dt><?php echo $item2['name']?>:</dt>
                                                <dd>
                                                <?php 
                                                    if(isset($item2['child']) && $item2['child'])
                                                    {
                                                        foreach ($item2['child'] as $item3)
                                                        {
                                                    ?>
                                                        <?php if(!empty($item3['e_id'])){?>
                                                            <label><input name="Fruit" type="checkbox" disabled="disabled" value="" checked /><?php echo $item3['name'].'('.$item3['e_id'].')'?></label>
                                                        <?php }else{?>
                                                            <label><input name="Fruit" type="checkbox" disabled="disabled" value="" /><?php echo $item3['name'].'(--)'?></label>
                                                        <?php }?>
                                                <?php 
                                                        }
                                                    }
                                                ?>
                                        </dd>
                            <?php 
                                    }
                                }
                            ?>
                            </dl>
                    </div>
                    <?php }?>

            </div>
        </div>
             </div>
                </div>


<?php

$this->registerCssFile('/dist/plugins/select2/select2.min.css', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);

$this->registerJsFile('/dist/plugins/daterangepicker/moment.min.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/daterangepicker/daterangepicker.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/select2/select2.full.min.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);

$_SCRIPT = <<<_SCRIPT
    var multi = $(".select2").select2();

    $("body").delegate("#addressee label","click",function(){
       var index = $(this).index();
           $(".addressee-tab-panel").eq(index).removeClass("none").siblings().addClass("none");
           $("input:checkbox:checked").attr('checked',false);
           multi.val(null).trigger("change");

    });

    $('#submit').click(function () {
        //$(this).unbind('click');
        var isCheck = true;
        var name = checkRequired('name', '激励名称不能为空');
        var money = isDecimal('money', '激励总额格式不正确');
        var type = $("input[name='options']:checked").val();

        var clue_price = $("input[name=clue_price]").val();
        var clue_to_intention_price = $("input[name=clue_to_intention_price]").val();
        var new_intention_price = $("input[name=new_intention_price]").val();
        var finish_phone_task_price = $("input[name=finish_phone_task_price]").val();
        var to_shop_price = $("input[name=to_shop_price]").val();
        var to_home_price = $("input[name=to_home_price]").val();
        var dingche_price = $("input[name=dingche_price]").val();
        var jiaoche_price = $("input[name=jiaoche_price]").val();
        
        var price = [];
        price.push("clue_price");
        price.push("clue_to_intention_price");
        price.push("new_intention_price");
        price.push("finish_phone_task_price");
        price.push("to_shop_price");
        price.push("to_home_price");
        price.push("dingche_price");
        price.push("jiaoche_price");
        var length = price.length;
        for(var i=0;i<price.length;i++) {
            var value = $("input[name="+price[i]+"]").val();
            if(value){
                if(/^-?\d+(\.\d+)?$/.test(value)){
                    $("input[name="+price[i]+"]").parent().find("#error").remove();
                } else {
                    if ($("input[name="+price[i]+"]").parent().find("#error").length == 0){
                        $("input[name="+price[i]+"]").parent().append("<span style='color:red' id='error'>*格式不正确</span>");
                    }
                    isCheck = false;
                }
            }
        }
        
        if (type == 'all'){
            var shopIds = ["1"];
        } else if (type == 'company'){
            var shopIds = checkShopIds();
        } else if(type == 'area') {
            var shopIds = $("select[name=active_area_ids]").val();
        } else {
            var shopIds = $("select[name=active_shop_ids]").val();
        }
        if(shopIds.length == 0)
            isCheck = false;
        if (!name || !money || !type) {
           isCheck = false;
        }
        if(!clue_price && !clue_to_intention_price && !new_intention_price && !finish_phone_task_price
            && !to_shop_price && !to_home_price && !dingche_price && !jiaoche_price) {
                if ($("#err").find("#error").length == 0){
                    $("#err").append("<span style='color:red' id='error'>*细分激励(至少设置一项)</span>");
                }
                isCheck = false;
            } else {
                $("#err").find("#error").remove();
        }
        if (isCheck){
            $.ajax({
              type: 'POST',
              url: 'create',
              data: {
              "type":type, "shop_ids": shopIds, "name": name, "money": money, "clue_price": clue_price,
              "clue_to_intention_price":clue_to_intention_price, "new_intention_price": new_intention_price,
              "finish_phone_task_price": finish_phone_task_price, "to_shop_price": to_shop_price,
              "to_home_price": to_home_price, "dingche_price":dingche_price, "jiaoche_price":jiaoche_price
              },
              success: function(data){
                if(data.code == 200) {
                    location.reload();
                } else {
                    alert(data.message);
                }
              },
              dataType: "json",
            });
        }
    });

    //判断货币
    function isDecimal( name, message) {
        var value = $("input[name="+name+"]").val();
   
        if(/^-?\d+(\.\d+)?$/.test(value)){
            $("input[name="+name+"]").parent().find("#error").remove();
        }else{
            if ($("input[name="+name+"]").parent().find("#error").length == 0){
                $("input[name="+name+"]").parent().append("<span style='color:red' id='error'>*"+message+"</span>");
            }
            return 0;
        }
        return value;
    }

    function checkRequired( name, message) {
        var value = $("input[name="+name+"]").val();
        if(value){
            $("input[name="+name+"]").parent().find("#error").remove();
        }else{
            if ($("input[name="+name+"]").parent().find("#error").length == 0){
                $("input[name="+name+"]").parent().append("<span style='color:red' id='error'>*"+message+"</span>");
            }
            return false;
        }
        return value;
    }

    function checkShopIds(){
        var active_shop_ids = new Array();
        $("input[name='active_shop_ids']:checked").each(function () {
            active_shop_ids.push(this.value);
        });
        if (active_shop_ids.length == 0) {
            if ($("#shop_ids").find("#error").length == 0){
                $("#shop_ids").append("<span style='color:red' id='error'>*激励对象不能为空</span>");
            }
            return false;
        } else {
            $("#shop_ids").find("#error").remove();
        }
        return active_shop_ids;
    }

    function GetRequest() {
         var url = location.search;
         var theRequest = new Object();
         if (url.indexOf("?") != -1) {
            var str = url.substr(1);
            strs = str.split("&");
            for(var i = 0; i < strs.length; i++) {
               theRequest[strs[i].split("=")[0]]=decodeURI(strs[i].split("=")[1]);
            }
         }
         return theRequest;
    }

    $("select[name=per-page]").change(function(){
        var param = GetRequest();
        var new_param = '?';
        var len = Object.keys(param).length;
        var i = 1;
        if(len == 0) {
           new_param += 'per-page=' + $(this).val();
        } else {
            var flag = 1;
            for(var key in param){
                if (key == 'per-page') {
                    flag = 0;
                   new_param += key+'='+$(this).val();
                } else {
                   new_param += key+'='+param[key];
                }
                if (i != len) {
                    new_param += '&';
                }
                i++;
            }
            if(flag == 1) {
                new_param += key+'='+$(this).val();
            }
        }
        var url = location.href;

        str = url.split("?");
        location.href = str[0]+new_param;
    });
_SCRIPT;

$this->registerJs($_SCRIPT);
?>

