(function($){
    $.fn.Cascader = function(options,changeHandle){
        var $alldata = [];
        var defaults ={
                dataurl:'demo.json',
                islevel:false,
                isall:false,
                onchange:$.change
            };
        options = $.extend({},defaults,options);

        return this.each(function(){
                var _this = $(this);
                    overflow = $("#overflow");
                    cascader_inputbox = _this.find(".cascader-inputbox");
                    cascader_input = _this.find(".cascader-input");
                    cascader_list = _this.find(".cascader-list");
                    cascader_menu = _this.find(".cascader-menu");
                    cascader_li = _this.find("li");

                    cascader_input.on("click focus",function(e){
                           $(".cascader-list").addClass("none");
                           var self = this;
                            inputClick(_this);
                    });

                    _this.delegate(".cascader-menu li","click",function(e){
                            li_this = $(this);
                            if(!options.isall){
                                if(li_this.index() == 0 && li_this.parent().index() == 0){
                                    li_this.parents(".cascader").find(".cascader-input").val("全部").nextAll(".sid").focus().val(0).blur().next().val("");
                                    li_this.parents(".cascader-list").addClass("none");
                                }else{
                                   liClick(li_this,_this);
                                }
                            }else{
                                liClick(li_this,_this);
                            }
                            e.stopPropagation();
                    });

                    $(document).bind("click",function(e){
                        var target  = $(e.target);
                        if(target.closest(".cascader").length == 0){
                            $(".cascader-list").addClass("none");
                        };
                        e.stopPropagation();
                    });

                    if(typeof(options.onchange) == "function"){
                        _this.find(".sid").on("change blur",function(){
                            options.onchange(_this);
                        });
                    }

                    function liClick(li_this,_this){
                        var $li_this = li_this;
                        var pid = $li_this.data("pid");
                        var id = $li_this.data("id");
                        var cascader_list = _this.find(".cascader-list");
                        var $li_parent_index =  $li_this.parent().index();
                        var html="",result="",sid="",ulevel="";

                        $li_this.addClass("active").siblings().removeClass("active");

                        if($li_this.hasClass("submenu")){
                            var level ="";
                            response = $alldata[0];
                            var lev = 'data-level="'+ ($li_this.data("level") * 100/100 + 1)+'"';
                            html += '<ul class="cascader-menu"><li '+ lev +'>全部</li>';
                                for( i in response){
                                        if(i == "id_"+id){
                                            for(var j in response[i]){
                                                if(options.islevel){
                                                    level = 'data-level="'+ response[i][j].level +'"';
                                                }
                                                if(response[i][j].submenu == 1){
                                                    html += '<li class="submenu" '+ level +' data-id="'+ response[i][j].id +'">'+ response[i][j].name +'<i class="fa fa-fw fa-chevron-right"></i></li>';
                                                }else{

                                                    html += '<li '+ level +' data-id="'+ response[i][j].id +'">'+ response[i][j].name +'</li>';
                                                }
                                            }
                                        }
                                }
                            html += '</ul>';
                            cascader_list.find("ul").each(function(){
                                if($(this).index() > $li_parent_index){
                                    $(this).remove();
                                }
                            });
                            cascader_list.append(html);
                        }else{
                            if($li_this.text() != "全部"){
                                cascader_list.find("ul").each(function(){
                                    if($(this).index() == $li_this.parents("ul").index()){
                                        result += $li_this.text();
                                        sid += $li_this.data("id");
                                    }else{
                                        if(cascader_list.find("ul").length-1 == $(this).index()){
                                            if($(this).find(".active").length > 0){
                                                result += $(this).find(".active").text();
                                                sid += $(this).find(".active").data("id");
                                            }
                                        }else{
                                            result += $(this).find(".active").text() + "/";
                                            sid += $(this).find(".active").data("id")+',';
                                        }
                                    }
                                    ulevel = $li_this.data("level");
                                    if($(this).index() > 0){
                                        $(this).remove();
                                    }else{
                                        $(this).parent().addClass("none");
                                    }
                                });
                            }else{
                                cascader_list.find("ul").each(function(){
                                    if($(this).index() < $li_parent_index &&　$(this).index() == 0){
                                        result += $(this).find(".active").text();
                                        sid += $(this).find(".active").data("id");
                                    }else if($(this).index() < $li_parent_index　&&　$(this).index() > 0){
                                        result += "/" + $(this).find(".active").text();
                                        sid += "," + $(this).find(".active").data("id");
                                    }else{
                                        result +='';
                                    }
                                });
                                ulevel = $li_this.parent().prev().find(".active").data("level");
                                cascader_list.addClass("none").find("ul").eq(0).siblings().remove();

                            }
                            cascader_list.prev().find("input[type=text]").val(result);
                            cascader_list.prev().find("input[type=hidden].level").val(ulevel);
                            cascader_list.prev().find("input[type=hidden].sid").focus().val(sid).blur();
                            overflow.addClass("none");
                        }
                    }

                    function inputClick(_this){
                        var cascader_list = _this.find(".cascader-list");
                        var cascader_sid = _this.find(".cascader-inputbox").find("input.sid").val();
                            if(cascader_list.hasClass("none")){
                                cascader_list.removeClass("none");
                                overflow.removeClass("none");
                            }else{
                                cascader_list.addClass("none");
                                overflow.addClass("none");
                            }
                            var html = "";
                            if(cascader_sid == "undefined"){
                                if($alldata.length == 0){
                                    $.get(options.dataurl,{},function(response){
                                        $alldata.push(response);
                                        uninputxh(response,_this);
                                    },'json');
                                }else{
                                    uninputxh($alldata[0],_this);
                                }
                            }else{
                                if($alldata.length == 0){
                                    $.get(options.dataurl,{},function(response){
                                        $alldata.push(response);
                                        inputxh(response,_this,cascader_sid);
                                    },'json');
                                }else{
                                    inputxh($alldata[0],_this,cascader_sid);
                                }
                            }

                    }

                    function uninputxh(a,_this){
                        var html="",level="",cascader_menu = _this.find(".cascader-menu");
                        for(var i in a.id_0){
                            if(options.islevel){
                                level = 'data-level="'+ a.id_0[i].level +'"';
                            }
                            if(a.id_0[i].submenu == 1){
                                html +='<li class="submenu" '+ level +' data-id="'+ a.id_0[i].id +'">'+ a.id_0[i].name +'<i class="fa fa-fw fa-chevron-right"></i></li>';
                            }else{
                                html +='<li '+ level +' data-id="'+ a.id_0[i].id +'">'+ a.id_0[i].name +'</li>';
                            }
                        }
                        cascader_menu.html(html);
                    }
                    function inputxh(a,_this,b){
                        var html="",level="",cascader_list = _this.find(".cascader-list");
                        var cascader_sid = b.split(",");
                        var active ="";
                            html += '<ul class="cascader-menu">';
                                    if(!options.isall){
                                        html+='<li data-id="0">全部</li>';
                                    }
                                    for(var i in a.id_0){
                                        if(options.islevel){
                                            level = 'data-level="'+ a.id_0[i].level +'"';
                                        }
                                        if(a.id_0[i].id == cascader_sid[i]){
                                            active = 'active" ';
                                        }else{
                                            active = '" ';
                                        }
                                        if(a.id_0[i].submenu == 1){
                                            html +='<li class="submenu '+active+ level +' data-id="'+ a.id_0[i].id +'">'+ a.id_0[i].name +'<i class="fa fa-fw fa-chevron-right"></i></li>';
                                        }else{
                                            html +='<li class="'+ active + level +' data-id="'+ a.id_0[i].id +'">'+ a.id_0[i].name +'</li>';
                                        }
                                    }
                            html += '</ul>';
                            for(var i=0;i<cascader_sid.length-1;i++){
                                var sid = "id_" + cascader_sid[i];
                                    for(var j in a){
                                        if(j == sid){
                                            if(options.islevel){
                                                level = 'data-level="'+ (i+2)*10/10 +'"';
                                            }
                                            html+='<ul class="cascader-menu"><li '+ level +'>全部</li>';
                                            for(var m in a[j]){
                                                if(options.islevel){
                                                    level = 'data-level="'+ a[j][m].level +'"';
                                                }
                                                if(a[j][m].id == cascader_sid[i+1]){
                                                    active = 'active" ';
                                                }else{
                                                    active = '" ';
                                                }
                                                if(a[j][m].submenu == 1){
                                                    html +='<li class="submenu '+ active + level +' data-id="'+ a[j][m].id +'">'+ a[j][m].name +'<i class="fa fa-fw fa-chevron-right"></i></li>';
                                                }else{
                                                    html +='<li class="'+ active + level +' data-id="'+ a[j][m].id +'">'+ a[j][m].name +'</li>';
                                                }
                                            }
                                            html+="</ul>";
                                        }
                                    }

                            }
                            cascader_list.html(html);
                    }

        });
    }
})(jQuery);

