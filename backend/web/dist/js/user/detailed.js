$(function () {
    //组织架构选择
    new Vue({
        el: '#orgSelect',
        data:function(){
            return {
                formInline:{
                    desc:[]
                },
                options1 : selectOrgJson,
                selectedOptions3 : defaultSelectArray
            }
        },
        methods: {
            handlechange_shopid:function(value){
                $("#shopid").val(value);
            }
        }
    });
        
	$('.x_id').click(function () {
		var id = $(this).attr('val');
                var oldValue = $.trim($('#shopid').val());
                var newValue = (oldValue == "" ? id : oldValue + ',' + id);
		$('#shopid').val( newValue );
		var level = parseInt(cengji);
		if (level < 30){
                    $('#form3').submit();
		}
	})
	//详情加载
	$('.info').click(function () {
		var url = $(this).attr('url');
		$('#info').attr('src',url);
	})
})

