<?php
return [
    'language' => 'zh-CN',
    'timeZone' => 'Asia/Shanghai',
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\DbManager'
        ],
        'formatter' => [
            'dateFormat' => 'yyyy-MM-dd',
            'datetimeFormat' => 'yyyy-MM-dd HH:mm:ss',
        ],
////        //自定义报错页面 - 各自模块定义 - 在此处定义的话console模块报错
//        'errorHandler' => [
//            'errorAction' => 'site/error',
//        ],
//        'db' => [//线上数据库
//            'class' => 'yii\db\Connection',
//            'dsn' => 'mysql:host=rm-uf6pi85t4azoit8ov.mysql.rds.aliyuncs.com;dbname=crm',
//            'username' => 'crm',
//            'password' => '8mys9xI0en',
//            'charset' => 'utf8',
//            'tablePrefix' => 'crm_',
//        ],
//        'cache' => [
//            'class' => 'yii\redis\Cache',
//            'keyPrefix' => 'CRM:'
//        ],
//        'redis' => [//线上redis
//            'class' => 'yii\redis\Connection',
//            'hostname' => 'f3b8186b7a2d4d96.m.cnsha.kvstore.aliyuncs.com',
//            'port' => 6379,
//            'password' => '6DMbETBRxe',
//            'database' => 'DB50',
//        ],

    ],
];
