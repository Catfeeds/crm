/**
 * 日志相关js
 */

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

var showLogs = {
    //清除搜索条件
    "clearSearch" : function(){
        $("form input[type='text']").each(function(){
            $(this).val('');
        });
    },
    //导出日志
    "downloadLogs" : function(){
        var queryString = '';
        queryString += 'search_time=' + $.trim($('#search_time').val());
        queryString += '&so=' + $.trim($('#so').val());
        queryString += '&isDownload=1';
        location.href='/logs/show-logs?' + queryString;
    }
};

