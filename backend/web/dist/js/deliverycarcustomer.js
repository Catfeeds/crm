$(function () {
     new Vue({
        el: '#orgSelect',
        data:function(){
            return {
                formInline:{
                    desc:[]
                },
                options1 : selectOrgJson
            }
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


    //渠道的门店选择
     new Vue({
        el: '#orgSelect2',
        data:function(){
            return {
                formInline:{
                    desc:[]
                },
                options1 : selectOrgJson
            }
        },
        methods: {
            handlechange_shopid:function(value){
                $("#shopid2").val(value);
                submitData2();
            }
        }
    });
    $('#input_type2').Cascader({"dataurl" : '/get-json-data-for-select/index?type=getInputType',islevel:false,onchange:function(a){
        submitData2()
    }});

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


    // cs_datetimepicker('#datetime');
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



function ajaxres(info_owner_id,search_time,level) {

    var url = 'get-deal-period';
    $.post(url,{'info_owner_id':info_owner_id,'search_time':search_time,'level':level},function(data){

        var data_new = JSON.parse(data);
        //处理数据 拼接图表参数对象
        var period_info_arr = new Array();
        var period_name_arr = new Array();
        var period_info_js = data_new.period_info;
        var child_info_js = data_new.child_info;
        var data_sum = data_new.data_sum;
        // var fail_tags_list_js = data_new.fail_tags_list;

        for(i in period_info_js){
            var str_all = {value:period_info_js[i]['sum_num'],name:period_info_js[i]['date_type_name']};
            period_info_arr.push(str_all);
            period_name_arr.push(period_info_js[i]['date_type_name'])
        }

        var chart = echarts.init(document.getElementById('chart'), 'shine');
        chart.setOption({
            color:['#ff825c','#fdce5d','#6bd0ff','#6be6c1','#5ecbda','#90b0fa','#b590f4','#6891d4'],
            tooltip : {
                trigger: 'item',
                formatter: "{a} <br/>{b} : {c} ({d}%)"
            },
            /*legend: {
                orient: 'vertical',
                left: 'left',
                data: period_name_arr
            },*/
            series : [
                {
                    name: '意向等级',
                    type: 'pie',
                    radius : '70%',
                    center: ['50%', '50%'],
                    data:period_info_arr,
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
            ]
        });

        $(window).resize(function() {
            chart.resize();
        });


        var html = '';
        var sort = 0;
        for(i in child_info_js){
            var data_one = child_info_js[i];
            sort ++
            console.log(data_one[1])
            html += '<tr>';
            html += '<td>'+sort+'</td>';
            html += '<td>'+data_one.name+'</td>';
            html += '<td>'+data_one[1]+'</td>';
            html += '<td>'+data_one[2]+'</td>';
            html += '<td>'+data_one[3]+'</td>';
            html += '<td>'+data_one[4]+'</td>';
            html += '<td>'+data_one[5]+'</td>';

            html += '</tr>';
        }

        html += '<tr>';
        html += '<td></td>';
        html += '<td>总计</td>';
        html += '<td>'+data_sum[1]['sum_num']+'</td>';
        html += '<td>'+data_sum[2]['sum_num']+'</td>';
        html += '<td>'+data_sum[3]['sum_num']+'</td>';
        html += '<td>'+data_sum[4]['sum_num']+'</td>';
        html += '<td>'+data_sum[5]['sum_num']+'</td>';

        html += '</tr>';

        $('#tbody').html(html);
    })
}

function ajaxres2(info_owner_id,search_time,level) {

    var url = 'get-input-type';
    $.post(url,{'info_owner_id':info_owner_id,'search_time':search_time,'level':level},function(data){

        var data_new = JSON.parse(data);
        //处理数据 拼接图表参数对象
        var input_type_arr = new Array();
        var input_type_name_arr = new Array();
        var input_type_js = data_new.input_type_info;
        var child_input_type_info_js = data_new.child_input_type_info;
        var top_input_type_list_js = data_new.top_input_type_list;
        var info_sum = data_new.info_sum;
        for(i in input_type_js){
            var str_all = {value:input_type_js[i]['sum_num'],name:input_type_js[i]['input_type_name']};
            input_type_arr.push(str_all);
            input_type_name_arr.push(input_type_js[i]['input_type_name'])
        }

        var chart1 = echarts.init(document.getElementById('chart1'), 'shine');
        chart1.setOption({
            color:['#ff825c','#fdce5d','#6bd0ff','#6be6c1','#5ecbda','#90b0fa','#b590f4','#6891d4'],
            tooltip : {
                trigger: 'item',
                formatter: "{a} <br/>{b} : {c} ({d}%)"
            },
            /*legend: {
                orient: 'vertical',
                left: 'left',
                data: input_type_name_arr
            },*/
            series : [
                {
                    name: '渠道来源',
                    type: 'pie',
                    radius : '70%',
                    center: ['50%', '50%'],
                    data:input_type_arr,
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
            ]
        });
        $(window).resize(function() {
            chart1.resize();
        });

        //拼接栏目
        var column = '';
        for(h in top_input_type_list_js){

            column  += '<th class="change">'+top_input_type_list_js[h]['input_type_name']+'</th>';
        }

        //拼接列表
        var sort = 0;
        var html = '';
        for(i in child_input_type_info_js){
            sort ++
            var data_one = child_input_type_info_js[i];
            html += '<tr>';
            html += '<td>'+sort+'</td>';
            html += '<td>'+data_one.info_owner_name+'</td>';
            for (j in top_input_type_list_js){
                html += '<td>'+data_one[top_input_type_list_js[j]['input_type_id']]+'</td>';
            }
            html += '</tr>';
        }

        html += '<tr>';
        html += '<td></td>';
        html += '<td>总计</td>';
        for (j in top_input_type_list_js){
            html += '<td>'+info_sum[top_input_type_list_js[j]['input_type_id']]['sum_num']+'</td>';
        }
        html += '</tr>';


        $(".change").remove();
        $('#tbody1').html(html);
        $('#column').append(column);
    })
}
