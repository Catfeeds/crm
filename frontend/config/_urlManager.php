<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/3/7
 * Time: 9:40
 */

return [
    'enablePrettyUrl' => true,
    'enableStrictParsing' => true,
    'showScriptName' => false,
    'rules' => [
        "<module:\w+>/<controller:\w+>/<action:\w+>/<id:\d+>"=>"<module>/<controller>/<action>",
        "<controller:\w+>/<action:\w+>/<id:\d+>"=>"<controller>/<action>",
        "<controller:\w+>/<action:\w+>"=>"<controller>/<action>",

        'GET /' => 'site/index',
        'GET /get-json-data-for-select/index' => 'get-json-data-for-select/index',//h5页面下拉单数据提供
        'GET sales/data-dictionary' => 'sales/default/data-dictionary',
        'GET sales/car-list' => 'sales/default/car-list',
        'GET sales/get-shop-name' => 'sales/default/get-shop-name',

        //sales
        'POST sales/notify-password' => 'sales/default/notify-password',
        'POST sales/validate-code' => 'sales/default/validate-code',
        'POST sales/login' => 'sales/login/index',
        'POST sales/user/change-password' => 'sales/user/change-password',

        'POST sales/clue/add' => 'sales/clue/add',
        'POST sales/clue/update' => 'sales/clue/update',
        'POST sales/clue/view' => 'sales/clue/view',
        'POST sales/clue/is-failed' => 'sales/clue/is-failed',
        'POST sales/clue/update-status' => 'sales/clue/update-status',
        'POST sales/clue/list' => 'sales/clue/list',
        'POST sales/clue/to-intent' => 'sales/clue/to-intent',
        'POST sales/clue/call' => 'sales/clue/call',
        'POST sales/clue/unassign-list' => 'sales/clue/unassign-list',
        'POST sales/clue/clue-claim' => 'sales/clue/clue-claim',

        'POST sales/customer' => 'sales/customer/index',
        'POST sales/customer/add' => 'sales/customer/add',
        'POST sales/customer/check-phone' => 'sales/customer/check-phone',
        'POST sales/customer/get-phone' => 'sales/customer/get-phone',
        'POST sales/customer/add-star' => 'sales/customer/add-star',
        'POST sales/customer/to-active' => 'sales/customer/to-active',
        'POST sales/customer/history' => 'sales/customer/history',
        'POST sales/customer/sms-list' => 'sales/customer/sms-list',
        'POST sales/customer/is-intention-customer' => 'sales/customer/is-intention-customer',

        'POST sales/task' => 'sales/task/index',
        'POST sales/task/add' => 'sales/task/add',
        'POST sales/task/view' => 'sales/task/view',
        'POST sales/task/cancel' => 'sales/task/cancel',
        'POST sales/task/cancel-task' => 'sales/task/cancel-task',
        'POST sales/task/update-phone-task' => 'sales/task/update-phone-task',

        'POST sales/talk/add' => 'sales/talk/add',
        'POST sales/talk/add-call' => 'sales/talk/add-call',

        'POST sales/money/income' => 'sales/money/income',
        'POST sales/money/expenditure' => 'sales/money/expenditure',
        'POST sales/money/cash-apply' => 'sales/money/cash-apply',
        'POST sales/board' => 'sales/board/index',
        'POST sales/board/clue' => 'sales/board/clue',
        'POST sales/board/task-phone' => 'sales/board/task-phone',
        'POST sales/board/talk-log' => 'sales/board/talk-log',
        'POST sales/board/intent' => 'sales/board/intent',
        'POST sales/board/give-car' => 'sales/board/give-car',


        'POST sales/cluesearch/index' => 'sales/clue-search/index',
        'POST sales/clue/getinfo' => 'sales/clue/get-info',
        'POST sales/task/getinfo' => 'sales/task/get-info',
        'POST sales/talk/gettalk' => 'sales/talk/get-talk',
        'POST sales/clue/thecar' => 'sales/clue/the-car',
        'POST sales/order/updatesave' => 'sales/order/update-save',
        'POST sales/order/getinfo' => 'sales/order/get-info',
        'POST sales/order/check-order' => 'sales/order/check-order',
        'POST sales/push/push-msg'   => 'sales/push/push-msg',

        //oa接口
        'POST sales/oa/jump'   => 'sales/oa/jump',

//        'POST sales/announcement/list'   => 'glsb/announcement-inbox/list',
        'POST sales/announcement/list'   => 'sales/announcement-inbox/list',
        'POST sales/announcement/add-read'   => 'sales/announcement-inbox/add-read',
        'POST sales/announcement/check-new'   => 'sales/announcement-inbox/check-new',
        'POST sales/self-update/check-version'         => 'sales/self-update/index',

        'POST sales/save-push-token'     => 'sales/save-push-token/index',
        'POST sales/notice/list'   => 'sales/notice-inbox/list',
        'POST sales/feedback/index'   => 'sales/feedback/index',
        'POST sales/pen-ding-order/index'   => 'sales/pen-ding-order/index',
        'POST sales/pen-ding-order/sub-order'   => 'sales/pen-ding-order/sub-order',
        'POST sales/pen-ding-order/info'   => 'sales/pen-ding-order/info',
        'POST sales/pen-ding-order/apply'   => 'sales/pen-ding-order/apply',
        'POST thirdpartyapi/files/file-save'   => 'thirdpartyapi/files/file-save',
        'POST sales/pen-ding-order/status'   => 'sales/pen-ding-order/status',

        'POST sales/xszs/log'   => 'sales/xszs/log',

        //分享
        'POST sales/share/update-share'   => 'sales/share/update-share',
        'POST sales/share/get-guid'   => 'sales/share/get-guid',

        //电商接口
        'POST sales/online-retailers/brands'   => 'sales/online-retailers/brands',
        'POST sales/online-retailers/series'   => 'sales/online-retailers/series',
        'POST sales/online-retailers/cars'   => 'sales/online-retailers/cars',

        // liujx add 账单信息 2017-06-19 start :
        'POST sales/member/bill' => 'sales/member/bill',
        // end;

        // edited liujx add 公海信息管理 2017-06-28 start :

        // 公海线索 - 线索列表
        'POST sales/gonghai/lists' => 'sales/gonghai/lists',
        // 公海线索 - 顾问跟进记录
        'POST sales/gonghai/logs' => 'sales/gonghai/logs',
        // 公海线索 - 进入公海原因
        'POST sales/gonghai/reasons' => 'sales/gonghai/reasons',
        // 公海线索 - 顾问认领
        'POST sales/gonghai/claim' => 'sales/gonghai/claim',

        // end

        // edited lijx add 接待提车客户 2017-07-25 start :
        'POST sales/mention-task/lists' => 'sales/mention-task/lists', // 列表
        'POST sales/mention-task/claim' => 'sales/mention-task/claim', // 认领
        // end

        'GET glsb/get-select-roles'    => 'glsb/default/get-select-roles',//管理速报 - 登录界面 角色选择数据提供接口
        'POST glsb/modify-password'     => 'glsb/reset-pwd/modify-password',
        'POST glsb/get-phone-code'      => 'glsb/reset-pwd/get-phone-code',
        'POST glsb/reset-password'      => 'glsb/reset-pwd/reset-pwd',
        'POST glsb/login'               => 'glsb/login/index',
        'POST glsb/save-push-token'     => 'glsb/save-push-token/index',
        'POST glsb/save-push-token/jpush'     => 'glsb/save-push-token/jpush',
        'POST sales/save-push-token/jpush'     => 'sales/save-push-token/jpush',

//        'POST glsb/modify-password'     => 'glsb/login/modify-password',
//        'POST glsb/reset-password'      => 'glsb/reset-pwd/reset-pwd',
        'POST glsb/user/update'         => 'glsb/user/update',
        'POST glsb/user/shop-salesman'  => 'glsb/user/shop-salesman',
        'POST glsb/talk/done-list'      => 'glsb/talk/done-list',
        'POST glsb/task/undo-list'      => 'glsb/task/undo-list',
        'POST glsb/clue/unconnect-list' => 'glsb/clue/unconnect-list',
        'POST glsb/clue/active'         => 'glsb/clue/active',
        'POST glsb/clue/unassign-list'  => 'glsb/clue/unassign-list',
        'POST glsb/notification-count'  => 'glsb/clue/notification-count',
        'POST glsb/clue/assign'         => 'glsb/clue/assign',
        'POST glsb/clue/overdue-list'         => 'glsb/clue/overdue-list',
        'POST glsb/clue/overdue-remind'         => 'glsb/clue/overdue-remind',
        'POST glsb/clue/unconnect-detail'   => 'glsb/clue/unconnect-detail',
        'POST glsb/clue/unassign-detail'    => 'glsb/clue/unassign-detail',
        'POST glsb/getdata/checkphonenum'   => 'glsb/get-data/check-phonenum',
        'POST glsb/announcement/list'   => 'glsb/announcement-inbox/list',

        'POST glsb/announcement/add-read'   => 'glsb/announcement-inbox/add-read',
        'POST glsb/announcement/check-new'   => 'glsb/announcement-inbox/check-new',

        'POST glsb/notice/list'   => 'glsb/notice-inbox/list',
        'POST glsb/feedback/index'   => 'glsb/feedback/index',

        'POST glsb/self-update/check-version'         => 'glsb/self-update/index',
        'POST glsb/data-statistics/overview'         => 'glsb/data-statistics/overview',
        'POST glsb/data-statistics/clue-list'         => 'glsb/data-statistics/clue-list',
        'POST glsb/data-statistics/clue-list-salesman'         => 'glsb/data-statistics/clue-list-of-salesman',
        'POST glsb/data-statistics/phone-task-list'         => 'glsb/data-statistics/phone-task-list',
        'POST glsb/data-statistics/phone-task-list-salesman'         => 'glsb/data-statistics/phone-task-list-of-salesman',
        'POST glsb/data-statistics/intention-customer-list'         => 'glsb/data-statistics/intention-customer-list',
        'POST glsb/data-statistics/intention-customer-list-salesman'         => 'glsb/data-statistics/intention-customer-list-of-salesman',
        'POST glsb/data-statistics/talk-list'         => 'glsb/data-statistics/talk-list',
        'POST glsb/data-statistics/talk-list-of-salesman'         => 'glsb/data-statistics/talk-list-of-salesman',
        'POST glsb/data-statistics/undeliver-car-list'         => 'glsb/data-statistics/undeliver-car-list',
        'POST glsb/data-statistics/undeliver-car-list-shop'         => 'glsb/data-statistics/undeliver-car-list-of-shop',
        'POST glsb/data-statistics/deliver-car-list'         => 'glsb/data-statistics/deliver-car-list',
        'POST glsb/data-statistics/deliver-car-list-shop'         => 'glsb/data-statistics/deliver-car-list-of-shop',
        'POST glsb/data-statistics/fail-clue-list'         => 'glsb/data-statistics/fail-clue-list',
        'POST glsb/data-statistics/fail-clue-list-shop'         => 'glsb/data-statistics/fail-clue-list-of-shop',
        'POST glsb/data-statistics/clue-detail'         => 'glsb/data-statistics/clue-detail',

        // eidted by liujx 2017-07-26 添加提车任务记录 start:
        'POST glsb/data-statistics/mention-car-list'         => 'glsb/data-statistics/mention-car-list',
        'POST glsb/data-statistics/mention-car-list-salesman'         => 'glsb/data-statistics/mention-car-list-salesman',
        // end

        'POST glsb/data-analysis/count-ranking'         => 'glsb/data-analysis/count-ranking',
        'POST glsb/data-analysis/rate-ranking'         => 'glsb/data-analysis/rate-ranking',
        'POST glsb/data-analysis/overview'         => 'glsb/data-analysis/overview',


        'POST glsb/data-statistics/talk-record'         => 'glsb/data-statistics/talk-record',
        'POST glsb/data-statistics/task-record'         => 'glsb/data-statistics/task-record',
        'POST glsb/data-statistics/customer-detail'         => 'glsb/data-statistics/customer-detail',

        'POST glsb/deal-trend/get-count-data'         => 'glsb/deal-trend/get-count-data',
        'POST glsb/deal-trend/get-rate-data'         => 'glsb/deal-trend/get-rate-data',

        'POST glsb/customer-analysis/get-intention-fail-customer'         => 'glsb/customer-analysis/get-intention-fail-customer',
        'POST glsb/customer-analysis/get-order-fail-customer'         => 'glsb/customer-analysis/get-order-fail-customer',
        'POST glsb/customer-analysis/intention-level'         => 'glsb/customer-analysis/intention-level',
        'POST glsb/customer-analysis/input-type'         => 'glsb/customer-analysis/input-type',
        'POST glsb/customer-analysis/conversion-funnel'         => 'glsb/customer-analysis/conversion-funnel',

        //与车城线上的交互的相关接口
        'POST thirdpartyapi/che/create-order-has-clue' => 'thirdpartyapi/che/create-order-has-clue',
        'POST thirdpartyapi/che/update-order' => 'thirdpartyapi/che/update-order',
        'POST thirdpartyapi/che/new-order' => 'thirdpartyapi/che/new-order',
        'POST thirdpartyapi/user/add-user-info' => 'thirdpartyapi/user/add-user-info',
        'POST thirdpartyapi/che-order/the-car' => 'thirdpartyapi/che-order/the-car',
        // crm 客服下单处理接口
        'POST thirdpartyapi/order/success' => 'thirdpartyapi/order/success',
        'POST thirdpartyapi/order/fail' => 'thirdpartyapi/order/fail',
        'POST thirdpartyapi/order/update' => 'thirdpartyapi/order/update',
        
        // edited by liujx 2017-6-22 给用户中心通过客户手机号 获取 线索信息 start:
        'GET thirdpartyapi/clue/get-clue' => 'thirdpartyapi/clue/get-clue',
        // end

        //oa接口
        'POST glsb/oa/jump'   => 'glsb/oa/jump',

        //会员
        'POST thirdpartyapi/member/registers' => 'thirdpartyapi/member/registers',
        'POST thirdpartyapi/member/check-member' => 'thirdpartyapi/member/check-member',

        //与组织架构和权限模块交互的相关的接口
        'POST thirdpartyapi/user-center/exchange-notify' => 'thirdpartyapi/user-center/exchange-notify',


        'POST sales/task/update-tasks' => 'sales/task/update-tasks',
        
        //重要的通讯录 - 管理速报版本
        'GET sales/default/important-phone-list' => 'sales/default/important-phone-list',
        //重要的通讯录 - 管理速报版本
        'GET glsb/default/important-phone-list' => 'glsb/default/important-phone-list',
    ],
];
