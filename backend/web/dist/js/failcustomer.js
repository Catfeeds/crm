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

    //处理数据 拼接图表参数对象

    // cs_datetimepicker('#datetime');
    //创建日期
    if($('#search_time2'))
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
        $('#search_time2').daterangepicker(config);
    }

    //选中
    $('#search_time2').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + " - " + picker.endDate.format('YYYY-MM-DD'));
        submitData2()
    });

    var info_owner_id = data_common.info_owner_id
    var input_type_id = data_common.input_type_id
    var search_time = data_common.search_time

    ajaxres1(info_owner_id,input_type_id,search_time)
    var select_page = $('#select_page').val();
    ajaxres2(search_time,1,select_page)
});

function submitData() {
    var input_type_id = $('#input_type_id').val();
    var info_owner_id = $('#shopid').val();
    var search_time = $('#search_time').val();
    if(search_time != ''){
        ajaxres1(info_owner_id,input_type_id,search_time)
    }
}
function submitData2() {
    var search_time = $('#search_time2').val();
    // var input_page = $('#input_page').val();
    var select_page = $('#select_page').val();
    // alert(select_page);return
    if(input_page != '' && select_page != ''){
        ajaxres2(search_time,1,select_page)
    }
}

function submitData3() {
    var search_time = $('#search_time2').val();
    var select_page = $('#select_page').val();
    // alert(select_page);return
    if(search_time != '' && select_page != ''){
        ajaxres2(search_time,1,select_page)
    }
}

function submitData4(currentPage) {
    var search_time = $('#search_time2').val();
    var select_page = $('#select_page').val();
    // alert(select_page);return
    if(search_time != '' && select_page != ''){
        ajaxres2(search_time,currentPage,select_page)
    }
}



function ajaxres1(info_owner_id,input_type_id,search_time) {

    var url = 'get-intention-fail-customer';
    $.post(url,{'info_owner_id':info_owner_id,'input_type_id':input_type_id,'search_time':search_time},function(data){

        var data_new = JSON.parse(data);
        //处理数据 拼接图表参数对象
        var fail_name_arr = new Array();
        var fail_info_arr = new Array();
        var fail_info_js = data_new.fail_info;
        var child_info_list_js = data_new.child_info_list;
        var fail_tags_list_js = data_new.fail_tags_list;
        var data_sum_new = data_new.data_sum_new;
        for(i in fail_info_js){
            var str_all = {value:fail_info_js[i]['sum_num'],name:fail_info_js[i]['tag_name']};
            fail_info_arr.push(str_all);
            fail_name_arr.push(fail_info_js[i]['tag_name'])
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
                data: fail_name_arr
            },*/
            series : [
                {
                    name: '意向战败原因',
                    type: 'pie',
                    radius : '70%',
                    center: ['50%', '50%'],
                    data:fail_info_arr,
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


        //拼接栏目
        var column = '';
        for(h in fail_tags_list_js){

            column  += '<th class="change">'+fail_tags_list_js[h]['tag_name']+'</th>';
        }

        //拼接列表
        var html = '';
        for(i in child_info_list_js){
            var data_one = child_info_list_js[i];
            html += '<tr>';
            html += '<td>'+(parseInt(i) + parseInt(1))+'</td>';
            html += '<td>'+data_one.info_owner_name+'</td>';
            for (j in fail_tags_list_js){
                html += '<td>'+data_one[fail_tags_list_js[j]['tag_id']]+'</td>';
            }

            html += '</tr>';
        }

        html += '<tr>';
        html += '<td></td>';
        html += '<td>总计</td>';

        for (j in fail_tags_list_js){
            html += '<td>'+data_sum_new[fail_tags_list_js[j]['tag_id']]['sum_num']+'</td>';
        }

        html += '</tr>';

        $(".change").remove();
        $('#tbody').html(html);
        $('#column').append(column);
    })
}

function ajaxres2(search_time,currentPage,perPage) {

    var url = 'get-order-fail-customer';
    $.post(url,{'search_time':search_time,'currentPage':currentPage,'perPage':perPage},function(data){

        var data_new = JSON.parse(data);
        // console.log(data_new)
        var info_list = data_new.info_list
        var pages_info = data_new.pages


        var pages = '';
        pages += '<li><a href="#" onclick="submitData4('+(pages_info.currentPage - 1)+')">«</a></li>'
        for (j=1;j<=pages_info.pageCount;j++){
            if(j == pages_info.currentPage){
                pages += '<li><a style="color: #00a0e9" href="#" onclick="submitData4('+j+')">'+j+'</a></li>'
            }else{
                pages += '<li><a href="#" onclick="submitData4('+j+')">'+j+'</a></li>'
            }

        }
        pages += '<li><a href="#" onclick="submitData4('+(parseInt(pages_info.currentPage) + 1)+')">»</a></li>'
        $('#page_num').html(pages);

        //拼接列表
        var html = '';
        for(i in info_list){
            html += '<tr>';
            html += '<td>'+(parseInt(i) + parseInt(1))+'</td>';
            html += '<td><a href="/customer/customer-detail?id='+info_list[i].id+'">'+info_list[i].customer_name+'</a></td>';
            html += '<td>'+info_list[i].car_type_name+'</td>';
            html += '<td>'+info_list[i].last_fail_time+'</td>';
            html += '<td>'+info_list[i].salesman_name+'</td>';
            html += '<td>'+info_list[i].fail_reason+'</td>';
            html += '</tr>';
        }

        $('#tbody2').html(html);

    })
}
