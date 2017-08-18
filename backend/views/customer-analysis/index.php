<?php


$this->registerJsFile('/dist/plugins/echarts.min.js', [
    'depends' => ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/js/customeranalysis.js', [
    'depends' => ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/daterangepicker/bootstrap-datepicker.js', [
    'depends' => ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/daterangepicker/bootstrap-datepicker_002.js', [
    'depends' => ['backend\assets\AdminLteAsset']
]);
$this->registerCssFile('/dist/css/datepicker3.css', [
    'depends' => ['backend\assets\AdminLteAsset']
]);

?>
<section class="content-header">
    <h1 class="page-title">线索分析 </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="alert alert-warning alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <div><i class="icon fa fa-warning"></i>转化率：以成交率为例，成交率 = 交车数 / （交车数 + 意向客户数） 。</div>
    </div>
    <div class="panel  border1">
        <div class="panel-heading pdb-0"><strong>线索渠道来源分析</strong></div>
        <div class="panel-body">
            <div class="form-inline">
                <div class="form-group mb-15 sm-mr-15">
                    <label class="control-label col-sm-4 t-r">区域&amp;门店：</label>
                    <select id="info_owner_id_1" name="info_owner_id"  class="form-control">
                    <?php foreach ($children as $item){?>
                        <option value="<?php $item['id']?>"><?php echo $item['name']?></option>
                    <?php }?>
                    </select>
<!--                    <input type="text" class="form-control">-->
                </div>
                <div class="form-group mb-15">
                    <label class="control-label col-sm-4 t-r">区域&amp;门店：</label>
                    <div class="timebox">
                        <input type="text" class="form-control" id="datetime"> <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="mb-15" id="chart1" style="min-width: 200px; height: 300px; -webkit-tap-highlight-color: transparent; user-select: none; position: relative; background: rgb(243, 243, 243);" _echarts_instance_="ec_1491803150698"><div style="position: relative; overflow: hidden; width: 517px; height: 300px; padding: 0px; margin: 0px; border-width: 0px; cursor: pointer;"><canvas width="517" height="300" data-zr-dom-id="zr_0" style="position: absolute; left: 0px; top: 0px; width: 517px; height: 300px; user-select: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); padding: 0px; margin: 0px; border-width: 0px;"></canvas></div><div style="position: absolute; display: none; border-style: solid; white-space: nowrap; z-index: 9999999; transition: left 0.4s cubic-bezier(0.23, 1, 0.32, 1), top 0.4s cubic-bezier(0.23, 1, 0.32, 1); background-color: rgba(0, 0, 0, 0.6); border-width: 0px; border-color: rgb(51, 51, 51); border-radius: 4px; color: rgb(255, 255, 255); font-style: normal; font-variant: normal; font-weight: normal; font-stretch: normal; font-size: 14px; font-family: &quot;Microsoft YaHei&quot;; line-height: 21px; padding: 5px; left: 300px; top: 155px;">访问来源 <br>直接访问 : 335 (13.08%)</div></div>
                        </div>
                        <div class="col-sm-4">
                            <div class="mb-15" id="chart2" style="min-width: 200px; height: 300px; -webkit-tap-highlight-color: transparent; user-select: none; position: relative; background: rgb(243, 243, 243);" _echarts_instance_="ec_1491803150699"><div style="position: relative; overflow: hidden; width: 517px; height: 300px; padding: 0px; margin: 0px; border-width: 0px; cursor: default;"><canvas width="517" height="300" data-zr-dom-id="zr_0" style="position: absolute; left: 0px; top: 0px; width: 517px; height: 300px; user-select: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); padding: 0px; margin: 0px; border-width: 0px;"></canvas></div><div></div></div>
                        </div>
                        <div class="col-sm-4">
                            <div class="mb-15" id="chart3" style="min-width: 200px; height: 300px; -webkit-tap-highlight-color: transparent; user-select: none; position: relative; background: rgb(243, 243, 243);" _echarts_instance_="ec_1491803150700"><div style="position: relative; overflow: hidden; width: 517px; height: 300px; padding: 0px; margin: 0px; border-width: 0px;"><canvas width="517" height="300" data-zr-dom-id="zr_0" style="position: absolute; left: 0px; top: 0px; width: 517px; height: 300px; user-select: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); padding: 0px; margin: 0px; border-width: 0px;"></canvas></div><div></div></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-list-check">
                            <thead class="lte-table-thead">
                            <tr>
                                <th width="50">序号</th>
                                <th>名称
                                    <div class="lte-table-column-sorter">
                                          <span class="lte-table-column-sorter-up on" title="↑"> <i class="fa fa-caret-up"></i>
                                          </span>
                                        <span class="lte-table-column-sorter-down off" title="↓"> <i class="fa fa-caret-down"></i>
                                          </span>
                                    </div>
                                    <div class="lte-filterbox">
                                        <i title="Filter Menu" class="fa fa-filter lte-dropdown-trigger ml-0 c-blue"></i>
                                        <div class="lte-table-filter-dropdown none">
                                            <ul class="lte-dropdown-menu lte-dropdown-menu-vertical  lte-dropdown-menu-root" role="menu" aria-activedescendant="" tabindex="0">
                                                <li class="lte-dropdown-menu-item" role="menuitem" aria-selected="false">
                                                    <label class="lte-checkbox-wrapper">
                                                        <input type="checkbox" class="lte-checkbox-input" value="on">
                                                    </label>
                                                    <span>Joe</span>
                                                </li>
                                                <li class="lte-dropdown-menu-item" role="menuitem" aria-selected="false">
                                                    <label class="lte-checkbox-wrapper">
                                                        <span class="lte-checkbox">
                                                          <input type="checkbox" class="lte-checkbox-input" value="on">
                                                          <span class="lte-checkbox-inner"></span>
                                                        </span>
                                                    </label>
                                                    <span>Jim</span>
                                                </li>
                                            </ul>
                                            <div class="lte-table-filter-dropdown-btns">
                                                <a class="lte-table-filter-dropdown-link confirm">OK</a>
                                                <a class="lte-table-filter-dropdown-link clean">Reset</a>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th>线索总数</th>
                                <th>跟进中</th>
                                <th>已转化</th>
                                <th>无效线索</th>
                                <th>有效率</th>
                            </tr>
                            </thead>
                            <tbody id="tbody">
                            <tr>
                                <td>1</td>
                                <td>南京区</td>
                                <td>566</td>
                                <td>121</td>
                                <td>45</td>
                                <td>12</td>
                                <td>5.67%</td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>江苏区</td>
                                <td>566</td>
                                <td>121</td>
                                <td>45</td>
                                <td>12</td>
                                <td>5.67%</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="panel  border1">
        <div class="panel-heading pdb-0"><strong>不同渠道线索有效率对比</strong></div>
        <div class="panel-body">
            <div class="form-inline">
                <div class="form-group mb-15 sm-mr-15">
                    <label class="control-label col-sm-4 t-r">区域&amp;门店：</label>
                    <select id="info_owner_id_2" name="info_owner_id"  class="form-control">
                        <?php foreach ($children as $item){?>
                            <option value="<?php $item['id']?>"><?php echo $item['name']?></option>
                        <?php }?>
                    </select>
                </div>
                <div class="form-group mb-15">
                    <label class="control-label col-sm-4 t-r">区域&amp;门店：</label>
                    <div class="timebox">
                        <input type="text" class="form-control" id="datetime1"> <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                    </div>
                </div>
            </div>
            <div class="row pdt-15">
                <div class="mb-15" id="chart4" style="min-width: 200px; height: 400px; -webkit-tap-highlight-color: transparent; user-select: none; position: relative; background: transparent;" _echarts_instance_="ec_1491803150701"><div style="position: relative; overflow: hidden; width: 1641px; height: 400px; padding: 0px; margin: 0px; border-width: 0px; cursor: default;"><canvas width="1641" height="400" data-zr-dom-id="zr_0" style="position: absolute; left: 0px; top: 0px; width: 1641px; height: 400px; user-select: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); padding: 0px; margin: 0px; border-width: 0px;"></canvas></div><div style="position: absolute; display: block; border-style: solid; white-space: nowrap; z-index: 9999999; transition: left 0.4s cubic-bezier(0.23, 1, 0.32, 1), top 0.4s cubic-bezier(0.23, 1, 0.32, 1); background-color: rgba(0, 0, 0, 0.6); border-width: 0px; border-color: rgb(51, 51, 51); border-radius: 4px; color: rgb(255, 255, 255); font-style: normal; font-variant: normal; font-weight: normal; font-stretch: normal; font-size: 14px; font-family: &quot;Microsoft YaHei&quot;; line-height: 21px; padding: 5px; left: 205.271px; top: 329px;">Mon<br><span style="display:inline-block;margin-right:5px;border-radius:10px;width:9px;height:9px;background-color:#3398DB"></span>直接访问 : 10</div></div>
            </div>
        </div>
    </div>
    <!-- /.row -->
</section>
