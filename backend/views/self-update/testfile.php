<?php
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
        <h1 class="page-title">APP版本管理</h1>
    </section>

<form class="form-horizontal" action="" id="form1" enctype="multipart/form-data" method="post">
    <input name="_csrf-backend" type="hidden" id="_csrf" value="<?php echo Yii::$app->request->csrfToken ?>">
    <input type="file" name="file">
    <input type="submit" value="提交">
</form>
    <!-- Main content -->

    <!-- /.content -->


<!--新建编辑弹出层 start-->

<!--新建编辑弹出层 start-->

<script>

</script>
