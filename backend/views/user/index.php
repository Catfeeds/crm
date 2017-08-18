<?php
$this->registerJsFile('/assets/js/user/index.js', [
    'depends'=> ['backend\assets\AdminLteAsset']
]);

?>
<link href="/dist/css/resetpwd.css" rel="stylesheet">
<div class="resetpwd-box">
    <form id="form1"  action="save" method="post">
        <div class="form-group has-feedback">
            <input type="password" id="password" class="form-control" name="password" placeholder="原密码">
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        </div>
        <div class="form-group has-feedback">
            <input type="password" id="new_password" class="form-control" name="new_password"  placeholder="新密码">
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        </div>
        <div class="form-group has-feedback">
            <input type="password" id="queren_password" class="form-control" name="password"  placeholder="确认新密码">
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        </div>
        <input type="button" id="submit" class="btn btn-primary btn-block" value="确认修改">
    </form>
</div>