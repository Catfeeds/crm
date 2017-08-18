<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/4/10
 * Time: 10:09
 */

use yii\helpers\Url;
use yii\helpers\Html;
?>

<section class="content-header">
    <h1 class="page-title"><a href="<?=Url::to(['index'])?>">返回</a>&nbsp;激励详情</h1>
</section>


<section class="content-body encourageinfo">
    <div class="row">
        <div class="col-lg-6 col-md-12 col-sm-12">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="panel box-none-border boxshadow-none encourage-detail border1">
                        <div class="panel-heading">
                            <strong><?=$model->name?></strong>
                            <?php if($model->status == 0) :?>
                            <?= Html::a('结束激励', ['end', 'id' => $model->id], [
                                'class' => 'label label-danger pull-right',
                                'data' => [
                                    'confirm' => '是否要确认结束激励?',
                                    'method' => 'post',
                                ],
                            ]) ?>
                            <?php else: ?>
                                <button type="submit" class="btn label label-danger pull-right" disabled="disabled">已结束</button>
                            <?php endif;?>
                        </div>
                        <div class="panel-body">
                            <div class="je">
                                <div class="t-c">
                                    <strong><?=number_format($sum, 2)?></strong>
                                    <p>已领取金额（元）</p>
                                    <p class="time">开始时间  <?=date('Y-m-d H:i', strtotime($model->start_time))?></p>
                                </div>
                                <div class="line"></div>
                                <div class="t-c">
                                    <strong><?=$model->money?></strong>
                                    <p>激励金额（元）</p>
                                    <p class="time">
                                        结束时间
                                        <?=($model->end_time == '0000-00-00 00:00:00') ? '--' : date('Y-m-d H:i', strtotime($model->end_time))?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="panel box-none-border boxshadow-none encourage-analysis border1">
                        <div class="panel-heading"><strong>激励分析</strong></div>
                        <div class="panel-body">
                            <div id="chart" style="width:100%; height:300px; background:#999; margin: 0 auto;">

                            </div>
                            <div class="col-sm-12 mt-20">
                                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                    <thead>
                                    <tr>
                                        <th>细分项</th>
                                        <th>占比</th>
                                        <th>金额(元)</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($excitationLogs)):?>
                                        <tr>
                                            <td colspan="3">无</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($excitationLogs as $k => $value): ?>
                                            <tr>
                                                <td><?=$value['title']?></td>
                                                <td><?=$value['rate']?>%</td>
                                                <td><?=$value['totalMoney']?></td>
                                            </tr>
                                        <?php endforeach;?>
                                    <?php endif;?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-12 col-sm-12">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="panel box-none-border boxshadow-none encourage-analysis  border1">
                        <div class="panel-heading"><strong>适用门店</strong></div>
                        <input type="button" class="btn btn-primary btn-sm mr-15" value="查看详情" data-toggle="modal" data-target="#myModal2">
<!--                        <div class="panel-body">-->
<!---->
<!--                            <div class="col-sm-12 mt-20">-->
<!--                                --><?php //foreach ($shops as $k => $shop):?>
<!--                                    <p>--><?//=$k?><!-- :-->
<!--                                    --><?php //foreach ($shop as $k => $v):?>
<!--                                         --><?//=$v?>
<!--                                    --><?php //endforeach; ?>
<!--                                    </p>-->
<!--                                --><?php //endforeach; ?>
<!--                            </div>-->
<!--                        </div>-->
                    </div>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="panel box-none-border boxshadow-none edu border1">
                        <div>
                            <div class="panel-heading"><strong>细分激励额度（元）</strong></div>
                            <div class="panel-body ">
                                <div class="col-sm-6 col-xs-6">
                                    <label class="col-sm-6">新建线索</label>
                                    <label class="col-sm-2"><?=$model->clue_price?></label>
                                </div>
                                <div class="col-sm-6 col-xs-6">
                                    <label class="col-sm-6">新建意向客户</label>
                                    <label class="col-sm-2"><?=$model->new_intention_price?></label>
                                </div>
                                <div class="col-sm-6 col-xs-6">
                                    <label class="col-sm-6">客户到店</label>
                                    <label class="col-sm-2"><?=$model->to_shop_price?></label>
                                </div>
                                <div class="col-sm-6 col-xs-6">
                                    <label class="col-sm-6">客户订车</label>
                                    <label class="col-sm-2"><?=$model->dingche_price?></label>
                                </div>
                                <div class="col-sm-6 col-xs-6">
                                    <label class="col-sm-6">线索转化</label>
                                    <label class="col-sm-2"><?=$model->clue_to_intention_price?></label>
                                </div>
                                <div class="col-sm-6 col-xs-6">
                                    <label class="col-sm-6">完成电话任务</label>
                                    <label class="col-sm-2"><?=$model->finish_phone_task_price?></label>
                                </div>
                                <div class="col-sm-6 col-xs-6">
                                    <label class="col-sm-6">上门拜访</label>
                                    <label class="col-sm-2"><?=$model->to_home_price?></label>
                                </div>
                                <div class="col-sm-6 col-xs-6">
                                    <label class="col-sm-6">客户成交</label>
                                    <label class="col-sm-2"><?=$model->jiaoche_price?></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="panel box-none-border boxshadow-none encourage-ranks border1">
                        <div class=" ">
                            <div class="panel-heading"><strong>激励排名</strong></div>
                            <div class="panel-body">
                                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                    <thead>
                                    <tr>
                                        <th>排名</th>
                                        <th>顾问</th>
                                        <th>领奖金额（元）</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($rank)):?>
                                        <td colspan="3">无</td>
                                    <?php else: ?>
                                        <?php foreach ($rank as $k => $value): ?>
                                        <tr>
                                            <td><?=$k+1?></td>
                                            <td><?=$value['salesman_name']?></td>
                                            <td><?=$value['totalMoney']?></td>
                                        </tr>
                                        <?php endforeach;?>
                                    <?php endif;?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /.row -->
</section>

    <div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title" id="myModalLabel">正在进行当前激励的门店</h4>
                </div>
                <div class="modal-body clearfix">
                    <div class="alert alert-warning alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <div>
                            <i class="icon fa fa-warning pull-left mb-lg"></i>
                            括号内为该激励在列表中的序号
                        </div>
                    </div>

                    <?php foreach ($tree as $item){
                        ?>
                        <div class="company">
                            <div class="title"><?php echo $item['name']?></div>
                            <dl class="dl-horizontal">
                                <?php 
                                    if(isset($item['child']) && $item['child'])
                                    {
                                        foreach ($item['child'] as $item2)
                                        {
                                ?>
                                    <dt><?php echo $item2['name']?>:</dt>
                                    <dd>
                                        <?php if(isset($item2['child']) && $item2['child']){?>
                                            <?php foreach ($item2['child'] as $item3){?>
                                                <?php if(!empty($item3['e_id'])){?>
                                                    <label><input name="Fruit" type="checkbox" disabled="disabled" value="" checked /><?php echo $item3['name'].'('.$item3['e_id'].')'?></label>

                                                <?php }else{?>
                                                    <label><input name="Fruit" type="checkbox" disabled="disabled" value="" /><?php echo $item3['name'].'(--)'?></label>

                                                <?php }?>
                                            <?php }?>

                                        <?php }?>
                                    </dd>
                                <?php 
                                        }
                                    }
                                ?>
                            </dl>
                        </div>
                    <?php }?>

                </div>
            </div>
        </div>
    </div>

<?php

$this->registerJsFile('/dist/plugins/echarts/echarts.min.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$data = [];
foreach ($excitationLogs as $v){
    $data[] = [
        'name' => $v['title'],
        'value' => $v['totalMoney']
    ];
}
$data = json_encode($data);
$script = <<<_SCRIPR

  $(function () {

    var chart = echarts.init(document.getElementById('chart'), 'shine');
        chart.setOption({
          tooltip : {
              trigger: 'item',
              formatter: "{a} <br/>{b} : {c} ({d}%)"
          },
          series : [
              {
                  name: '激励分析',
                  type: 'pie',
                  radius : '55%',
                  center: ['50%', '50%'],
                  data:{$data},
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
});

_SCRIPR;

$this->registerJs($script);