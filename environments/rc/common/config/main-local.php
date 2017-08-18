<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=192.168.1.15;dbname=crm',
            'username' => 'root',
            'password' => 'mysql',
            'charset' => 'utf8',
            'tablePrefix' => 'crm_',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            'useFileTransport' => true,
        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
            'keyPrefix' => 'CRM:'
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '192.168.1.87',
            'port' => 6379,
            'password' => 'redis_6379',
            'database' => '9',
        ],
    ],
];





