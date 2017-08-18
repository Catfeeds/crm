//渠道来源
if (input_types != '') {

	var dataObj=eval("("+input_types+")");//转换为json对象
	var count = dataObj.length
	for (var i = 0 ; i < count; i++) {
		$(":checkbox[val='i"+dataObj[i]+"']").prop("checked",true);
	}

}

//创建方式
if (create_types != '') {

	var dataObj=eval("("+create_types+")");//转换为json对象
	var count = dataObj.length
	for (var i = 0 ; i < count; i++) {
		$(":checkbox[val='c"+dataObj[i]+"']").prop("checked",true);
	}

}
//信息来源
if (sourves != '') {

	var dataObj=eval("("+sourves+")");//转换为json对象
	var count = dataObj.length
	for (var i = 0 ; i < count; i++) {
		$(":checkbox[val='s"+dataObj[i]+"']").prop("checked",true);
	}

}
//顾问查询
$('.sub').click(function () {
	$('#form2').submit();
})
