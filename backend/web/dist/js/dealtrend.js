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

    
    $('#input_type').Cascader({"dataurl" : '/get-json-data-for-select/index?type=getInputType',islevel:false,onchange:function(a){
        submitData()
    }});

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

    // console.log(data_common)
    var info_owner_id = data_common.info_owner_id
    var info_owner_name = data_common.info_owner_name

    var input_type_id = data_common.input_type_id
    var input_type_name = data_common.input_type_name
    var search_time = data_common.search_time

    ajaxres(input_type_id,info_owner_id,search_time);
    ajaxres2(input_type_id,info_owner_id,search_time);

    $('#info_owner_id').val(info_owner_id)
    $('#info_owner_name').val(info_owner_name)

    $('#input_type_id').val(input_type_id)
    $('#input_type_name').val(input_type_name)


    $('#info_owner_id2').val(info_owner_id)
    $('#info_owner_name2').val(info_owner_name)

    $('#input_type_id2').val(input_type_id)
    $('#input_type_name2').val(input_type_name)

});

function submitData() {
    var info_owner_id = $('#shopid').val();
    var input_type_id = $('#input_type_id').val();

    var year = $('#search_year').val();

    if(year != ''){
        ajaxres(input_type_id,info_owner_id,year)
    }

}
function submitData2() {
    var info_owner_id = $('#shopid2').val();
    var input_type_id = $('#input_type_id2').val();
    var year = $('#search_year2').val();

    if(year != ''){
        ajaxres2(input_type_id,info_owner_id,year)
    }
}


function ajaxres(input_type_id,info_owner_id,year) {

    var url = 'get-count-data';
    $.post(url,{'input_type_id':input_type_id,'info_owner_id':info_owner_id,'year':year},function(data) {

        var data_new = JSON.parse(data);
        //处理数据 拼接图表参数对象
        var count_data_arr = new Array();
        var month_arr = new Array();
        var count_info_js = data_new;


        for(i in count_info_js){
            var str_all = {value:count_info_js[i]['value'],name:count_info_js[i]['month']};
            count_data_arr.push(str_all);
            month_arr.push(count_info_js[i]['month'])
        }


        var chart = echarts.init(document.getElementById('main1'), 'shine');
        chart.setOption({
            title: {
                subtext: '成交量',
                left:'3%',
                subtextStyle:{
                    color:'#333'
                }
            },
            tooltip: {
                trigger: 'axis'
            },
            /*legend: {
                data: ['成交量', '']
            },*/
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis: {
                type: 'category',
                boundaryGap: false,
                data: month_arr
            },
            yAxis: {
                type: 'value'
            },
            series: [
                {
                    name: '成交量',
                    type: 'line',
                    stack: '总量',
                    data: count_data_arr
                }/*,
                {
                    name: '',
                    type: 'line',
                    stack: '总量',
                    data: []
                }*/
            ]
        });


        $(window).resize(function () {
            chart.resize();
        });
    });
}


function ajaxres2(input_type_id,info_owner_id,year) {

    var url = 'get-rate-data';
    $.post(url,{'input_type_id':input_type_id,'info_owner_id':info_owner_id,'year':year},function(data) {

        var data_new = JSON.parse(data);
        //处理数据 拼接图表参数对象
        var rate_data_arr = new Array();
        var month_arr = new Array();
        var rate_info_js = data_new;


        for(i in rate_info_js){
            var str_all = {value:rate_info_js[i]['value'],name:rate_info_js[i]['month']};
            rate_data_arr.push(str_all);
            month_arr.push(rate_info_js[i]['month'])
        }



        var chart1 = echarts.init(document.getElementById('main2'), 'shine');
        chart1.setOption({
            title: {
                subtext: '成交率',
                left:'3%',
                subtextStyle:{
                    color:'#333'
                }
            },
            tooltip: {
                trigger: 'axis',
                formatter: function (params, ticket, callback) {
                    console.log(params);
                    var v = "";
                    for(var i=0;i<params.length;i++){
                       v += params[i].name +'<br><span style="display:inline-block;margin-right:5px;border-radius:10px;width:9px;height:9px;background-color:'+ params[i].color +'"></span>' + params[i].seriesName+" : " + params[i].value +"%";
                    }
                    return v;
                }
            },
            /*legend: {
                data: ['成交率', '']
            },*/
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis: {
                type: 'category',
                boundaryGap: false,
                data: month_arr
            },
            yAxis: {
                type: 'value',
                axisLabel:{
                    formatter:'{value}%'
                }
            },
            series: [
                {
                    name: '成交率',
                    type: 'line',
                    stack: '总量',
                    data: rate_data_arr
                }/*,
                {
                    name: '',
                    type: 'line',
                    stack: '总量',
                    data: []
                }*/
            ]
        });

        $(window).resize(function () {
            chart1.resize()
        });
    });
}