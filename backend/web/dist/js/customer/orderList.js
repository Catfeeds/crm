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

//状态
if (status != '') {

    var dataObj=eval("("+status+")");//转换为json对象
    var count = dataObj.length
    for (var i = 0 ; i < count; i++) {
        $(":checkbox[val=o"+dataObj[i]+"]").prop("checked",true);
    }

}

if (create_time != ''){
    $('#c'+create_time).addClass('on');
    $('#create_time').val(create_time);
}
else if (create_card_time != ''){
    $('#cc'+create_card_time).addClass('on');

    $('#create_card_time').val(create_card_time);
}
else if (predict_car_delivery_time != ''){
    $('#p'+predict_car_delivery_time).addClass('on');
    $('#predict_car_delivery_time').val(predict_car_delivery_time);
}

//订车日期
function create_times() {

    $('#predict_car_delivery_time').val('');
    $('#create_card_time').val('');

    var create_time = $('#create_time').val();

    $('#create_time').val(create_time == 'asc' ? 'desc' : 'asc');
    $('#form1').submit();
}

//建卡日期
function create_card_times() {

    $('#create_time').val('');
    $('#predict_car_delivery_time').val('');

    var create_card_time = $('#create_card_time').val();

    $('#create_card_time').val(create_card_time ==  'asc' ? 'desc' : 'asc');
    $('#form1').submit();
}

//预计交车日期
function predict_car_delivery_times() {

    $('#create_time').val('');
    $('#create_card_time').val('');

    var predict_car_delivery_time = $('#predict_car_delivery_time').val();

    $('#predict_car_delivery_time').val(predict_car_delivery_time ==  'asc' ? 'desc' : 'asc');
    $('#form1').submit();
}


//顾问查询
$('.sub').click(function () {
    $('#form1').submit();
})
