<?php
/**
 * Created by PhpStorm.
 * User: Think
 * Date: 2017/7/6
 * Time: 20:29
 */

namespace console\controllers;

use common\helpers\Helper;
use common\models\Clue;
use common\models\ClueWuxiao;
use common\models\Customer;
use frontend\modules\sales\logic\GongHaiGic;
use yii\console\Controller;

/**
 * Class TempController 临时跑一下的脚本
 * @package console\controllers
 */
class TempController extends Controller
{
//    /**
//     * 历史战败客户投入公海
//     */
//    public function actionFail()
//    {
//        $all = Clue::find()->where(['is_fail' => 1])->asArray()->all();
//        if ($all) {
//            // 获取到客户ID
//            $arrIds = array_column($all, 'customer_id');
//
//            // 查询客户信息
//            $customer = Customer::find()->where(['in', 'id', $arrIds])->indexBy('id')->asArray()->all();
//            if ($customer) {
//                foreach ($all as &$value) {
//                    if (isset($customer[$value['customer_id']])) {
//                        $value['area_id'] = $customer[$value['customer_id']]['area'];
//                    }
//                }
//
//                unset($value);
//            }
//
//            // 查询这个客户有没有活动线索在跑
//            $haves = Clue::find()->select('id,customer_id')
//                ->where([
//                    'customer_id' => $arrIds,
//                    'is_fail' => 0,
//                    'status' => [0, 1, 2]
//                ])
//                ->indexBy('customer_id')
//                ->asArray()
//                ->all();
//
//            if ($haves) {
//                foreach ($all as $key => $value) {
//                    if (isset($haves[$value['customer_id']])) {
//                        unset($all[$key]);
//                    }
//                }
//            }
//
//            try {
//                GongHaiGic::addGongHai1($all, 6);
//            } catch (\Exception $e) {
//                Helper::logs('error/'.date('Ymd').'-tmp-fail-error.log', [
//                    'time' => date('Y-m-d H:i:s'),
//                    'error' => $e->getMessage(),
//                ]);
//            }
//        }
//
//        echo date('Y-m-d H:i:s'). 'OK'.PHP_EOL;
//    }
//
//    /**
//     * 历史门店无人认领的客户投入公海
//     */
//    public function actionHistory()
//    {
//        $clue = Clue::find()
//            ->where("status=0 and salesman_id=0 and is_assign=0 and create_time <= " . time())
//            ->asArray()
//            ->all();
//        if (!empty($clue)){
//
//            $customer_ids = [];
//            $ids = [];
//            foreach ($clue as $v) {
//                array_push($customer_ids, $v['customer_id']);
//                array_push($ids, $v['id']);
//            }
//
//            // 查询客户地址
//            $customer = Customer::find()->select('id,area')->where(['in', 'id', $customer_ids])->asArray()->all();
//
//            foreach ($clue as $k => $v) {
//                foreach ($customer as $val) {
//                    if ($v['customer_id'] == $val['id']) {
//                        $clue[$k]['area_id'] = $val['area'];
//                        break;
//                    }
//                }
//            }
//
//            foreach ($clue as $k => $v) {
//                if (!isset($v['area_id']))
//                    unset($clue[$k]);
//            }
//
//            try {
//                if (GongHaiGic::addGongHai1($clue, 3)){
//                    //删除24小时门店无人认领的数据
//                    Clue::deleteAll(['in','id', $ids]);
//                }
//            } catch (\Exception $e) {
//                Helper::logs('/error/'.date('Ymd').'-tmp-history-error.log', [
//                    'time' => date('Y-m-d H:i:s'),
//                    'error' => $e->getMessage(),
//                ]);
//            }
//
//        }
//    }

    /**
     * 无效线索
     */
    public function actionWuXiao()
    {
        $all = ClueWuxiao::find()->where(['fail_tags' => [2, 6, 7, 9, 10, 44]])->asArray()->all();
        if ($all) {
            // 获取到客户ID
            $arrIds = array_column($all, 'customer_id');

            // 查询客户信息
            $customer = Customer::find()->where(['in', 'id', $arrIds])->indexBy('id')->asArray()->all();
            if ($customer) {
                foreach ($all as &$value) {
                    if (isset($customer[$value['customer_id']])) {
                        $value['area_id'] = $customer[$value['customer_id']]['area'];
                    }
                }

                unset($value);
            }

            // 查询这个客户有没有活动线索在跑
            $haves = Clue::find()->select('id,customer_id')
                ->where([
                    'customer_id' => $arrIds,
                    'is_fail' => 0,
                    'status' => [0, 1, 2]
                ])
                ->indexBy('customer_id')
                ->asArray()
                ->all();

            if ($haves) {
                foreach ($all as $key => $value) {
                    if (isset($haves[$value['customer_id']])) {
                        unset($all[$key]);
                    }
                }
            }

            try {
                GongHaiGic::addGongHai1($all, 6);
            } catch (\Exception $e) {
                Helper::logs('error/'.date('Ymd').'-tmp-fail-error.log', [
                    'time' => date('Y-m-d H:i:s'),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        echo date('Y-m-d H:i:s'). 'OK'.PHP_EOL;
    }

}