<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',

            'dsn' => 'mysql:host=192.168.1.22;dbname=crm',
            'username' => 'crm',
            'password' => 'crm!2#',

//            'dsn' => 'mysql:host=localhost;dbname=crm',
//            'username' => 'che',
//            'password' => '',

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
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '192.168.1.16',
            'port' => 6379
        ],
    ],
];