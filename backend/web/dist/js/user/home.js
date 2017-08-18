$(function () {


	$('#t1').removeClass('active');
	$('#t2').removeClass('active');
	$('#t3').removeClass('active');

	$('#t'+time).addClass('active focus');
	$('#addtime').val(addtime);


	$('#title_time').html(addtime);


	$(".btn").click(function () {

		$('#time').val($(this).attr('value'));
		$('#addtime').val('');
		$('#form1').submit();
	})

	 //创建日期
    if($('#addtime'))
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
        $('#addtime').daterangepicker(config);
    }


    //选中
    $('#addtime').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + " - " + picker.endDate.format('YYYY-MM-DD'));
		$('#time').val('');
		$('#form1').submit();
    });



})