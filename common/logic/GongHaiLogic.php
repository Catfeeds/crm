<?php
/**
 * 功    能：公海原因字典
 */
namespace common\logic;

use common\models\Clue;
use common\models\GongHaiLog;
use common\models\OrganizationalStructure;
use frontend\modules\sales\logic\GongHaiGic;
use Yii;
use common\models\GongHaiReason;
use common\helpers\Helper;


class GongHaiLogic
{
    // 定义信息来源的ID 名称为 公海线索
    const SOURCE_ID = 20;

    // 定义渠道来源ID 名称为 公海线索
    const INPUT_TYPE_ID = 46;


    /**
     * 功    能：通过id获取进入公海的原因名
     * 参    数：无
     * 作    者：于凯
     * 修改日期：2017-6-28
     */
    public static function getGonghaiReasonName($id)
    {
        $strName = '';
        $seas = GongHaiReason::findOne($id);
        if ($seas) $strName = $seas->reason_name;
        return $strName;
    }


    /**
     * 功    能：获取进入公海信息
     * 参    数：无
     * 作    者：于凯
     * 修改日期：2017-6-28
     */
    public static function getGonghaiReasonInfo()
    {
        $sql = 'SELECT `id`,`reason_name` AS `name` FROM `crm_gonghai_reason`';
        return Yii::$app->db->createCommand($sql)->queryAll();
    }

    /**
     * 从公海出去的处理方法(包括认领和下发)
     * @param \common\models\Gonghai                 $mixSeas 查询出来的公海信息
     * @param \common\models\OrganizationalStructure $shop    查询出来的门店信息
     * @param \common\models\User|null               $user      认领人信息 对象
     * @param null $admin     分发人信息 数组
     * @return array 正常返回 ['status' => true, 'message' => 'success']
     */
    public static function highSeasGoOut($mixSeas, $shop, $user = null, $admin = null)
    {
        // 默认返回数据
        $arrReturn = [
            'status' => false,
            'message' => '存在问题',
        ];

        // 是否已经处理
        $isHandle = true;

        // redis 保存的 key
        $key = 'CRM:gonghai:'.$mixSeas->id;

        // 添加redis验证处理，防止并发抢资源
        if ($user && is_object($user)) {
            /**
             * edited by liujx 2017-7-3 添加验证 start:
             *
             * 查询这个用户在线索表里面是否存在 意向 和 订车线索
             */
            $clue = ClueValidate::validateExists([
                'and',
                ['=', 'customer_phone', $mixSeas->customer_phone],
                ['=', 'is_fail', 0],
                ['in', 'status', [0, 1, 2]]
            ]);

            if ($clue) {
                $mixSeas->delete();
                $isHandle = false;
                $arrReturn['message'] = '这个客户已经有顾问在跟进了,不能重复跟进';
            } else {
                // 先读数据
                $redis = Yii::$app->redis;
                if ($redis->get($key)) {
                    $arrReturn['message'] = '该线索已经有顾问在认领了,请稍后再试';
                    $isHandle = false;
                } else {
                    $redis->set($key, $mixSeas->id);
                }
            }

            // end;
        }

        // 之前没有处理过，redis 中不存在或者是下发
        if ($isHandle) {
            // 认领人和分配人必须要有一个
            if ($user || $admin) {
                // 开始处理
                $transaction = Yii::$app->db->beginTransaction();
                $isSubmit = false;

                try {
                    // 第一步写入线索信息
                    $clue = new Clue();

                    // 客户信息
                    $clue->customer_id = $mixSeas->customer_id;
                    $clue->customer_phone = $mixSeas->customer_phone;
                    $clue->customer_name = $mixSeas->customer_name;

                    $clue->clue_source = self::SOURCE_ID;           // 信息来源
                    $clue->clue_input_type = self::INPUT_TYPE_ID;   // 渠道来源

                    // 查询车系信息
                    $intention = (new CarBrandAndType())->getBrandAndFactoryInfoByTypeId($mixSeas->intention_id);
                    if ($intention) {
                        $strName = $intention[$mixSeas->intention_id]['car_brand_type_name'];
                    } else {
                        $strName = $mixSeas->intention_des;
                    }

                    // 车系信息
                    $clue->intention_id = $mixSeas->intention_id;
                    $clue->intention_des = $strName;

                    // 其他信息
                    $clue->create_time = time();                    // 创建时间
                    $clue->assign_time = $clue->create_time;        // 分配时间
                    $clue->is_assign = 1;                           // 是否分配
                    $clue->create_type = 6;                         // 公海进入
                    $clue->status = 0;                              // 线索状态

                    // 门店信息
                    $clue->shop_id = $shop->id;
                    $clue->shop_name = $shop->name;

                    /**
                     * user 需要判断是认领还是下发
                     *
                     * 下发的时候 创建人和分配人 使用 系统登录用户
                     * 认领的时间 创建人和分配人 使用 认领人
                     */
                    if ($user && is_object($user)) {
                        // 顾问信息
                        $clue->salesman_id = $user->id;
                        $clue->salesman_name = $user->name;

                        // 创建人姓名
                        $clue->create_person_name = $user->name;

                        // 分配人信息
                        $clue->who_assign_name = $user->name;
                        $clue->who_assign_id = $user->id;
                    } else {
                        // 创建人姓名
                        $clue->create_person_name = $admin['name'];

                        // 分配人信息
                        $clue->who_assign_name = $admin['name'];
                        $clue->who_assign_id = $admin['id'];
                    }

                    // 生成线索信息
                    if ($clue->save()) {
                        // 第二步，如果是认领的话，还需要添加顾问记录

                        // 存在用户信息还要生成记录
                        if ($user && is_object($user)) {
                            // 添加记录
                            $objLogs = new GongHaiLog();
                            $objLogs->customer_phone = $mixSeas->customer_phone;
                            $objLogs->start_time = $clue->create_time;
                            $objLogs->end_time = 0;
                            $objLogs->salesman_name = $user->name;
                            $objLogs->reason_id = 0;
                            $objLogs->reason_name = '';
                            $objLogs->shop_id = $shop->id;
                            $objLogs->shop_des = OrganizationalStructure::getParentNames($shop->id);
                            $objLogs->clue_id = 0;
                            if ($objLogs->save()) {
                                $isSubmit = true;
                            } else {
                                $arrReturn['message'] = '添加顾问跟进记录失败';
                                $arrReturn['error'] = $objLogs->getErrors();
                            }
                        } else {
                            $isSubmit = true;
                        }

                        // 最后删除公海记录信息
                        if ($isSubmit) {
                            if ($mixSeas->delete()) {
                                $isSubmit = true;
                            } else {
                                $isSubmit = false;
                                $arrReturn['message'] = '删除公海线索信息失败';
                            }
                        }
                    } else {
                        $arrReturn['message'] = '线索信息生成失败';
                        $arrReturn['error'] = $clue->getErrors();
                    }

                    // 事务处理成功
                    if ($isSubmit) {
                        $transaction->commit();
                        $arrReturn = ['status' => true, 'message' => 'success'];
                    } else {
                        $transaction->rollBack();
                    }
                } catch (\Exception $e) {
                    $arrReturn['message'] = $e->getMessage();
                    $transaction->rollBack();
                }

            } else {
                $arrReturn['message'] = '认领人或者分配人必须存在一个';
            }

            // 返回之前一点要删除redis 保存的数据
            Yii::$app->redis->del($key);
        }

        return $arrReturn;
    }

    /**
     * 战败投入公海
     * @param int $failTags 战败标签
     * @param string $failReason 战败标签说明（原因）
     * @param \common\models\Clue $clue 线索信息
     * @return bool
     */
    public static function failIntoSeas($failTags, $failReason, $clue)
    {
        $isReturn = false;

        // 只有指定的战败才投入公海
        if (in_array($failTags, Yii::$app->params['arrDefeatedReason'])) {
            // 查询公海日志是否存在信息  第一次记录创建时间
            $gongHaiLog = GongHaiLog::find()->where([
                'customer_phone' => $clue->customer_phone
            ])->orderBy('id desc')->one();

            // 改客户只有存在记录
            if ($gongHaiLog && empty($gongHaiLog->end_time)) {
                /* @var $gongHaiLog \common\models\GongHaiLog */
                // 已经存在数据 跟新
                $gongHaiLog->end_time = time();
                $gongHaiLog->reason_id = 1;
                $gongHaiLog->reason_name = GongHaiLogic::getGonghaiReasonName(1);
                $gongHaiLog->defeated_reason = $failReason;
                $gongHaiLog->clue_id = $clue->id;
            } else {
                // 不存在 新增第一次记录
                $gongHaiLog = new GongHaiLog();
                $gongHaiLog->customer_phone = $clue->customer_phone;
                $gongHaiLog->start_time = $clue->create_time;
                $gongHaiLog->end_time = time();
                $gongHaiLog->salesman_name = $clue->salesman_name;

                // 进入公海信息
                $gongHaiLog->reason_id = 1;
                $gongHaiLog->reason_name = GongHaiLogic::getGonghaiReasonName(1);

                $gongHaiLog->defeated_reason = $failReason;

                // 门店信息
                $gongHaiLog->shop_id = $clue->shop_id;
                $gongHaiLog->shop_des = OrganizationalStructure::getParentNames($clue->shop_id);

                // 线索信息
                $gongHaiLog->clue_id = $clue->id;
            }

            // 先保存公海记录信息
            if (!$gongHaiLog->save()) {
                // 记录日志
                Helper::logs('gonghai/' . date('Ym') . '-fail-into-seas-error.log', [
                    'time' => date('Y-m-d H:i:s'),
                    'message' => '添加公海记录失败',
                    'error' => $gongHaiLog->getErrors(),
                ]);
            }

            // 增加公海信息
            try {
                // 进入公海
                if (GongHaiGic::addGongHai($clue)) {
                    $isReturn = true;
                }
            } catch (\Exception $e) {
                // 记录日志
                Helper::logs('gonghai/' . date('Ym') . '-fail-into-seas-error.log', [
                    'time' => date('Y-m-d H:i:s'),
                    'message' => '投入公海处理失败',
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $isReturn;
    }
}
