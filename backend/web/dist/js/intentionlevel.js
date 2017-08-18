$(function () {
     new Vue({
        el: '#orgSelect',
        data:function(){
            return {
                formInline:{
                    desc:[]
                },
                options1 : selectOrgJson,
                selectedOptions3 : defaultSelectArray
            };
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

    //处理数据 拼接图表参数对象
    var intention_name_arr = new Array();
    var intention_info_arr = new Array();
    // var intention_info_js = data_new.intention_info;
    // var intention_info_child_js = data_new.intention_info_child;
    for(i in intention_info_js){
        var str_all = {value:intention_info_js[i]['sum_num'],name:intention_info_js[i]['intention_level_name']};
        intention_info_arr.push(str_all);
        intention_name_arr.push(intention_info_js[i]['intention_level_name'])
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
            data: intention_name_arr
        },*/
        series : [
            {
                name: '意向等级',
                type: 'pie',
                radius : '70%',
                center: ['50%', '50%'],
                data:intention_info_arr,
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


    // ajaxres()
});

function submitData() {
    var info_owner_id = $('#shopid').val();
    var input_type_id = $('#input_type_id').val();
    setTimeout(function(){
        $('#form').submit();
    },150);
}

    // cs_datetimepicker('#datetime');
// function ajaxres() {
//
//     var url = 'get-intention-level';
//     $.post(url,{'yearandmonth':'','area_id':''},function(data){
// console.log(data)
//         var data_new = JSON.parse(data);
//         //处理数据 拼接图表参数对象
//         var intention_name_arr = new Array();
//         var intention_info_arr = new Array();
//         var intention_info_js = data_new.intention_info;
//         var intention_info_child_js = data_new.intention_info_child;
//         for(i in intention_info_js){
//             var str_all = {value:intention_info_js[i]['sum_num'],name:intention_info_js[i]['intention_level_name']};
//             intention_info_arr.push(str_all);
//             intention_name_arr.push(intention_info_js[i]['intention_level_name'])
//         }
//
//         var chart = echarts.init(document.getElementById('chart'), 'shine');
//         chart.setOption({
//             tooltip : {
//                 trigger: 'item',
//                 formatter: "{a} <br/>{b} : {c} ({d}%)"
//             },
//             legend: {
//                 orient: 'vertical',
//                 left: 'left',
//                 data: intention_name_arr
//             },
//             series : [
//                 {
//                     name: '访问来源',
//                     type: 'pie',
//                     radius : '55%',
//                     center: ['50%', '50%'],
//                     data:intention_info_arr,
//                     itemStyle: {
//                         emphasis: {
//                             shadowBlur: 10,
//                             shadowOffsetX: 0,
//                             shadowColor: 'rgba(0, 0, 0, 0.5)'
//                         }
//                     }
//                 }
//             ]
//         });
//         $(window).resize(function() {
//             chart.resize();
//         });
//
//
//
//         // <tr>
//         // <td>1</td>
//         // <td>南京区</td>
//         // <td>566</td>
//         // <td>121</td>
//         // <td>45</td>
//         // <td>12</td>
//         // <td>5.67%</td>
//         // </tr>
//
//         var html = '';
//         for(i in intention_info_child_js){
//             var data_one = intention_info_child_js[i];
//             html += '<tr>';
//             html += '<td>'+(parseInt(i) + parseInt(1))+'</td>';
//             html += '<td>'+data_one.info_owner_name+'</td>';
//             html += '<td>'+(parseInt(data_one.H)+parseInt(data_one.A)+parseInt(data_one.B)+parseInt(data_one.C)+parseInt(data_one.N))+'</td>';
//             html += '<td>'+parseInt(data_one.H)+'</td>';
//             html += '<td>'+data_one.A+'</td>';
//             html += '<td>'+data_one.B+'</td>';
//             html += '<td>'+data_one.C+'</td>';
//             html += '<td>'+data_one.N+'</td>';
//
//
//             html += '</tr>';
//         }
//         $('#tbody').html(html);
//     })
//
//
//
// }
