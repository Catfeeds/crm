
if (create_card_times != ''){
    $('#c'+create_card_times).addClass('on');
    $('#create_card_time').val(create_card_times);
}

if(last_view_times != '') {
    $('#l'+last_view_times).addClass('on');
    $('#last_view_time').val(last_view_times);
}

//信息来源
if (sourve != '') {

    var dataObj=eval("("+sourve+")");//转换为json对象
    var count = dataObj.length
    for (var i = 0 ; i < count; i++) {
        $(":checkbox[value='"+dataObj[i]+"']").prop("checked",true);
    }

}


//意向等级
if (intention != '') {

    var dataObj=eval("("+intention+")");//转换为json对象
    var count = dataObj.length
    for (var i = 0 ; i < count; i++) {
        $(":checkbox[val='"+dataObj[i]+"']").prop("checked",true);
    }

}


//建卡时间排序
function create_card_time() {
    $('#last_view_time').val('');
    var create_card_time = $('#create_card_time').val();

    $('#create_card_time').val(create_card_time == 'asc' ? 'desc' : 'asc');
   $('#form1').submit();
}

//最后联系时间排序
function last_view_time(sort) {

    $('#create_card_time').val('');
    var last_view_time = $('#last_view_time').val();
    $('#last_view_time').val(last_view_time == 'asc' ? 'desc' : 'asc');
    $('#form1').submit();
}

$('.sub').click(function () {
    $('#form1').submit();
})
