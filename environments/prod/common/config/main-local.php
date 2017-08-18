<?php
return [
    'components' => [
        'db' => [//线上数据库
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=rm-uf6pi85t4azoit8ov.mysql.rds.aliyuncs.com;dbname=crm',
            'username' => 'crm',
            'password' => '8mys9xI0en',
            'charset' => 'utf8',
            'tablePrefix' => 'crm_',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
            'keyPrefix' => 'CRM:'
        ],
        'redis' => [//线上redis
            'class' => 'yii\redis\Connection',
            'hostname' => 'f3b8186b7a2d4d96.m.cnsha.kvstore.aliyuncs.com',
            'port' => 6379,
            'password' => '6DMbETBRxe',
            'database' => '50',
        ],
    ]
];