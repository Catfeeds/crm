$(function () {
    $('#datetimepicker').datepicker({
        format: 'yyyy-m',
        autoclose: true,
        startView: 1,
        minViewMode: 1,
        minView: 3,
        maxView:3,
        forceParse: true,
        language: 'zh-CN'
    }).on('changeDate',function(ev){
        $('#month_title').text($(this).val());
        ajaxres($(this).val(),$('#area_id').val());
    });

    ajaxres($('#datetimepicker').val(),0);
    ajaxres2($('#datetimepicker').val(),0);

});

function ajaxres(yearandmonth,area_id) {

    var url = 'get-ajax-data';
    $.post(url,{'yearandmonth':yearandmonth,'area_id':area_id},function(data){

        var data_new = JSON.parse(data);
        <!--    var clue_source_list = '-->//echo json_encode($clue_source_list);?>//'
//    var clue_info_all = '<?php //echo json_encode($clue_info_all);?>//'
//    var clue_info_fail = '<?php //echo json_encode($clue_info_fail);?>//'
//    var clue_info_zhuanhua = '<?php //echo json_encode($clue_info_zhuanhua);?>//'
//    var table_list = '<?php //echo json_encode($table_list);?>//'

        var clue_source_list_all = new Array();
        var clue_source_list_fail = new Array();
        var clue_source_list_zhuanhua = new Array();
        var clue_info_all_data = new Array();
        var clue_info_fail_data = new Array();
        var clue_info_zhuanhua_data = new Array();

        var clue_info_all_js = data_new.clue_info_all;
        var clue_info_fail_js = data_new.clue_info_fail;
        var clue_info_zhuanhua_js = data_new.clue_info_zhuanhua;
        var table_list_js = data_new.table_list;


        for(i in clue_info_all_js){
            var str_all = {value:clue_info_all_js[i]['value'],name:clue_info_all_js[i]['name']};
            clue_info_all_data.push(str_all);
            clue_source_list_all.push(clue_info_all_js[i]['name'])
        }

        for(i in clue_info_fail_js){
            var str_fail = {value:clue_info_fail_js[i]['value'],name:clue_info_fail_js[i]['name']};
            clue_info_fail_data.push(str_fail);
            clue_source_list_fail.push(clue_info_fail_js[i]['name'])
        }
        for(i in clue_info_zhuanhua_js){
            var str_zhuanhua = {value:clue_info_zhuanhua_js[i]['value'],name:clue_info_zhuanhua_js[i]['name']};
            clue_info_zhuanhua_data.push(str_zhuanhua);
            clue_source_list_zhuanhua.push(clue_info_zhuanhua_js[i]['name'])
        }
        console.log(clue_info_zhuanhua_data)
        var chart1 = echarts.init(document.getElementById('chart1'), 'shine');
        chart1.setOption({
            title : {
                text: '全部线索',
                x:'center',
                top:'2%'
            },
            tooltip : {
                trigger: 'item',
                formatter: "{a} <br/>{b} : {c} ({d}%)"
            },
            legend: {
                orient: 'vertical',
                left: '2%',
                top:'2%',
                data: clue_source_list_all
            },
            series : [
                {
                    name: '访问来源',
                    type: 'pie',
                    radius : '55%',
                    center: ['50%', '60%'],
                    data:clue_info_all_data,
                    itemStyle: {
                        emphasis: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }
            ],
            backgroundColor:'#F3F3F3'
        });

        var chart2 = echarts.init(document.getElementById('chart2'), 'shine');
        chart2.setOption({
            title : {
                text: '无效线索',
                x:'center',
                top:'2%'
            },
            tooltip : {
                trigger: 'item',
                formatter: "{a} <br/>{b} : {c} ({d}%)"
            },
            legend: {
                orient: 'vertical',
                left: '2%',
                top:'2%',
                data: clue_source_list_fail
            },
            series : [
                {
                    name: '访问来源',
                    type: 'pie',
                    radius : '55%',
                    center: ['50%', '60%'],
                    data:clue_info_fail_data,
                    itemStyle: {
                        emphasis: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }
            ],
            backgroundColor:'#F3F3F3'
        });


        var chart3 = echarts.init(document.getElementById('chart3'), 'shine');
        chart3.setOption({
            title : {
                text: '已转化线索',
                x:'center',
                top:'2%'
            },
            tooltip : {
                trigger: 'item',
                formatter: "{a} <br/>{b} : {c} ({d}%)"
            },
            legend: {
                orient: 'vertical',
                left: '2%',
                top:'2%',
                data: clue_source_list_zhuanhua
            },
            series : [
                {
                    name: '访问来源',
                    type: 'pie',
                    radius : '55%',
                    center: ['50%', '60%'],
                    data:clue_info_zhuanhua_data,
                    itemStyle: {
                        emphasis: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }
            ],
            backgroundColor:'#F3F3F3'
        });



        $(window).resize(function() {
            chart1.resize();
            chart2.resize();
            chart3.resize();
            chart4.resize();
        });

        var html = '';
        for(i in table_list_js){
            html += '<tr>';
            html += '<td>'+(parseInt(i) + parseInt(1))+'</td>';
            html += '<td>'+table_list_js[i].name+'</td>';
            html += '<td>'+table_list_js[i].num_all+'</td>';
            html += '<td>'+table_list_js[i].num_genjinzhong+'</td>';
            html += '<td>'+table_list_js[i].num_zhuanhua+'</td>';
            html += '<td>'+table_list_js[i].num_fail+'</td>';
            html += '<td>'+(table_list_js[i].num_zhuanhua*100/table_list_js[i].num_all).toFixed(2)+'%</td>';
            html += '</tr>';
        }
        $('#tbody').html(html);

    });
}


function ajaxres2(yearandmonth,area_id) {

    var url = 'get-clue-rate';
    $.post(url, {'yearandmonth': yearandmonth, 'area_id': area_id}, function (data) {

        var data_new = JSON.parse(data);
        var rate_list_name = new Array();
        var rate_list_value = new Array();

        for(i in data_new){
            rate_list_name.push(data_new[i]['input_type_name'])
        }
        for(i in data_new){
            rate_list_value.push(data_new[i]['rate'])
        }

console.log(data_new);
        var chart4 = echarts.init(document.getElementById('chart4'), 'shine');
        chart4.setOption({
            color: ['#3398DB'],
            tooltip : {
                trigger: 'axis',
                axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                    type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                }
            },
            legend: {
                data:['直接访问']
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis : [
                {
                    type : 'category',
                    data :rate_list_name,
                    axisTick: {
                        alignWithLabel: true
                    }
                }
            ],
            yAxis : [
                {
                    type : 'value'
                }
            ],
            series : [
                {
                    name:'直接访问',
                    type:'bar',
                    barWidth: '60%',
                    data:rate_list_value
                }
            ]
        });

    });
}






