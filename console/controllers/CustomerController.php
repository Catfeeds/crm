<?php

namespace console\controllers;

use common\helpers\Helper;
use common\models\Area;
use common\models\ClueWuxiao;
use common\models\Customer;
use common\models\Clue;
use common\models\CustomerErrorLog;
use common\models\ShopArea;
use frontend\modules\sales\logic\MemberLogic;
use yii\helpers\ArrayHelper;

/**
 * Class CustomerController 客户信息对应脚本
 * @desc
 * --- actionIndex 补全客户的地址信息
 * --- actionUser  客户在用户中心注册失败，重新注册
 * @package console\controllers
 */
class CustomerController extends \yii\console\Controller
{
    /**
     * 处理客户地址信息脚本
     */
    public function actionIndex()
    {
        $arrLogs = [
            'time' => date('Y-m-d H:i:s'),
            'error' => [],
        ];

        // 查询到客户信息
        $customers = Customer::find()->where(['area' => [0, 3301]])->all();

        // 输出查询条件
        $arrLogs['sql'] = Customer::find()->where(['area' => [0, 3301]])->createCommand()->getRawSql().PHP_EOL;
        $arrLogs['number'] = count($customers);

        if ($customers) {

            // 查询全部的门店对应的地址信息
            $shopAreas = ShopArea::find()->indexBy('shop_id')->asArray()->all();

            foreach ($customers as $value) {
                /* @var $value \common\models\Customer */
                // 1.查询这个客户的活线索
                $clue = Clue::find()->select('shop_id')
                    ->where(['customer_id' => $value->id, 'is_fail' => 0])
                    ->orderBy('last_view_time DESC')
                    ->one();
                if (!$clue) {
                    // 2.查询这个客户的其它线索
                    $clue = Clue::find()->select('shop_id')
                        ->where(['customer_id' => $value->id, 'is_fail' => 1])
                        ->orderBy('last_view_time DESC')
                        ->one();
                    if (!$clue) {
                        // 3.查询无效线索
                        $clue = ClueWuxiao::find()->select('shop_id')
                            ->where(['customer_id' => $value->id])
                            ->orderBy('last_view_time DESC')
                            ->one();
                    }
                }

                // 三步查询都没有的话，不处理
                if ($clue) {
                    /* @var $clue \common\models\Clue  */

                    // 门店对应的地址信息存在
                    if (isset($shopAreas[$clue->shop_id])) {
                        /* @var $tmp \common\models\ShopArea */
                        $tmp = $shopAreas[$clue->shop_id];

                        // 获取市或者区的信息
                        $area = Area::getByName($tmp['shiName'], $tmp['quOrXian']);
                        if ($area) {
                            $value->area = $area->id;
                            if (!$value->save()) {
                                $arrLogs['error'][] = $value->getErrors();
                            }
                        }
                    }
                } else {
                    // 记录日志
                    $arrLogs['error'][] = [
                        'error' => '没有线索信息',
                        'customer' => [
                            'id' => $value->id,
                            'phone' => $value->phone,
                            'name' => $value->name,
                            'area' => $value->area
                        ],
                    ];
                }
            }
        }

        // 记录日志输出信息
        Helper::logs('error/'.date('Ymd').'-customer.log', $arrLogs);
        echo date('Y-m-d H:i:s').' OK '.PHP_EOL;
    }

    /**
     * 处理客户在用户中心注册失败，重新注册
     */
    public function actionUser()
    {
        // 查询到全部的失败用户
        $all = CustomerErrorLog::find()->all();
        if ($all) {
            $arrPhone = ArrayHelper::getColumn($all, 'phone');
            // 查询客户信息
            $customers = Customer::find()->where([
                'and',
                ['in', 'phone', $arrPhone],
            ])->indexBy('phone')->all();

            $member = new MemberLogic();
            // 处理数据
            foreach ($all as $value) {
                /* @var $value \common\models\CustomerErrorLog */
                // 存在这个客户
                if (isset($customers[$value->phone])) {
                    /* @var $customer \common\models\Customer */
                    $customer = $customers[$value->phone];
                    $member->addMember($customer->id);
                }

                // 删除数据
                $value->delete();
            }
        }

        echo date('Y-m-d H:i:s').' OK Log number: ' . count($all) . PHP_EOL;
    }
}