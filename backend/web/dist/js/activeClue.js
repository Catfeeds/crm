$(document).ready(function(){
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
    
});


/**
 * 异常客户
 */
var activeClue = {
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
            alert('请选择客户');return
        }

        //新建的时候弹层的数据清除掉
        activeClue.clearLayerData();
        $('#myModal').show();
    },
    //取消查看弹层
    "look_cancelLayer" : function(){
        $('#look_myModal').hide();
    },
    //取消新建弹层
    "cancelLayer" : function(){
        activeClue.clearLayerData();
        $('#myModal').hide();
    },
    "clearLayerData" : function(){
        $('#myModalLabel').html('选择店员');
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


    //提交激活数据
    "submitactiveForm" : function(){

        var checked = 0;
        $("input:checkbox").each(function(){
            if(this.checked == true){
                checked ++
            }
        });
        if(checked == 0){
            alert('请选择客户');return
        }

        //拼接数据
        var sendData = {};

        var id_arr = [];
        $("input:checkbox:checked").each(function(){
            id_arr.push(this.value);
        });

        sendData.id_arr= id_arr;
        sendData.shop_id = $('#shopid').val();
        $.post('/active-clue/active', sendData, function(res){
            activeClue.cancelLayer();
            layer.msg('操作成功',{icon:1,time:1000,offset: '20px',skin: 'demo-class'},function(){
                location.reload(true);
            });
        });
    },

    //提交重新分配数据
    "submitreassignForm" : function(){
        var sendData = {};

        var id_arr = [];
        $(".input_id:input:checkbox:checked").each(function(){
            id_arr.push(this.value);
        });

        // sendData.salesman_id = $("option:selected").val();
        sendData.salesman_id = $("#salesman_id").val();

        sendData.id_arr= id_arr;
        sendData.shop_id = $('#shopid').val();
        $.post('/active-clue/reassign', sendData, function(res){
            activeClue.cancelLayer();
            layer.msg('操作成功',{icon:1,time:1000,offset: '20px',skin: 'demo-class'},function(){
                location.reload(true);
            });
        });
    },

    //提交重新分配数据  无人跟进客户页面  和其他参数不同
    "submitreassignFormnofollow" : function(){
        var sendData = {};

        sendData.source = 'nofollow';

        var id_arr = [];
        $(".input_id:input:checkbox:checked").each(function(){
            id_arr.push(this.value);
        });

        // sendData.salesman_id = $("option:selected").val();
        sendData.salesman_id = $("#salesman_id").val();

        sendData.id_arr= id_arr;

        $.post('/active-clue/reassign', sendData, function(res){
            activeClue.cancelLayer();
            layer.msg('操作成功',{icon:1,time:1000,offset: '20px',skin: 'demo-class'},function(){
                location.reload(true);
            });
        });
    },

    //导出数据
    "exportData":function() {
        $('#exportData').val(1)
        $('#form').submit()
        $('#exportData').val(0)
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