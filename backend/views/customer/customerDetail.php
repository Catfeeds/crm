<?php

    $this->registerCssFile('/css/layer.css', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);

    $this->registerJsFile('/dist/js/layer.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);
    $this->registerJsFile('/dist/js/customer/img.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);

?>
    <!--客户详情页面-->
    <section class="content-header">
      <h1 class="page-title">客户详情</h1>
    </section>

    <!-- Main content -->
    <section class="content-body">
      <div class="row">
        <div class="col-md-12">
             <ul class="nav nav-tabs kh-info">
                <li class="active"><a href="#customer" data-toggle="tab" aria-expanded="true">客户信息</a></li>
                 <?php if($isCheck != 1){?>
                <li class=""><a href="#buycarinfo" data-toggle="tab" aria-expanded="false">购车信息</a></li>
                <li class=""><a href="#talkrecord" data-toggle="tab" aria-expanded="false">商谈记录</a></li>
                <li class=""><a href="#phonetask" data-toggle="tab" aria-expanded="false">电话任务</a></li>
                 <?php }?>
             </ul>
        </div>
        <div class="tab-content clearfix">
          <div class="tab-pane col-md-12 active" id="customer">
            <div class="border1 bd-t0">
               <div class="panel boxshadow-none bdb">
                    <div class="panel-heading"><h5><strong>客户信息</strong></h5></div>
                    <dl class="clearfix">
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">姓名：</dd>
                            <dd class="col-sm-7"><?php echo ($customerInfo['name'] ? $customerInfo['name'] : '--');?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">手机号：</dd>
                            <dd class="col-sm-7"><?php echo ($customerInfo['phone'] ? $customerInfo['phone'] : '--');?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">备用电话：</dd>
                            <dd class="col-sm-7"><?php echo ($customerInfo['spare_phone'] ? $customerInfo['spare_phone'] : '--');?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">微信号：</dd>
                            <dd class="col-sm-7"><?php echo ($customerInfo['weixin'] ? $customerInfo['weixin'] : '--');?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">性别：</dd>
                            <dd class="col-sm-7">
                                <?php
                                    echo ($customerInfo['sex'] == 1 ? "男" : ($customerInfo['sex'] == 2 ? "女" : '--'));
                                ?>
                            </dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <?php if($clueInfo['status'] >= 1){?>
                            <dd style="float:left;width:100px;">年龄：</dd>
                            <dd class="col-sm-7">
                                <?php 
                                    $strAgeName = $objDataDic->getAgeGroupName($customerInfo['age_group_level_id']);
                                    echo ($strAgeName ? $strAgeName : '--');
                                ?>
                            </dd>
                         </div>
                        <div class="col-md-4 mb-md clearfix">
                            <?php }?>
                            <dd style="float:left;width:100px;">职业：</dd>
                            <dd class="col-sm-7">
                                <?php 
                                    $strProfession = $objDataDic->getProfessionName($customerInfo['profession']);
                                    echo ($strProfession ? $strProfession : '--');
                                ?>
                            </dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">客户来源：</dd>
                            <dd class="col-sm-7">
                                <?php 
                                    $strSource =  $objDataDic->getSourceName($clueInfo['clue_source']);
                                    echo ($strSource ? $strSource : '--');
                                ?>
                            </dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">地区：</dd>
                            <dd class="col-sm-7">
                                <?php 
                                    $strArea = $objDataDic->areaCodeToName($customerInfo['area']);
                                    echo ($strArea ? $strArea : '--');
                                ?>
                            </dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <?php if($clueInfo['status'] >= 1){?>
                            <dd style="float:left;width:100px;">详细地址：</dd>
                            <dd class="col-sm-7"><?php echo ($customerInfo['address'] ? $customerInfo['address'] : '--');?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <?php }?>
                            <dd style="float:left;width:100px;">说明：</dd>
                            <dd class="col-sm-7"><?php echo ($clueInfo['des'] ? $clueInfo['des'] : '--');?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <?php if($clueInfo['status'] >= 1){?>
                            <dd style="float:left;width:100px;">归属顾问：</dd>
                            <dd class="col-sm-7"><?php echo ($clueInfo['salesman_name'] ? $clueInfo['salesman_name'] : '--');?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <?php }?>
                            <!--战败的显示战败顾问-->
                            <?php if($clueInfo['is_fail'] == 1){?>
                            <dd style="float:left;width:100px;">战败顾问：</dd>
                            <dd class="col-sm-7"><?php echo ($clueInfo['salesman_name'] ? $clueInfo['salesman_name'] : '--');?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <?php }?>
                            <!--交车客户显示交车顾问-->
                            <?php if($clueInfo['status'] == 3){?>
                            <dd style="float:left;width:100px;">交车顾问：</dd>
                            <dd class="col-sm-7"><?php echo ($clueInfo['salesman_name'] ? $clueInfo['salesman_name'] : '--');?></dd>
                        </div>
                        <?php }?>
                    </dl>
                    <div class="clear"></div>
               </div>
                <!--线索客户-->
                <?php if($clueInfo['status'] == 0){ ?>
               <div class="panel boxshadow-none bdb">
                    <div class="panel-heading"><h5><strong>意向购车信息</strong></h5></div>
                    <dl class="clearfix">
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">意向车型：</dd>
                            <dd class="col-sm-7"><?php echo ($clueInfo['intention_des'] ? $clueInfo['intention_des'] : '--'); ?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">购买方式：</dd>
                            <dd class="col-sm-7">
                                <?php 
                                    $strBuyType = $objDataDic->getBuyTypeName($clueInfo['buy_type']); 
                                    echo ($strBuyType ? $strBuyType : '--');
                                ?>
                            </dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">拟购时间：</dd>
                            <dd class="col-sm-7">
                                <?php 
                                    $strPt = $objDataDic->getPlannedPurchaseTime($clueInfo['planned_purchase_time_id']); 
                                    echo ($strPt ? $strPt : '--');
                                ?>
                            </dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">报价信息：</dd>
                            <dd class="col-sm-7"><?php echo ($clueInfo['quoted_price'] ? $clueInfo['quoted_price'] : '--'); ?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">促销内容：</dd>
                            <dd class="col-sm-7"><?php echo ($clueInfo['sales_promotion_content'] ? $clueInfo['sales_promotion_content'] : '--'); ?></dd>
                        </div>
                    </dl>
                    <div class="clear"></div>
               </div>
               <div class="panel boxshadow-none bdb">
                    <div class="panel-heading"><h5><strong>分配信息</strong></h5></div>
                    <dl class="clearfix">
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">分配人：</dd>
                            <dd class="col-sm-7"><?php echo ($clueInfo['who_assign_name'] ? $clueInfo['who_assign_name'] : '--');?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">分配时间：</dd>
                            <dd class="col-sm-7">
                                <?php echo ($clueInfo['assign_time'] ? date('Y-m-d H:i:s', $clueInfo['assign_time']) : '--');?>
                            </dd>
                        </div>
                    </dl>
                    <div class="clear"></div>
               </div>
                <?php }?>
            </div>
          </div>
          <div class="tab-pane col-md-12" id="buycarinfo">
            <div class="border1 bd-t0">
                <!--意向客户 订车客户  交车客户-->
                <?php if($clueInfo['status'] >= 1){ ?>
                <div class="panel boxshadow-none bdb">
                    <?php
                        switch($clueInfo['status'])
                        {
                            case 1 : $title = '意向购车信息'; break;;
                            case 2 : $title = '购车信息'; break;;
                            case 3 : $title = '购车信息'; break;;
                        }
                    ?>
                    <div class="panel-heading"><h5><strong><?php echo $title;?></strong></h5></div>
                    <dl class="clearfix">
                        <?php if($clueInfo['status'] == 1){?>
                        <!--意向等级 意向车型 意向客户-->
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">意向等级：</dd>
                            <dd class="col-sm-7"><?php echo ($clueInfo['intention_level_des'] ? $clueInfo['intention_level_des'] : '--');?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">意向车型：</dd>
                            <dd class="col-sm-7"><?php echo ($clueInfo['intention_des'] ? $clueInfo['intention_des'] : '--');?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">备选车型：</dd>
                            <dd class="col-sm-7"><?php echo ($clueInfo['spare_intention_id'] ? $clueInfo['spare_intention_id'] : '--');?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">购买方式：</dd>
                            <dd class="col-sm-7">
                                <?php 
                                    $strBuyType = $objDataDic->getBuyTypeName($clueInfo['buy_type']); 
                                    echo ($strBuyType ? $strBuyType : '--');
                                ?>
                            </dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">拟购时间：</dd>
                            <dd class="col-sm-7">
                                <?php 
                                    $strPt = $objDataDic->getPlannedPurchaseTime($clueInfo['planned_purchase_time_id']); 
                                    echo ($strPt ? $strPt : '--');
                                ?>
                            </dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">报价信息：</dd>
                            <dd class="col-sm-7"><?php echo ($clueInfo['quoted_price'] ? $clueInfo['quoted_price'] : '--'); ?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">促销内容：</dd>
                            <dd class="col-sm-7"><?php echo ($clueInfo['sales_promotion_content'] ? $clueInfo['sales_promotion_content'] : '--'); ?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">对比车型：</dd>
                            <dd class="col-sm-7">
                                <?php 
                                    echo ($clueInfo['contrast_intention_id'] ? $clueInfo['contrast_intention_id'] : '--');
                                ?>
                            </dd>
                        </div>
                       <?php } else if($clueInfo['status'] == 2){?>
                        <!--订车时间 订车客户-->
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">订车日期：</dd>
                            <dd class="col-sm-7">
                                <?php echo ($orderInfo['create_time'] ? date('Y-m-d', $orderInfo['create_time']) : '--');?>
                            </dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">预计交车日期：</dd>
                            <dd class="col-sm-7">
                                <?php echo ($orderInfo['predict_car_delivery_time'] ? date('Y-m-d', $orderInfo['predict_car_delivery_time']) : '--');?>
                            </dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">车型：</dd>
                            <dd class="col-sm-7"><?php echo ($orderInfo['car_type_name'] ? $orderInfo['car_type_name'] : '--');?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">颜色/配置：</dd>
                            <dd class="col-sm-7"><?php echo ($orderInfo['color_configure'] ? $orderInfo['color_configure'] : '--');?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">购买方式：</dd>
                            <dd class="col-sm-7">
                                <?php 
                                    $strBuyType = $objDataDic->getBuyTypeName($orderInfo['buy_type']);
                                    echo ($strBuyType ? $strBuyType : '--');
                                ?>
                            </dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">贷款年限：</dd>
                            <dd class="col-sm-7"><?php echo ($orderInfo['loan_period'] ? $orderInfo['loan_period'] : '--');?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">订金：</dd>
                            <dd class="col-sm-7"><?php echo ($orderInfo['deposit'] ? $orderInfo['deposit'] : '--');?>元</dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">成交价格：</dd>
                            <dd class="col-sm-7"><?php echo ($orderInfo['delivery_price'] ? $orderInfo['delivery_price'] : '--');?>元</dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">优惠价格：</dd>
                            <dd class="col-sm-7"><?php echo ($orderInfo['discount_price'] ? $orderInfo['discount_price'] : '--');?>元</dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">车牌号：</dd>
                            <dd class="col-sm-7"><?php echo ($orderInfo['car_number'] ? $orderInfo['car_number'] : '--');?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">发动机号：</dd>
                            <dd class="col-sm-7"><?php echo ($orderInfo['engine_code'] ? $orderInfo['engine_code'] : '--');?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">车架号：</dd>
                            <dd class="col-sm-7"><?php echo ($orderInfo['frame_number'] ? $orderInfo['frame_number'] : '--');?></dd>
                        </div>
                       <?php } else if($clueInfo['status'] == 3){ ?>
                        <!--交车客户-->
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">购车日期：</dd>
                            <dd class="col-sm-7"><?php echo ($orderInfo['car_delivery_time'] ? date('Y-m-d', $orderInfo['car_delivery_time']) : '--');?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">车型：</dd>
                            <dd class="col-sm-7"><?php echo ($orderInfo['car_type_name'] ? $orderInfo['car_type_name'] : '--');?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">颜色/配置：</dd>
                            <dd class="col-sm-7"><?php echo ($orderInfo['color_configure'] ? $orderInfo['color_configure'] : '--');?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                        <dd style="float:left;width:100px;">购买方式：</dd>
                            <dd class="col-sm-7">
                                <?php 
                                    $strBuyType = $objDataDic->getBuyTypeName($orderInfo['buy_type']);
                                    echo ($strBuyType ? $strBuyType : '--');
                                ?>
                            </dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">贷款年限：</dd>
                            <dd class="col-sm-7"><?php echo ($orderInfo['loan_period'] ? $orderInfo['loan_period'] : '--');?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">订金：</dd>
                            <dd class="col-sm-7"><?php echo ($orderInfo['deposit'] ? $orderInfo['deposit'] : '--');?>元</dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">成交价格：</dd>
                            <dd class="col-sm-7"><?php echo ($orderInfo['delivery_price'] ? $orderInfo['delivery_price'] : '--');?>元</dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">优惠价格：</dd>
                            <dd class="col-sm-7"><?php echo ($orderInfo['discount_price'] ? $orderInfo['discount_price'] : '--');?>元</dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">车牌号：</dd>
                            <dd class="col-sm-7"><?php echo ($orderInfo['car_number'] ? $orderInfo['car_number'] : '--');?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">发动机号：</dd>
                            <dd class="col-sm-7"><?php echo ($orderInfo['engine_code'] ? $orderInfo['engine_code'] : '--');?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">车架号：</dd>
                            <dd class="col-sm-7"><?php echo ($orderInfo['frame_number'] ? $orderInfo['frame_number'] : '--');?></dd>
                        </div>
                        <?php } ?>
                    </dl>
                    <div class="clear"></div>
                </div>
                <?php } ?>
                <!--订车客户  交车客户-->
                <?php if($clueInfo['status'] > 1) {?>
               <div class="panel boxshadow-none bdb">
                    <div class="panel-heading"><h5><strong>保险</strong></h5></div>
                    <dl class="clearfix">
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">本店投保：</dd>
                            <dd class="col-sm-7"><?php echo ($orderInfo['is_insurance'] == 1 ? "是" : "否"); ?></dd>
                        </div>
                        <?php if($orderInfo['is_insurance'] == 1){?>
                        <div class="col-md-4 mb-md clearfix">    
                            <dd style="float:left;width:100px;">保险到期：</dd>
                            <dd class="col-sm-7"><?php echo ($orderInfo['insurance_time'] ? date('Y-m-d', $orderInfo['insurance_time']) : '--'); ?></dd>
                        </div>
                        <?php }?>
                    </dl>
                    <div class="clear"></div>
               </div>
               <div class="panel boxshadow-none bdb">
                    <div class="panel-heading"><h5><strong>其他</strong></h5></div>
                    <dl class="clearfix">
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">是否加装：</dd>
                            <dd class="col-sm-7"><?php echo ($orderInfo['is_add'] == 1 ? '是' : '否'); ?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">精品装饰：</dd>
                            <dd class="col-sm-7"><?php echo ($orderInfo['add_content'] ? $orderInfo['add_content'] : '--'); ?></dd>
                        </div>
                        <div class="col-md-4 mb-md clearfix">
                            <dd style="float:left;width:100px;">赠送：</dd>
                            <dd class="col-sm-7"><?php echo ($orderInfo['give'] ? $orderInfo['give'] : '--'); ?></dd>
                        </div>
                    </dl>
                    <div class="clear"></div>
               </div>
                <?php } ?>
            </div>
          </div>
          <div class="tab-pane col-md-12" id="talkrecord">
            <h5 class="cont-title"><strong>商谈记录</strong></h5>
            <div class="box box-none-border">
              <div class="box-body no-padding">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-list-check">
                      <thead>
                        <tr>
                          <th>序号</th>
                          <th>联系时间</th>
                          <th>类型</th>
                          <th>标签</th>
                          <th>商谈内容</th>
                          <th>图片</th>
                          <th>录音</th>
                        </tr>
                      </thead>
                      <tbody>
                          <?php foreach($talkList as $k => $val){ ?>
                            <tr>
                                <td><?php echo ($k + 1); ?></td>
                                <td><?php echo ($val['create_time'] ? date('Y-m-d H:i', $val['create_time']) : '--');?></td>
                                <td><?php echo $objDataDic->getTalkTypeName($val['talk_type']); ?></td>
                                <td>
                                    <?php
                                        $arrTag = array_filter(explode(',', $val['select_tags']));
                                        $strTas =  implode(',', array_filter($objDataDic->getTagNamebyIds($arrTag)));
                                        echo ($strTas ? $strTas : '--');
                                    ?>
                                </td>
                                <td><?php echo ($val['content'] ? $val['content'] : '--'); ?></td>
                                <td>
                                    <div id="layer-photos-demo" class="layer-photos-demo">
                                    <?php
                                        $arrImgs = array_filter(explode(',', $val['imgs']));

                                        $intCount = count($arrImgs);

                                        if($intCount)
                                        {
                                            foreach ($arrImgs as $img_v){
                                    ?>
                                  <img src="<?php echo $img_v;?>" width="50" height="50" onclick='img(<?php echo $intCount;?>)'>
                                    <!--此处有图片控件-->
                                    <?php
                                            }
                                        }
                                        else
                                        {
                                            echo '--';
                                        }
                                    ?>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                        $arrVoices = array_filter(explode(',', $val['voices']));
                                        $intCount = count($arrVoices);
                                        if($intCount)
                                        {
                                            foreach($arrVoices as $voices)
                                            {
                                    ?>
                                    <audio controls="controls">
                                        <source src="<?php echo $voices;?>" type="audio/mpeg">
                                    </audio>
                                    <br />
                                    <!--此处播放录音-->
                                    <?php
                                            }
                                        }
                                        else
                                        {
                                            echo '--';
                                        }
                                    ?>
                                </td>
                            </tr>
                          <?php }?>
                      </tbody>
                    </table>
                </div>
              </div>
            </div>
          </div>
          <div class="tab-pane col-md-12" id="phonetask">
            <h5 class="cont-title"><strong>电话记录</strong></h5>
            <div class="box box-none-border">
              <div class="box-body no-padding">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-list-check">
                      <thead>
                        <tr>
                          <th>序号</th>
                          <th>任务时间</th>
                          <th>来源</th>
                          <th>描述</th>
                          <th>完成时间</th>
                          <th>状态</th>
                          <th>备注</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach($taskList as $k => $val){ ?>
                        <tr>
                            <td><?php echo ($k + 1);?></td>
                            <td><?php echo ($val['task_date'] ? $val['task_date'] : '--'); ?></td>
                            <td><?php echo ($val['task_from'] ? $val['task_from'] : '--');?></td>
                            <td><?php echo empty($val['task_des']) ? '--'  : $val['task_des'];?></td>
                            <td>
                                <?php
                                    if(!empty($val['end_time']))
                                    {
                                        echo date('Y-m-d', $val['end_time']); 
                                    }else {
                                        echo '--';
                                    }
                                ?>
                            </td>
                            <td>
                                <?php
                                    if($val['is_cancel'] == 1)
                                    {
                                        echo '已取消';
                                    }
                                    else if($val['is_finish'] == 2) // 2 - 已完成， 1 - 未完成
                                    {
                                        echo '已完成';
                                    }
                                    else
                                    {
                                        echo '未完成';
                                    }
                                ?>
                            </td>
                            <td>
                                <?php echo ($val['cancel_reason'] ? $val['cancel_reason'] : ' -- ') ?>
                            </td>
                        </tr>
                        <?php } ?>
                      </tbody>
                    </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
  