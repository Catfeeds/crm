$(function () {
    $('#datetimepicker').datepicker({
        format: 'yyyy-mm',
        autoclose: true,
        startView: 1,
        minViewMode: 1,
        minView: 3,
        maxView:3,
        forceParse: true,
        language: 'zh-CN'
    }).on('changeDate',function(ev){
        dingche('ding-che-ajax',$(this).val(),$('#d1').val());
    });

    $('#datetimepicker1').datepicker({
        format: 'yyyy-mm',
        autoclose: true,
        startView: 1,
        minViewMode: 1,
        minView: 3,
        maxView:3,
        forceParse: true,
        language: 'zh-CN'
    }).on('changeDate',function(ev){
        chengjiao('cheng-jiao-ajax',$(this).val(),$('#d2').val());
    });

    //初始化订车
    dingche('ding-che-ajax','',1);
    //初始化成交
    chengjiao('cheng-jiao-ajax','',1);


    //订车按区域门店顾问点击事件
    $('.dc').click(function () {
        var level = $(this).attr('val')
        $('#d1').val(level);
        var time = $('#datetimepicker').val();
        dingche('ding-che-ajax',time,level);
    })

    //成交按区域门店顾问点击事件
    $('.cj').click(function () {
        var level = $(this).attr('val')
        $('#d2').val(level);
        var time = $('#datetimepicker1').val();
        chengjiao('cheng-jiao-ajax',time,level);
    })



    function dingche(url,time,level) {

        $.post(url,{'time':time,'level':level},function (res) {

            $('#dingche').html(res);
        });
    }

    function chengjiao(url,time,level) {
        $.post(url,{'time':time,'level':level},function (res) {

            $('#chengjiao').html(res);
        });
    }
});


