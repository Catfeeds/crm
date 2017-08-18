<?php
return [

    //权限相关参数 - 本文件配置线上的，测试环境和开发环境 自己在 params-local.php文件中做覆盖
    'quan_xian' => [
        'auth_sso_url' => 'http://test.sso-server.checheng.net',//单点登录地址
        'auth_broker_id' => '1564008852796463',//项目appID
        'auth_broker_secret' => 'bc215b07eadcf943b132cd9705e221ed',//配置的项目 Secret
        'auth_sso_login_url' => 'http://test.sso.checheng.net',//跳转的单点登录页面
        'auth_sso_modify_password_url' => 'http://test.sso.checheng.net/profile/password.php',//单点登录修改密码url
        'auth_api_url' => 'http://test.qx-api.checheng.net/api',//拉取组织和人员以及角色信息的接口地址
        'auth_token' => 'af42c8786178a2460e6a63653aef9d2a',//token
    ],
    //进销存测试环境
    'jxc' => [
        'b_token' => '09b1df81025a40e6a1652c21ea11df62',//crm请求进销存token
        'z_token' => '4a16c45425ad4e01ad1d3e86d8d3944f',//进销存请求crm token
        'url' => 'http://test.erp.che.com/'//进销存测试地址
    ],
    //车城电商地址
    'che_com' => [
        'cheApi' => 'http://test.api.che.com/',//接口地址
        'jumpurl' => 'http://test.order.crm.che.com/#/'
    ],
    'member' => [//会员中心地址
        'url' => 'http://test.uc.che.com/',
        'tokenKey'=> 'dRpg6fPFdasfdasfdasfadsCep3sOL-4qvtZ'
    ],
    'apiAddrUrl' => [
        'url' => 'test.crm.che.com'
    ],
    /**
     * OA地址
     * 测试http://192.168.1.128:8010/
     * 开发http://192.168.1.128:8011/
     * 正式http://oa.admin.che.com/
     */
    'oa' => [
        'url' => 'http://192.168.1.128:8011/'
    ],

    // 电商下单 - 默认分配的顾问信息
    'arrConsultant' => [
        'id' => 450,
        'name' => '彭青'
    ],

    // 电商下单 - 默认分配的门店信息
    'arrShop' => [
        'id' => 228,
        'name' => 'VIP店'
    ],
];