<?php

use yii\helpers\Html;

$this->title = '客户查询';

$this->registerJsFile('/dist/plugins/daterangepicker/moment.min.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
    ]);
$this->registerJsFile('/dist/plugins/daterangepicker/daterangepicker.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
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

$this->registerCssFile('/dist/plugins/select2/select2.min.css', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/select2/select2.full.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);

?>
<section class="content-header">
    <h1 class="page-title">客户查询</h1>
</section>
<section class="content-body">
    <form action="/" method="post" id="search-form">
        <div class="box advanced-search-form mb-lg">
            <div class="row">
                <div class="form-group col-lg-4 col-md-6">
                    <label for="" class="control-label col-sm-3 t-r">区域&门店：</label>
                    <div class="col-md-8"  id="orgSelect">
                        <el-cascader
                                placeholder="请选择"
                                size="small"
                                :options="options1"
                                v-model="selectedOptions3"
                                @change="handlechange_shopid"
                                change-on-select
                                filterable
                                clearable
                        ></el-cascader>
                        <input id="input-shop_id" name="shop_id" type="hidden">
                    </div>
                </div>
                <div class="form-group col-lg-4 col-md-6">
                    <label for="" class="control-label col-sm-3 t-r">关键字：</label>
                    <div class="col-sm-9 col-md-9">
                        <input class="form-control" type="text" id="keyword" name="keyword" value="" placeholder="姓名/手机/顾问">
                    </div>
                </div>
                <div class="form-group col-lg-4 col-md-4">
                    <label for="" class="control-label col-sm-3 t-r">线索创建日期：</label>
                    <div class="col-sm-9 col-md-9">
                        <div class="calender-picker double-time" id="datetime" style="height:34px;padding:5px 15px;">
                            <div class="timeinputbox">
                                <input type="text" id="input-create_time" class="date-time" name="create_time" value="" placeholder="请输入时间" style="width:100%;padding-left:0;">
                                <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-lg-4 col-md-4">
                    <label for="" class="control-label col-sm-3 t-r">客户状态：</label>
                    <div class="col-sm-9 col-md-9">
                        <?=\yii\helpers\Html::dropDownList('status', 'All', $clueStatus, [
                            'class' => 'form-control',
                            'id' => 'select-status'
                        ])?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-lg-12 col-md-12">
                    <div class="pull-right mr-15">
                        <input class="btn btn-primary btn-sm pull-left mr-15" value="查询" type="submit">
                        <input class="btn btn-default btn-sm pull-left" value="清除" id="clear" type="button">
                        <a href="javascript:;" id="advanced-search" class="btn">高级搜索</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="mb-md">
            <input class="btn btn-primary btn-sm mr-15" value="导出列表" onclick="m.export()" type="button">
        </div>
        <!-- 渠道来源 -->
        <input type="hidden" id="input-status" name="clue_status" />
        <!-- 渠道来源 -->
        <input type="hidden" id="input-clue_input_type" name="clue_input_type" />
        <!-- 信息来源 -->
        <input type="hidden" id="input-clue_source" name="clue_source" />
        <!-- 线索认领状态 -->
        <input type="hidden" id="input-clue_claim_status" name="clue_claim_status" />
        <!-- 转为意向时间 -->
        <input type="hidden" id="input-create_card_time" name="create_card_time" />
        <!-- 意向等级 -->
        <input type="hidden" id="input-intention_level_id" name="intention_level_id" />
        <!-- 订车时间 -->
        <input type="hidden" id="input-ding_time" name="ding_time" />
        <!-- 当前订单状态 -->
        <input type="hidden" id="input-order_status" name="order_status" />
        <!-- 交车时间 -->
        <input type="hidden" id="input-jiao_time" name="jiao_time" />
        <!-- 战败时间 -->
        <input type="hidden" id="input-last_fail_time" name="last_fail_time" />
        <!-- 战败类型 -->
        <input type="hidden" id="input-fail_status" name="fail_status" />
        <!-- 战败原因 -->
        <input type="hidden" id="input-fail_reason" name="fail_reason" />
    </form>
    <p id="advanced-p" style="display: none">通过高级搜索筛选出23位客户</p>
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
                <h4 class="modal-title">高级搜索</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" id="advanced-form">
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">区域&门店：</label>
                        <div class="col-sm-8"  id="orgSelect1">
                            <el-cascader
                                    placeholder="请选择(单选)"
                                    size="small"
                                    :options="options1"
                                    v-model="selectedOptions3"
                                    @change="handlechange_shopid"
                                    change-on-select
                                    filterable
                                    clearable
                            ></el-cascader>
                            <input id="vue-shop-id" name="shop_id" type="hidden">
                        </div>
                        <div class="col-sm-2">
                            <a class="btn" onclick="objShop1.$children[0].handlePick([], true);">清除</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">渠道来源：</label>
                        <div class="col-sm-8">
                            <?=Html::dropDownList('clue_input_type', null, $arrInputType, [
                                'class' => 'form-control select2',
                                'multiple' => 'multiple',
                                'id' => 'search-clue-input-type',
                            ])?>
                        </div>
                        <div class="col-sm-2">
                            <a class="btn clear-input">清除</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">信息来源：</label>
                        <div class="col-sm-8">
                            <?=Html::dropDownList('clue_source', null, $arrSources, [
                                'class' => 'form-control select2',
                                'multiple' => 'multiple',
                                'id' => 'search-clue-source'
                            ])?>
                        </div>
                        <div class="col-sm-2">
                            <a class="btn clear-input">清除</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">当前客户状态：</label>
                        <div class="col-sm-8">
                            <?php unset($clueStatus['All']); ?>
                            <?=Html::dropDownList('status', null, $clueStatus, [
                                'class' => 'form-control select2',
                                'multiple' => 'multiple',
                                'id' => 'search-clue-status'
                            ])?>
                        </div>
                        <div class="col-sm-2">
                            <a class="btn clear-input">清除</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">线索创建时间：</label>
                        <div class="col-sm-8">
                            <div class="calender-picker double-time" style="height:34px;padding:5px 15px;">
                                <div class="timeinputbox">
                                    <input type="text" class="date-time" name="create_time" value="" placeholder="请输入时间" style="width:100%;padding-left:0;">
                                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <a class="btn clear-input">清除</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">转为为意向时间：</label>
                        <div class="col-sm-8">
                            <div class="calender-picker double-time" style="height:34px;padding:5px 15px;">
                                <div class="timeinputbox">
                                    <input type="text" class="date-time" name="create_card_time" value="" placeholder="请输入时间" style="width:100%;padding-left:0;">
                                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <a class="btn clear-input">清除</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">意向等级：</label>
                        <div class="col-sm-8">
                            <?=\yii\helpers\Html::dropDownList('intention_level_id', null, $intentions, [
                                'class' => 'form-control select2',
                                'multiple' => 'multiple',
                                'id' => 'search-intention-level-id'
                            ])?>
                        </div>
                        <div class="col-sm-2">
                            <a class="btn clear-input">清除</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">订车时间：</label>
                        <div class="col-sm-8">
                            <div class="calender-picker double-time" style="height:34px;padding:5px 15px;">
                                <div class="timeinputbox">
                                    <input type="text" class="date-time" name="ding_time" value="" placeholder="请输入时间" style="width:100%;padding-left:0;">
                                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <a class="btn clear-input">清除</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">当前订单状态：</label>
                        <div class="col-sm-8">
                            <?=\yii\helpers\Html::dropDownList('order_status', null, $orderStatus, [
                                'class' => 'form-control select2',
                                'multiple' => 'multiple',
                                'style' => 'width:100%',
                                'id' => 'search-order-status'
                            ])?>
                        </div>
                        <div class="col-sm-2">
                            <a class="btn clear-input">清除</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">交车时间：</label>
                        <div class="col-sm-8">
                            <div class="calender-picker double-time" style="height:34px;padding:5px 15px;">
                                <div class="timeinputbox">
                                    <input type="text" class="date-time" name="jiao_time" value="" placeholder="请输入时间" style="width:100%;padding-left:0;">
                                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <a class="btn clear-input">清除</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">战败时间：</label>
                        <div class="col-sm-8">
                            <div class="calender-picker double-time" style="height:34px;padding:5px 15px;">
                                <div class="timeinputbox">
                                    <input type="text" class="date-time" name="last_fail_time" value="" placeholder="请输入时间" style="width:100%;padding-left:0;">
                                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <a class="btn clear-input">清除</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">战败类型：</label>
                        <div class="col-sm-8">
                            <?=\yii\helpers\Html::dropDownList('fail_status', null, $failStatus, [
                                'class' => 'form-control select2',
                                'multiple' => 'multiple',
                                'id' => 'search-fail-status'
                            ])?>
                        </div>
                        <div class="col-sm-2">
                            <a class="btn clear-input">清除</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">战败原因：</label>
                        <div class="col-sm-8">
                            <?=\yii\helpers\Html::dropDownList('fail_reason', null, $failReasons, [
                                'class' => 'form-control select2',
                                'multiple' => 'multiple',
                                'id' => 'search-fail-reason'
                            ])?>
                        </div>
                        <div class="col-sm-2">
                            <a class="btn clear-input">清除</a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="submit-search">确定</button>
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
    var oInputType = <?=$inputTypes?>,
        oSource = <?=$sources?>,
        oAreas = <?=$areas?>,
        oShops = <?=$shops?>,
        oClueStatus = <?=$clueStatusJson?>,
        isAdvancedSearch = false;

    function getParentNameArea(id)
    {
        var strName = "";
        if (id !== 0 && id) {
            if (oAreas[id]) {
                var arr = [oAreas[id].name];
                if (oAreas[id].level != 0) {
                    var str = getParentNameArea(oAreas[id].pid);
                    if (str !== "" && str !== "--") {
                        arr.unshift(str);
                    }
                }

                strName = arr.join("-");
            }
        } else {
            strName = "--";
        }

        return strName;
    }

    function getParentNameShop(id) {
        var strName = "";
        if (id != 0 && id) {
            if (oShops[id]) {
                var arr = [oShops[id].name];
                if (oShops[id].pid != 0) {
                    var str = getParentNameShop(oShops[id].pid);
                    if (str !== "" && str !== "--") {
                        arr.unshift(str);
                    }

                }

                strName = arr.join("-");
            }
        } else {
            strName = "--";
        }

        return strName;
    }

    /**
     *
     * @param str
     * @param number
     */
    function getArray(str, number) {
        if (str !== "--") {
            return str.split("-").slice(-2).join("-");
        }

        return str;
    }

    var url = "<?=\yii\helpers\Url::toRoute(['customer/customer-detail', 'id' => '_ID_'])?>"
    var objShop, objShop1;
    var m = meTables({
        title: "客户查询",
        operations: {isOpen: false},
        searchType: "top",
        search: {render: false},
        bCheckbox: false,
        table: {
            "bLengthChange": false,
            "iDisplayLength": 20,
            "order": false,
            "aoColumns":[
                {"title": "序号", "data": null, "createdCell": function(td, data, array, row) {
                    $(td).html(m.table.page.info().page * m.table.page.info().length + row + 1);
                }, "bSortable": false},
                {"title": "客户姓名", "data": "customer_name", "sName": "create_time", "bSortable": false,"createdCell": function(td, data, array) {
                    data = $.trim(data);
                    data = data && data != "" && data != " " ? data : "--";
                    data = "<a href=\"" + url.replace("_ID_", array.id) + (array.status == 0 ? "&ischeck=1" : "") +"\">" + data + "</a>"
                    $(td).html(data);
                }},
                {"title": "手机号码", "data": "customer_phone", "sName": "customer_phone","bSortable": false},
                {"title": "所在地", "data": "area", "sName": "area","bSortable": false, "createdCell": function(td, data) {
                    $(td).html(data === "0" ? "--" : getParentNameArea(data));
                }},
                {"title": "渠道来源", "data": "clue_input_type", "sName": "clue_input_type", "bSortable": false, "createdCell": function(td, data) {
                    if (data && oInputType[data]) {
                        data = oInputType[data];
                    }
                    $(td).html(data ? data : "--");
                }},
                {"title": "信息来源", "data": "clue_source", "sName": "clue_source", "bSortable": false, "createdCell": function(td, data) {
                    if (data && oSource[data]) {
                        data = oSource[data];
                    }
                    $(td).html(data ? data : "--");
                }},
                {"title": "意向等级", "data": "intention_level_des", "sName": "intention_level_des", "bSortable": false, "createdCell": function(td, data) {
                    $(td).html(data ? data : "--");
                }},
                {"title": "意向车系", "data": "intention_des", "sName": "intention_des",  "bSortable": false,
                    "createdCell": function(td, data) {
                        $(td).html(data ? data : "--");
                    }},
                {"title": "顾问", "data": "salesman_name", "sName": "salesman_name", "bSortable": false, "createdCell": function(td, data) {
                    $(td).html(data ? data : "--");
                }},
                {"title": "门店", "data": "shop_id", "sName": "shop_id", "bSortable": false, "createdCell": function(td, data) {
                    $(td).html(getArray(getParentNameShop(data)));
                }},
                {"title": "提车门店", "data": "new_shop_name", "sName": "new_shop_name", "bSortable": false},
                {"title": "提车顾问", "data": "new_salesman_name", "sName": "new_salesman_name", "bSortable": false},
                {"title": "状态", "data": "status", "sName": "status", "bSortable": false, "createdCell": function(td, data) {
                    $(td).html(oClueStatus[data] ? oClueStatus[data] : "--");
                }}
            ],

            "drawCallback": function(){
                var number = m.table.page.info().recordsDisplay, $p = $("#advanced-p");
                if (isAdvancedSearch) {
                    $p.html("通过高级搜索筛选出" + number + "位客户").show();
                } else {
                    $p.hide();
                }

                $("#show-table_paginate ul.pagination").prepend("<li class='paginate_button'><a> 共" + number + " 条 </a></li>");
            }
        }
    });

    $(function(){
        m.init();

        $("#show-table_info").hide();

        // 高级搜索
        $("#advanced-search").click(function(){
            $("#myModal").modal({backdrop: "static"});
        });

        // 当选择客户状态，需要更新
        $("#select-status").change(function(){
            var v = $(this).val();
            $("#input-status").val(v === "All" ? "" : v);
        });

        // 多选优化
        $(".select2").css("width", "100%").attr("data-placeholder", "请选择(多选)").select2();

        // 清除
        $(".clear-input").click(function(){
            var $div = $(this).parent().prev("div");
            $div.find("select").val("").trigger("change");
            $div.find('input').val("")
        });

        // 提交搜索
        $("#submit-search").click(function(){
            // 表单赋值
            var data = $("#advanced-form").serializeArray(), $fm = $("#search-form");
            for (var x in data) {
                if ($.inArray(data[x].name, [
                    "clue_input_type[]", "clue_source[]",
                    "intention_level_id[]", "fail_status[]",
                    "fail_reason[]", "order_status[]", "status[]"
                ]) !== -1) {
                    continue;
                }

                $fm.find("#input-" + data[x].name).val(data[x].value);
            }

            // 获取值(多选按钮处理)
            $(".select2").each(function(){
                var v = $(this).val(), n = $(this).attr("name");
                if (n) {
                    n = n.replace("[]", "");
                }
                v = $.isArray(v) ? v.join(",") : "";
                $("#input-" + n).val(v);
            });

            isAdvancedSearch = true;
            $("#select-status").val("All");
            $("#myModal").modal("hide");
            m.search(true);
        });

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
        $("input.date-time").daterangepicker(config).on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + " - " + picker.endDate.format('YYYY-MM-DD'));
        });

        $("#clear").click(function(){
            var $fm = $("#search-form");
            $fm.get(0).reset();
            $fm.find("input[type=hidden]").val("");
            objShop.$children[0].handlePick([], true);
            isAdvancedSearch = false;
            m.search(true);
        });

        var defaultSelectString = '';
        var defaultSelectArray = defaultSelectString.split(",");

        // 区域&门店
        objShop = new Vue({
            el: '#orgSelect',
            data:function(){
                return {
                    formInline:{
                        desc:[]
                    },
                    options1 : <?=$orgList?>,
                    selectedOptions3: defaultSelectArray
                }
            },
            methods: {
                handlechange_shopid:function(value){
                    $("#input-shop_id").val(value);
                }
            }
        });

        objShop1 = new Vue({
            el: '#orgSelect1',
            data:function(){
                return {
                    formInline:{
                        desc:[]
                    },
                    options1 : <?=$orgList?>,
                    selectedOptions3: defaultSelectArray
                }
            },
            methods: {
                handlechange_shopid:function(value){
                    $("#vue-shop-id").val(value);
                }
            }
        });
    });
</script>
<?php $this->endBlock(); ?>
