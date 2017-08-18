<!DOCTYPE html>
<html lang="en" class="pixel-ratio-1"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Cache-Control" content="no-cache">
    <meta http-equiv="Expires" content="-1">

    <script type="text/javascript" src="/js/info/jquery-2.1.4.js" charset="utf-8"></script>


</head>

<body>

</body></html>
<script type="application/javascript">

    var clue_id = '<?php echo $_GET['clue_id'];?>';
    var salesman_id = '<?php echo $_GET['salesman_id'];?>';
    var url = "/thirdpartyapi/che-order/the-car";
    $.post(url,{'clue_id':clue_id,'salesman_id':salesman_id},function (data) {
     if(data.statusCode == 1){
         console.log(data.data)
        window.location.href=data.data;
     }else {
         document.write(data.content);
     }

    },'json')

</script>