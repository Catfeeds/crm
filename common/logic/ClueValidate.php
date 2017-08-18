<?php
/**
 * Created by PhpStorm.
 * User: liujx
 * Date: 2017/6/27
 * Time: 10:52
 */

namespace common\logic;

use common\helpers\Helper;
use common\models\Clue;
use common\models\OrganizationalStructure;
use common\models\User;

/**
 * Class ClueValidate
 * @package common\logic
 */
class ClueValidate
{
    /**
     * 分享新增线索的信息来源ID
     * name 销售助手分享页面
     */
    const SHARE_CLUE_SOURCE_ID = 21;

    /**
     * 分享新增线索的渠道来源ID
     * name 销售助手分享
     */
    const SHARE_CLUE_INPUT_TYPE_ID = 47;

    /**
     * 验证制定条件的线索信息是否存在
     * @param array $where 查询条件
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function validateExists($where)
    {
        return Clue::find()->where($where)->orderBy('id DESC')->one();
    }

    /**
     * 处理分享时候线索的生成
     * @param \common\models\Customer $customer 客户信息
     * @param \common\models\Share $share 分享信息
     * @param array $clueParams 线索需要写入的信息
     * @return array|Clue|null|\yii\db\ActiveRecord
     */
    public static function shareValidateOrInsertClue($customer, $share, $clueParams = [])
    {
        // 第一步，优先考虑存在活线索的情况(意向客户、订车客户),存在改情况，不新增线索
        $clue = Clue::find()->where([
            'customer_id' => $customer->id,
            'status' => [Clue::STATUS_WILL, Clue::STATUS_BOOK], // 1 => 意向, 2 => 订车
            'is_fail' => 0
        ])->orderBy('id desc')->one();

        // 判断是否存在线索
        if ($clue) {
            /* @var $clue \common\models\Clue */
            // 存在活的线索，那么要处理这个线索是否没有分配顾问的情况
            if (empty($clue->salesman_id)) {
                $clue = self::randAssignSalesman($clue);
            }
        } else {

            // 第二步查询这个客户存在线索(线索状态，也是不用新增线索，更新线索)
            $clue = Clue::find()->where([
                'customer_id' => $customer->id,
                'status' => Clue::STATUS_CLUB, // 0 => 线索
                'is_fail' => 0
            ])->orderBy('id desc')->one();

            // 存在线索
            if ($clue) {
                /* @var $clue \common\models\Clue */
                // 没有顾问信息
                if (empty($clue->salesman_id)) {
                    // 没有门店信息 (需要分配门店和顾问)
                    if (empty($clue->shop_id)) {
                        // 顾问信息
                        $user = User::findOne($share->salesman_id);
                        $clue->salesman_id = $share->salesman_id;
                        $clue->salesman_name = $user ? $user->name : '';

                        // 门店信息
                        $shop = OrganizationalStructure::findOne($share->shop_id);
                        $clue->shop_id = $share->shop_id;
                        $clue->shop_name = $shop ? $shop->name : '';

                        // 需要更新意向车系信息
                        if ($clueParams) $clue->attributes = $clueParams;

                        // 保存修改,失败的话，记录日志
                        if (!$clue->save()) {
                            // 记录日志
                            Helper::logs('/error/share/'.date('Ym').'-error.log', [
                                'time' => date('Y-m-d H:i:s'),
                                'error' => $clue->getErrors()
                            ]);

                            // 返回null
                            $clue = null;
                        }
                    } else {
                        // 有门店，随机分配顾问，并更新车系信息
                        $clue = self::randAssignSalesman($clue, $clueParams);
                    }
                } else {
                    // 需要更新线索信息
                    if ($clueParams) {
                        $clue->attributes = $clueParams;
                        // 保存修改,失败的话，记录日志
                        if (!$clue->save()) {
                            // 记录日志
                            Helper::logs('/error/share/'.date('Ym').'-error.log', [
                                'time' => date('Y-m-d H:i:s'),
                                'error' => $clue->getErrors()
                            ]);

                            // 返回null
                            $clue = null;
                        }
                    }
                }
            } else {
                // 不存在线索，那么要新增一个线索
                $clue = new Clue();

                // 查询这个客户是否存在交车线索
                $hasClue = Clue::findOne([
                    'customer_id' => $customer->id,
                    'status' => 3,
                    'is_fail' => 0
                ]);

                // 存在的话，新线索信息需要使用原来的顾问信息
                if ($hasClue && $hasClue->salesman_id) {
                    // 顾问信息
                    $clue->salesman_id = $hasClue->salesman_id;
                    $clue->salesman_name = $hasClue->salesman_name;

                    // 门店信息
                    $clue->shop_id = $hasClue->shop_id;
                    $clue->shop_name = $hasClue->shop_name;
                } else {
                    // 顾问信息
                    $salesman = User::find()->where(['id' => $share->salesman_id, 'is_delete' => 0])->asArray()->one();
                    if (!$salesman) {
                        // 顾问不存在，那么要在这个门店中随机一个顾问
                        $shop = OrganizationalStructure::findOne(['id' => $share->shop_id, 'is_delete' => 0]);
                        if ($shop) {
                            $clue->shop_id = $share->shop_id;
                            $clue->shop_name = $shop ? $shop->name : '';
                            $salesman = OrganizationalStructure::getRandomUser($clue->shop_id);
                        } else {
                            // 门店关了，不处理
                            Helper::logs('error/share/'.date('Ymd').'-error.log', [
                                'time' => date('Y-m-d H:i:s'),
                                'error' => '分享的顾问不存在，顾问所在门店也关了',
                                'shop' => $share->shop_id,
                                'salesman' => $share->salesman_id,
                            ]);

                            $clue = null;
                        }
                    }

                    // 线索信息
                    if ($clue && $salesman) {

                        // 顾问信息
                        $clue->salesman_id = $salesman['id'];
                        $clue->salesman_name = $salesman['name'];

                        // 门店信息
                        $clue->shop_id = $share->shop_id;
                        $clue->shop_name = $share->shop_name;
                    }
                }

                if ($clue) {
                    // 客户信息
                    $clue->customer_id = $customer->id;
                    $clue->customer_phone = $customer->phone;
                    $clue->customer_name = $customer->name;

                    // 信息来源和渠道来源信息
                    $clue->clue_source = self::SHARE_CLUE_SOURCE_ID;
                    $clue->clue_input_type = self::SHARE_CLUE_INPUT_TYPE_ID;

                    // 车系信息
                    if ($clueParams) $clue->attributes = $clueParams;

                    // 其他信息
                    $clue->create_time = time();
                    $clue->assign_time = $clue->create_time;
                    $clue->is_assign = 1;

                    // 创建人就是这个顾问
                    $clue->create_person_name = $clue->salesman_name;
                    $clue->who_assign_name = $clue->salesman_name;
                    $clue->who_assign_id = $clue->salesman_id;
                    $clue->create_type = 7; // 分享
                    $clue->status = 0;

                    // 判断是否成功
                    if (!$clue->save(false)) {
                        // 记录日志
                        Helper::logs('/error/share/'.date('Ym').'-error.log', [
                            'time' => date('Y-m-d H:i:s'),
                            'error' => $clue->getErrors()
                        ]);

                        // 返回null
                        $clue = null;
                    }
                }
            }
        }

        return $clue;
    }

    /**
     * 如果线索的顾问没有分配，那么给这个线索根据门店，在门店中随机分配一个顾问
     * @param \common\models\Clue $clue 线索信息AR 对象
     * @param array $params 其他需要更新线索的信息
     * @return null|\common\models\Clue
     */
    public static function randAssignSalesman($clue, $params = [])
    {
        if (empty($clue->salesman_id)) {
            // 没有传递顾问信息，需要随机获取一个顾问信息
            $user = OrganizationalStructure::getRandomUser($clue->shop_id);
            if ($user) {
                // 更新顾问信息
                $clue->salesman_id = $user['id'];
                $clue->salesman_name = $user['name'];

                // 存在其他需要更新的信息
                if ($params) {
                    $clue->attributes = $params;
                }

                // 执行新增
                if (!$clue->save()) {
                    // 没有顾问信息记录日志
                    Helper::logs('/error/share/'.date('Ym').'-error.log', [
                        'shop_id' => $clue->shop_id,
                        'clue' => $clue->toArray(),
                        'message' => '该线索没有顾问，随机分配顾问出现错误',
                        'error' => $clue->getErrors()
                    ]);

                    $clue = null;
                }
            } else {
                // 没有顾问信息记录日志
                Helper::logs('/error/share/'.date('Ym').'-error.log', [
                    'shop_id' => $clue->shop_id,
                    'clue' => $clue->toArray(),
                    'error' => '这个门店没有顾问哦'
                ]);

                $clue = null;
            }
        }

        return $clue;
    }
}