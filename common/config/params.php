<?php
//项目基本信息的定义 - 暂时未找到合适的位置，先放在这里
defined('PROD_CODE') or define('PROD_CODE', '100');//产品名称
defined('PROD_NAME') or define('PROD_NAME', 'CRM');//产品名称
defined('APP_NAME') or define('APP_NAME', 'CRM系统');//项目名称
defined('APP_ID') or define('APP_ID', '100');//项目编号
defined('APP_VERSION') or define('APP_VERSION', '1.5.1');//版本号
if(!defined('LOG_DIR'))
{
    if (strtoupper(substr(PHP_OS,0,3))==='WIN') {
        define('LOG_DIR', 'D:\log-test');//日志插件记录的日志存放处
    } else {
        define('LOG_DIR', '/data/logs/');//日志插件记录的日志存放处
    }
}
\cheframework\logs\Log::instance()->register();

return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'user.passwordResetTokenExpire' => 3600,
    //权限相关参数 - 本文件配置线上的，测试环境和开发环境 自己在 params-local.php文件中做覆盖
    'quan_xian' => [
        'auth_sso_url' => 'http://auth.admin.che.com',//单点登录地址
        'auth_broker_id' => '1564438672652512',//项目appID
        'auth_broker_secret' => '4a5fc71ce117d47c998a8179c14f3c17',//配置的项目 Secret
        'auth_sso_login_url' => 'http://admin.che.com/login.php',//跳转的单点登录页面
        'auth_sso_modify_password_url' => 'http://admin.che.com/profile/password.php',//单点登录修改密码url
        'auth_api_url' => 'http://sso.checheng.net/api',//拉取组织和人员以及角色信息的接口地址
        'auth_token' => '327ba99c95c27c62ec38494d5cffe094',//token
    ],
    'jxc' => [
        'b_token' => '09b1df81025a40e6a1652c21ea11df62',//crm请求进销存token
        'z_token' => '4a16c45425ad4e01ad1d3e86d8d3944f',//进销存请求crm token
        'url' => 'http://erp.admin.che.com/'//erp地址地址
    ],
    'che_com' => [//车城电商地址
        'cheApi' => 'http://api.che.com/',//接口地址
        'jumpurl' => 'http://order.crm.che.com/#/',
    ],
    //华为推送按照端所需解析参数 不同版本可能不同  每次去比当前版本小的最大版本对应的参数
    'huaweipush_string' => [
        'glsb' => [
            '0' => 'intent:#Intent;launchFlags=0x10000000;component=com.glsb/com.crm.report.MainActivity;S.global_action=glsb;end',
            '4' => 'intent://com.glsb/glsb#Intent;scheme=che_cheng;launchFlags=0x10000000;S.global_action=clue_allocation;end',
        ],
        'sales' => [
            '0' => 'intent://com.hua.wei.push/inform#Intent;scheme=market;launchFlags=0x10000000;S.global_action=inform;end',
            '6' => 'intent://com.xszs/xszs#Intent;scheme=che_cheng;launchFlags=0x10000000;S.global_action=inform;end',
        ],
        'clue_claim_sales' => [
            '0' => '',
            '9' => 'intent://com.xszs/xszs#Intent;scheme=che_cheng;launchFlags=0x10000000;S.global_action=store_clue;end'
        ],

        'client_order_success_sales' => [ // 客户电商下单成功
            '0' => 'intent://com.xszs/xszs#Intent;scheme=che_cheng;launchFlags=0x10000000;S.global_action=customer;B.ps=true;end',
            '6' => 'intent://com.xszs/xszs#Intent;scheme=che_cheng;launchFlags=0x10000000;S.global_action=customer;B.ps=true;end'
        ],

        'client_order_fail_sales' => [ // 客户电商下单失败
            '0' => 'intent://com.xszs/xszs#Intent;scheme=che_cheng;launchFlags=0x10000000;S.global_action=customer;B.ps=false;end',
            '6' => 'intent://com.xszs/xszs#Intent;scheme=che_cheng;launchFlags=0x10000000;S.global_action=customer;B.ps=false;end'
        ],

        // 销售助手-提车任务提醒
        'mention_task_sales' => [
            '0' => 'intent://com.xszs/xszs#Intent;scheme=che_cheng;launchFlags=0x10000000;S.global_action=mention_car;end',
            '6' => 'intent://com.xszs/xszs#Intent;scheme=che_cheng;launchFlags=0x10000000;S.global_action=mention_car;end'
        ]
    ],

    //会员中心地址
    'member' => [
        'url' => 'http://api.user.admin.che.com/',
        'tokenKey'=> 'dRpg6fPFdasfdasfdasfadsCep3sOL-4qvtZ'
    ]
    ,
    'apiAddrUrl' => [
        'url' => 'api.crm.che.com'
    ],
    /**
     * OA地址
     * 正式http://oa.admin.che.com/
     */

    'oa' => [
        'url' => 'http://oa.admin.che.com/'
    ],

    // 指定线索设为无效的几种的原因投入公海(9购车时间6个月以上, 10区域不符合、44车型未上市、)
    'arrInvalidReason' => [2, 6, 7, 9, 10, 44],

    // 指定意向战败时的几种原因投入公海(14区域销售限制、15不接受异地提车)
    'arrDefeatedReason' => [4, 13, 14, 15, 16, 17, 18, 31, 47],

    // 电商下单 - 默认分配的顾问信息
    'arrConsultant' => [
        'id' => 343,
        'name' => '彭青'
    ],

    // 电商下单 - 默认分配的门店信息
    'arrShop' => [
        'id' => 129,
        'name' => 'VIP1店'
    ],

    // 微信分享公众号配置信息 appId 和 名称 appSecret
    'weixin' => [
        'appId' => 'wx67e7d957689d27ef',
        'appSecret' => '0470be436f4e0b0a57487e747e0216f4',
    ],
    
];
