$(function () {
    // console.log($(".lte-filterbox").offset().top + '/' + $(".lte-filterbox").offset().left);
    //渠道来源的门店大区选择
     new Vue({
        el: '#orgSelect',
        data:function(){
            return {
                formInline:{
                    desc:[]
                },
                options1 : selectOrgJson
            };
        },
        methods: {
            handlechange_shopid:function(value){
                $("#shopid").val(value);
                submitData();
            }
        }
    });
    
    //创建日期
    if($('#search_time'))
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
        $('#search_time').daterangepicker(config);
    }

    //选中
    $('#search_time').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + " - " + picker.endDate.format('YYYY-MM-DD'));
        submitData()
    });

    //对比率中的组织门店选择
     new Vue({
        el: '#orgSelect2',
        data:function(){
            return {
                formInline:{
                    desc:[]
                },
                options1 : selectOrgJson
            };
        },
        methods: {
            handlechange_shopid2:function(value){
                $("#shopid2").val(value);
                submitData2();
            }
        }
    });

    //创建日期
    if($('#search_time2'))
    {
        var config2 = {"opens": "left", "autoApply": true, "autoUpdateInput": false, "locale": {"format": 'YYYY-MM-DD',
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
        $('#search_time2').daterangepicker(config2);
    }

    //选中
    $('#search_time2').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + " - " + picker.endDate.format('YYYY-MM-DD'));
        submitData2()
    });

    var info_owner_id = data_common.info_owner_id
    var search_time = data_common.search_time

    ajaxres(info_owner_id,search_time);
    ajaxres2(info_owner_id,search_time);
});

function submitData() {
    var info_owner_id = $('#shopid').val();

    var search_time = $('#search_time').val();
    if(search_time != ''){
        ajaxres(info_owner_id,search_time)
    }

}
function submitData2() {
    var info_owner_id = $('#shopid2').val();

    var search_time = $('#search_time2').val();
    if(search_time != ''){
        ajaxres2(info_owner_id,search_time)
    }

}



function ajaxres(info_owner_id,search_time) {

    var url = 'get-clue-data';
    $.post(url,{'info_owner_id':info_owner_id,'search_time':search_time},function(data){
        var data_new = JSON.parse(data);
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
        var table_list_sum = data_new.table_list_sum;


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

        var chart1 = echarts.init(document.getElementById('chart1'), 'shine');
        chart1.setOption({
            color:['#ff825c','#fdce5d','#6bd0ff','#6be6c1','#5ecbda','#90b0fa','#b590f4','#6891d4'],
            title : {
                text: '全部线索',
                x:'center',
                top:'2%'
            },
            tooltip : {
                trigger: 'item',
                formatter: "{a} <br/>{b} : {c} ({d}%)"
            },
            /*legend: {
                orient: 'vertical',
                left: '2%',
                top:'2%',
                data: clue_source_list_all
            },*/
            series : [
                {
                    name: '渠道来源',
                    type: 'pie',
                    radius : '55%',
                    center: ['50%', '55%'],
                    data:clue_info_all_data,
                    label:{
                        normal:{
                            textStyle:{
                                color:'#333'
                            }
                        }
                    },
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
            color:['#ff825c','#fdce5d','#6bd0ff','#6be6c1','#5ecbda','#90b0fa','#b590f4','#6891d4'],
            title : {
                text: '无效线索',
                x:'center',
                top:'2%'
            },
            tooltip : {
                trigger: 'item',
                formatter: "{a} <br/>{b} : {c} ({d}%)"
            },
            /*legend: {
                orient: 'vertical',
                left: '2%',
                top:'2%',
                data: clue_source_list_fail
            },*/
            series : [
                {
                    name: '渠道来源',
                    type: 'pie',
                    radius : '55%',
                    center: ['50%', '55%'],
                    data:clue_info_fail_data,
                    label:{
                        normal:{
                            textStyle:{
                                color:'#333'
                            }
                        }
                    },
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
            color:['#ff825c','#fdce5d','#6bd0ff','#6be6c1','#5ecbda','#90b0fa','#b590f4','#6891d4'],
            title : {
                text: '已转化线索',
                x:'center',
                top:'2%'
            },
            tooltip : {
                trigger: 'item',
                formatter: "{a} <br/>{b} : {c} ({d}%)"
            },
            /*legend: {
                orient: 'vertical',
                left: '2%',
                top:'2%',
                data: clue_source_list_zhuanhua
            },*/
            series : [
                {
                    name: '渠道来源',
                    type: 'pie',
                    radius : '55%',
                    center: ['50%', '55%'],
                    data:clue_info_zhuanhua_data,
                    label:{
                        normal:{
                            textStyle:{
                                color:'#333'
                            }
                        }
                    },
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
        });

        var html = '';
        for(i in table_list_js){
            html += '<tr>';
            html += '<td>'+(parseInt(i) + parseInt(1))+'</td>';
            html += '<td>'+table_list_js[i].name+'</td>';
            html += '<td>'+table_list_js[i].num_all+'</td>';
            html += '<td>'+table_list_js[i].num_unassign+'</td>';
            html += '<td>'+table_list_js[i].num_genjinzhong+'</td>';
            html += '<td>'+table_list_js[i].num_zhuanhua+'</td>';
            html += '<td>'+table_list_js[i].num_fail+'</td>';
            html += '<td>'+table_list_js[i].rate+'%</td>';
            // html += '<td>'+(table_list_js[i].num_zhuanhua*100/table_list_js[i].num_all).toFixed(2)+'%</td>';
            html += '</tr>';
        }
        html += '<tr>';
        html += '<td></td>';
        html += '<td>总计</td>';
        html += '<td>'+table_list_sum.num_all+'</td>';
        html += '<td>'+table_list_sum.num_unassign+'</td>';
        html += '<td>'+table_list_sum.num_genjinzhong+'</td>';
        html += '<td>'+table_list_sum.num_zhuanhua+'</td>';
        html += '<td>'+table_list_sum.num_fail+'</td>';
        html += '<td>'+table_list_sum.rate+'%</td>';
        // html += '<td>'+(table_list_js[i].num_zhuanhua*100/table_list_js[i].num_all).toFixed(2)+'%</td>';
        html += '</tr>';
        $('#tbody').html(html);


    });
}


function ajaxres2(info_owner_id,search_time) {

    var url = 'get-clue-rate';
    $.post(url, {'info_owner_id': info_owner_id, 'search_time': search_time}, function (data) {

        var data_new = JSON.parse(data);
        var rate_list_name = new Array();
        var rate_list_value = new Array();

        for(i in data_new){
            rate_list_name.push(data_new[i]['input_type_name'])
        }
        for(i in data_new){
            rate_list_value.push(data_new[i]['rate'])
        }


        var chart4 = echarts.init(document.getElementById('chart4'), 'shine');
        chart4.setOption({
            color: ['#3398DB'],
            tooltip : {
                trigger: 'axis',
                axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                    type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                },
                formatter: function (params, ticket, callback) {
                    console.log(params);
                    var v = "";
                    for(var i=0;i<params.length;i++){
                       v += params[i].name +'<br><span style="display:inline-block;margin-right:5px;border-radius:10px;width:9px;height:9px;background-color:'+ params[i].color +'"></span>' + params[i].seriesName+" : " + params[i].value +"%";
                    }
                    return v;
                }
            },
            legend: {
                data:['线索有效率'],
                right:'4%'
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
                    type : 'value',
                    axisLabel:{
                        formatter:'{value}%'
                    }
                }
            ],
            series : [
                {
                    name:'线索有效率',
                    type:'bar',
                    barWidth: '60%',
                    data:rate_list_value
                }
            ]
        });

        $(window).resize(function() {
            chart4.resize();
        });
    });
}






