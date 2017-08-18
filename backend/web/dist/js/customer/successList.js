
if (create_card_time != ''){
    $('#cc'+create_card_time).addClass('on');

    $('#create_card_time').val(create_card_time);
}
else if (car_delivery_time != ''){
    $('#c'+car_delivery_time).addClass('on');
    $('#car_delivery_time').val(car_delivery_time);
}

//本店投保赋值
if (is_insurance != '') {

    var dataObj=eval("("+is_insurance+")");//转换为json对象
    var count = dataObj.length
    for (var i = 0 ; i < count; i++) {
        $(":checkbox[val=i"+dataObj[i]+"]").prop("checked",true);
    }

}

//购买方式赋值
if (buy_types != '') {

    var dataObj=eval("("+buy_types+")");//转换为json对象
    var count = dataObj.length
    for (var i = 0 ; i < count; i++) {
        $(":checkbox[val=b"+dataObj[i]+"]").prop("checked",true);
    }

}


//购买方式赋值
if (is_add != '') {

    var dataObj=eval("("+is_add+")");//转换为json对象
    var count = dataObj.length
    for (var i = 0 ; i < count; i++) {
        $(":checkbox[val=is"+dataObj[i]+"]").prop("checked",true);
    }

}


//建卡日期
function create_card_times() {

    $('#car_delivery_time').val('');

    var create_card_time = $('#create_card_time').val();

    $('#create_card_time').val(create_card_time ==  'asc' ? 'desc' : 'asc');
    $('#form1').submit();
}

//购车日期
function car_delivery_times() {

    $('#create_card_times').val('');

    var car_delivery_time = $('#car_delivery_time').val();

    $('#car_delivery_time').val(car_delivery_time ==  'asc' ? 'desc' : 'asc');
    $('#form1').submit();
}


//顾问查询
$('.sub').click(function () {
    $('#form1').submit();
})
