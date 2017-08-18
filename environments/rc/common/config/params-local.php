<?php
return [

    //权限相关参数 - 本文件配置线上的，测试环境和开发环境 自己在 params-local.php文件中做覆盖
    'quan_xian' => [
        'auth_sso_url' => 'http://in.sso-server.checheng.net',//单点登录地址
        'auth_broker_id' => '1564438672652512',//项目appID
        'auth_broker_secret' => '4a5fc71ce117d47c998a8179c14f3c17',//配置的项目 Secret
        'auth_sso_login_url' => 'http://in.sso.checheng.net',//跳转的单点登录页面
        'auth_sso_modify_password_url' => 'http://in.sso.checheng.net/profile/password.php',//
        'auth_api_url' => 'http://in.qx-api.checheng.net/api',//拉取组织和人员以及角色信息的接口地址
        'auth_token' => '61d08b77da5e0fef8e433c608b059820',//token
    ],
    //进销存测试环境
    'jxc' => [
        'b_token' => '09b1df81025a40e6a1652c21ea11df62',//crm请求进销存token
        'z_token' => '4a16c45425ad4e01ad1d3e86d8d3944f',//进销存请求crm token
        'url' => 'http://rc.erp.admin.che.com/' //进销存测试地址
    ],
    //车城电商地址
    'che_com' => [
        'cheApi' => 'http://rc.api.che.com/',
        'jumpurl' => 'http://rc.order.crm.che.com/#/'
    ],
    'member' => [//会员中心地址
        'url' => 'http://rc.user.api.che.com/',
        'tokenKey'=> 'dRpg6fPFdasfdasfdasfadsCep3sOL-4qvtZ'
    ],
    'apiAddrUrl' => [
        'url' => 'rc.crm.api.che.com'
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
];
