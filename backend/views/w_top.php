<?php
    $this->registerJsFile('/dist/js/data_dictionary.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);
    $this->registerJsFile('/assets/js/checkform.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);
    $this->registerJsFile('/dist/js/layer.js', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);
    $this->registerCssFile('/css/layer.css', [
        'depends'=> ['backend\assets\AdminLteAsset']
    ]);
?>