function img(json,count) {

    layer.photos({
        photos : '#layer-photos-demo' //格式见API文档手册页
        ,anim : count //0-6的选择，指定弹出图片动画类型，默认随机
    });
}