$(document).ready(function(){


    //搜索时间
    if($('#search_time'))
    {
        var config = {"opens": "left", "autoApply": true, "autoUpdateInput": false, "locale": {"format": 'YYYY-MM-DD',
                    'daysOfWeek': ['日', '一', '二', '三', '四', '五','六'],
                    'monthNames': ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
                    'firstDay': 1
    }};
        if($.trim($('#search_time_start').val()) !== '')
        {
            config.startDate = $.trim($('#search_time_start').val());
            config.endDate = $.trim($('#search_time_end').val());
            config.autoUpdateInput = true;
        }
        $('#search_time').daterangepicker(config);
    }
    //选中
    $('#search_time').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + " - " + picker.endDate.format('YYYY-MM-DD'));
    });
});
//顾问查询
$('.sub').click(function () {
    $('#form').submit();
})

var activeClue = {
//清空搜索内容
    "clearSeachCondition": function () {
        // $("#sear").attr('value','');
        $("form#form input[type='text']").each(function () {
            $(this).attr('value', '');
            // $(this).val('');
        });
    }
}