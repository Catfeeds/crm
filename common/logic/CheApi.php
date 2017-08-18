<?php

namespace common\logic;

use common\helpers\Helper;
use common\models\User;
use yii;
use yii\helpers\Json;
use common\common\PublicMethod;

/**
 * Class CheApi 车城电商接口
 * @package common\logic
 */
class CheApi
{
    /**
     * @var string 定义请求地址
     */
    private  $url = '';

    /**
     * @var array 定义请求参数
     */
    private $mixRequest = [];

    /**
     * CheApi constructor.初试化方法
     * @param string $url
     */
    public function __construct($url = '')
    {
        $this->url = $url;
        if (empty($this->url)) {
            $this->url = Yii::$app->params['che_com']['cheApi'];
        }
    }

    /**
     * 发送get 请求
     * @param string $api api接口地址
     * @param array $params 请求参数
     * @param array $options curl 其他配置
     * @return mixed
     */
    public function get($api, $params = [], $options = [])
    {
        // 处理地址和请求参数
        $url = rtrim($this->url, '/').'/'.ltrim($api, '/');
        $this->mixRequest = [
            'time' => date('Y-m-d H:i:s'),
            'url' => $url,
            'request' => $params,
        ];
        if ($params) {
            $url .= '?'.http_build_query($params);
        }

        // 发送请求处理返回结果
        $mixReturn = $this->mixRequest['response'] = PublicMethod::http_get($url, [], $options);
        if ($this->mixRequest['response']) {
            $mixReturn = Json::decode($mixReturn);
        }

        return $mixReturn;
    }

    /**
     * 通知订车和交车门店顾问信息
     * @param string $strCheOrderId 车城订单号
     * @param int $intOrderCarSalesmanId 订车顾问
     * @param int $intDeliverySalesmanId 交车顾问
     * @param int $intOrderCarShopId 订车门店
     * @param int $intDeliveryShopId 交车门店
     * @return array 返回数组
     */
    public function noticeUpdateStoreSale(
        $strCheOrderId,
        $intOrderCarSalesmanId,
        $intDeliverySalesmanId,
        $intOrderCarShopId,
        $intDeliveryShopId
    )
    {
        // 查询顾问信息
        $users = User::find()
            ->where(['id' => [$intOrderCarSalesmanId, $intDeliverySalesmanId]])
            ->indexBy('id')
            ->asArray()
            ->all();
        $arrReturn = [
            'status' => false,
            'message' => '顾问信息存在问题',
        ];

        if (isset($users[$intOrderCarSalesmanId]) && isset($users[$intDeliverySalesmanId])) {
            // 请求参数
            $params = [
                'orderNo' => $strCheOrderId,

                // 交车顾问信息
                'pickStoreOrganizationId' => (int)$intDeliveryShopId,
                'pickStoreSaleId' => (int)$intDeliverySalesmanId,
                'pickStoreSaleMobile' => $users[$intDeliverySalesmanId]['phone'],
                'pickStoreSaleName' => $users[$intDeliverySalesmanId]['name'],

                // 订车顾问信息
                'reserveStoreOrganizationId' => (int)$intOrderCarShopId,
                'reserveStoreSaleId' => (int)$intOrderCarSalesmanId,
                'reserveStoreSaleMobile' => $users[$intOrderCarSalesmanId]['phone'],
                'reserveStoreSaleName' => $users[$intOrderCarSalesmanId]['name']
            ];

            // 发送请求记录日志
            $arrResult = $this->get('api/CRM/updateStoreSale', $params);
            Helper::logs('che-api/'.date('Ymd').'-notice-update-store-sale.log', $this->mixRequest);

            if (!empty($arrResult['code']) && $arrResult['code'] == 200) {
                $arrReturn = [
                    'status' => true,
                    'message' => 'success',
                    'data' => $arrResult
                ];
            } else {
                $arrReturn['message'] = empty($arrResult['description']) ? '请求失败' : $arrResult['description'];
            }
        }

        return $arrReturn;
    }

}