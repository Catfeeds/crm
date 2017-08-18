<?php
/**
 * Created by PhpStorm.
 * User: liujx
 * Date: 2017/6/19
 * Time: 17:57
 */

namespace frontend\modules\sales\controllers;

use common\helpers\Helper;
use common\models\Customer;
use frontend\modules\sales\logic\MemberLogic;
use frontend\modules\sales\models\Clue;
use yii;
use yii\helpers\Json;

/**
 * Class MemberController
 * @package frontend\modules\sales\controllers
 * @author liujx
 * @desc 用户信息接口控制器
 */
class MemberController extends AuthController
{
    // 账单中订单类型对应的显示说明
    private $arrOrderTypeDesc = [
        1 => ['title' => '充', 'desc' => '充值-银行卡充值'],
        2 => ['title' => '电商', 'desc' => '消费-车城电商'],
        3 => ['title' => '退', 'desc' => '退款-车城电商'],
        4 => ['title' => '提', 'desc' => '提现'],
    ];

    /**
     * actionBill() 账单信息
     */
    public function actionBill()
    {
        $mixReturn = [];
        $arrLogs = [
            'time' => date('Y-m-d H:i:s'),
            'ip' => Helper::getIpAddress(),
        ];

        // 请求参数
        $strParams = Yii::$app->request->post('p');
        $this->handleParams('请求参数为空');
        if ($strParams) {
            // 记录请求过来的参数
            $arrLogs['params'] = $params = Json::decode($strParams);
            $this->handleParams('线索ID不存在');
            if (isset($params['clue_id']) && !empty($params['clue_id'])) {
                $clue = Clue::findOne((int)$params['clue_id']);
                $this->handleParams('线索信息不存在');
                if ($clue) {
                    $this->handleParams('客户信息不存在');
                    $customer = Customer::findOne($clue->customer_id);
                    if ($customer) {
                        $this->handleParams('success', 200);
                        if ($customer->member_id) {

                            $request = [
                                'uid' => $customer->member_id,
                                'page' => isset($params['currentPage']) ? (int)$params['currentPage'] : 1,
                                'per-page' => isset($params['perPage']) ? (int)$params['perPage'] : 10,
                            ];

                            // 订单类型
                            if (isset($params['order_type'])) {
                                $request['order_type'] = $params['order_type'];
                            }

                            // 订单状态
                            if (isset($params['status'])) {
                                $request['status'] = $params['status'];
                            }

                            // 请求用户中心获取数据
                            $member = new MemberLogic();
                            $response = $member->get('/inside/trade/list', $request);
                            $arrLogs['curl'] = $member->getRequestInfo();
                            if ($response && isset($response['err_code']) && $response['err_code'] === 0) {
                                // 按照要求返回数据
                                $mixReturn = [
                                    'pages' => $response['data']['pages'],
                                    'models' => $response['data']['list']
                                ];

                                // 存在数据处理下时间戳问题
                                if (!empty($mixReturn['models'])) {
                                    foreach ($mixReturn['models'] as $key =>  &$value) {
                                        // 修改下时间
                                        $value['created_at'] = strtotime($value['created_at']);
                                        $value['updated_at'] = strtotime($value['updated_at']);

                                        // 处理类型
                                        if (!empty($this->arrOrderTypeDesc[$value['order_type']])) {
                                            $value['order_type_title'] = $this->arrOrderTypeDesc[$value['order_type']]['title'];
                                            $value['order_type_desc'] = $this->arrOrderTypeDesc[$value['order_type']]['desc'];
                                        } else {
                                            $value['order_type_title'] = '车城';
                                            $value['order_type_desc'] = '车城电商';
                                        }
                                    }

                                    unset($value);
                                }
                            } else {
                                $this->handleParams('接口返回错误');
                            }
                        }
                    }
                }
            }
        }

        // 记录日志返回
        Helper::logs('sales/'.$this->id.'/'.date('Ymd').'-'.$this->action->id.'.log', $arrLogs);
        return $mixReturn;
    }
}