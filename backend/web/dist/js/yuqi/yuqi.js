if (start_times != ''){
    $('#'+start_times).addClass('on');
    $('#start_time').val(start_times);
}

//下发时间
function start_time() {
    var start_time = $('#start_time').val();
    $('#start_time').val(start_time == 'asc' ? 'desc' : 'asc');
    $('#form1').submit();
}
//意向等级
if (intention != '') {
    var dataObj=eval("("+intention+")");//转换为json对象
    var count = dataObj.length
    for (var i = 0 ; i < count; i++) {
        $(":checkbox[val='y"+dataObj[i]+"']").prop("checked",true);
    }
}

//状态
if (is_lianxi != '') {
    var dataObj=eval("("+is_lianxi+")");//转换为json对象
    var count = dataObj.length
    for (var i = 0 ; i < count; i++) {
        $(":checkbox[val='"+dataObj[i]+"']").prop("checked",true);
    }
}
//门店
if (shop != '') {
    var dataObj=eval("("+shop+")");//转换为json对象
    var count = dataObj.length;
    for (var i = 0 ; i < count; i++) {
        $(":checkbox[val='s"+dataObj[i]+"']").prop("checked",true);
    }
}
//查询
$('.sub').click(function () {
    $('#form1').submit();
})
