/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function(){
    if($('#orgSelect'))
    {
        //门店切换
         new Vue({
            el: '#orgSelect',
            data:function(){
                return {
                    formInline:{
                        desc:[]
                    },
                    options1 : selectOrgJson,
                    selectedOptions3 : defaultSelectArray
                };
            },
            methods: {
                handlechange_shopid:function(value){
                    $("#shopid").val(value);
                }
            }
        });
    };

    
    //全选
    $(".table-list-check thead th input[type='checkbox']").click(function(){
        if($(this).prop("checked")){
          $(this).prop("checked",true);
          $(this).parents("thead").next().find("tr").each(function(){
             $(this).find("td").eq(0).find("input[type='checkbox']").prop("checked",true);
          });
        }else{
          $(this).prop("checked",false);
          $(this).parents("thead").next().find("tr").each(function(){
             $(this).find("td").eq(0).find("input[type='checkbox']").prop("checked",false);
          });
        }
    });

    //单个全选或取消全选
    $(".table-list-check tbody tr td input[type='checkbox']").click(function(){
        var bol = false;
        var trnum =$(this).parents("tbody").find("tr input[type='checkbox']").length;
        var checkednum =$(this).parents("tbody").find("tr :checked").length;
          if( trnum == checkednum){
              $(".table-list-check thead th input[type='checkbox']").prop("checked",true);
          }else{
              $(".table-list-check thead th input[type='checkbox']").prop("checked",false);
          }
    });

    //创建日期
    if($('#addtime'))
    {
        var config = {"opens": "left", "autoApply": true,"dateLimit": {"months": 6}, "autoUpdateInput": false, "locale": {"format": 'YYYY-MM-DD',
        'daysOfWeek': ['日', '一', '二', '三', '四', '五','六'],
        'monthNames': ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
        'firstDay': 1
    }};
        if($.trim($('#startDateSelect').val()) != '')
        {
            config.startDate = $.trim($('#startDateSelect').val());
            config.endDate = $.trim($('#endDateSelect').val());
            config.autoUpdateInput = true;
        }
        $('#addtime').daterangepicker(config);
    }

    //建卡日期 - 由线索转换为意向客户的时间点
    if($('#createCardTime'))
    {
        var config = {"opens": "left", "autoApply": true,"dateLimit": {"months": 6}, "autoUpdateInput": false, "locale": {"format": 'YYYY-MM-DD',
                    'daysOfWeek': ['日', '一', '二', '三', '四', '五','六'],
                    'monthNames': ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
                    'firstDay': 1
    }};
        if($.trim($('#startCreateCardDate').val()) != '')
        {
            config.startDate = $.trim($('#startCreateCardDate').val());
            config.endDate = $.trim($('#endCreateCardDate').val());
            config.autoUpdateInput = true;
        }
        $('#createCardTime').daterangepicker(config);
    }
    //订车日期
    if($('#orderTime'))
    {
        var config = {"opens": "left", "autoApply": true,"dateLimit": {"months": 6}, "autoUpdateInput": false, "locale": {"format": 'YYYY-MM-DD',
                    'daysOfWeek': ['日', '一', '二', '三', '四', '五','六'],
                    'monthNames': ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
                    'firstDay': 1
    }};
        if($.trim($('#startOrderDate').val()) != '')
        {
            config.startDate = $.trim($('#startCreateCardDate').val());
            config.endDate = $.trim($('#endCreateCardDate').val());
            config.autoUpdateInput = true;
        }
        $('#orderTime').daterangepicker(config);
    }
    //交车日期
    if($('#deliveryTime'))
    {
        var config = {"opens": "left", "autoApply": true,"dateLimit": {"months": 6}, "autoUpdateInput": false, "locale": {"format": 'YYYY-MM-DD',
            'daysOfWeek': ['日', '一', '二', '三', '四', '五','六'],
            'monthNames': ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
            'firstDay': 1
    }};
        if($.trim($('#startDeliveryDate').val()) != '')
        {
            config.startDate = $.trim($('#startDeliveryDate').val());
            config.endDate = $.trim($('#endDeliveryDate').val());
            config.autoUpdateInput = true;
        }
        $('#deliveryTime').daterangepicker(config);
    }
    //选中
    $('#addtime, #createCardTime, #orderTime, #deliveryTime').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + " - " + picker.endDate.format('YYYY-MM-DD'));
    });
    //重新分配
    if($('#chongxinfenpei'))
    {
        $('#chongxinfenpei').click(function(){
            //判断是否有选中，之后显示销售人员选择框
            if($('input[name="checkbox"]:checked').length > 0)
            {
                $('#myModal').show();
            }
            else
            {
                alert('请先勾选');
            }
        });
    }


});

var isAjaxing = false;

var customer_list = {

    //清除搜索条件
    "clearSeachCondition" : function(){
        $("form input[type='text']").each(function(){
            $(this).val('');
        });
//        $('#addtime').val('');
//        $('#so').val('');
    },
    // 导出线索客户数据
    "downloadClueList" : function(){
        var queryString = '';
        queryString += 'searchTime=' + $.trim($('#addtime').val());
        queryString += '&so=' + $.trim($('#so').val());
        queryString += '&isDownload=1';
        queryString += '&shop_id=' + $.trim($('#shopid').val());        
        location.href='/customer/get-clue-customer?' + queryString;
    },
    // 导出意向客户数据
    "downloadIntentionList" : function(){
        var queryString = '';
        queryString += 'createCardTime=' + $.trim($('#createCardTime').val());
        queryString += '&so=' + $.trim($('#so').val());
        queryString += '&isDownload=1';
        queryString += '&shop_id=' + $.trim($('#shopid').val());        
        location.href='/customer/get-intention-customer?' + queryString;
    },
    //导出订车客户
    "downloadOrderList" : function(){
        var queryString = '';
        queryString += 'createCardTime=' + $.trim($('#createCardTime').val());
        queryString += '&orderTime=' + $.trim($('#orderTime').val());
        queryString += '&so=' + $.trim($('#so').val());
        queryString += '&isDownload=1';
        queryString += '&shop_id=' + $.trim($('#shopid').val());        
        location.href='/customer/get-order-customer?' + queryString;
    },
    //导出交车客户
    "downloadSuccessList" : function(){
        var queryString = '';
        queryString += 'createCardTime=' + $.trim($('#createCardTime').val());
        queryString += '&deliveryTime=' + $.trim($('#deliveryTime').val());
        queryString += '&so=' + $.trim($('#so').val());
        queryString += '&isDownload=1';
        queryString += '&shop_id=' + $.trim($('#shopid').val());        
        location.href='/customer/get-success-customer?' + queryString;
    },
    //导出交车客户
    "downloadFailList" : function(){
        var queryString = '';
        queryString += 'searchTime=' + $.trim($('#addtime').val());
        queryString += '&so=' + $.trim($('#so').val());
        queryString += '&isDownload=1';
        queryString += '&shop_id=' + $.trim($('#shopid').val());        
        location.href='/customer/get-fail-customer?' + queryString;
    },
    //导出保有客户数据
    "downloadKeepList" : function(){
        var queryString = '';
        queryString += 'deliveryTime=' + $.trim($('#deliveryTime').val());
        queryString += '&so=' + $.trim($('#so').val());
        queryString += '&isDownload=1';
        queryString += '&shop_id=' + $.trim($('#shopid').val());        
        location.href='/customer/get-keep-customer?' + queryString;
    },
    //意向客户的销售顾问添加一条电话任务
    "addTask": function(){
        if(isAjaxing){return ;}//ajax正在提交中
        var sendData = {"clue_ids" : []};
        $('input[name="checkbox"]:checked').each(function(){
            sendData.clue_ids.push(parseInt($(this).attr('data-id')));
        });
        if(sendData.clue_ids.length == 0)
        {
            alert('请勾选');
            return ;
        }
        isAjaxing = true;
        $.post('/customer/ajax-intention-customer-add-task', sendData, function(res){
            isAjaxing = false;
            if(res.code == 0)
            {
                alert('推送任务成功！');
                location.reload(true);//刷新页面
            }
            else
            {
                alert(res.errMsg);
            }
        }, 'json');
    },
    //保有客户重新分配 - 重新分配一下客户
    "KeepCustomerReset":function(){
        if(isAjaxing){return ;}//ajax正在提交中
        var sendData = {"customer_ids" : []};
        $('input[name="checkbox"]:checked').each(function(){
            sendData.customer_ids.push(parseInt($(this).attr('data-id')));
        });
        if(sendData.customer_ids.length == 0)
        {
            alert('请勾选');
            return ;
        }
        //选中的销售id
        sendData.saleman_id = $('select[name="saleman_id"]').val();
        sendData.saleman_name = $('select[name="saleman_id"]').find("option:selected").text();
        isAjaxing = true;
        $.post('/customer/ajax-keep-customer-reset', sendData, function(res){
            isAjaxing = false;
            if(res.code == 0)
            {
                location.reload(true);//刷新页面
            }
            else
            {
                alert(res.errMsg);
            }
        }, 'json');
    },

    //17-04-24 lzx
    "submitactiveForm" : function(){

        var sendData = {};

        var id_arr = [];
        $("input.input_id:checkbox:checked").each(function(){
            id_arr.push(this.value);
        });
        var intention_level = $('#intention_level').val()
// alert(intention_level); return
        sendData.id_arr = id_arr;
        sendData.intention_level= intention_level;

        $.post('/customer/active', sendData, function(res){
            location.reload(true);
        });
    },

    "checkactiveForm" : function () {
        var checked = 0;
        $("input:checkbox").each(function(){
            if(this.checked == true){
                checked ++
            }
        });
        if(checked == 0){
            $('#notice').show();
            alert('您还没有选择客户');return
        }
        $('#myModal').modal("show");
    }
};


