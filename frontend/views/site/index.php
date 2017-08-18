<html>

<head>
    <meta charset="utf-8">
    <script src="//cdn.bootcss.com/jquery/3.1.1/jquery.js"></script>
    <!-- 新 Bootstrap 核心 CSS 文件 -->
    <link rel="stylesheet"
          href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">

    <!-- 可选的Bootstrap主题文件（一般不用引入） -->
    <!-- <link rel="stylesheet"
        href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap-theme.min.css"> -->

    <!-- jQuery文件。务必在bootstrap.min.js 之前引入 -->
    <script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>

    <!-- 最新的 Bootstrap 核心 JavaScript 文件 -->
    <script src="//cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

    <!--<script src="/statics/js/apitest/json2.js"></script>-->

    <!-- 引入配置文件 -->
</head>

<body>
<div class="container">
    <div class="row">
        <p class="lead">本页面是为了方便测试API接口，请选择接口来测试，测试时请根据需要修改参数，json字符串会自动加密传到服务器</p>
        <form class="form-inline">
            <div class="form-group">
                <label>请求方式</label>
                <select class="form-control" id="req_method">
                    <option value="POST">POST</option>
                    <option value="GET">GET</option>
                </select>
            </div>
            <div class="form-group">
                <label>模块</label>
                <input type="text" class="form-control" id="api_module"  value="sales" placeholder="模块名">
            </div>
            <div class="form-group">
                <label for="api_name">路由</label>
                <select id="api_name" class="form-control">
                </select>
                <!-- <input type="text"
                    class="form-control" id="api_name" placeholder="getIndexList-首页列表"> -->
            </div>
            <button type="button" class="btn btn-success begin_submit">开始请求</button>
            <br/>


        </form>

        <table class="table table-striped table-bordered .table-hover">
            <thead>
            <tr>
                <th><label >Access_Token</label>
                    <input type="text" class="form-control" id="access_token" placeholder="1" value="BFr51qNKb7NVurCzYBJwtqNl-1Xk3Zjk"></th>
                <th class="col-lg-6"></th>
            </tr>
            <tr>
                <th class="col-lg-4">Api中data请求参数名</th>
                <th class="col-lg-5">请求值</th>
                <th class="col-lg-3"><button type="button" class="btn btn-info add_param" onclick="javascript:add_param();">添加参数</button></th>
            </tr>
            </thead>
            <tbody class="req_tbody">
            <tr>
                <td class="col-lg-4"><input type="text" class="form-control req_key" placeholder="key"></td>
                <td class="col-lg-5"><input type="text" class="form-control req_value" placeholder="value"></td>
                <td class="col-lg-3"><button class="btn btn-info add_param" onclick="javascript:add_param();">添加参数</button><button class="btn btn-warning" onclick="javascript:del_param(this)">删除参数</button></td>
            </tr>
            </tbody>
        </table>

        <table class="table table-striped table-bordered .table-hover">
            <thead>
            <tr>
                <th>请求body(已经格式化为json格式)</th>
                <th>响应body</th>
            </tr>
            </thead>
            <tbody class="res_tbody">
            <tr>
                <td class="col-lg-4"><textarea class="form-control req_body" rows="20" ></textarea></td>
                <td class="col-lg-8"><textarea class="form-control res_body" rows="20" ></textarea></td>
            </tr>
            <tr>
                <td class="col-lg-12" colspan="2"><textarea class="form-control desc" rows = 20 readonly="true"></textarea></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
</body>
<script>
    var config = [
        {
            "api_name":"clue/list",//接口名
            "api_explain":"线索客户列表"
        },
        {
            "api_name":"clue/view",//接口名
            "api_explain":"客户详情"
        },
        {
            "api_name":"clue/add",//接口名
            "api_explain":"新增意向客户"
        },
        {
            "api_name":"clue/update",//接口名
            "api_explain":"更新客户"
        },
        {
            "api_name":"customer",//接口名
            "api_explain":"意向客户列表"
        },
        {
            "api_name":"task",//接口名
            "api_explain":"任务列表"
        },
        {
            "api_name":"talk/add",//接口名
            "api_explain":"新增交谈记录"
        }

    ];

    var req_tbody = $(".req_tbody");
    var req_body = $(".req_body");
    var res_body = $(".res_body");
    var desc_body = $(".desc");

    //删除一个参数
    function del_param( obj)
    {
        $(obj).parents("tr").remove();
    }

    //添加一个参数
    function add_param()
    {
        var tr = $('<tr><td class="col-lg-4"><input type="text" class="form-control req_key" placeholder="key"></td><td class="col-lg-5"><input type="text" class="form-control req_value" placeholder="value"></td><td class="col-lg-3"><button class="btn btn-info add_param" onclick="javascript:add_param();">添加参数</button><button class="btn btn-warning" onclick="javascript:del_param(this)">删除参数</button></td></tr>');
        tr.appendTo(req_tbody);
    }

    $(function(){

        var api_select = $("#api_name");
        //遍历配置,将api分配到select框中
        for (var i = 0; i < config.length; i++) {
            console.log(config[i]);
            var option = $("<option  config_id='" + i + "' value='" + config[i]['api_name'] + "'>" + config[i]['api_name'] + "-" + config[i]['api_explain'] + "</option>");
            option.appendTo(api_select);
        }

        /*<tr>
         <td class="col-lg-4"><input type="text" class="form-control req_key" placeholder="key"></td>
         <td class="col-lg-5"><input type="text" class="form-control req_value" placeholder="value"></td>
         <td class="col-lg-3"><button class="btn btn-info add_param" onclick="javascript:add_param();">添加参数</button><button class="btn btn-warning" onclick="javascript:del_param(this)">删除参数</button></td>
         </tr>*/

        //当改变接口值
        var tr = $('<tr>'+
            '<td class="col-lg-4"><input type="text" class="form-control req_key" placeholder="key"></td>' +
            '<td class="col-lg-5"><input type="text" class="form-control req_value" placeholder="value"></td>' +
            '<td class="col-lg-3"><button class="btn btn-info add_param" onclick="javascript:add_param();">添加参数</button><button class="btn btn-warning del_param" onclick="javascript:del_param(this)">删除参数</button></td>'+
            '</tr>');

        var require_span = $('<span style="margin-left: 8px;color:green;">必填</span>');

        function reset_req_tbody()
        {
            req_tbody.html('');
            req_body.text('');
            res_body.text('');
            var config_id = api_select.find("option:selected").attr("config_id");
            var config_data = config[config_id]['data'];
            console.log(config_data);

            var desc = config[config_id]['desc'];
            if(desc !== undefined)
            {
                var text_desc = '';
                for (var i = 0; i < desc.length ; i++) {
                    var text_desc = text_desc + desc[i] + "\r\n";
                }
            }
            desc_body.text(text_desc);

            for (x in config_data) {
                //console.log(x);
                var clone_tr = tr.clone();
                clone_tr.find(".req_key").val(x);
                clone_tr.find(".req_value").attr(config_data[x]);
                console.log(config_data[x]['require']);
                if(config_data[x]['require'] !== undefined && config_data[x]['require'] === true)
                {
                    require_span.appendTo(clone_tr.find("td")[2]);
                    clone_tr.find(".del_param").remove();
                }
                clone_tr.appendTo(req_tbody);
            }
        }

        //当改变接口时，充值请求表单
        api_select.change(reset_req_tbody);

        //初始化tbody表单
        reset_req_tbody();

        //发起测试请求
        function begin_submit()
        {
            var api_module = $("#api_module").val();
            var api_controller = $("#api_controller").val();
            var api_name = api_select.val();
            if(api_name == '')
            {
                alert('请填写接口名');
                return false;
            }
            var url = '/' + api_module + '/' + api_name;
            var req_method = $("#req_method").val();
            var trs = req_tbody.find("tr");
            var req_data = {};
            trs.each(function(index){
                req_data[$(this).find(".req_key").val()] = $(this).find(".req_value").val();
            });
            var json_str = JSON.stringify(req_data);
            console.log('send_request------: ' + json_str);
            //渠道号，版本号
            var accessToken = $("#access_token").val();
            var req_r = JSON.stringify({'access_token':accessToken});
            var data_copy = {p:json_str,r:req_r};
            console.log(data_copy);

            $.ajax({
                url:url,
                type: req_method,
                data: data_copy,
                success:function(msg)
                {
                    //格式化输出请求json数据
                    req_body.html(JSON.stringify(data_copy));
                    res_body.html(JSON.stringify(msg, null, 4));
                },

            });

        }

        $(".begin_submit").click(function(){
            begin_submit();
        });

    });
</script>

</html>