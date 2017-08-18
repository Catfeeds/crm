/**
 * Created by Administrator on 2017/3/27.
 */
$("body").delegate("#addressee label","click",function(){
    var index = $(this).index();
    $(".addressee-tab-panel").eq(index).removeClass("none").siblings().addClass("none");
});


var check_type = 1;

/**
 * 发布公告
 */
var announcementSend = {
    //新建弹出层
    "createLayer" : function(){
        //新建的时候弹层的数据清除掉
        announcementSend.clearLayerData();
        $('#myModal').show();
    },
    //取消查看弹层
    "look_cancelLayer" : function(){
        $('#look_myModal').hide();
    },
    //取消新建弹层
    "cancelLayer" : function(){
        announcementSend.clearLayerData();
        $('#myModal').hide();
    },
    "clearLayerData" : function(){
        $('#myModalLabel').html('新增公告');
        $('#input_id').val('');
        $('#input_addressee_des').val('');
        $('#input_title').val('');
        $('#input_content').val('');
    },
    //查看弹出层
    "lookLayer" : function(obj){
        $('#look_myModalLabel').html('查看公告');
        var strJson = obj.parent().find('.thisDataSpan').text();
        var objJson = eval('(' + strJson + ')');
        // console.log(objJson);
        $('#look_id').html(objJson.id);
        $('#look_addressee_des').html(objJson.addressee_des);
        $('#look_title').html(objJson.title);
        $('#look_content').html(objJson.content);
        $('#look_send_person_name').html(objJson.send_person_name);
        $('#look_send_time').html(objJson.time_detail_display);

        // var newDate = new Date();
        // newDate.setTime(objJson.send_time * 1000);
        // $('#look_send_time').html(newDate.toDateString());
        //(new Date()).pattern("yyyy-MM-dd EE hh:mm:ss")
        $('#look_myModal').show();
    },
    "submitForm" : function(){
        var sendData = {};


        var type = $("input[name='options']:checked").val();
        sendData.options = type;
        if (type == 'all'){
            var shopIds = '';
        } else if (type == 'company'){
            var shopIds = checkShopIds();
        } else if(type == 'area') {
            var shopIds = $("select[name=active_area_ids]").val();
        } else {
            var shopIds = $("select[name=active_shop_ids]").val();
        }
// console.log(shopIds)

        var input_title = $('#input_title').val();
        var input_content = $('#input_content').val();
        var input_send_person = $('#input_send_person').val();
        if(input_title == ''){
            //alert('请填写标题');
            $("#title-error").html('请填写标题')
            return
        }else{
            $("#title-error").html('')
        }
        if(input_send_person == ''){
            $("#send-person-error").html('请填写标题')
            return
        }else{
            $("#send-person-error").html('')
        }
        if(input_content == ''){
            $("#content-error").html('请填写标题')
            return
        }else{
            $("#content-error").html('')
        }

        sendData.id_arr= shopIds;
        // sendData.title= $('#input_title').val();
        sendData.title= input_title;

        // sendData.content= $('#input_content').val();
        sendData.content= input_content;
        sendData.send_person= input_send_person;


        $.post('/announcement-send/create', sendData, function(res){
            // console.log(res)
            location.reload(true);
        }, 'json');
    },


};

function checkShopIds(){
    var active_shop_ids = new Array();
    $("input[name='active_company_ids']:checked").each(function () {
        active_shop_ids.push(this.value);
    });
    if (active_shop_ids.length == 0) {
        // if ($("#shop_ids").find("#error").length == 0){
        //     $("#shop_ids").append("<span style='color:red' id='error'>*激励对象不能为空</span>");
        // }
        return false;
    } else {
        $("#shop_ids").find("#error").remove();
    }
    return active_shop_ids;
}

//该公告发布对象前选择框 选择状态
function ShowAddresseeDes(id_str) {
        id_arr = id_str.split(",");
        $('.shop_checkbox').prop('checked',false)
        for(i in id_arr)
        {
            $('#shop_'+id_arr[i]).prop('checked',true)
        }

}