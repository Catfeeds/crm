$(function () {
    //组织-门店
     new Vue({
        el: '#orgSelect',
        data:function(){
            return {
                formInline:{
                    desc:[]
                },
                options1 : selectOrgJson,
                selectedOptions3 : defaultSelectArray
            }
        },
        methods: {
            handlechange_shopid:function(value){
                $("#shopid").val(value);
                submitData()
            }
        }
    });
    
    
    $('#input_type').Cascader({"dataurl" : '/get-json-data-for-select/index?type=getInputType',islevel:false,onchange:function(a){
        submitData()
    }});

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



    // var data_new = JSON.parse(funnel_data);
    //处理数据 拼接图表参数对象
    var funnel_name_arr = new Array();
    var expect_data_arr = new Array();
    var real_data_arr = new Array();
    // var expect_data_js = data_new.expect_data;
    // var real_data_js = data_new.real_data;


    for(i in funnel_data.expect_data){
        var str_expect = {value:funnel_data.expect_data[i]['value'],name:funnel_data.expect_data[i]['name']};
        expect_data_arr.push(str_expect);
        funnel_name_arr.push(funnel_data.expect_data[i]['name'])
    }

    for(i in funnel_data.real_data_rate){
        var str_real = {value:funnel_data.real_data_rate[i]['value'],name:funnel_data.real_data_rate[i]['name']};
        real_data_arr.push(str_real);
    }




    //日期
    // cs_datetimepicker('#date-time');
    console.log(real_data_arr);
    var chart = echarts.init(document.getElementById('funnel'), 'shine');
    chart.setOption({
        color:['#ff825c','#fdce5d','#6bd0ff','#6be6c1','#5ecbda','#90b0fa','#b590f4','#6891d4'],
        /*legend: {
            data: funnel_name_arr
        },*/
        series: [{
            name: '预期',
            type: 'funnel',
            left: '5%',
            width: '75%',
            label: {
                normal: {
                    formatter: '{b}',
                    textStyle:{
                        color:'#333'
                    }
                },
                emphasis: {
                    position: 'inside',
                    formatter: '{b}',//预期: {c}%
                    textStyle:{
                        color:'#333'
                    }
                }
            },
            labelLine: {
                normal: {
                    show: false
                }
            },
            itemStyle: {
                normal: {
                    opacity: 0.7
                }
            },
            data: expect_data_arr
        }, {
            name: '实际',
            type: 'funnel',
            left: '5%',
            width: '75%',
            maxSize: '100%',
            sort:'none',
            label: {
                normal: {
                    position: 'inside',
                    formatter: '{c}%',
                    textStyle: {
                        color: '#fff'
                    }
                },
                emphasis: {
                    position: 'inside',
                    formatter: '{b}: {c}%'
                }
            },
            itemStyle: {
                normal: {
                    opacity: 0.5,
                    borderColor: '#fff',
                    borderWidth: 2
                }
            },
            data: real_data_arr
        }]
    });
    $(window).resize(function() {
        chart.resize();
    });
});

function submitData() {
    var info_owner_id = $('#shop_id').val();
    var input_type_id = $('#input_type_id').val();
    var search_time = $('#search_time').val();
    if(input_type_id !== '' && search_time !== ''){
      setTimeout(function(){
          $('#form').submit();
      },150);
    }

}
