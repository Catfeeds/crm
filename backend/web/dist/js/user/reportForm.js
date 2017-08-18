$(function () {

	$('#check').click(function () {

		$('#status').removeClass('error');
		$('#addtime').removeClass('error');
		var statuss = $('#status').val();
		var check = true;

		if (statuss == 0) {
			$('#status').addClass('error');
			check = false;
		}
		if ($('#addtime').val() == '') {
			$('.calender-picker').addClass('error');
			check = false;
		}
		if (check)
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
	});

	$('#status').val(status);

	$('#status').change(function () {
		var status = $(this).val();

		if (status <= 4){
			$('#form2').attr('action','clue-index');
		}else if (status == 5) {
			$('#form2').attr('action','talk-index');
		}else if (status == 6) {
			$('#form2').attr('action','order-index');
		}
	})


})

