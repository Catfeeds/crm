<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/4/17
 * Time: 11:53
 */
use yii\helpers\Url;
$page = Yii::$app->request->get('page') ? : 1;
?>

<section class="content-header">
    <h1 class="page-title">商谈记录</h1>
</section>

<section class="content-body">
    <div class="box advanced-search-form mb-lg">
        <form method="get" action="<?=Url::to(['index'])?>" class="form-horizontal">
            <div class="row">
                <div class="form-group col-md-4">
                    <label for="inputEmail3" class="control-label col-sm-3">关键词:</label>
                    <div class="col-sm-9">
                        <input type="text" name="keyword" value="<?=Yii::$app->request->get('keyword')?>" class="form-control" placeholder="姓名/顾问/标签/商谈内容">
                    </div>
                </div>
                <div class="form-group col-md-4">
                    <label for="inputPassword3" class="control-label col-sm-3">联系时间:</label>
                    <div class="col-sm-9">
                        <div class="calender-picker double-time" id="datetime">
                            <div class="timeinputbox">
                                <input type="text" class="form-control" value="<?=Yii::$app->request->get('date_time')?>" name="date_time" id="addtime" placeholder="请输入时间">
                                <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="pull-right mr-15">
                        <button type="submit" class="btn btn-primary btn-sm pull-left mr-15 pull-left">搜索</button>
                        <a href="index"><button type="button" class="btn btn-default btn-sm pull-left">清除</button></a>
                    </div>
                </div>
            </div>
        </form>
        <input type="hidden" id="startDateSelect" value="<?php echo $startDate; ?>" />
        <input type="hidden" id="endDateSelect" value="<?php echo $endDate; ?>" />
    </div>

    <div class="mb-md">
        <a href="<?=Url::to(['export', 'keyword' => Yii::$app->request->get('keyword'), 'date_time' => Yii::$app->request->get('date_time')])?>" type="button" class="btn btn-primary btn-sm">导出列表</a>
    </div>
<link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-default/index.css">
    <div class="box box-none-border">
        <div class="box-body no-padding">
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-list-check">
                    <thead class="lte-table-thead">
                    <tr>
                        <th width="50">序号</th>
                        <th width="115">联系时间</th>
                        <th width="100">顾问</th>
                        <th width="100">门店</th>
                        <th width="80">姓名</th>
                        <th width="100">客户状态</th>
                        <th width="100">商谈类型</th>
                        <th width="170">标签</th>
                        <th width="170">商谈内容</th>
                        <th width="80" class="t-c">图片</th>
                        <th width="60" class="t-c">录音</th>
                        <th width="70">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data as $k => $v):?>
                    <tr>
                        <td><?php
                            $per_page = empty(Yii::$app->request->get('per-page')) ? 20 : Yii::$app->request->get('per-page');
                            echo ($k+1) +  $per_page  * ($page - 1);
                            ?></td>
                        <td><?=$v['create_time']?></td>
                        <td><?=$v['salesman_name']?></td>
                        <td><?=$v['shop_name']?></td>
                        <td><?=$v['customer_name']?></td>
                        <td><?=$v['status']?></td>
                        <td><?=$v['type_name']?></td>
                        <td><?=$v['tag_name']?></td>
                        <td><?=$v['content']?></td>
                        <td style="width:60px; max-width:60px;" class="t-c">
                            <p class="btn img_show border1" data='<?=$v['imgs']?>'><i class="fa fa-photo mr-sm"></i><?=$v['img_count']?></p>
                        </td>
                        <td  style="width:67px; max-width:67px;" class="t-c">
                        <?php if ($v['voices']): ?>
                            <button class="btn audio-btn" data-src="<?=$v['voices']?>">播放</button>
                            <?php else: ?>
                            --
                        <?php endif; ?>
                        </td>
                        <td>
                        <?php if ($v['voices']): ?>
                            <a href="<?=$v['voices']?>"> 下载录音</a>
                         <?php else: ?>
                            --
                        <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="box-footer pdt-md pull-right bd-t0">
                <div class="paginationbox">


                    <ul class="pagination no-margin">
                        <?php

                        // 显示分页
                        echo common\logic\LinkPager::widget([
                            'pagination' => $pagination,
                            'firstPageLabel' => "首页",
                            'prevPageLabel' => '上一页',
                            'nextPageLabel' => '下一页',
                            'lastPageLabel' => '末页',
                        ]);
                        ?>
                    </ul>
                </div>
            </div>
        </div>
        <div id="cascader">
            <el-cascader
                placeholder="试试搜索：指南"
                :options="options"
                filterable
            ></el-cascader>
        </div>
    </div>
    <!-- /.row -->
</section>
<?php
$this->registerJsFile('/dist/plugins/daterangepicker/moment.min.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/daterangepicker/daterangepicker.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$js = <<<_SCRIPT
    //创建日期
    if($('#addtime'))
    {
        var config = {"opens": "left", "autoApply": true,"dateLimit": {"months": 6}, "autoUpdateInput": false, "locale": {"format": 'YYYY-MM-DD',
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
        $('#addtime').daterangepicker(config);
        //选中
        $('#addtime').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + " - " + picker.endDate.format('YYYY-MM-DD'));
        });

    }

    var myVideo=document.getElementById("video1");

    /*$("#play").click(function(){
        myVideo.play();
    });*/
    $(".img_show").click(function(){
        var data = JSON.parse($(this).attr('data'));
        console.log(data);
        layer.photos({
            photos: data, //格式见API文档手册页
            anim: 5
        });
    });
    $(".audio-btn").click(function(){
        var src = $(this).data("src");
        var html = "";
            /*html =  '<div class="audio">'+
                    '    <div class="">'+
                    '        <video src="'+ src +'" controls="controls">'+
                    '            您的浏览器不支持 video 标签。'+
                    '        </video>'+
                    '    </div>'+
                    '</div>';*/
            html =  '<div class="audio">'+
                    '    <div class="">'+
                    '        <audio src="'+ src +'" controls="controls">'+
                    '            您的浏览器不支持 video 标签。'+
                    '        </audio>'+
                    '    </div>'+
                    '</div>';

        layer.open({
            title:"语音播放",
            type: 1,
            skin: 'layui-layer-video',
            closeBtn: 1,
            anim: 2,
            shadeClose: true,
            content: html
        });
    });

    function GetRequest() {
         var url = location.search;
         var theRequest = new Object();
         if (url.indexOf("?") != -1) {
            var str = url.substr(1);
            strs = str.split("&");
            for(var i = 0; i < strs.length; i++) {
               theRequest[strs[i].split("=")[0]]=decodeURI(strs[i].split("=")[1]);
            }
         }
         return theRequest;
    }

    $("select[name=per-page]").change(function(){
        url = changeUrl('per-page', $(this).val());
        location.href = url;
    });

    function changeUrl(attribute, value){
        var param = GetRequest();
        var new_param = '?';
        var len = Object.keys(param).length;
        var i = 1;
        if(len == 0) {
           new_param += attribute+'=' + value;
        } else {
            var flag = 1;
            for(var key in param){
                if (key == attribute) {
                    flag = 0;
                   new_param += key+'='+value;
                } else {
                   new_param += key+'='+param[key];
                }
                if (i != len) {
                    new_param += '&';
                }
                i++;
            }
            if(flag == 1) {
                new_param += '&'+attribute +'='+value;
            }
        }
        var url = location.href;

        str = url.split("?");
        return str[0]+new_param;
    }

_SCRIPT;
$this->registerJs($js);

