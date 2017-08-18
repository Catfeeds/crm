(function ($, AdminLTE) {

  "use strict";
  //全选
    $(".table-list-check thead th input[type='checkbox']").click(function(){
        if($(this).prop("checked")){
          $(this).prop("checked",true);
          $(this).parents("thead").next().find("tr").each(function(){
             $(this).find("td").eq(0).find("input[type='checkbox']").prop("checked",true);
          });
        }else{
          $(this).prop("checked",false);
          $(this).parents("thead").next().find("tr").each(function(){
             $(this).find("td").eq(0).find("input[type='checkbox']").prop("checked",false);
          });
        }
    });

    //单个全选或取消全选
    $(".table-list-check tbody tr td input[type='checkbox']").click(function(){
        var bol = false;
        var trnum =$(this).parents("tbody").find("tr input[type='checkbox']").length;
        var checkednum =$(this).parents("tbody").find("tr :checked").length;
          if( trnum == checkednum){
              $(".table-list-check thead th input[type='checkbox']").prop("checked",true);
          }else{
              $(".table-list-check thead th input[type='checkbox']").prop("checked",false);
          }
    });

    /*筛选*/
    $("body").delegate(".lte-table-filter-dropdown .lte-table-filter-dropdown-btns .confirm","click",function(){
          if($(this).parents(".lte-table-filter-dropdown").find(":checked").length > 0){
            $(".lte-filterbox .fa-filter").addClass("c-blue");
          }else{
            $(".lte-filterbox .fa-filter").removeClass("c-blue");
          }
          $(this).parents(".lte-table-filter-dropdown").addClass("none");
    });

    $("body").delegate(".lte-table-filter-dropdown .lte-table-filter-dropdown-btns .clean","click",function(){
        $(this).parents(".lte-table-filter-dropdown").find(":checkbox").prop("checked",false);
        $(".lte-filterbox .fa-filter").removeClass("c-blue");
        $(this).parents(".lte-table-filter-dropdown").addClass("none");
    });

    $("body").delegate(".lte-filterbox .fa-filter","click",function(){
        var contex = $(this).parents(".lte-filterbox").find(".lte-table-filter-dropdown");
            if(contex.hasClass("none")){
              contex.removeClass("none");
            }else{
              contex.addClass("none");
            }
    });


    /*排序*/
    $("body").delegate(".lte-table-column-sorter","click",function(){
        $(this).find("span.on").removeClass("on").addClass("off").siblings().removeClass("off").addClass("on");
        //.removeClass("off").addClass("on").siblings().removeClass("on").addClass("off");
    });

    $(document).bind("click",function(e){
      var target  = $(e.target);
          if(target.closest(".lte-filterbox").length == 0){
              $(".lte-table-filter-dropdown").addClass("none");
          };
          e.stopPropagation();
    });

    $(".custom-dropdown ul li a").click(function(){
        var val = $(this).text();
        $(this).parents(".custom-dropdown").find(":hidden").val(val).next().find("span").html(val);
    });
    //导航
   // menu();

})(jQuery, $.AdminLTE);

function cs_datetimepicker(contex,options,format,num){
  var date = new Date();
  var defaults ={
              "opens": "left",
              "autoApply": true,
              "autoUpdateInput":true,
              "dateLimit": {"months": 6},
              "minDate":'2015-12-31',
              "maxDate":date.toLocaleString().split(" ")[0],
              "locale": {
                "format": format,
                'applyLabel' : '确定',
                'cancelLabel' : '取消',
                'daysOfWeek': ['日', '一', '二', '三', '四', '五','六'],
                'monthNames': ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
                'firstDay': 1
              },
      }
  var settings = $.extend({}, defaults , options);
    $(contex).daterangepicker(settings);

    $(contex).on('apply.daterangepicker', function(ev, picker) {
      switch(num){
        case 'single':
          $(contex).val(picker.endDate.format(format));
        break;
        default:
          $(contex).val(picker.startDate.format(format)+"~"+picker.endDate.format(format));
        break;
      }

    });
}


function check_null(a){
  var bol = false;
  var val = $(a).val();
      if(val == ''){
        $(a).addClass("error");
        bol = false;
      }else{
        $(a).removeClass("error");
        bol = true;
      }
      return bol;
}

function check_upload(){
  if($(".uploadlist li").length == 0){
      return false;
  }else{
      return true;
  }
}


/*function menu(){
    var html ='<section class="sidebar">'+
              '<ul class="sidebar-menu">'+
              '  <li class="active treeview">'+
              '    <a href="javascript:;">'+
              '      <i class="fa fa-dashboard"></i> <span>首页</span>'+
              '      <span class="pull-right-container">'+
              '        <i class="fa fa-angle-left pull-right"></i>'+
              '      </span>'+
              '    </a>'+
              '    <ul class="treeview-menu">'+
              '      <li><a href="/demo/"><i class="fa fa-circle-o"></i>首页</a></li>'+
              '      <li><a href="/demo/statistics/regional_sales.html"><i class="fa fa-circle-o"></i>销售指标-大区</a></li>'+
              '      <li><a href="/demo/changepwd.html"><i class="fa fa-circle-o"></i>修改密码</a></li>'+
              '    </ul>'+
              '  </li>'+
              '  <li class="active treeview">'+
              '    <a href="javascript:;">'+
              '      <i class="fa fa-dashboard"></i> <span>Dashboard</span>'+
              '      <span class="pull-right-container">'+
              '        <i class="fa fa-angle-left pull-right"></i>'+
              '      </span>'+
              '    </a>'+
              '    <ul class="treeview-menu">'+
              '      <li><a href="/demo/list.html"><i class="fa fa-circle-o"></i>列表demo</a></li>'+
              '      <li><a href="/demo/info.html"><i class="fa fa-circle-o"></i>详情</a></li>'+
              '      <li><a href="/demo/auditing.html"><i class="fa fa-circle-o"></i>审核任务</a></li>'+
              '    </ul>'+
              '  </li>'+
              '  <li class="active treeview">'+
              '    <a href="javascript:;">'+
              '      <i class="fa fa-dashboard"></i> <span>线索管理</span>'+
              '      <span class="pull-right-container">'+
              '        <i class="fa fa-angle-left pull-right"></i>'+
              '      </span>'+
              '    </a>'+
              '    <ul class="treeview-menu active">'+
              '      <li><a href="/demo/addthread.html"><i class="fa fa-circle-o"></i>线索导入</a></li>'+
              '      <li><a href="/demo/dealthread.html"><i class="fa fa-circle-o"></i>线索处理</a></li>'+
              '    </ul>'+
              '  </li>'+
              '  <li class="active treeview">'+
              '    <a href="javascript:;">'+
              '      <i class="fa fa-dashboard"></i> <span>人员管理</span>'+
              '      <span class="pull-right-container">'+
              '        <i class="fa fa-angle-left pull-right"></i>'+
              '      </span>'+
              '    </a>'+
              '    <ul class="treeview-menu active">'+
              '      <li><a href="/demo/personmanage.html"><i class="fa fa-circle-o"></i>人员管理</a></li>'+
              '      <li><a href="/demo/casting.html"><i class="fa fa-circle-o"></i>角色权限管理</a></li>'+
              '      <li><a href="/demo/permission.html"><i class="fa fa-circle-o"></i>权限分配</a></li>'+
              '    </ul>'+
              '  </li>'+
              '  <li class="active treeview">'+
              '    <a href="javascript:;">'+
              '      <i class="fa fa-dashboard"></i> <span>基础数据设置</span>'+
              '      <span class="pull-right-container">'+
              '        <i class="fa fa-angle-left pull-right"></i>'+
              '      </span>'+
              '    </a>'+
              '    <ul class="treeview-menu active">'+
              '      <li><a href="/demo/basicdata/intention-level.html"><i class="fa fa-circle-o"></i>意向等级设置</a></li>'+
              '      <li><a href="/demo/basicdata/clewreason.html"><i class="fa fa-circle-o"></i>战败原因设置</a></li>'+
              '      <li><a href="/demo/basicdata/smstemplate.html"><i class="fa fa-circle-o"></i>短信模板设置</a></li>'+
              '    </ul>'+
              '  </li>'+
              '  <li class="active treeview">'+
              '    <a href="javascript:;">'+
              '      <i class="fa fa-dashboard"></i> <span>运营管理</span>'+
              '      <span class="pull-right-container">'+
              '        <i class="fa fa-angle-left pull-right"></i>'+
              '      </span>'+
              '    </a>'+
              '    <ul class="treeview-menu active">'+
              '      <li><a href="/demo/operate/encourage.html"><i class="fa fa-circle-o"></i>激励管理</a></li>'+
              '      <li><a href="/demo/operate/app-version.html"><i class="fa fa-circle-o"></i>App版本管理</a></li>'+
              '      <li><a href="/demo/operate/notice.html"><i class="fa fa-circle-o"></i>公告管理</a></li>'+
              '    </ul>'+
              '  </li>'+
              '  <li class="active treeview">'+
              '    <a href="javascript:;">'+
              '      <i class="fa fa-dashboard"></i> <span>统计图表</span>'+
              '      <span class="pull-right-container">'+
              '        <i class="fa fa-angle-left pull-right"></i>'+
              '      </span>'+
              '    </a>'+
              '    <ul class="treeview-menu active">'+
              '      <li><a href="/demo/statistics/ranklist.html"><i class="fa fa-circle-o"></i>排行榜</a></li>'+
              '      <li><a href="/demo/statistics/transformation.html"><i class="fa fa-circle-o"></i>转化漏斗</a></li>'+
              '      <li><a href="/demo/statistics/clew-analysis.html"><i class="fa fa-circle-o"></i>线索分析</a></li>'+
              '      <li><a href="/demo/statistics/intention-analysis.html"><i class="fa fa-circle-o"></i>意向分析</a></li>'+
              '      <li><a href="/demo/statistics/fail-analysis.html"><i class="fa fa-circle-o"></i>战败分析</a></li>'+
              '      <li><a href="/demo/statistics/deliver-analysis.html"><i class="fa fa-circle-o"></i>交车分析</a></li>'+
              '      <li><a href="/demo/statistics/turnover-trend.html"><i class="fa fa-circle-o"></i>成交趋势</a></li>'+
              '    </ul>'+
              '  </li>'+
              '</ul>'+
            '</section>';
    $(".main-sidebar").html(html);

    var url = (window.location.href).split("/");
    var page = url[url.length-1];
        $(".sidebar .treeview-menu li").each(function(){
            var url1 = $(this).find("a").attr("href").split("/");
            var page1 = url1[url1.length-1];
            if(page == page1){
              $(this).addClass("active").parents(".treeview").siblings().find("li").removeClass("active");
            }
        });
}*/



