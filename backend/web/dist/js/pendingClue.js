
/**
 * 异常客户
 */

$(function () {
    $.post("/get-json-data-for-select/index?type=getOrgInfos",{},function(response){
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



    //创建日期
    if($('#addtime'))
    {
        var date = new Date();

        var config = {"opens": "left",
            "autoApply": true,
            "autoUpdateInput": false,
            "dateLimit": {"months": 6},
            "minDate":'2016-06-31',
            "maxDate":date.toLocaleString().split(" ")[0],
            "locale": {"format": 'YYYY-MM-DD',
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


    //选中
    $('#addtime').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + " - " + picker.endDate.format('YYYY-MM-DD'));
    });
})

var pendingClue = {
    //新建弹出层
    "createLayer" : function(){
        //判断是否选中客户
        var checked = 0;
        $("input:checkbox").each(function(){
            if(this.checked == true){
                checked ++
            }
        });
        if(checked == 0){
            alert('请选择线索');return
        }

        //新建的时候弹层的数据清除掉
        pendingClue.clearLayerData();
        $('#myModal').show();
    },
    "gonghai" : function(){
        //判断是否选中客户
        var checked = 0;
        $("input:checkbox").each(function(){
            if(this.checked == true){
                checked ++
            }
        });
        if(checked == 0){
            alert('请选择线索');return
        }
        // document.getElementById('form1').submit();
        //获取表单内容
        var post = $('#form1').serialize();
        var url = $('#form1').attr('action');
        $.post(url,post,function(data){
            if(data.code == 200){
                layer.msg('操作成功',{icon:1,time:1000,offset: '20px',skin: 'demo-class'},function(){
                    location.reload(true);
                });
            }else{
                layer.msg(data.mes,{icon:1,time:1000,offset: '20px',skin: 'demo-class'},function(){

                });
            }

        },'json');



    }
    ,
    //取消查看弹层
    "look_cancelLayer" : function(){
        $('#look_myModal').hide();
    },
    //取消新建弹层
    "cancelLayer" : function(){
        pendingClue.clearLayerData();
        $('#myModal').hide();
    },

    //分配单个用户
    "assign" : function(id){
        pendingClue.checkall();
        $('#id_'+id).prop('checked',true);
        pendingClue.createLayer();
    },

    "clearLayerData" : function(){
        $('#myModalLabel').html('选择门店');
        $('#input_id').val('');
        $('#input_addressee_des').val('');
        $('#input_title').val('');
        $('#input_content').val('');
    },

    //全选 取消全选
    "checkall" : function(){

        if($('#checkall').prop('checked') == true){

            $("input:checkbox").each(function(){
                this.checked = true
            });
        }else{

            $("input:checkbox").each(function(){
                this.checked = false
            });
        }
    },

    //提交重新分配数据
    "submitassignForm" : function(){
        var sendData = {};

        var id_arr = [];
        $(".input_id:input:checkbox:checked").each(function(){
            id_arr.push(this.value);
        });

        // sendData.salesman_id = $("option:selected").val();
        sendData.org_info = $("#shop_id").val();

        sendData.id_arr= id_arr;

        $.post('/pending-clue/assign', sendData, function(json){
            if (json.errCode === 0){
                pendingClue.cancelLayer();
                layer.msg(json.errMsg,{icon:1,time:3000,offset: '20px',skin: 'demo-class'},function(){
                    location.reload(true);
                });
            } else {
                layer.msg(json.errMsg, {icon:5,time:3000,offset: '20px',skin: 'demo-class'});
            }

        });
    },

    //清空搜索内容
    "clearSeachCondition" : function() {
        // $("#sear").attr('value','');
        $("form#form input[type='text']").each(function () {
            $(this).attr('value', '');
            // $(this).val('');
        });
    }
};


