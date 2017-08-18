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
        post();
    });

    //门店下拉控件
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
                post();
            }
        }
    });

    ajaxres($('#datetimepicker').val(),xxid,levels);



});

function post() {
    var level = $('#level').val();
    var id = null;
    var sid = $('#sid').val();

    if (level == 1) {
        ajaxres($('#datetimepicker').val(),sid,1);//公司下面的区域信息

    }else if(level  == 2) {
        if (levels == 0){
            id = sid.split(',')[1];
        }else {
            id = sid;
        }

        ajaxres($('#datetimepicker').val(),id,2);//区域下面的门店信息
    }
}



function ajaxres(yearandmonth,id,level) {

    //level 0 每个公司的数据  1 公司下面的区域数据  2区域下面的门店数据ajaxarea
    var url = null;
    if (level == 0) url = 'ajaxarea';
    if (level == 1) url = 'ajaxarea1';
    if (level == 2) url = 'ajaxarea2';



    $.post(url,{'yearandmonth':yearandmonth,'id':id},function(data){

       var count = data.counts;
       var shop = new Array();//公司、区、或门店名
       var target_num = new Array();//销售目标
       var finish_num = new Array();//实际完成

       $('#ajaxitem').html(data.html);


       for(var i = 0 ; i < count ;i++){
           shop.push(data[i]['name']);
           target_num.push(data[i]['list']['target_num']);
           finish_num.push(data[i]['list']['finish_num']);

       }


       //报表信息
       var chart = echarts.init(document.getElementById('chart'), 'shine');
       chart.setOption(
           option = {
               color: ['#3398DB',"#7CFC00"],
               tooltip : {
                   trigger: 'axis',
                   axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                       type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                   }
               },
               legend: {
                   align: 'right',
                   right:'4%',
                   orient:'vertical',
                   itemWidth:15,
                   itemHight:15,
                   data:[
                       {
                           name: '目标完成',
                           icon: 'circle',
                       },
                       {
                           name: '实际完成',
                           icon: 'circle',
                       }
                   ],
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
                       data : shop,
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
                       name:'目标完成',
                       type:'bar',
                       barWidth: '40px',
                       data:target_num
                   },
                   {
                       name:'实际完成',
                       type:'bar',
                       barWidth: '40px',
                       data:finish_num
                   }
               ]
           }
       );

       $(window).resize(function() {
           chart.resize();
       });


   },'json')
}

