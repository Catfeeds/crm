<?php

use \yii\helpers\Url;

$this->registerJsFile('/dist/plugins/daterangepicker/moment.min.js', [
    'depends' => ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/daterangepicker/daterangepicker.js', [
    'depends' => ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/vue-element/vue.min.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/vue-element/index.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerCssFile('/dist/plugins/vue-element/index.css', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);

$this->registerJsFile('/dist/js/user/detailed.js', [
    'depends' => ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/js/user/date.js', [
    'depends' => ['backend\assets\AdminLteAsset']
]);
?>
<section class="content-header clearfix">
    <h1 class="page-title">明细查询 <span class="c-red font14 ml-10">最近更新时间：<?php echo $area['upTime']; ?></span></h1>
</section>

<section class="content-body">
    <div class="box advanced-search-form mb-lg">
        <form class="form-horizontal" action="index" method="get" id="form3">
            <input type="hidden" name="time" value="1">
            <div class="row">
                <div class="form-group col-md-4">
                    <label class="control-label col-sm-3">区域&门店：</label>
                    <div class="col-md-8"  id="orgSelect">
                        <el-cascader
                                placeholder="请选择"
                                size="small"
                                :options="options1"
                                v-model="selectedOptions3"
                                @change="handlechange_shopid"
                                change-on-select
                                filterable
                            ></el-cascader>
                        <input id="shopid" name="shop_id" type="hidden" value="<?php echo (isset($get['shop_id']) ? $get['shop_id'] : '') ?>">
                    </div>
                </div>
                <div class="form-group col-md-4">
                    <label for="" class="control-label col-sm-3">日期：</label>
                    <div class="col-sm-9">
                        <input class="form-control" id="addtime" name="addtime" value="<?php echo $get['addtime']; ?>"
                               type="text">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="pull-right mr-15">
                        <input class="btn btn-primary btn-sm pull-left mr-15" value="查询" type="submit">
                        <a href="index?check=1" class="pull-left"><input class="btn btn-default btn-sm" value="清除"
                                                                         id="clear" type="button"></a>
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
                        <th style="color: #000;">名称</th>
                        <th style="color: red;">新增线索</th>
                        <?php if ($level!=30){?>
                        <th style="color: red; ">未认领</th>
                        <?php }?>
                        <th style="color: red;">未跟进线索</th>
                        <th style="color: green;">新增意向客户</th>
                        <th style="color: green;">跟进中意向客户</th>
                        <th style="color: blue;">电话任务</th>
                        <th style="color: blue;">任务完成</th>
                        <th style="color: blue;">任务取消</th>
                        <th style="color: blueviolet;">商谈数</th>
                        <th style="color: blueviolet;">到店数</th>
                        <th style="color: blueviolet;">上门数</th>
                        <th style="color: steelblue;">订车数</th>
                        <th style="color: steelblue;">交车数</th>
                        <th style="color: steelblue;">战败</th>
                        <th style="color: steelblue;">交车任务</th>
                        <th style="color: steelblue;">未跟进交车任务</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($data)) {
                        $xx_id = $groupBy;
                        $new_clue_num          = 0;
                        $xiansuo_genjin_count  = 0;
                        $new_intention_num     = 0;
                        if ($level != 3)//不是门店才有未下发的线索
                            $not_issued_num        = 0;
                        $yixiang_genjin        = 0;
                        $phone_task_num        = 0;
                        $finish_phone_task_num = 0;
                        $cancel_phone_task_num = 0;
                        $talk_num              = 0;
                        $to_shop_num           = 0;
                        $to_home_num           = 0;
                        $ding_che_num          = 0;
                        $chengjiao_num         = 0;
                        $fail_num              = 0;
                        $intMentionTask = $intNotMentionTask = 0;
                        $selectShopId = isset($get['shop_id']) ? $get['shop_id'] : 0;
                        foreach ($data as $k => $v) {
                            ?>
                            <tr>
                                <td>
                                    <?php echo ++$k; ?>
                                </td>
                                <td>
                                    <?php if ($level < 30){?>
                                    <a href="#" class="x_id" val="<?php echo $v['list'][$xx_id] ?>" val_name="<?php echo $v['list']['name']; ?>">
                                        <?php echo $v['list']['name']; ?>
                                    </a>
                                <?php }else{echo $v['list']['name'];}?>
                                </td>
                                <td>

                                    <a href="#"
                                       url="new-xian-suo?level=<?php echo $v['list']['this_level']; ?>&id=<?php echo $v['list'][$xx_id]; ?>&type=0&addtime=<?php echo $get['addtime']; ?>&shop_id=<?php echo $selectShopId; ?>"
                                       class="info">
                                        <?php
                                        echo $v['list']['new_clue_num'];
                                        $new_clue_num += $v['list']['new_clue_num'];
                                        ?>
                                    </a>
                                </td>
                                <?php if ($level != 30){?>
                                <td>
                                    <a href="#"
                                       url="new-xian-suo?level=<?php echo $v['list']['this_level']; ?>&id=<?php echo $v['list'][$xx_id]; ?>&type=4&addtime=<?php echo $get['addtime']; ?>&shop_id=<?php echo $selectShopId; ?>"
                                       class="info">
                                        <?php
                                        echo $v['list']['not_issued_num'];
                                        $not_issued_num += $v['list']['not_issued_num'];
                                        ?>
                                    </a>
                                </td>
                                <?php }?>
                                <td>
                                    <a href="#"
                                       url="new-xian-suo?level=<?php echo $v['list']['this_level']; ?>&id=<?php echo $v['list'][$xx_id]; ?>&type=1&addtime=<?php echo $get['addtime']; ?>&shop_id=<?php echo $selectShopId; ?>"
                                       class="info">
                                        <?php
                                        echo $v['xiansuo_genjin']['num'];
                                        $xiansuo_genjin_count += $v['xiansuo_genjin']['num'];
                                        ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="#"
                                       url="new-xian-suo?level=<?php echo $v['list']['this_level']; ?>&id=<?php echo $v['list'][$xx_id]; ?>&type=2&addtime=<?php echo $get['addtime']; ?>&shop_id=<?php echo $selectShopId; ?>"
                                       class="info">
                                        <?php
                                        echo $v['list']['new_intention_num'];
                                        $new_intention_num += $v['list']['new_intention_num'];
                                        ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="#"
                                       url="new-xian-suo?level=<?php echo $v['list']['this_level']; ?>&id=<?php echo $v['list'][$xx_id]; ?>&type=3&addtime=<?php echo $get['addtime']; ?>&shop_id=<?php echo $selectShopId; ?>"
                                       class="info">
                                        <?php
                                        echo $v['yixiang_genjin']['num'];
                                        $yixiang_genjin += $v['yixiang_genjin']['num'];
                                        ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="#"
                                       url="task?level=<?php echo $v['list']['this_level']; ?>&id=<?php echo $v['list'][$xx_id]; ?>&type=0&addtime=<?php echo $get['addtime']; ?>&shop_id=<?php echo $selectShopId; ?>"
                                       class="info">
                                        <?php
                                        echo $v['list']['phone_task_num'];
                                        $phone_task_num += $v['list']['phone_task_num'];
                                        ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="#"
                                       url="task?level=<?php echo $v['list']['this_level']; ?>&id=<?php echo $v['list'][$xx_id]; ?>&type=1&addtime=<?php echo $get['addtime']; ?>&shop_id=<?php echo $selectShopId; ?>"
                                       class="info">
                                        <?php
                                        echo $v['list']['finish_phone_task_num'];
                                        $finish_phone_task_num += $v['list']['finish_phone_task_num'];
                                        ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="#"
                                       url="task?level=<?php echo $v['list']['this_level']; ?>&id=<?php echo $v['list'][$xx_id]; ?>&type=2&addtime=<?php echo $get['addtime']; ?>&shop_id=<?php echo $selectShopId; ?>"
                                       class="info">
                                        <?php
                                        echo $v['list']['cancel_phone_task_num'];
                                        $cancel_phone_task_num += $v['list']['cancel_phone_task_num'];
                                        ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="#"
                                       url="talk?level=<?php echo $v['list']['this_level']; ?>&id=<?php echo $v['list'][$xx_id]; ?>&type=0&addtime=<?php echo $get['addtime']; ?>&shop_id=<?php echo $selectShopId; ?>"
                                       class="info">
                                        <?php
                                        echo $v['list']['talk_num'];
                                        $talk_num += $v['list']['talk_num'];
                                        ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="#"
                                       url="talk?level=<?php echo $v['list']['this_level']; ?>&id=<?php echo $v['list'][$xx_id]; ?>&type=1&addtime=<?php echo $get['addtime']; ?>&shop_id=<?php echo $selectShopId; ?>"
                                       class="info">
                                        <?php
                                        echo $v['list']['to_shop_num'];
                                        $to_shop_num += $v['list']['to_shop_num'];
                                        ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="#"
                                       url="talk?level=<?php echo $v['list']['this_level']; ?>&id=<?php echo $v['list'][$xx_id]; ?>&type=2&addtime=<?php echo $get['addtime']; ?>&shop_id=<?php echo $selectShopId; ?>"
                                       class="info">
                                        <?php
                                        echo $v['list']['to_home_num'];
                                        $to_home_num += $v['list']['to_home_num'];
                                        ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="#"
                                       url="car?level=<?php echo $v['list']['this_level']; ?>&id=<?php echo $v['list'][$xx_id]; ?>&type=0&addtime=<?php echo $get['addtime']; ?>&shop_id=<?php echo $selectShopId; ?>"
                                       class="info">
                                        <?php
                                        echo $v['list']['ding_che_num'];
                                        $ding_che_num += $v['list']['ding_che_num'];
                                        ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="#"
                                       url="car?level=<?php echo $v['list']['this_level']; ?>&id=<?php echo $v['list'][$xx_id]; ?>&type=1&addtime=<?php echo $get['addtime']; ?>&shop_id=<?php echo $selectShopId; ?>"
                                       class="info">
                                        <?php
                                        echo $v['list']['chengjiao_num'];
                                        $chengjiao_num += $v['list']['chengjiao_num'];
                                        ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="#"
                                       url="fail?level=<?php echo $v['list']['this_level']; ?>&id=<?php echo $v['list'][$xx_id]; ?>&addtime=<?php echo $get['addtime']; ?>&shop_id=<?php echo $selectShopId; ?>"
                                       class="info">
                                        <?php
                                        echo $v['list']['fail_num'];
                                        $fail_num += $v['list']['fail_num'];
                                        ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="#"
                                       url="<?=Url::toRoute([
                                           'mention-task',
                                           'level' => $v['list']['this_level'],
                                           'id' => $v['list'][$xx_id],
                                           'addtime' => $get['addtime'],
                                           'shop_id' => $selectShopId,
                                            'type' => 'mention-task'
                                       ])?>"
                                       class="info">
                                        <?php
                                        echo $v['list']['mention_task'];
                                        $intMentionTask += $v['list']['mention_task'];
                                        ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="#"
                                       url="<?=Url::toRoute([
                                           'mention-task',
                                           'level' => $v['list']['this_level'],
                                           'id' => $v['list'][$xx_id],
                                           'addtime' => $get['addtime'],
                                           'shop_id' => $selectShopId,
                                           'type' => 'not-mention-task'
                                       ])?>"
                                       class="info">
                                        <?php
                                        echo $v['list']['not_mention_task'];
                                        $intNotMentionTask += $v['list']['not_mention_task'];
                                        ?>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td></td>
                            <td>总计</td>
                            <td><?php echo $new_clue_num; ?></td>
                            <?php if ($level != 30){?>
                            <td><?php echo $not_issued_num; ?></td>
                            <?php }?>
                            <td><?php echo $xiansuo_genjin_count; ?></td>
                            <td><?php echo $new_intention_num; ?></td>
                            <td><?php echo $yixiang_genjin; ?></td>
                            <td><?php echo $phone_task_num; ?></td>
                            <td><?php echo $finish_phone_task_num; ?></td>
                            <td><?php echo $cancel_phone_task_num; ?></td>
                            <td><?php echo $talk_num; ?></td>
                            <td><?php echo $to_shop_num; ?></td>
                            <td><?php echo $to_home_num; ?></td>
                            <td><?php echo $ding_che_num; ?></td>
                            <td><?php echo $chengjiao_num; ?></td>
                            <td><?php echo $fail_num; ?></td>
                            <td><?php echo $intMentionTask; ?></td>
                            <td><?php echo $intNotMentionTask; ?></td>
                        </tr>
                        <?php
                    } ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
    <div>
        <iframe id="info" src="" width="100%" height="1200px" frameborder="no"></iframe>
    </div>

</section>
<script type="text/javascript">
    var cengji = '<?php echo $level;?>';
    var selectOrgJson = eval('<?php echo $selectOrgJson; ?>');
    var defaultSelectString = '<?php echo (isset($get['shop_id']) ? $get['shop_id'] : '') ?>';
    var defaultSelectArray = defaultSelectString.split(",");

</script>