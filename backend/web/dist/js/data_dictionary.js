/**
 * 后台数据字典相关操作的js
 */

/**
 * 意向等级相关的js
 */

var isAjaxIng = false;
var intention = {

    //新建弹出层
    "createLayer" : function(){
        //新建的时候弹层的数据清除掉
        intention.clearLayerData();
        $('#myModal').show();
    },
    //取消编辑、新建弹层
    "cancelLayer" : function(){
        intention.clearLayerData();
        $('#myModal').hide();
    },
    "clearLayerData" : function(){
        $('#myModalLabel').html('新增意向等级');
        $('#input_name').val('');
        $('#input_id').val('');
        $('#input_des').val('');
        $('#input_frequency_day').val('');
        $('#input_total_times').val('');
//        $('#input_has_today_task').attr('checked', 'true');
        $('#input_has_today_task').removeAttr('checked ');
    },
    //编辑弹出层
    "updateLayer" : function(obj){
        var strJson = obj.parent().find('.thisDataSpan').text();
        var objJson = eval('(' + strJson + ')');
        $('#myModalLabel').html('编辑意向等级');
        $('#input_id').val(objJson.id);
        $('#input_name').val(objJson.name);
        $('#input_des').val(objJson.des);
        $('#input_frequency_day').val(objJson.frequency_day);
        $('#input_total_times').val(objJson.total_times);
        if(objJson.has_today_task == 1)
        {
            $('#input_has_today_task').attr('checked', 'true');
        }
        $('#myModal').show();
    },
    "submitForm" : function(){

        if(isAjaxIng){ return '';}//正在ajax提交中

        if(checkform()) {
            var sendData = {};
            sendData.id = $('#input_id').val();
            sendData.name = $('#input_name').val();
            sendData.des = $('#input_des').val();
            sendData.frequency_day = $('#input_frequency_day').val();
            sendData.total_times = $('#input_total_times').val();
            sendData.has_today_task = ($('#input_has_today_task').is(':checked') ? 1 : 0);
            if (sendData.name && sendData.des && sendData.frequency_day && sendData.total_times) {
                isAjaxIng = true;
                $.post('/intention/update-or-create', sendData, function (res) {
                    isAjaxIng = false;
                    if(res.statusCode == 200){
                        resAlert(true);
                    }else{
                        resAlert(false,res.message);
                    }

                }, 'json');
            }
        }
    },
    "updateStatus" : function(obj){
        if(isAjaxIng){ return '';}//正在ajax提交中
        var strJson = obj.parent().find('.thisDataSpan').text();
        var objJson = eval('(' + strJson + ')');
        var sendData = {};
        sendData.id = objJson.id;
        sendData.status = (objJson.status == 1 ? 0 :1);
        isAjaxIng = true;
        $.post('/intention/update-status', sendData, function(res){
            isAjaxIng = false;
            location.reload(true);
        });
    },
    "showDemoPng" : function(){
    },
};

/**
 * 职业数据字典相关js
 */
var profession = {
    //新建弹出层
    "createLayer" : function(){
        //新建的时候弹层的数据清除掉
        profession.clearLayerData();
        $('#myModal').show();
    },
    //取消编辑、新建弹层
    "cancelLayer" : function(){
        profession.clearLayerData();
        $('#myModal').hide();
    },
    "clearLayerData" : function(){
        $('#myModalLabel').html('新增职业');
        $('#input_name').val('');
        $('#input_des').val('');
        $('#input_id').val('');
    },
    //编辑弹出层
    "updateLayer" : function(obj){
        $('#myModalLabel').html('编辑职业');
        var strJson = obj.parent().find('.thisDataSpan').text();
        var objJson = eval('(' + strJson + ')');
        $('#input_name').val(objJson.name);
        $('#input_des').val(objJson.des);
        $('#input_id').val(objJson.id);
        $('#myModal').show();
    },
    "submitForm" : function(){
        if(isAjaxIng){ return '';}//正在ajax提交中
        if(checkform()) {
            var sendData = {};
            sendData.id = $('#input_id').val();
            sendData.name = $('#input_name').val();
            sendData.des = $('#input_des').val();
            if (sendData.name) {
                isAjaxIng = true;
                $.post('/profession/update-or-create', sendData, function (res) {
                    isAjaxIng = false;
                    if(res.statusCode == 200){
                        resAlert(true);
                    }else{
                        resAlert(false,res.message);
                    }
                }, 'json');
            }
        }
    },
    "updateStatus" : function(obj){
        if(isAjaxIng){ return '';}//正在ajax提交中
        var strJson = obj.parent().find('.thisDataSpan').text();
        var objJson = eval('(' + strJson + ')');
        var sendData = {};
        sendData.id = objJson.id;
        sendData.status = (objJson.status == 1 ? 0 :1);
        isAjaxIng = true;
        $.post('/profession/update-status', sendData, function(res){
            isAjaxIng = false;
            location.reload(true);
        });
    }
};

/**
 * 信息来源
 */
var source = {
    //新建弹出层
    "createLayer" : function(){
        //新建的时候弹层的数据清除掉
        source.clearLayerData();
        $('#myModal').show();
    },
    //取消编辑、新建弹层
    "cancelLayer" : function(){
        source.clearLayerData();
        $('#myModal').hide();
    },
    "clearLayerData" : function(){
        $('#myModalLabel').html('新增信息来源');
        $('#input_name').val('');
        $('#input_des').val('');
        $('#input_id').val('');
    },
    //编辑弹出层
    "updateLayer" : function(obj){
        $('#myModalLabel').html('编辑信息来源');
        var strJson = obj.parent().find('.thisDataSpan').text();
        var objJson = eval('(' + strJson + ')');
        $('#input_name').val(objJson.name);
        $('#input_des').val(objJson.des);
        $('#input_id').val(objJson.id);
        $('#myModal').show();
    },



    "submitForm" : function(){
        if(isAjaxIng){ return '';}//正在ajax提交中

        if(checkform()) {
            var sendData = {};
            sendData.id = $('#input_id').val();
            sendData.name = $('#input_name').val();
            sendData.des = $('#input_des').val();

            if (sendData.name) {
                isAjaxIng = true;
                $.post('/source/update-or-create', sendData, function (res) {
                    isAjaxIng = false;
                    if(res.statusCode == 200){
                        resAlert(true);
                    }else{
                        resAlert(false,res.message);
                    }
                }, 'json');
            }
        }
    },


    "updateStatus" : function(obj){
        if(isAjaxIng){ return '';}//正在ajax提交中
        var strJson = obj.parent().find('.thisDataSpan').text();
        var objJson = eval('(' + strJson + ')');
        var sendData = {};
        sendData.id = objJson.id;
        sendData.status = (objJson.status == 1 ? 0 :1);
        isAjaxIng = true;
        $.post('/source/update-status', sendData, function(res){
            isAjaxIng = false;
            location.reload(true);
        });
    }
};
/**
 * 渠道来源数据字典js
 */
var inputType = {
    //新建弹出层
    "createLayer" : function(){
        //新建的时候弹层的数据清除掉
        inputType.clearLayerData();
        $('#myModal').show();
    },
    //取消编辑、新建弹层
    "cancelLayer" : function(){
        inputType.clearLayerData();
        $('#myModal').hide();
    },
    "clearLayerData" : function(){
        $('#myModalLabel').html('新增渠道来源');
        $('#input_name').val('');
        $('#input_des').val('');
        $('#input_id').val('');
    },
    //编辑弹出层
    "updateLayer" : function(obj){
        $('#myModalLabel').html('编辑渠道来源');
        var strJson = obj.parent().find('.thisDataSpan').text();
        var objJson = eval('(' + strJson + ')');

        $('#input_name').val(objJson.name);
        $('#input_des').val(objJson.des);
        $('#input_id').val(objJson.id);
        $('#yuqi_time').val(objJson.yuqi_time);

        if (objJson.is_yuqi == 1) {
            $('#s0').removeClass('active');
            $('#s1').addClass('active');
            $('#d1').show();
        }else {
            $('#s1').removeClass('active');
            $('#s0').addClass('active');
            $('#d1').hide();
        }
        $('#myModal').show();
    },
    "is_yuqi" : function(is_y) {
        $('#is_yuqi').val(is_y);
        if (is_y == 1) {
            $('#s0').removeClass('active');
            $('#s1').addClass('active');
            $('#d1').show();
        }
        else {
            $('#s1').removeClass('active');
            $('#s0').addClass('active');
            $('#d1').hide();
        }

    },
    "submitForm" : function(){
        if(isAjaxIng){ return '';}//正在ajax提交中
        if(checkform()) {
            var sendData = {};
            sendData.id = $('#input_id').val();
            sendData.name = $('#input_name').val();
            sendData.des = $('#input_des').val();
            var is_yuqi = $('#is_yuqi').val();
            var yuqi_time = $('#yuqi_time').val();
            $('#errors').text('');

            if (is_yuqi == 1) {
                sendData.yuqi_time = yuqi_time;
                if (yuqi_time < 0.1 || yuqi_time > 48 || isNaN(yuqi_time) == true) {
                    $('#errors').text('时长输入有误！');
                    return ;
                }
            }else{
                sendData.yuqi_time = 0;
            }
            sendData.is_yuqi = is_yuqi;

            if (sendData.name) {
                isAjaxIng = true;
                $.post('/input-type/update-or-create', sendData, function (res) {
                    isAjaxIng = false;
                    if(res.statusCode == 200){
                        resAlert(true);
                    }else{
                        resAlert(false,res.message);
                    }
                }, 'json');
            }
        }
    },
    "updateStatus" : function(obj){
        if(isAjaxIng){ return '';}//正在ajax提交中
        var strJson = obj.parent().find('.thisDataSpan').text();
        var objJson = eval('(' + strJson + ')');
        var sendData = {};
        sendData.id = objJson.id;
        sendData.status = (objJson.status == 1 ? 0 :1);
        isAjaxIng = true;
        $.post('/input-type/update-status', sendData, function(res){
            isAjaxIng = false;
            location.reload(true);
        });
    }
};

/**
 * 年龄段数据字典
 */
var ageGroup = {
    //新建弹出层
    "createLayer" : function(){
        //新建的时候弹层的数据清除掉
        ageGroup.clearLayerData();
        $('#myModal').show();
    },
    //取消编辑、新建弹层
    "cancelLayer" : function(){
        ageGroup.clearLayerData();
        $('#myModal').hide();
    },
    "clearLayerData" : function(){
        $('#myModalLabel').html('新增年龄段');
        $('#input_name').val('');
        $('#input_des').val('');
        $('#input_id').val('');
    },
    //编辑弹出层
    "updateLayer" : function(obj){
        $('#myModalLabel').html('编辑年龄段');
        var strJson = obj.parent().find('.thisDataSpan').text();
        var objJson = eval('(' + strJson + ')');
        $('#input_name').val(objJson.name);
        $('#input_des').val(objJson.des);
        $('#input_id').val(objJson.id);
        $('#myModal').show();
    },
    "submitForm" : function(){
        if(isAjaxIng){ return '';}//正在ajax提交中
        if(checkform()) {
            var sendData = {};
            sendData.id = $('#input_id').val();
            sendData.name = $('#input_name').val();
            sendData.des = $('#input_des').val();
            if (sendData.name) {
                isAjaxIng = true;
                $.post('/age-group/update-or-create', sendData, function (res) {
                    isAjaxIng = false;
                    if(res.statusCode == 200){
                        resAlert(true);
                    }else{
                        resAlert(false,res.message);
                    }
                }, 'json');
            }
        }
    },
    "updateStatus" : function(obj){
        if(isAjaxIng){ return '';}//正在ajax提交中
        var strJson = obj.parent().find('.thisDataSpan').text();
        var objJson = eval('(' + strJson + ')');
        var sendData = {};
        sendData.id = objJson.id;
        sendData.status = (objJson.status == 1 ? 0 :1);
        isAjaxIng = true;
        $.post('/age-group/update-status', sendData, function(res){
            isAjaxIng = false;
            location.reload(true);
        });
    }
};

/**
 * 拟购时间数据字典
 */
var plannedPurchaseTime = {
    //新建弹出层
    "createLayer" : function(){
        //新建的时候弹层的数据清除掉
        plannedPurchaseTime.clearLayerData();
        $('#myModal').show();
    },
    //取消编辑、新建弹层
    "cancelLayer" : function(){
        plannedPurchaseTime.clearLayerData();
        $('#myModal').hide();
    },
    "clearLayerData" : function(){
        $('#myModalLabel').html('新增拟购时间');
        $('#input_name').val('');
        $('#input_des').val('');
        $('#input_id').val('');
    },
    //编辑弹出层
    "updateLayer" : function(obj){
        $('#myModalLabel').html('编辑拟购时间');
        var strJson = obj.parent().find('.thisDataSpan').text();
        var objJson = eval('(' + strJson + ')');
        $('#input_name').val(objJson.name);
        $('#input_des').val(objJson.des);
        $('#input_id').val(objJson.id);
        $('#myModal').show();
    },
    "submitForm" : function(){
        if(isAjaxIng){ return '';}//正在ajax提交中
        if(checkform()) {
            var sendData = {};
            sendData.id = $('#input_id').val();
            sendData.name = $('#input_name').val();
            sendData.des = $('#input_des').val();
            if (sendData.name) {
                isAjaxIng = true;
                $.post('/planned-purchase-time/update-or-create', sendData, function (res) {
                    isAjaxIng = false;
                    if (res.statusCode == 200) {
                        resAlert(true);
                    } else {
                        resAlert(false, res.message);
                    }
                }, 'json');
            }
        }
    },
    "updateStatus" : function(obj){
        if(isAjaxIng){ return '';}//正在ajax提交中
        var strJson = obj.parent().find('.thisDataSpan').text();
        var objJson = eval('(' + strJson + ')');
        var sendData = {};
        sendData.id = objJson.id;
        sendData.status = (objJson.status == 1 ? 0 :1);
        isAjaxIng = true;
        $.post('/planned-purchase-time/update-status', sendData, function(res){
            isAjaxIng = false;
            location.reload(true);
        });
    }
};


/**
 * 标签
 */
var tags = {
    //新建弹出层
    "createLayer" : function(){
        //新建的时候弹层的数据清除掉
        tags.clearLayerData();
        $('#myModal').show();
    },
    //取消编辑、新建弹层
    "cancelLayer" : function(){
        tags.clearLayerData();
        $('#myModal').hide();
    },
    "clearLayerData" : function(){
        $('#myModalLabel').html('新增');
        $('#input_name').val('');
        $('#input_id').val('');
    },
    //编辑弹出层
    "updateLayer" : function(obj){
        $('#myModalLabel').html('编辑');
        var strJson = obj.parent().find('.thisDataSpan').text();
        var objJson = eval('(' + strJson + ')');
        $('#input_name').val(objJson.name);
        $('#input_id').val(objJson.id);
        $('#myModal').show();
    },
    "submitForm" : function(){
        if(isAjaxIng){ return '';}//正在ajax提交中
        if(checkform()) {
            if ($('#input_name').val() == '') {
                alert('名称不能为空！');
                return;
            }
            var sendData = {};
            sendData.id = $('#input_id').val();
            sendData.name = $('#input_name').val();
            sendData.type = $('#input_type').val();
            isAjaxIng = true;
            $.post('/tags/update-or-create', sendData, function (res) {
                isAjaxIng = false;
                if (res.statusCode == 200) {
                    resAlert(true);
                } else {
                    resAlert(false, res.message);
                }
            }, 'json');
        }
    },
    "updateStatus" : function(obj){
        if(isAjaxIng){ return '';}//正在ajax提交中
        var strJson = obj.parent().find('.thisDataSpan').text();
        var objJson = eval('(' + strJson + ')');
        var sendData = {};
        sendData.id = objJson.id;
        sendData.status = (objJson.status == 1 ? 0 :1);
        isAjaxIng = true;
        $.post('/tags/update-status', sendData, function(res){
            isAjaxIng = false;
            location.reload(true);
        });
    }
};


/**
 * 战败标签
 */
var failTags = {
    //新建弹出层
    "createLayer" : function(){
        //新建的时候弹层的数据清除掉
        failTags.clearLayerData();
        $('#myModal').show();
    },
    //取消编辑、新建弹层
    "cancelLayer" : function(){
        failTags.clearLayerData();
        $('#myModal').hide();
    },
    "clearLayerData" : function(){
        $('#myModalLabel').html('新增');
        $('#input_name').val('');
        $('#input_id').val('');
        if($('#input_des'))
        {
            $('#input_des').val('');//订单战败列表才有
            $('#input_group').val('');//订单战败列表才有
        }
    },
    //编辑弹出层
    "updateLayer" : function(obj){
        $('#myModalLabel').html('编辑');
        var strJson = obj.parent().find('.thisDataSpan').text();
        var objJson = eval('(' + strJson + ')');
        $('#input_name').val(objJson.name);
        $('#input_id').val(objJson.id);
        if($('#input_des'))
        {
            $('#input_des').val(objJson.des);//订单战败列表才有
            $('#input_group').val(objJson.group);//订单战败列表才有
        }
        $('#myModal').show();
    },
    "submitForm" : function(){
        if(isAjaxIng){ return '';}//正在ajax提交中
        if(checkform()) {
            var sendData = {};
            sendData.id = $('#input_id').val();
            sendData.name = $('#input_name').val();
            sendData.type = $('#input_type').val();

            if (typeof($('#input_des').val()) != "undefined") {
                sendData.des = $('#input_des').val();//订单战败列表才有
                sendData.group = $('#input_group').val();//订单战败列表才有
                if ($.trim(sendData.des) == '' || !sendData.group || $.inArray(sendData.group, ['price', 'product', 'service', 'bank', 'others']) == -1) {
                    return '';//必填项不全
                }
            }
            isAjaxIng = true;
            $.post('/fail-tags/update-or-create', sendData, function (res) {
                isAjaxIng = false;
                if (res.statusCode == 200) {
                    resAlert(true);
                } else {
                    resAlert(false, res.message);
                }
            }, 'json');
        }
    },
    "updateStatus" : function(obj){
        if(isAjaxIng){ return '';}//正在ajax提交中
        var strJson = obj.parent().find('.thisDataSpan').text();
        var objJson = eval('(' + strJson + ')');
        var sendData = {};
        sendData.id = objJson.id;
        sendData.status = (objJson.status == 1 ? 0 :1);
        isAjaxIng = true;
        $.post('/fail-tags/update-status', sendData, function(res){
            isAjaxIng = false;
            location.reload(true);
        });
    }
};

/**
 * 短信模板
 */
var phoneLetterTmp = {
    //新建弹出层
    "createLayer" : function(){
        //新建的时候弹层的数据清除掉
        phoneLetterTmp.clearLayerData();
        $('#myModal').show();
    },
    //取消编辑、新建弹层
    "cancelLayer" : function(){
        phoneLetterTmp.clearLayerData();
        $('#myModal').hide();
    },
    "clearLayerData" : function(){
        $('#myModalLabel').html('新增');
        $('#input_title').val('');
        $('#input_id').val('');
        $('#input_content').val('');
        if($('#input_use_scene'))
        {
            $('#input_use_scene').val('');
        }
    },
    //编辑弹出层
    "updateLayer" : function(obj){
        $('#myModalLabel').html('编辑');
        var strJson = obj.parent().find('.thisDataSpan').text();
        var objJson = eval('(' + strJson + ')');
        $('#input_title').val(objJson.title);
        $('#input_content').val(objJson.content);
        $('#input_id').val(objJson.id);
        if($('#input_use_scene'))
        {
            $('#input_use_scene').val(objJson.use_scene);
        }
        $('#myModal').show();
    },
    "submitForm" : function(){
        if(isAjaxIng){ return '';}//正在ajax提交中
        if(checkform()) {
            var sendData = {};
            sendData.id = $('#input_id').val();
            sendData.title = $('#input_title').val();
            sendData.type = $('#input_type').val();
            sendData.content = $('#input_content').val();
            if ($('#input_use_scene')) {
                sendData.use_scene = $('#input_use_scene').val();
            }
            if (sendData.title && sendData.content) {
                isAjaxIng = true;
                $.post('/phone-letter-tmp/update-or-create', sendData, function (res) {
                    isAjaxIng = false;
                    if (res.statusCode == 200) {
                        resAlert(true);
                    } else {
                        resAlert(false, res.message);
                    }
                }, 'json');
            }
        }
    },
    "updateStatus" : function(obj){
        if(isAjaxIng){ return '';}//正在ajax提交中
        var strJson = obj.parent().find('.thisDataSpan').text();
        var objJson = eval('(' + strJson + ')');
        var sendData = {};
        sendData.id = objJson.id;
        sendData.status = (objJson.status == 1 ? 0 :1);
        isAjaxIng = true;
        $.post('/phone-letter-tmp/update-status', sendData, function(res){
            isAjaxIng = false;
            location.reload(true);
        });
    },
    "delete" : function(obj){
        if(isAjaxIng){ return '';}//正在ajax提交中

        $is_check = confirm('您确定要删除此条信息吗?');
        if ($is_check) {

            var strJson = obj.parent().find('.thisDataSpan').text();
            var objJson = eval('(' + strJson + ')');
            var sendData = {};
            sendData.id = objJson.id;
            isAjaxIng = true;
            $.post('/phone-letter-tmp/delete', sendData, function(res){
                isAjaxIng = false;
                location.reload(true);
            });
        }
    }
};


