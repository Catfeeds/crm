<?php

$this->title = '门店管理';
$depends = ['depends' => ['backend\assets\AdminLteAsset']];
$this->registerJsFile('/dist/plugins/vue-element/vue.min.js', $depends);
$this->registerJsFile('/dist/plugins/vue-element/index.js', $depends);
$this->registerCssFile('/dist/plugins/vue-element/index.css', $depends);
?>
<section class="content-header">
    <h1 class="page-title">门店管理</h1>
</section>
<section class="content-body">
    <div class="box advanced-search-form mb-lg">
        <form action="<?=\yii\helpers\Url::toRoute(['index'])?>" id="search-form">
            <div class="row">
                <div class="form-group col-lg-4 col-md-6">
                    <label for="" class="control-label col-sm-3 t-r">门店：</label>
                    <div class="col-md-8"  id="orgSelect">
                        <el-cascader
                                placeholder="<?=$shop_name?>"
                                size="small"
                                :options="options1"
                                v-model="selectedOptions3"
                                @change="handlechange_shopid"
                                change-on-select
                                filterable
                                clearable
                        ></el-cascader>
                        <input id="input-shop_id" name="shop_id" value="<?=$shop_id?>" type="hidden">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 t-r">
                    <div class="pull-right mr-15">
                        <input class="btn btn-primary btn-sm pull-left mr-15" value="查询" type="submit">
                        <input class="btn btn-default btn-sm pull-left" value="清除" id="clear" type="button">
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="box box-none-border">
        <div class="box-body no-padding">
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-list-check">
                    <thead>
                        <tr>
                            <th width="60">序号</th>
                            <th align="center" style="text-align: center">门店</th>
                            <th align="center" style="text-align: center">编辑时间</th>
                            <th align="center" style="text-align: center">状态</th>
                            <th align="center" style="text-align: center">操作</th>
                        </tr>
                    </thead>
                    <?php if ($lists): ?>
                    <tbody>
                        <?php foreach ($lists as $key => $value) : ?>
                        <tr>
                            <td align="center"><?=$key+1?></td>
                            <td align="center"><?=$value['name']?></td>
                            <td align="center"><?=empty($value['updated_at']) ? '--' : $value['updated_at']?></td>
                            <td align="center"><?=empty($value['status']) ? '无效' : '有效'?></td>
                            <td align="center">
                                <a data="<?=$value['id']?>" data-status="<?=empty($value['status']) ? 0 : 1?>" href="javascript:;" class="set-close">设置为无效</a>
                                <a data="<?=$value['id']?>" class="set-update">编辑</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="myModal" tabindex="-1" data-backdrop='static' aria-labelledby="myModalLabel">

</div>

<?php $this->beginBlock('javascript') ?>
<script type="text/javascript">
    var objShop = null;
    var defaultSelectString = '';
    var defaultSelectArray = defaultSelectString.split(",");
    $(function(){

        // 编辑页面
        $(".set-update").click(function () {
            $('#myModal').load("<?=\yii\helpers\Url::toRoute('update')?>" + "?id=" + $(this).attr("data")).modal();
        });

        $("#clear").click(function(){
            $("#search-form").get(0).reset();
            objShop.$children[0].handlePick([], true);
        });

        // 区域&门店
        objShop = new Vue({
            el: '#orgSelect',
            data: function () {
                return {
                    formInline: {
                        desc: []
                    },
                    options1: <?=$orgList?>,
                    selectedOptions3: defaultSelectArray
                }
            },
            methods: {
                handlechange_shopid: function (value) {
                    $("#input-shop_id").val(value);
                }
            }
        });
    });
</script>
<?php $this->endBlock(); ?>
