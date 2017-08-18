<?php
    $this->title = '公海线索';

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

    $this->registerJsFile('/dist/plugins/dataTables/jquery.dataTables.min.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);
    $this->registerJsFile('/dist/plugins/dataTables/jquery.dataTables.bootstrap.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);
    $this->registerJsFile('/dist/js/meTables.js', [
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

    $this->registerCssFile('/dist/plugins/dataTables/css/jquery.dataTables.min.css', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);

?>
<section class="content-header">
    <h1 class="page-title">公海线索</h1>
</section>
<section class="content-body">
    <form action="/" method="post" id="search-form">
    <div class="box advanced-search-form mb-lg">
        <div class="row">
            <div class="form-group col-lg-4 col-md-6">
                <label for="" class="control-label col-sm-3 t-r">关 键 字：</label>
                <div class="col-sm-9 col-md-9">
                    <input class="form-control" type="text" id="keyword" name="keyword" value="" placeholder="姓名/手机/车型">
                </div>
            </div>
            <div class="form-group col-lg-4 col-md-4">
                <label for="" class="control-label col-sm-3 t-r">创建日期：</label>
                <div class="col-sm-9 col-md-9">
                    <div class="calender-picker double-time" id="datetime" style="height:34px;padding:5px 15px;">
                        <div class="timeinputbox">
                            <input type="text" id="addtime" name="create_time" value="" placeholder="请输入时间" style="width:100%;padding-left:0;">
                            <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group col-lg-4 col-md-6">
                <label for="" class="control-label col-sm-3 t-r">所在地：</label>
                <div class="col-sm-9 col-md-9" id="xfarea">
                    <el-cascader
                            placeholder="请选择地区"
                            size="small"
                            :options="options2"
                            @change="handlechange_xfarea"
                            filterable
                            clearable
                    ></el-cascader>
                    <input id="area" name="area" type="hidden" value="">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-lg-4 col-md-6">
                <label for="" class="control-label col-sm-3 t-r">车系：</label>
                <div class="col-sm-9 col-md-9" id="yxcx">
                    <el-cascader
                            placeholder="请选择车系"
                            size="small"
                            :options="options"
                            @change="handlechange_yxcx"
                            filterable
                            clearable
                    ></el-cascader>
                    <input id="intention_id" name="intention_id" type="hidden" placeholder="车系信息">
                </div>
            </div>
            <div class="form-group col-lg-4 col-md-4">
                <label for="" class="control-label col-sm-3 t-r">已战败次数：</label>
                <div class="col-sm-9 col-md-9">
                    <input class="form-control" type="text" name="defeat_num" placeholder="请输入战败次数" />
                </div>
            </div>
            <div class="form-group col-lg-4 col-md-6">
                <div class="pull-right mr-15">
                    <input class="btn btn-primary btn-sm pull-left mr-15" value="查询" type="submit">
                    <input class="btn btn-default btn-sm pull-left" value="清除" id="clear" type="button">
                </div>
            </div>
        </div>
    </div>
    <div class="mb-md">
        <input class="btn btn-primary btn-sm mr-15" value="下发到门店" onclick="m.issued()" type="button">
        <input class="btn btn-primary btn-sm mr-15" value="删除" onclick="m.deleteAll()" type="button">
    </div>
    </form>
    <div class="box box-none-border">
        <div class="box-body no-padding">
            <table class="table table-hover table-bordered" id="show-table" style="margin-bottom: 20px;"></table>
        </div>
    </div>
</section>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">公海线索下发到门店-选择门店</h4>
            </div>
            <div class="modal-body">
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
                                    clearable
                            ></el-cascader>
                            <input class="required" id="shop_id" name="shop_id" type="hidden" value="">
                            <input type="hidden" value="" id="ids" name="ids" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary" id="submit-issued">确定下发</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
<style>
    .dataTables_paginate {float: right !important;}
    .isHide {display: none},
</style>
<?php $this->beginBlock('javascript') ?>
<script type="text/javascript">

    /**
     * 处理手机号
     * @param  data
     * @returns {string}
     */
    function handlePhone(data) {
        var str = '--';
        if (data) {
            str = data.substring(0, 3) + '****' + data.substring(-4, 4)
        }

        return str;
    }

    var objShop, objCar, objArea;
    var m = meTables({
        title: "公海线索",
        operations: {isOpen: false},
        searchType: "top",
        search: {render: false},
        table: {
            "bLengthChange": false,
            "iDisplayLength": 20,
            "aoColumns":[
                {"title": "序号", "data": "id", "sName": "id", "createdCell": function(td, data, array, row) {
                    $(td).html(m.table.page.info().page * m.table.page.info().length + row + 1);
                }, "bSortable": false},
                {"title": "进入公海时间", "data": "create_time", "defaultOrder": "desc", "sName": "create_time", "createdCell" : mt.dateTimeString},
                {"title": "姓名", "data": "customer_name", "sName": "customer_name", "bSortable": false, "createdCell": function(td, data) {
                    $(td).html(data ? data : "--");
                }},
                {"title": "手机号码", "data": "customer_phone", "sName": "customer_phone","bSortable": false,"createdCell": function(td, data) {
                    $(td).html(handlePhone(data));
                }},
                {"title": "所在地", "data": "area_name", "sName": "area_name","bSortable": false, "createdCell": function(td, data) {
                    $(td).html(data ? data : "--");
                }},
                {"title": "车系", "data": "intention_des", "sName": "intention_des", "bSortable": false, "createdCell": function(td, data) {
                    $(td).html(data ? data : "--");
                }},
                {"title": "意向车型", "data": "chexing_des", "sName": "chexing_des",  "bSortable": false,
                    "createdCell": function(td, data) {
                        $(td).html(data ? data : "--");
                    }},
                {"title": "进入公海原因", "data": "reason_des", "sName": "reason_des", "bSortable": false, "createdCell": function(td, data) {
                    $(td).html(data ? data : "--");
                }}
            ],
            "drawCallback": function(){
                $("#show-table_paginate ul.pagination").prepend("<li class='paginate_button'><a> 共" + m.table.page.info().recordsDisplay + " 条 </a></li>");
            }
        }
    });

    mt.fn.extend({
        issued: function() {
            var self = this, data = [], obj = $("#ids");
            obj.val("");
            objShop.$children[0].handlePick([], true);

            // 数据添加
            $(this.options.sTable + " tbody input:checkbox:checked").each(function(){
                var row = parseInt($(this).val()),
                    tmp = self.table.data()[row] ? self.table.data()[row] : null;
                if (tmp && tmp[self.options.pk]) data.push(tmp[self.options.pk]);
            });

            // 数据为空提醒
            if (data.length < 1)  {
                return layer.msg(self.getLanguage("noSelect"), {icon:5});
            }

            obj.val(data.join(","));
            $("#myModal").modal({backdrop: "static"});
        }
    });

    $(function(){
        m.init();

        $("#show-table_info").hide();

        // 时间处理
        var config = {
            "opens": "left",
            "autoApply": true,
            "dateLimit": {"months": 6},
            "autoUpdateInput": false,
            "locale": {
                "format": 'YYYY-MM-DD',
                'daysOfWeek': ['日', '一', '二', '三', '四', '五','六'],
                'monthNames': ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
                'firstDay': 1
            }
        };

        // 时间处理
        $('#create_time').daterangepicker(config).on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + " - " + picker.endDate.format('YYYY-MM-DD'));
        });

        $("#clear").click(function(){
            $("#search-form").get(0).reset();
            objArea.$children[0].handlePick([], true);
            objCar.$children[0].handlePick([], true);
            m.search(true);
        });


        // 确定下发
        $("#submit-issued").click(function(){
            // 获取值
            var ids = $("#ids").val(), shops = $("#shop_id").val();
            if (ids && shops) {
                mt.ajax({
                    url: "<?=\yii\helpers\Url::toRoute(['issued'])?>",
                    data: {
                        "ids": ids,
                        "shops": shops
                    },
                    type: "POST",
                    dataType: "json"
                }).done(function(response) {

                    if (response.errCode === 0) {
                        if (response.data.error_number > 0) {
                            response.errCode = 1;
                            response.errMsg += "<br/>" + response.data.error_info;
                        }

                        $("#myModal").modal("hide");
                        m.search(false);
                    }

                    layer.msg(response.errMsg, {icon: response.errCode === 0 ? 6 : 5});
                });
            } else {
                layer.msg("没有选择门店或者没有选择线索", {icon: 5});
            }
        });

        // 门店
        $.ajax({
            url: "/get-json-data-for-select/index?type=getOrgInfos",
            type: "POST",
            dataType: "json",
            success: function(response) {
                objShop = new Vue({
                    el: '#xfmd',
                    data: function(){
                        return {
                            formInline:{
                                desc:[]
                            },
                            options1: response
                        };
                    },
                    methods: {
                        handlechange_xfmd: function(value){
                            $("#shop_id").val(value);
                        }
                    }
                })
            }
        });

        // 车系
        $.ajax({
            url: "/get-json-data-for-select/index?type=getCar",
            type: "POST",
            dataType: "json",
            success: function(response) {
                objCar = new Vue({
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
                });
            }
        });

        // 所在地址
        objArea = new Vue({
            el: '#xfarea',
            data:function(){
                return {
                    formInline:{
                        desc:[]
                    },
                    options2: <?=$areas?>

                }
            },
            methods: {
                handlechange_xfarea:function(value){
                    $("#area").val(value);
                }
            }
        });
    });
</script>
<?php $this->endBlock(); ?>
