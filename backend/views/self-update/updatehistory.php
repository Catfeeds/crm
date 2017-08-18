<?php
use yii\widgets\LinkPager;
$this->registerJsFile('/dist/plugins/daterangepicker/moment.min.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/daterangepicker/daterangepicker.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
//$this->registerJsFile('/dist/plugins/echarts.min.js', [
//    'depends' => ['backend\assets\AdminLteAsset']
//]);
//$this->registerJsFile('/dist/js/appSelfUpdate.js', [
//    'depends'=> ['backend\assets\AdminLteAsset']
//]);
//$this->registerJsFile('/dist/plugins/daterangepicker/bootstrap-datepicker.js', [
//    'depends' => ['backend\assets\AdminLteAsset']
//]);
//$this->registerJsFile('/dist/plugins/daterangepicker/bootstrap-datepicker_002.js', [
//    'depends' => ['backend\assets\AdminLteAsset']
//]);
//$this->registerCssFile('/dist/css/datepicker3.css', [
//    'depends' => ['backend\assets\AdminLteAsset']
//]);


$this->registerCssFile('/dist/plugins/tokenfield/jquery-ui.css', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerCssFile('/dist/plugins/tokenfield/bootstrap-tokenfield.css', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerCssFile('/dist/plugins/tokenfield/tokenfield-typeahead.css', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/tokenfield/jquery-ui.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/tokenfield/typeahead.bundle.min.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/plugins/tokenfield/bootstrap-tokenfield.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
$this->registerJsFile('/dist/js/appSelfUpdate.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);
?>
<section class="content-header">
    <h1 class="page-title"><a href="/self-update/index">&lt;返回</a>&nbsp;APP版本更新历史</h1>
</section>

    <!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-sm-6">
            <h4 class="t-c mb-25">Android版本更新历史</h4>
            <ul class="timeline marginauto custom-timeline" id="android_id">
                <?php foreach ($android_list as $item){?>

                    <li><i class="fa bg-blue"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fa fa-clock-o"></i><?php echo date('Y-m-d',$item['create_time'])?></span>
                            <h3 class="timeline-header"><?php echo $item['versionName']?></h3>
                            <div class="timeline-body">
                                <?php echo str_replace("\n", "<br>", $item['content'])?>
                            </div>
                        </div>
                    </li>
                <?php }?>
            </ul>
            <input type="button" class="btn btn-primary getMore" onclick="appSelfUpdate.moreList('<?php echo $app_name?>')" value="加载更多">
        </div>
        <div class="col-sm-6">
            <h4 class="t-c mb-25">ios版本更新历史</h4>
            <ul class="timeline marginauto custom-timeline" id="ios_id">
                <?php foreach ($ios_list as $item){?>

                    <li><i class="fa bg-blue"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fa fa-clock-o"></i><?php echo date('Y-m-d',$item['create_time'])?></span>
                            <h3 class="timeline-header"><?php echo $item['versionName']?></h3>
                            <div class="timeline-body">
                                <?php echo str_replace("\n", "<br>", $item['content'])?>
                            </div>
                        </div>
                    </li>
                <?php }?>
            </ul>
            <input class="btn btn-primary getMore" type="button" onclick="appSelfUpdate.moreList('<?php echo $app_name?>')" value="加载更多">
        </div>
    </div>
    <!-- /.row -->
</section>
    <!-- /.content -->


<script>

</script>
