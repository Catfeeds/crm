<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'chelog'],
    'controllerNamespace' => 'frontend\controllers',
    'modules' => [
        'sales' => 'frontend\modules\sales\Module',
        'glsb' => 'frontend\modules\glsb\Module',
        'thirdpartyapi' => 'frontend\modules\thirdpartyapi\Module'
    ],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-frontend',
        ],
        'chelog' => [//初始化车城日志插件
            'class' => 'common\che\Logs'
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                if (Yii::$app->controller->id != 'info') {
                    $response = $event->sender;
                    if ($response->statusCode == 500) {
                        $response->data = [
                            'code' => $response->statusCode,
                            'message' => $response->data['message'],//助手
                            'msg' => $response->data['message'],//管理速报
                            'data' => null,
                        ];
                    }
                }
            },

        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-frontend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error'],
                    'logVars' => []
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/new-error',
        ],
        'urlManager' => require(__DIR__.'/_urlManager.php'),
    ],
    'params' => $params,
];
