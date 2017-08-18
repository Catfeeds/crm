//顾问赋值
if (user_name != '') {

    var dataObj=eval("("+user_name+")");//转换为json对象
    var count = dataObj.length
    for (var i = 0 ; i < count; i++) {
        $(":checkbox[value='"+dataObj[i]+"']").prop("checked",true);
    }

}

//状态赋值
if (status != '') {

    var dataObj=eval("("+status+")");//转换为json对象
    var count = dataObj.length
    for (var i = 0 ; i < count; i++) {
        $(":checkbox[value='"+dataObj[i]+"']").prop("checked",true);
    }

}


//信息来源
if (sourve != '') {

    var dataObj=eval("("+sourve+")");//转换为json对象
    var count = dataObj.length
    for (var i = 0 ; i < count; i++) {
        $(":checkbox[value='"+dataObj[i]+"']").prop("checked",true);
    }

}

if (create_time != ''){
    $('#c'+create_time).addClass('on');
    $('#create_time').val(create_time);
}
if(last_view_times != '') {
    $('#l'+last_view_times).addClass('on');
    $('#last_view_time').val(last_view_times);
}
//创建时间排序
function timeSort() {

    $('#last_view_time').val('');//清空最后联系时间

    var create_time = $('#create_time').val();

    $('#create_time').val(create_time == 'asc' ? 'desc' : 'asc');
    $('#form1').submit();
}

//最后联系时间排序
function last_view_time() {

    $('#create_time').val('');//清空创建时间

    var last_view_time = $('#last_view_time').val();

    $('#last_view_time').val(last_view_time ==  'asc' ? 'desc' : 'asc');
    $('#form1').submit();
}


//顾问查询
$('.sub').click(function () {
    $('#form1').submit();
})
