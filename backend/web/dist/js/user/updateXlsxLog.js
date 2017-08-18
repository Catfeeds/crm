$('#'+update_time).addClass('on');
$('#update_time').val(update_time);


//创建时间排序
function update_times() {

    var update_time = $('#update_time').val();

    $('#update_time').val(update_time == 'asc' ? 'desc' : 'asc');
    $('#form1').submit();
}

