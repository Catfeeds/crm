<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/3/6
 * Time: 16:38
 */

namespace frontend\modules\sales\controllers;


use yii\log\Logger;
use yii\rest\Controller;
use yii\data\Pagination;

/**
 * 基础控制器
 * Class BaseController
 * @package frontend\modules\v1\controllers
 */
class BaseController extends Controller
{
    /**
     * 定义使用的版本号
     * @var string
     */
    protected $version = 'v1.0';

    /**
     * 定义请求的参数
     * @var null
     */
    protected $mixRequest = [];


    /**
     * 缓存用户基本信息
     * @var array 
     */
    public $userinfo = [];
    /**
     * 获取公共数据
     * @return mixed
     */
    public function getPData()
    {
        return json_decode(\Yii::$app->request->post('p'), true);
    }

    /**
     * 获取公共数据
     * @return mixed
     */
    public function getRData()
    {
        return json_decode(\Yii::$app->request->post('r'), true);
    }


    /**
     * 格式化数据
     * @var string
     */
    public $serializer = 'common\server\Serializer';

    /**
     * 返回错误
     * @param int $code
     * @param  $message
     * @return array
     */
    public function paramError($code = 4000, $message = '请求参数错误')
    {
        \Yii::$app->params['code'] = $code;
        \Yii::$app->params['message'] = $message;
        return [];
    }

    /**
     * 记录请求参数
     *
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
//        $json = json_encode(['r' => $this->getRData(), 'p' => $this->getPData()],320);
//        $file = fopen(\Yii::getAlias('@frontend/runtime/logs/api.log'),"w+");
//        fwrite($file, $json);
//        fclose($file);
        if(YII_ENV_DEV) {
            if (\Yii::$app->request->isPost) {
                \Yii::getLogger()->log(PHP_EOL.'POST：'.json_encode(\Yii::$app->request->post()), Logger::LEVEL_ERROR);
                if($_FILES) {
                    \Yii::getLogger()->log(PHP_EOL . 'FILES：' . json_encode($_FILES), Logger::LEVEL_ERROR);
                }
            } else {
                \Yii::getLogger()->log(json_encode(\Yii::$app->request->get()), Logger::LEVEL_ERROR);
            }
        }

        $rtn = parent::beforeAction($action);
        if($rtn)//登录校验成功的，获取登录信息初始化到成员变量userinfo中
        {
            $user = \Yii::$app->user->identity;
            if($user)
            {
                $arrCache = \Yii::$app->cache->get(md5($user->access_token));
                if(is_array($arrCache))
                {
                    $this->userinfo = [
                        'shop_id' => $arrCache['shop_id'],
                        'role_id' => $arrCache['role_id'],
                    ];
                }
            }
        }
        return $rtn;
    }

    /**
     * 打印
     * @param $res 数据
     */
    public function dump($res) {
        echo "<pre>";
        print_r($res);
        exit;
    }

    /**
     * 获取分页信息
     * @param  array $params   请求分页的配置参数
     * @param  int   $intTotal 数据总条数
     * @return array
     */
    protected function getPage($params, $intTotal)
    {
        // 处理分页信息
        $pagination = new Pagination(['totalCount' => $intTotal]);
        $pagination->setPageSize(empty($params['perPage']) ? 10 : $params['perPage']);
        $pagination->setPage(empty($params['currentPage']) ? 0 : $params['currentPage'] - 1);
        return [
            // 分页信息
            'pages' => [
                'totalCount' => intval($pagination->totalCount),
                'pageCount' => intval($pagination->getPageCount()),
                'currentPage' => intval($pagination->getPage() + 1),
                'perPage' => intval($pagination->getPageSize()),
            ],

            'offset' => $pagination->offset,
            'limit' => $pagination->limit,
        ];
    }
}