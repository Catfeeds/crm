function fun(){
    var hw = $(window).width();
    var h = document.body.scrollHeight;
    if(hw < 641 && hw > 319){
        $('html').css({fontSize:(hw/6.4)+'px'});
    }else{
        $('html').css({fontSize:100});
    }
    $("#overflow").height(h);
}

fun();
$(window).resize(function(){
    fun();
});

//随机数
function num_random(){
    var result = Math.ceil(Math.random()*100000);
    return result;
}

//获取参数
function GetQueryString(name)
{
    var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if(r!=null)return  unescape(r[2]); return null;
}


/*$(window).scroll(function(){
    var scrolltop = $("body").scrollTop();
        if(scrolltop>0){
            $(".filterbox").addClass("fixed");
        }else{
            $(".filterbox").removeClass("fixed");
        }
});*/

$("#overflow").click(function(){
    var _this = $(this);
    setTimeout(function() {
        _this.addClass("none");
        $(".input-wrapper").next().addClass("none");
    }, 150);
});

$('.cascader-list li').on('touchstart',function(e){
    e.preventDefault();
});


/*显示隐藏图表*/
$(".chart_box .m-switch input").change(function(){
    if(!$(this).prop("checked")){
        $(this).parents(".chart_box").find(".table").addClass("none");
    }else{
        $(this).parents(".chart_box").find(".table").removeClass("none");
    }
});


function sxchange(a,b,c){
    var result=[];
        if(a){
            var id = $(".channelbox .input-wrapper").find("input").data("id");
            if(id == undefined){
                result.push("");
            }else{
                result.push(id);
            }
        }
        if(b){
            result.push($(".timebox .input-wrapper").find("input").val());
        }
        return result;
}


//表格显示数据处理
function deal(a,b){
    if(a != 0){
        a = a + b;
    }
    return a;
}


//饼图
function pieoption(a,c,d){ //a:option 参数  b:legend 数据 c:图表名称 d:图表数据
    //console.log(d);
    a ={
        color:['#ff825c','#fdce5d','#6bd0ff','#6be6c1','#5ecbda','#90b0fa','#b590f4','#6891d4'],
        tooltip : {
            trigger: 'item',
            formatter: "{a} <br/>{b} : {c} ({d}%)"
        },
        /*legend: {
            orient: 'vertical',
            left: 'left',
            data: b,
            show: false
        },*/
        series : [
            {
                name: c,
                type: 'pie',
                radius : '80%',
                center: ['50%', '50%'],
                data:d,
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
    };
    return a;
}

//折线图
function Brokenline(a,b,top,formmat,islegend){ //a:变量名 b:图表数据 c:数据单位
    var v = [];
        for (var i = 0; i < b.data.length; i++) {
            if(islegend){
               var name = b.legend[i];
            }
            v.push({
                        name,
                        type: 'line',
                        data:b.data[i],
                        lineStyle: {
                            normal: {
                                width: 2
                            }
                        }
                });
        };

        if(islegend){
            var legend = {
                    top: '0%',
                    right: '2%',
                    align: 'right',
                    orient: 'vertical',
                    itemWidth: 16,
                    itemHeight: 16,
                    padding:[0,0],
                    textStyle:{fontSize:12},
                    icon:'rect',
                    data: b.legend,
            }
        }
        a = {
                color:["#ff825c","#fdce5d","#6bd0ff"],
                tooltip : {
                    trigger: 'axis',
                    formatter :formmat,
                    axisPointer:{
                        lineStyle:{
                            color:'#fdc235'
                        }
                    }
                },
                legend:legend,
                grid: {
                    left: '2%',
                    right: '2%',
                    top: top,
                    bottom: '3%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: b.x,
                    boundaryGap: false,
                    splitLine: {
                        show: true,
                        interval: 'auto',
                        lineStyle: {
                            color: ['#eee']
                        }
                    },
                    axisTick: {
                        show: false
                    },
                    axisLine: {
                        lineStyle: {
                            color: '#b3b3b3'
                        }
                    },
                    axisLabel: {
                        margin: 10,
                        textStyle: {
                            fontSize: 12,
                            align:'right'
                        }
                    }
                },
                yAxis: {
                    type: 'value',
                    splitLine: {
                        lineStyle: {
                            color: ['#f3f3f3']
                        }
                    },
                    axisTick: {
                        show: false
                    },
                    axisLine: {
                        lineStyle: {
                            color: '#b3b3b3'
                        }
                    },
                    axisLabel: {
                        margin: 10,
                        textStyle: {
                            fontSize: 12
                        }
                    }
                },
                series: v
            };
    return a;
}


//漏斗
function funnel(a,b){ //a:option 参数   b:图表数据
    var v = [];

    a = {
            tooltip: {
                trigger: 'item',
                formatter: "{a} <br/>{b} : {c}%"
            },
            series: [
                {
                    name: "预期",
                    type: 'funnel',
                    left: '5%',
                    width: '80%',
                    label: {
                        normal: {
                            formatter: '{b}'
                        },
                        emphasis: {
                            position:'inside',
                            formatter: '{b}: {c}%'
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
                    data:b.expect_data
                },
                {
                    name: "实际",
                    type: 'funnel',
                    left: '5%',
                    width: '80%',
                    maxSize: '100%',
                    label: {
                        normal: {
                            position: 'inside',
                            formatter: '{c}%',
                            textStyle: {
                                color: '#fff'
                            }
                        },
                        emphasis: {
                            position:'inside',
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
                    data:b.real_data
                }
            ]
        };

    return a;
}

/**时间选择
        ** 年月
        **/
        var startyear = 2015;
        var year = new Date().getFullYear();
        var month = new Date().getMonth();
        if($(".timebox .input-wrapper").find(".yearmonth").val() ==""){
                var m="";
                if((month+1)>9){
                    m = month+1;
                }else{
                    m = "0"+ (month+1);
                }
                datetime = year +'-'+ m;
            $(".timebox .input-wrapper").find(".yearmonth").val(datetime);
        }else{
            $(".timebox .input-wrapper").find("input").val(year);
        }
        $(".timebox .input-wrapper").click(function(){
            if(!$(this).parent().hasClass("year")){
                $(this).parent().siblings().find(".input-wrapper").next().addClass("none");
                if($(this).next().hasClass("none")){
                    $(this).next().removeClass("none");
                    $(".swiper-wrapper").html('');
                    var cur_show_year = $(this).find("input").val().split("-")[0];
                    var cur_show_month = $(this).find("input").val().split("-")[1]*100/100;
                    start_swipe_yearmonth(cur_show_year,cur_show_month);
                    $("#overflow").removeClass("none");
                }else{
                    $(this).next().addClass("none");
                    $(".swiper-wrapper").html('');
                    $("#overflow").addClass("none");
                }
            }else{
                $(this).parent().siblings().find(".input-wrapper").next().addClass("none");
                if($(this).next().hasClass("none")){
                    $(this).next().removeClass("none");
                    $(".swiper-wrapper").html('');
                    var cur_show_year = $(this).find("input").val();
                    start_swipe_year(cur_show_year);
                    $("#overflow").removeClass("none");
                }else{
                    $(this).next().addClass("none");
                    $(".swiper-wrapper").html('');
                    $("#overflow").addClass("none");
                }
            }

        });

        var swiper = new Swiper('.swiper-container', {
            nextButton: '.next',
            prevButton: '.prev',
            pagination: '.swiper-pagination',
            paginationClickable: true,
            spaceBetween: 0
        });

        $("body").delegate(".swiper-slide li a","click",function(){
            var _this = $(this);
            var parents = _this.parents(".timebox");
            if(!parents.hasClass("year")){
                var y = $(this).parents(".swiper-slide").attr("data-year");
                var m = $(this).find("span").text();
                    if(y*1 <= year && m*1 <= (month+1)){
                        if(m<10){ m = '0' + m;}
                        $(this).parents(".timelist").prev().find("input").val(y+'-'+m);
                        $(this).parent().addClass("active").siblings().removeClass("active");
                        $(this).parents(".swiper-slide").siblings().find("li").removeClass("active");
                        setTimeout(function() {
                            _this.parents(".timelist").addClass("none");
                        },100);
                        $("#overflow").addClass("none");
                    }else if(y*1 < year){
                        if(m<10){ m = '0' + m;}
                        $(this).parents(".timelist").prev().find("input").val(y+'-'+m);
                        $(this).parent().addClass("active").siblings().removeClass("active");
                        $(this).parents(".swiper-slide").siblings().find("li").removeClass("active");
                        setTimeout(function() {
                            _this.parents(".timelist").addClass("none");
                        },100);
                        $("#overflow").addClass("none");
                    }
            }else{
                var y = $(this).find("span").text();
                    if(y*1 <= year){
                        $(this).parents(".timelist").prev().find("input").val(y);
                        $(this).parents(".timelist").prev().find("input[type=text]").val(y);
                        $(this).parent().addClass("active").siblings().removeClass("active");
                        $(this).parents(".swiper-slide").siblings().find("li").removeClass("active");
                        setTimeout(function() {
                            _this.parents(".timelist").addClass("none");
                        },100);
                        $("#overflow").addClass("none");
                    }
            }

        });

        //年
        function start_swipe_year(m){//m为显示的当前年份，n为显示的当前月份
            var num = Math.ceil((m-startyear)/10);
                if(num == 0 ){
                    num = 1;
                }

                for(var j = 1; j <= num+1; j++){
                    swiper.appendSlide(slidedata_year(j,startyear,m));
                }

                slidenum = Math.floor((m-startyear)/10);
                swiper.slideTo(slidenum,1, false);

                swiper.on('slideChangeStart',function(swiper){
                    swiper.lockSwipes();
                });

                swiper.on('slideChangeEnd',function(swiper){
                    swiper.unlockSwipes();
                    var len = swiper.slides.length;
                    if(swiper.activeIndex == swiper.slides.length-1){
                        var newyear = swiper.activeIndex+2;
                            swiper.appendSlide(slidedata_year(newyear,startyear,m));
                    }
                });
        }

        function slidedata_year(a,b,c){
            var html="";
            var class_active = $(".swiper-slide li").hasClass("active");
                html+='<div class="swiper-slide"><ul>';
                for(var i = (a-1)*10 + b ; i < a*10 + b ; i++){
                    if( i == c && !class_active){
                            html+='<li class="active" ><a ';
                            if(i > year){
                                html+='class="c-timegray" ';
                            }
                            html+=' href="javascript:;"><span>'+ i +'</span></a></li>';
                     }else{
                            html+='<li><a ';
                            if(i > year){
                                html+='class="c-timegray" ';
                            }
                            html+=' href="javascript:;"><span>'+ i +'</span></a></li>';
                    }
                }
                html+="</ul></div>";
            return html;
        }



        //年月
        function start_swipe_yearmonth(m,n){//m为显示的当前年份，n为显示的当前月份
            if(startyear <= year){
                for(var i = m-startyear;i >= 0;i--){
                    swiper.appendSlide(slidedata_yearmonth(m-i,startyear,m,n));
                }
                swiper.appendSlide(slidedata_yearmonth(m*1+1,startyear,m,n));
                swiper.slideTo((m-startyear)*1,1, false);
            }

            swiper.on('slideChangeStart',function(swiper){
                swiper.lockSwipes();
            });

            swiper.on('slideChangeEnd',function(swiper){
                swiper.unlockSwipes();
                var len = swiper.slides.length;
                if(swiper.activeIndex == swiper.slides.length-1){
                    var datayear = $(".swiper-wrapper .swiper-slide").eq(swiper.activeIndex).attr("data-year");
                    var newyear = datayear*1+1;
                        swiper.appendSlide(slidedata_yearmonth(newyear,startyear,m,n));
                }
            });
        }

        function slidedata_yearmonth(a,b,c,d){
            var html='<div class="swiper-slide" data-year="'+ a +'"><ul>';
            var class_active = $(".swiper-slide li").hasClass("active");
            for(var i=1;i<13;i++){
                if(d == i && a == c && !class_active){
                        html+='<li class="active" ><a ';
                        if(a == year && i > (month+1)){
                            html+='class="c-timegray" ';
                        }else if(a > year){
                            html+='class="c-timegray" ';
                        }
                        html+=' href="javascript:;"><span>'+ i +'</span>月</a></li>';
                 }else{
                        html+='<li><a ';
                        if(a == year && i > (month+1)){
                            html+='class="c-timegray" ';
                        }else if(a > year){
                            html+='class="c-timegray" ';
                        }
                        html+=' href="javascript:;"><span>'+ i +'</span>月</a></li>';
                }
            }
            return html+"</ul></div>";
        }


//渠道选择
    var channel_data = [];
        $("body").delegate(".channelbox li a","click",function(){
            var this_li = $(this).parent();
            var value = this_li.text();
            var id = this_li.data("id");
                this_li.addClass("active").siblings().removeClass("active").parent().addClass("none").prev().find("input").val(value).attr("data-id",id);
                $("#overflow").addClass("none");
        });
        $(".channelbox .input-wrapper").click(function(){
            if($(this).next().hasClass("none")){
                $(this).parent().siblings().find(".input-wrapper").next().addClass("none");
                $(this).next().removeClass("none");
                $("#overflow").removeClass("none");
                var v = $(this).find("input").val();
                var $channel = $("body").find(".channelbox");
                if($channel.length > 0){
                    if(channel_data.length == 0){
                        $.get("/get-json-data-for-select/index?type=getInputType",{},function(response){
                            channel_data = response;
                            channel(v,response,$channel);
                        },"json");
                    }else{
                        channel(v,channel_data,$channel);
                    }
                }
            }else{
               $(this).next().addClass("none");
               $("#overflow").addClass("none");
            }

        });


    //渠道来源数据
    function channel(v,response,a){
        var html ='';
            for(var i in response.id_0){
                if(i == 0){
                    if(v == '全部' || v == '渠道来源'){
                        html+='<li class="active" data-id="0"><a href="javascript:;">全部</a></li><li data-id="'+ response.id_0[i].id +'"><a href="javascript:;">'+ response.id_0[i].name +'</a></li>';
                    }else{
                        html+='<li data-id="0"><a href="javascript:;">全部</a></li><li data-id="'+ response.id_0[i].id +'"><a href="javascript:;">'+ response.id_0[i].name +'</a></li>';
                    }
                }else if(response.id_0[i].name == v){
                    html+='<li class="active" data-id="'+ response.id_0[i].id +'"><a href="javascript:;">'+ response.id_0[i].name +'</a></li>';
                }else{
                    html+='<li data-id="'+ response.id_0[i].id +'"><a href="javascript:;">'+ response.id_0[i].name +'</a></li>';
                }
            }
            a.find("ul").html(html);
    }

/**
** 自定义联动选择框
*/

(function($) {
        $.fn.Cascader = function(options){
            var defaults = {
                    dataurl:'demo.json',
                    contex:'.cascader-inputbox',
                    contex1:'.cascader-input',
                    islevel:false
                }

            var Cascader = {};
                Cascader.settings = $.extend({}, defaults , options);

            var $this = $(this);
            var $cascader_inputbox = $(this).find(Cascader.settings.contex);
            var $cascader_input = $cascader_inputbox.find(Cascader.settings.contex1);
            var $cascader_list = $(this).find(".cascader-list");
            var $cascader_menu = $cascader_list.find(".cascader-menu");
            var $cascader_li = $cascader_menu.find("li");
            var $overflow = $("#overflow");
            var $dataurl = Cascader.settings.dataurl;
            var $alldata = [];

            $cascader_inputbox.click(function(e){
                inputClick();
                e.stopPropagation();
            });


            $("body").delegate(".cascader-menu li","click",function(e){
                li_this =  $(this);
                liClick(li_this);
                e.stopPropagation();
            });

            $(document).click(function () {
                $cascader_list.addClass("none");
            });


            function liClick(li_this){
                $cascader_input.focus();
                var $li_this = li_this;
                var pid = $li_this.data("pid");
                var id = $li_this.data("id");
                var $li_parent_index =  $li_this.parent().index();
                var html='',result="",dataid='';

                $li_this.addClass("active").siblings().removeClass("active");

                if($li_this.hasClass("submenu")){
                    var level = "";
                    var response = $alldata[0];
                        if($li_this.attr("data-level")){
                            level= 'data-level="'+ ($li_this.data("level") * 100/100 + 1)+'"';
                        }
                        html += '<ul class="cascader-menu"><li '+ level +'>全部</li>';
                        for( i in response)
                            if(i == "id_"+id){
                                for(var j in response[i]){
                                    if(Cascader.settings.islevel){
                                        level = 'data-level="'+ response[i][j].level +'"';
                                    }
                                    if(response[i][j].submenu == 1){
                                        html += '<li class="submenu" '+ level +' data-id="'+ response[i][j].id +'">'+ response[i][j].name +'<i class="fa fa-fw fa-chevron-right"></i></li>';
                                    }else{
                                        html += '<li '+ level +' data-id="'+ response[i][j].id +'">'+ response[i][j].name +'</li>';
                                    }
                                }
                            }
                        html += '</ul>';
                        $cascader_list.find("ul").each(function(){
                            if($(this).index() > $li_parent_index){
                                $(this).remove();
                            }
                        });
                        $cascader_list.append(html);
                }else{
                    if($li_this.text() != "全部"){
                        /*$cascader_list.find("ul").each(function(){
                            if($(this).index() == $li_parent_index){
                                result = $(this).find(".active").text();
                            }else{
                                if($cascader_list.find("ul").length-1 == $(this).index()){
                                    if($(this).find(".active").length > 0){
                                        result = $(this).find(".active").text();
                                    }
                                }else{
                                    result = $(this).find(".active").text();
                                }
                            }
                        });*/
                        result = $li_this.text();
                        dataid = $li_this.data("id");
                        $cascader_list.addClass("none").find("ul").not($cascader_list.find("ul").first()).remove();
                        //$li_this.parents(".cascader_list");
                    }else{
                        $cascader_list.find("ul").each(function(){
                            if($(this).index() < $li_parent_index && $(this).index() == 0){
                                result = $(this).find(".active").text();
                            }else if($(this).index() < $li_parent_index　&&　$(this).index() > 0){
                                result = $(this).find(".active").text();
                            }else{
                                dataid = $(this).prev().find(".active").data("id");
                                result = $(this).prev().find(".active").text();
                            }
                        });

                        $cascader_list.addClass("none").find("ul").eq(0).siblings().remove();
                        if(result == null ){
                           result = $li_this.text();
                        }
                    }

                    if(result.length>6){
                            result = result.substr(0,6) + "...";
                    }
                    $cascader_list.prev().find("input[type=button]").val(result).attr("data-id",dataid);
                    $cascader_list.prev().find("input[type=hidden]").val($li_this.data("level"));
                    $overflow.addClass("none");
                }
            }

            function inputClick(){
                if($cascader_list.hasClass("none")){
                    $cascader_list.removeClass("none");
                    $overflow.removeClass("none");
                    var html = "";
                    if($alldata.length == 0){
                        $.get($dataurl,Cascader.settings.data,function(response){
                            $alldata.push(response);
                            inputxh(response);
                        },'json');
                    }else{
                        inputxh($alldata[0]);
                    }
                }else{
                    $cascader_list.addClass("none");
                    $overflow.addClass("none");
                }
            }

            function inputxh(a){
                var response = a,html='<li data-id="0">全部</li>',level="";
                    for(var i in response.id_0){
                        if(Cascader.settings.islevel){
                            level = 'data-level="'+ response.id_0[i].level +'"';
                        }
                        if(response.id_0[i].submenu == 1){
                            html +='<li class="submenu" '+ level +' data-id="'+ response.id_0[i].id +'"><span>'+ response.id_0[i].name +'</span><i class="fa fa-fw fa-chevron-right"></i></li>';
                        }else{
                            html +='<li '+ level +' data-id="'+ response.id_0[i].id +'"><span>'+ response.id_0[i].name +'</span></li>';
                        }
                    }
                    $cascader_list.find(".cascader-menu").html(html);
            }
        }
})(window.Zepto || window.jQuery);

