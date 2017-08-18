var config = [
	{
		"api_name":"getClueList",//接口名
		"api_explain":"意向客户",
		"data":{
			"type":{
				"value":"list_top", "require":true,
				"placeholder":"list_top-王牌.list_hot_search-热门,list_danji-单机,list_fast_up-飙升,list_zjbb-装机"
			},//value:默认值，require为true表示必填，false为不必填，placeholder为输入提示
			"page":{"value":"1", "placeholder":"分页值"},
		}//接口需要的数据，值为默认值
	}

];
