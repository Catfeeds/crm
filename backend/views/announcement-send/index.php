<?php
use common\logic\LinkPager;
$this->registerCssFile('/dist/plugins/tokenfield/jquery-ui.css', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerCssFile('/dist/plugins/tokenfield/bootstrap-tokenfield.css', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerCssFile('/dist/plugins/tokenfield/tokenfield-typeahead.css', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerCssFile('/dist/plugins/select2/select2.min.css', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/tokenfield/jquery-ui.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/tokenfield/typeahead.bundle.min.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/tokenfield/bootstrap-tokenfield.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/js/announcementSend.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/select2/select2.full.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
?>

<!--<link rel="stylesheet" type="text/css" href="/dist/js/announcementSend.js">-->

<section class="content-header">
    <h1 class="page-title">公告管理</h1>
</section>

<!-- Main content -->
<section class="content-body">
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="mb-md clearfix">
        <!--        <div class="col-sm-12 col-xs-12">-->
        <!--            <ul class="nav nav-tabs custom-tab mb-15">-->
        <!--                <li><a href="/profession/index">职业</a></li>-->
        <!--                <li><a href="/source/index" title='广播、户外广告...'>渠道来源</a></li>-->
        <!--                <li><a href="/input-type/index" title='后台导入、后台新建...'>信息来源</a></li>-->
        <!--                <li class="active"><a href="/age-group/index">年龄段</a> </li>-->
        <!--                <li><a href="/planned-purchase-time/index">拟购时间</a></li>-->
        <!--            </ul>-->
        <!--        </div>-->
                <input class="btn btn-primary btn-sm mr-10" onclick="announcementSend.createLayer()" value="发布公告" type="button">
            </div>
            <div class="box-body no-padding">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-list-check">
                        <thead>
                        <tr>
                            <th width="60">序号</th>
                            <th class="t-c" width="25%">公告标题</th>
                            <th class="t-c" width="25%">发布人</th>
                            <th class="t-c" width="25%">发布对象</th>
                            <th class="t-c" width="100">发布时间</th>
<!--                            <th class="t-c" width="100">公告正文</th>-->
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $i = 0;
                        $page = empty($_GET['page']) ? 1 : $_GET['page'];
                        foreach($models as $val)
                        {
                            $i ++
                            ?>
                            <tr>
                                <td style="display: none"><?php echo $val['id'];?></td>
                                <td><?php echo  (($page - 1) * 20) + $i;?></td>
                                <td>
                                    <span class="thisDataSpan" style="display:none">
                                        <?php echo json_encode($val); ?>
                                    </span>
                                    <a onclick="announcementSend.lookLayer($(this));" href="javascript:;" intentionId="<?php echo $val['id'];?>"><?php echo $val['title'];?></a>
                                </td>
                                <td class="t-c"><?php echo $val['send_person_name'];?></td>
                                <td>
                                    <?php echo $val['addressee_des'];?>
                                    <span style="color: #00a65a" data-toggle="modal" data-target="#myModal2" onclick="ShowAddresseeDes('<?php echo $val['addressee_id'];?>')">门店列表<span>
                                </td>
                                <td class="t-c"><?php echo date('Y-m-d H:i',$val['send_time']);?></td>
<!--                                <td class="t-c va-m">-->
<!--                                    <div class="operation">-->
<!--                                            <span class="thisDataSpan" style="display:none">-->
<!--                                                --><?php //echo json_encode($val); ?>
<!--                                            </span>-->
<!--                                        <a onclick="announcementSend.lookLayer($(this));" href="javascript:;" intentionId="--><?php //echo $val['id'];?><!--">点击查看</a>-->
<!--                                    </div>-->
<!--                                </td>-->
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="box-footer pdt-md pull-right bd-t0">
                <?php
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

<!--新建弹出层 start-->
<div class="modal fade in" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;">
    <div class="modal-dialog notice-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" onclick="announcementSend.cancelLayer();"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">编辑公告</h4>
            </div>
            <div class="modal-body clearfix">
                <form class="form-horizontal" action="/announcement-send/create" method="post" id="form">
                    <div class="row">
                    <input class="form-control" id="input_id" placeholder="公告的id，编辑数据用到" type="hidden">

                        <div class="col-md-12 col-sm-12 mb-15">
                            <label for="" class="control-label col-sm-3"><span class="c-red mr-5">*</span>收件人：</label>
                            <?php if(empty($shopList)):?>
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
                                            <?php foreach ($companyArray as $company):?>
                                                <label class="form-label">
                                                    <input name="active_company_ids" type="checkbox" value="<?=$company['id']?>">
                                                    <?=$company['name']?>
                                                </label>
                                            <?php endforeach;?>
                                        </div>

                                        <div class="addressee-tab-panel mt-md none" style="margin-top:10px;">
                                            <select name="active_area_ids" class="form-control select2" multiple="multiple" data-placeholder="请选择" style="width: 100%;">
                                                <?php foreach ($areaList as $k =>$area):?>
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
                                                        <?php foreach ($shopList as $k =>$shop):?>
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
                            <div style="display:none;">
                                <select>
                                    <option class="addressee" value="1" onclick="add_addess(this)">11</option>
                                    <option class="addressee" value="2" onclick="add_addess(this)">22</option>
                                </select>
                            </div>
                            <div class="form-group border-none col-md-12 col-sm-12">
                                <label for="inputname" class="control-label col-sm-2"><span class="c-red mr-5">*</span>标题：</label>
                                <div class="col-sm-10">
                                    <input class="form-control" id="input_title" name="title" placeholder="请输入标题" type="text">
                                    <span class="c-red" id="title-error"></span>
                                </div>
                            </div>
                            <div class="form-group border-none col-md-12 col-sm-12">
                                <label for="inputname" class="control-label col-sm-2"><span class="c-red mr-5">*</span>签名：</label>
                                <div class="col-sm-10">
<!--                                    --><?php //print_r( Yii::$app->user->identity);?>
<!--                                    <input class="form-control" id="input_send_person" type="text" value="--><?php //echo Yii::$app->user->identity->name;?><!--" readonly>-->
                                    <input class="form-control" id="input_send_person" name="send_person" type="text" placeholder="请输入签名" value="">
                                    <span class="c-red" id="send-person-error"></span>
                                </div>
                            </div>
                            <div class="form-group border-none col-md-12 col-sm-12">
                                <label for="inputname" class="control-label col-sm-2"><span class="c-red mr-5">*</span>公告内容：</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" id="input_content" name="content" rows="10" placeholder="请输入内容"></textarea>
                                    <span class="c-red" id="content-error"></span>
                                </div>
                            </div>
                        </div>
                </form>
            </div>
            <div class="modal-footer">
                <button onclick="announcementSend.cancelLayer();" type="button" class="btn btn-default btn-sm" data-dismiss="modal">取消</button>
                <button onclick="announcementSend.submitForm();" type="button" class="btn btn-primary btn-sm">确认</button>
<!--                <button onclick="$('#form').submit()" type="button" class="btn btn-primary btn-sm">确认</button>-->
            </div>
        </div>
    </div>
</div>
<!--新建弹出层 start-->


<!--查看弹出层 start-->
<div class="modal fade in" id="look_myModal" tabindex="-1" role="dialog" aria-labelledby="look_myModalLabel" style="display: none;">
    <div class="modal-dialog notice-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button onclick="announcementSend.look_cancelLayer();" type="button" class="close" data-dismiss="modal" aria-label="Close" ><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">公告详情</h4>
            </div>
            <div class="modal-body clearfix">
                <form class="form-horizontal">
                    <input class="form-control" id="look_id" placeholder="公告的id，编辑数据用到" type="hidden">
                        <div class="row">
                            <div class="form-group border-none col-md-12 col-sm-12">
                                <label for="inputaddressee_des" class="control-label col-sm-2 notice-label"><span class="c-red mr-5">*</span>公告门店：</label>
                                <div class="col-sm-10 pdl-0">
                                    <span id="look_addressee_des"></span>
                                </div>
                            </div>
                            <div class="form-group border-none  col-md-12 col-sm-12">
                                <label for="inputtime" class="control-label col-sm-2 notice-label"><span class="c-red mr-5">*</span>时间：</label>
                                <div class="col-sm-10 pdl-0">
                                    <span id="look_send_time"></span>
                                </div>
                            </div>
                            <div class="form-group border-none  col-md-12 col-sm-12" >
                                <div class="col-md-12 font-md c-gray" id="look_content" style="line-height: 1.5;">

                                </div>
                            </div>
<!--                            <div class="form-group border-none  col-md-12 col-sm-12">-->
<!--                                <label for="inputname" class="control-label col-sm-2"><span class="c-red mr-5">*</span>标题：</label>-->
<!--                                <div class="col-sm-10">-->
<!--                                    <span id="look_title"></span>-->
<!--                                </div>-->
<!--                            </div>-->
                            <div class="form-group border-none  col-md-12 col-sm-12">
                                <div class="col-sm-12 t-r font-md c-gray"  id="look_send_person_name">
                                </div>
                            </div>

                        </div>
                </form>
            </div>
            <div class="modal-footer">
                <button onclick="announcementSend.look_cancelLayer();" type="button" class="btn btn-primary btn-sm" data-dismiss="modal">确定</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">公告发布范围</h4>
            </div>
            <div class="modal-body clearfix">
<!--                <div class="alert alert-warning alert-dismissible">-->
<!--                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>-->
<!--                    <div>-->
<!--                        <i class="icon fa fa-warning pull-left mb-lg"></i>-->
<!--                        公告-->
<!--                    </div>-->
<!--                </div>-->

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
                                                    <label><input class="shop_checkbox" id="shop_<?php echo $item3['id']?>" name="Fruit" type="checkbox" disabled="disabled" value=""/><?php echo $item3['name']?></label>
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
$_SCRIPT = <<<___SCRIPT
    $("body").delegate("#addressee label","click",function(){
       var index = $(this).index();
           $(".addressee-tab-panel").eq(index).removeClass("none").siblings().addClass("none");
    });

    //标签
    $('#myModal,#look_myModal').on('shown.bs.modal', function (e) {
        $('#tokenfield').tokenfield({
          autocomplete: {
            source:['上海店','南京店','江苏店','杭州店','苏州店','乌鲁木齐店','南京店','江苏店','杭州店','苏州店','内蒙古店','南京店','江苏店','杭州店','苏州店','乌鲁木齐店'],
            delay: 100
          },
          showAutocompleteOnFocus: true
        });
    });
    $(".select2").select2();
___SCRIPT;
$this->registerJs($_SCRIPT);
?>


