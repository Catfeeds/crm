/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function(){
    
    //手机号输入框失焦事件
    $('#loginform-username').blur(function(){
        var phone = $.trim($(this).val());//13041665260
        if (phone === '') {
            return ;
        }
        $.get('/site/get-select-roles', {"phone":phone}, function(res){
            if (res.code > 0) {
                alert(res.msg);
            } else {
                var Html = '<option value="placeholder">角色和门店选择</option>';
                for (var i in res.data) {
                    var tmp = res.data[i];
                    if(tmp.shop_list) {
                        for ( var j in tmp.shop_list) {
                            var shop = tmp.shop_list[j];
                            Html += '<option value="" roleId="' + tmp.role_id + '" shopId="' + shop.shop_id + '" >' + tmp.role_name + ' - ' + shop.shop_name + '</option>';
                        }
                    } else {
                        Html += '<option value="" roleId="' + tmp.role_id + '" shopId="0" >' + tmp.role_name+ '</option>';
                    }
                }
                $('#loginform-roleandshop').html(Html);
            }
        }, 'json');
    });
    
    //下拉框选中后赋值
    $('#loginform-roleandshop').change(function(){
        var selectOpt = $(this).find("option:selected");
        $('#loginform-shopid').val(selectOpt.attr('shopId'));
        $('#loginform-roleid').val(selectOpt.attr('roleId'));
    });
    
});