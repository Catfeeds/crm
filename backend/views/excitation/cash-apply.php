<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/4/17
 * Time: 12:00
 */


$page = Yii::$app->request->get('page') ? : 1;
?>
    <section class="content-header">
        <h1 class="page-title">提现处理</h1>
    </section>

<section class="content-body">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-none-border">
                <div class="box-body no-padding">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-list-check">
                            <thead>
                            <tr>

                                <th width="60">序号</th>
                                <th>申请人</th>
                                <th>
                                    所属区域
                                    <div class="lte-filterbox">
                                        <i title="Filter Menu" class="fa fa-filter lte-dropdown-trigger ml-0"></i>
                                        <div  class="lte-table-filter-dropdown none">
                                            <ul class="lte-dropdown-menu lte-dropdown-menu-vertical  lte-dropdown-menu-root" role="menu" aria-activedescendant="" tabindex="0">
                                                <?php foreach ($area as $k => $v):?>
                                                <li class="lte-dropdown-menu-item" role="menuitem" aria-selected="false">
                                                    <label class="lte-checkbox-wrapper">
                                                        <input type="checkbox" name="area_ids" class="lte-checkbox-input" value="<?=$k?>">
                                                    </label>
                                                    <span><?=$v?></span>
                                                </li>
                                                <?php endforeach;?>
                                            </ul>
                                            <div class="lte-table-filter-dropdown-btns">
                                                <div class="lte-table-filter-dropdown-link confirm">确定</div>
                                                <a href="<?=\yii\helpers\Url::to([''])?>" class="lte-table-filter-dropdown-link clean">重置</a>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th>所属门店</th>
                                <th>提现金额</th>
                                <th>钱包剩余</th>
                                <th>
                                    申请时间
                                    <div class="lte-table-column-sorter">
                                        <span alt="addtime" class="lte-table-column-sorter-up off" title="↑"> <i class="fa fa-caret-up"></i></span>
                                        <span alt="addtime" class="lte-table-column-sorter-down off" title="↓"> <i class="fa fa-caret-down"></i></span>
                                    </div>
                                </th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($data as $k => $v) :?>
                                <tr>
                                    <td><?=($k+1) +  20 * ($page - 1)?></td>
                                    <td><?=$v['salesman_name']?></td>
                                    <td><?=$v['area']?></td>
                                    <td><?=$v['shop_name']?></td>
                                    <td><?=$v['money']?></td>
                                    <td><?=$v['has_money']?></td>
                                    <td><?=date("Y-m-d H:i", strtotime($v['addtime']))?></td>
                                    <td>
                                        <div class="operation">
                                            <a href="javascript:void(0)" title="<?=$v['id']?>" id="pass">通过</a>
                                            <span>|</span>
                                            <a href="javascript:void(0)" title="<?=$v['id']?>" id="back">驳回</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach;?>
                            </tbody>
                        </table>
                    </div>
                    <div class="box-footer pdt-md pull-right bd-t0">
                        <div class="paginationbox">
                            <div class="jump pull-right">跳转<input type="text" class="form-control">页</div>
                            <div class="display-page pull-right">
                                <select name="per-page" class="form-control">
                                    <option <?=(Yii::$app->request->get('per-page') == 10) ? 'selected ':'';?>value="10">10条/页</option>
                                    <option <?=(Yii::$app->request->get('per-page') == 20 || !Yii::$app->request->get('per-page')) ? 'selected ':'';?>value="20">20条/页</option>
                                    <option <?=(Yii::$app->request->get('per-page') == 30) ? 'selected ':'';?>value="30">30条/页</option>
                                </select>
                            </div>
                            <ul class="pagination  no-margin">
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
            </div>
        </div>
    </div>
    <!-- /.row -->
</section>

<?php

$js = <<<_SCRIPT

    $("#pass").click(function(){     
        var id = $(this).attr('title');
        
        //询问框
        layer.confirm('确定通过该提现申请吗？确认后，需要财务将该笔金额打到顾问账户中。',{
            title:'提现申请通过',
            icon: 1,
            skin: 'layer-ext-moon'
        }, function(){
            sendAjax(id, 'pass')
        });
    });
    $("#back").click(function(){     
        var id = $(this).attr('title');
        
        //询问框
        layer.confirm('驳回该提现申请，该顾问钱包资金将不会变动',{
            title:'确认驳回该提现申请吗？',
            icon: 2,
            skin: 'layer-ext-moon'
        }, function(){
            sendAjax(id, 'back')
        });
    });

    function sendAjax(id, type) {
        $.ajax({
          type: 'POST',
          url: 'confirm',
          data: {"id": id,"type": type},
          success: function(data){
            if(data.code == 200) {
                layer.msg(data.message,{time: 1000});
                location.reload();
            } else {
                layer.msg(data.message);
            }
          },
          dataType: "json",
        });
    }
 
    $('.lte-table-column-sorter-up').click(function(){
        url = changeUrl($(this).attr('alt'),'asc');
        location.href = url;
    });
    
    $('.lte-table-column-sorter-down').click(function(){
        url = changeUrl($(this).attr('alt'),'desc');
        location.href = url;
    });
    
    $('.lte-table-filter-dropdown-link').click(function(){
        var active_shop_ids = new Array();
        $("input[name='area_ids']:checked").each(function () {
            active_shop_ids.push(this.value);
        });
        active_shop_ids.join(",")
        url = changeUrl('area_ids', active_shop_ids);
        location.href = url;
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
;


