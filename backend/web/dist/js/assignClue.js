/**
 * Created by Administrator on 2017/3/27.
 */
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
    
    //查询按钮
    $('#searchBtn').click(function(){
        $('form').submit();
    });
    
    //清除
    $('#clearBtn').click(function(){
        $('form input').each(function(){
            $(this).val('');
        });
    });
    
});


/**
 * 分配线索
 */
var assignClue = {
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
        assignClue.clearLayerData();
        $('#myModal').show();
    },
    //取消查看弹层
    "look_cancelLayer" : function(){
        $('#look_myModal').hide();
    },
    //取消新建弹层
    "cancelLayer" : function(){
        assignClue.clearLayerData();
        $('#myModal').hide();
    },
    "clearLayerData" : function(){
        $('#myModalLabel').html('选择店员');
        $('#input_id').val('');
        $('#input_addressee_des').val('');
        $('#input_title').val('');
        $('#input_content').val('');
    },

    //全选
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

    //查看弹出层
    "lookLayer" : function(obj){
        $('#look_myModalLabel').html('查看公告');
        var strJson = obj.parent().find('.thisDataSpan').text();
        var objJson = eval('(' + strJson + ')');
        console.log(objJson);
        $('#look_id').html(objJson.id);
        $('#look_addressee_des').html(objJson.addressee_des);
        $('#look_title').html(objJson.title);
        $('#look_content').html(objJson.content);
        $('#look_send_person_name').html(objJson.send_person_name);
        $('#look_send_time').html(objJson.send_time);
        $('#look_myModal').show();
    },

    //提交信息
    "submitassignForm" : function(){
        var sendData = {};

        var id_arr = [];
        $(".input_id:input:checkbox:checked").each(function(){
            id_arr.push(this.value);
        });

        sendData.salesman_id = $("#salesman_id").val();

        sendData.id_arr= id_arr;

        $.post('/assign-clue/assign', sendData, function(res){
            //console.log($("#myModal").length);
            assignClue.cancelLayer();
            layer.msg('操作成功',{icon:1,time:1000,offset: '20px',skin: 'demo-class'},function(){
                location.reload(true);
            });
        });
    },
};