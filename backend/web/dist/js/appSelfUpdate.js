/**
 *
 */
$(function () {
    //创建日期
    if($('#search_time'))
    {
        var config = {"opens": "left", "autoApply": true, "autoUpdateInput": false, "locale": {"format": 'YYYY-MM-DD',
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
        $('#search_time').daterangepicker(config);
    }

    //选中
    $('#search_time').on('apply.daterangepicker', function(ev, picker) {

        $(this).val(picker.startDate.format('YYYY-MM-DD') + " - " + picker.endDate.format('YYYY-MM-DD'));
    });
});

var currentPage_ios = 2;
var currentPage_android = 2;
var currentPage = 2;


var ios_or_android;

var appSelfUpdate = {
    //清除搜索条件
    "clearSeachCondition" : function(){
        $('#search:input').each(function(){
            $(this).val('');
        });
//        $('#addtime').val('');
//        $('#so').val('');
    },
    //新建安卓弹出层
    "createLayerandroid" : function(){
        //新建的时候弹层的数据清除掉
        appSelfUpdate.clearLayerData();
        $('#myModal').show();
        $('#upfile').show();
        ios_or_android = 'android';
        $('#app_type_3').hide();
        $('#app_type_4').hide();
        $('#file_url').hide();
        $('#app_type_1').prop("selected",true);
        $('#myModalLabel').html('新增安卓版本');
    },
    //新建iOS弹出层
    "createLayerios" : function(){
        //新建的时候弹层的数据清除掉
        appSelfUpdate.clearLayerData();
        $('#myModal').show();
        $('#upfile').hide();
        ios_or_android = 'ios';
        $('#app_type_1').hide();
        $('#app_type_2').hide();
        $('#app_type_3').prop("selected",true);
        $('#myModalLabel').html('新增ios版本');
    },
    //取消编辑、新建弹层
    "cancelLayer" : function(){
        appSelfUpdate.clearLayerData();
        $('#myModal').hide();
    },
    "clearLayerData" : function(){
//            $('#myModalLabel').html('新增安卓版本');
        $('#input_id').val('');
        $('#versionName').val('');
        $('#versionCode').val('');
        $('#file').val('');
        $('#file_url').val('');
        $('#content').val('');
        $('#tips').val('');

    },
    //编辑弹出层
    "updateLayerandroid" : function(obj){
        $('#myModalLabel').html('编辑android版本');
        var strJson = obj.parent().find('.thisDataSpan').text();
        var objJson = eval('(' + strJson + ')');
        $('#upfile').show();
        $('#file').val('');
        $('#input_id').val(objJson.id);
        $('#versionName').val(objJson.versionName);
        $('#versionCode').val(objJson.versionCode);
        $('#file_url').val(objJson.file_url);
        $('#content').val(objJson.content);
        $('#tips').val(objJson.tips);

        if(objJson.is_forced_update == 1){
            $('#noforced_update').prop('selected',false);
            $('#forced_update').prop('selected',true);
        }else{
            $('#forced_update').prop('selected',false);
            $('#noforced_update').prop('selected',true);
        }

        $('#app_type_3').hide();
        $('#app_type_4').hide();
        if(objJson.app_id == 2){
            $('#app_type_1').prop("selected",false);
            $('#app_type_2').prop("selected",true);
        }else{
            $('#app_type_2').prop("selected",false);
            $('#app_type_1').prop("selected",true);
        }
        $('#myModal').show();
    },
    //编辑弹出层
    "updateLayerios" : function(obj){
        $('#myModalLabel').html('编辑ios版本');
        var strJson = obj.parent().find('.thisDataSpan').text();
        var objJson = eval('(' + strJson + ')');
        $('#upfile').hide();
        $('#input_id').val(objJson.id);
        $('#versionName').val(objJson.versionName);
        $('#versionCode').val(objJson.versionCode);
        $('#content').val(objJson.content);
        $('#tips').val(objJson.tips);

        if(objJson.is_forced_update == 1){
            $('#noforced_update').prop('selected',false);
            $('#forced_update').prop('selected',true);
        }else{
            $('#forced_update').prop('selected',false);
            $('#noforced_update').prop('selected',true);
        }

        $('#app_type_1').hide();
        $('#app_type_2').hide();
        if(objJson.app_id == 3){
            $('#app_type_4').prop("selected",false);
            $('#app_type_3').prop("selected",true);
        }else{
            $('#app_type_3').prop("selected",false);
            $('#app_type_4').prop("selected",true);
        }
        $('#myModal').show();


        // $('#myModalLabel').html('编辑ios版本');
        // var strJson = obj.parent().find('.thisDataSpan').text();
        // var objJson = eval('(' + strJson + ')');
        // $('#input_name').val(objJson.name);
        // $('#input_des').val(objJson.des);
        // $('#input_id').val(objJson.id);
        // $('#myModal').show();
    },
    "submitForm" : function(){
        var versionName = $('#versionName').val();
        var versionCode = $('#versionCode').val();
        var file = $('#file').val();
        var content = $('#content').val();
        var tips = $('#tips').val();

        if(versionName == ''){
            $("#versionName-error").html('*请填写版本号');
            return
        }else{
            $("#versionName-error").html('');
        }
        if(versionCode == ''){
            $("#versionCode-error").html('*请填写更新编号');
            return
        }else{
            $("#versionCode-error").html('');
        }

        if(ios_or_android == 'android' &&file == ''){
            $("#file-error").html('*请选择文件');
            return
        }else{
            $("#file-error").html('');
        }

        if(content == ''){
            $("#content-error").html('*请填写更新内容');
            return
        }else{
            $("#content-error").html('');
        }
        if(tips == ''){
            //alert('请填写备注');
            $('#tips-error').html('*请填写备注');
            return
        }else{
            $("#tips-error").html('');
        }

        $('#form1').submit();

        // var sendData = {};
        // sendData.ios_or_android= ios_or_android;
        // sendData.id= $('#input_id').val();
        // sendData.versionName= $('#input_versionName').val();
        // sendData.versionCode = $('#input_versionCode').val();
        // sendData.content = $('#input_content').val();
        // sendData.tips = $('#input_tips').val();
        // sendData.is_forced_update = $('#input_is_forced_update').val();
        //
        // sendData.app_id = $("option:selected").val();
        // // sendData.app_name = $("option:selected").html();
        //
        // $.post('/self-update/update-or-create', sendData, function(res){
        //     console.log(res)
        //     // location.reload(true);
        // }, 'json');
    },
    // "updateStatus" : function(obj){
    //     var strJson = obj.parent().find('.thisDataSpan').text();
    //     var objJson = eval('(' + strJson + ')');
    //     var sendData = {};
    //     sendData.id = objJson.id;
    //     sendData.status = (objJson.status == 1 ? 0 :1);
    //     $.post('/age-group/update-status', sendData, function(res){
    //         location.reload(true);
    //     });
    // }
    "moreList" : function(app_name){

        $.post('/self-update/ajax-update-history', {'app_name':app_name,'currentPage':currentPage}, function(res){

            if(res == 'no'){
                alert('没有数据了')
                $('.getMore').hide()
                return
            }
                currentPage ++

            var data = JSON.parse(res)
            var android_list = data['android_list']
            var ios_list = data['ios_list']

            var ios_str = '';
            for(i in ios_list){

                ios_str += '<li><i class="fa bg-blue"></i>'
                ios_str += '<div class="timeline-item">'
                ios_str += '<span class="time"><i class="fa fa-clock-o"></i>'+ios_list[i]['create_time']+'</span>'
                ios_str += '<h3 class="timeline-header">'+ios_list[i]['versionName']+'</h3>'
                ios_str += '<div class="timeline-body">'
                ios_str += ios_list[i]['content']
                ios_str += '</div>'
                ios_str += '</div>'
                ios_str += '</li>'
            }

            $('#ios_id').append(ios_str)

            var android_str = '';
            for(i in android_list){
                android_str += '<li><i class="fa bg-blue"></i>'
                android_str += '<div class="timeline-item">'
                android_str += '<span class="time"><i class="fa fa-clock-o"></i>'+android_list[i]['create_time']+'</span>'
                android_str += '<h3 class="timeline-header">'+android_list[i]['versionName']+'</h3>'
                android_str += '<div class="timeline-body">'
                android_str += android_list[i]['content']
                android_str += '</div>'
                android_str += '</div>'
                android_str += '</li>'
            }

            $('#android_id').append(android_str)

        });
    }
};