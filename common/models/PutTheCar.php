<?php

namespace common\models;

use common\common\PublicMethod;
use common\logic\CheApi;
use common\logic\JxcApi;
use yii;
use common\helpers\Helper;
use common\logic\CarBrandAndType;
use yii\db\ActiveRecord;
use common\logic\CompanyUserCenter;
use common\logic\PhoneLetter;
use common\logic\NoticeTemplet;

/**
 * This is the model class for table "{{%put_the_car}}".
 *
 * @property integer $id
 * @property integer $clue_id
 * @property string $order_id
 * @property string $customer_name
 * @property string $customer_phone
 * @property integer $old_shop_id
 * @property string $old_shop_name
 * @property integer $old_salesman_id
 * @property string $old_salesman_name
 * @property integer $new_shop_id
 * @property string $new_shop_name
 * @property integer $new_salesman_id
 * @property string $new_salesman_name
 * @property integer $status
 * @property integer $create_time
 * @property integer $confirm_time
 * @property integer $the_car_time
 * @property string $yu_ding_che_xing
 * @property integer $yu_ding_che_xing_id
 * @property integer $claim_time
 * @property integer $next_handle_time
 */
class PutTheCar extends ActiveRecord
{
    const STATUS_CREATE = 0; // 创建
    const STATUS_UNDONE = 1; // 未完成（订单确认后更新这个字段）
    const STATUS_CARRY_OUT = 2; // 完成
    const STATUS_DELETE = 3;    // 战败（删除）

    const REDIS_CLAIM_KEY = 'CRM:put_the_car:claim_';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%put_the_car}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                'clue_id', 'old_shop_id', 'old_salesman_id',
                'new_shop_id', 'new_salesman_id', 'create_time',
                'the_car_time', 'yu_ding_che_xing_id',
                'status', 'confirm_time', 'claim_time',
                'next_handle_time'
            ], 'integer'],
            [['customer_phone'], 'string', 'max' => 15],
            [['old_shop_id', 'old_salesman_id', 'new_shop_id', 'order_id'], 'required'],
            [['old_shop_name', 'old_salesman_name', 'new_shop_name', 'new_salesman_name', 'yu_ding_che_xing', 'customer_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'clue_id' => '线索id',
            'order_id' => '订单ID',
            'customer_name' => '客户姓名',
            'customer_phone' => '客户手机号',
            'old_shop_id' => '老门店id',
            'old_shop_name' => '老门店名称',
            'old_salesman_id' => '老顾问id',
            'old_salesman_name' => '老顾问名字',
            'new_shop_id' => '新门店id',
            'new_shop_name' => '新门店名称',
            'new_salesman_id' => '新顾问id',
            'new_salesman_name' => '新顾问名字',
            'status' => '完成状态',
            'create_time' => '任务创建时间',
            'the_car_time' => '订车时间',
            'yu_ding_che_xing' => '预定车型说明',
            'yu_ding_che_xing_id' => '预定车型id',
            'claim_time' => '认领时间',
            'next_handle_time' => '下一次处理时间',
        ];
    }

    /**
     * 添加提车任务
     *
     * @param \common\models\Clue $clue 线索信息
     * @param \common\models\Order $order 订单信息
     * @param integer $newShopId 分配的门店ID
     * @return null|\common\models\PutTheCar
     */
    public static function addMentionTheTask($clue, $order, $newShopId)
    {
        $mixReturn = null;
        $model = new PutTheCar();
        $model->create_time = $model->the_car_time = time();
        $model->clue_id = $clue->id;
        $model->old_salesman_id = $clue->salesman_id;
        $model->old_salesman_name = $clue->salesman_name;
        $model->old_shop_id = $clue->shop_id;
        $model->old_shop_name = $clue->shop_name;
        $model->order_id = $order->order_id;
        $model->customer_name = $clue->customer_name;
        $model->customer_phone = $clue->customer_phone;
        $model->status = self::STATUS_CREATE;
        $model->next_handle_time = $model->create_time;

        // 车型信息
        $model->yu_ding_che_xing_id = $order->che_car_id;
        $model->yu_ding_che_xing = $order->che_car_name;

        // 添加品牌信息
        $array = (new CarBrandAndType())->getBrandAndFactoryInfoByTypeId($order->car_type_id);
        if ($array && isset($array[$order->car_type_id])) {
            $array = $array[$order->car_type_id];
            // 品牌 车系 车型
            $model->yu_ding_che_xing = $array['brand_name'] . ' ' . $array['car_brand_type_name'] . ' ' . $model->yu_ding_che_xing;
        }

        $model->new_shop_id = $newShopId;
        $shop = OrganizationalStructure::findOne($model->new_shop_id);
        if ($shop) {
            $model->new_shop_name = $shop->name;
        }

        // 添加成功返回这个model
        try {
            if ($model->save()) {
                $mixReturn = $model;
            }
        } catch (\Exception $e) {
            // 记录错误日志
            Helper::logs('error/' . date('Ymd') . '-put-the-car-error.log', [
                'time' => date('Y-m-d H:i:s'),
                'error' => $e->getMessage()
            ]);
        }

        return $mixReturn;
    }

    /**
     * 认领任务
     *
     * @param integer $id 认领的任务ID
     * @param  \common\models\User $user 认领的顾问信息
     * @return array 返回数组 ['status' => true] 表示成功
     */
    public static function toClaim($id, $user)
    {
        $arrReturn = [
            'status' => false,
            'message' => '记录信息不存在'
        ];

        /* @var $redis \yii\redis\Connection */
        $redis = Yii::$app->redis;
        $key = self::REDIS_CLAIM_KEY . $id;

        if ($redis->get($key)) {
            $arrReturn['message'] = '该任务正在被其他顾问认领,请稍后再试...';
        } else {
            // 第一步查询到指定的任务
            $mentionTask = self::findOne([
                'id' => $id,
                'new_shop_id' => $user->shop_id,
                'status' => PutTheCar::STATUS_UNDONE,
                'new_salesman_id' => 0
            ]);

            if ($mentionTask) {
                // 生存缓存数据
                $redis->setex($key, 180, $mentionTask->id . '|' . $mentionTask->clue_id);

                // 查询订单信息
                $order = Order::findOne(['order_id' => $mentionTask->order_id]);

                $transaction = Yii::$app->db->beginTransaction();
                $isSubmit = false;

                try {
                    // 修改数据
                    $mentionTask->new_salesman_id = $user->id;
                    $mentionTask->new_salesman_name = $user->name;
                    $mentionTask->claim_time = time();

                    // 数据保存成功
                    if ($mentionTask->save()) {

                        $objApi = new JxcApi();

                        // 日志信息
                        $arrLogs = [
                            'time' => date('Y-m-d H:i:s'),
                            'url' => rtrim($objApi->url, '/') . '/api/sale/updateSellerId',
                            'request' => [
                                'clueNo' => $mentionTask->order_id,             // 我们订单号
                                'onlineSaleNo' => $order->che_order_id,         // 车城订单号
                                'sellerId' => $mentionTask->old_salesman_id,    // 下单顾问
                                'storeId' => $mentionTask->old_shop_id,         // 下单门店
                                'delSellerId' => $mentionTask->new_salesman_id, // 交车顾问
                                'delStoreId' => $mentionTask->new_shop_id,      // 交车门店
                            ],

                            'response' => '',
                        ];

                        $token = '_tk' . $objApi->b_token;
                        ksort($arrLogs['request']);
                        $strSign = implode('', $arrLogs['request']) . $token;
                        $strSign = md5($strSign);
                        $arrLogs['request']['sign'] = $strSign;
                        // 执行请求 记录日志信息
                        $arrLogs['response'] = PublicMethod::http_post($arrLogs['url'], $arrLogs['request'], [CURLOPT_TIMEOUT => 30]);
                        Helper::logs('erp/' . date('Ymd') . '-updateSellerId.log', $arrLogs);
                        if ($arrLogs['response']) {
                            $response = yii\helpers\Json::decode($arrLogs['response']);
                            if (!empty($response['statusCode']) && $response['statusCode'] == 1) {
                                $isSubmit = true;

                                // 通知电商订车和交车门店顾问信息
                                (new CheApi())->noticeUpdateStoreSale(
                                    $order->che_order_id,
                                    $mentionTask->old_salesman_id,
                                    $mentionTask->new_salesman_id,
                                    $mentionTask->old_shop_id,
                                    $mentionTask->new_shop_id
                                );
                            } else {
                                $arrReturn['message'] = isset($response['content']) ? $response['content'] : '请求erp 返回失败';
                            }
                        } else {
                            $arrReturn['message'] = '请求erp 返回失败';
                        }
                    } else {
                        $arrReturn['message'] = '保存数据失败';
                    }

                    // 提交这个事务
                    if ($isSubmit) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                    }

                } catch (\Exception $e) {
                    $transaction->rollBack();
                    $arrReturn['message'] = $e->getMessage();
                }

                // 事务处理成功
                if ($isSubmit) {
                    $arrReturn['status'] = true;
                    $arrReturn['message'] = '认领成功';
                    $arrReturn['model'] = $mentionTask;
                } else {
                    $redis->del($key);
                    $arrReturn['message'] = '认领失败';
                }
            }
        }

        return $arrReturn;
    }

    /**
     * 通过门店信息查询提车任务记录信息
     *
     * @param array|int $shops 查询门店信息，可以是单个门店
     * @param int $start 开始时间
     * @param int $end 结束时间
     * @return int 数据量
     */
    public static function getMentionCount($shops, $start, $end)
    {
        $total = self::find()->where([
            'and',
            ['new_shop_id' => $shops],
            ['>', 'new_salesman_id', 0],
            ['!=', 'status', self::STATUS_DELETE],
            ['between', 'claim_time', $start, $end]
        ])->count();

        return (int)$total;
    }

    /**
     * 通过门店信息查询未分配提车任务记录信息
     *
     * @param array|int $shops 查询门店信息，可以是单个门店
     * @param int $start 开始时间
     * @param int $end 结束时间
     * @return int 数据量
     */
    public static function getNotMentionCount($shops, $start, $end)
    {
        $total = PutTheCar::find()->where([
            'and',
            ['new_shop_id' => $shops],
            ['=', 'new_salesman_id', 0],
            ['=', 'status', PutTheCar::STATUS_UNDONE],
            ['between', 'confirm_time', $start, $end]
        ])->count();

        return (int)$total;
    }

    /**
     * 获取待提车客户总数
     * @param int $shopId 门店id
     * @return int 总数
     */
    public static function getPutTheCarCount($shopId)
    {
        $total = PutTheCar::find()->where([
            'and',
            ['new_shop_id' => $shopId],
            ['=', 'new_salesman_id', 0],
            ['=', 'status', PutTheCar::STATUS_UNDONE]
        ])->count();

        return (int)$total;
    }

    /**
     * 交车任务订单确认，需要更新交车任务状态和发送通知信息
     *
     * @param string $order_id 我们订单ID
     * @return bool 处理成功返回true
     */
    public static function updateMention($order_id)
    {
        $isReturn = false;
        // 查询订单号 对应的 创建 任务
        $one = self::findOne(['order_id' => $order_id, 'status' => self::STATUS_CREATE]);
        if ($one) {
            // 修改任务状态 确认时间、下一次处理时间
            $one->status = self::STATUS_UNDONE;
            $one->confirm_time = time();
            $one->next_handle_time = $one->confirm_time;

            if ($one->save()) {
                // 查询到这个门店下的顾问信息
                $users = (new CompanyUserCenter())->getShopSales($one->new_shop_id);
                if ($users) {

                    // 客户没有名字使用手机号
                    $strCustomerName = $one->customer_name ? $one->customer_name : $one->customer_phone;

                    $phoneObject = new PhoneLetter();
                    $appObject = new NoticeTemplet();
                    $arrUserIds = [];
                    foreach ($users as $val) {
                        // 推送语言短信
                        // $phoneObject->sendMentionTaskAllVoice($val['phone'], $strCustomerName, $one->yu_ding_che_xing);

                        // 推送文字短信
                        $phoneObject->sendMentionTaskAllSMS($val['phone'], $strCustomerName, $one->yu_ding_che_xing);

                        $arrUserIds[] = $val['id'];
                    }

                    // 推送APP通知
                    $appObject->sendNoticeByType(
                        'mentionTask',
                        0,
                        implode(',', $arrUserIds),
                        [
                            '[customer_name]' => $strCustomerName,
                            '[che_car_name]' => $one->yu_ding_che_xing
                        ],
                        'mention_task'
                    );
                }

                $isReturn = true;
            }
        }

        return $isReturn;
    }

    /**
     * 获取分配的提车信息
     * @param integer $userId 分配的用户id
     * @param integer $putTheCarStatus 分配表状态
     * @return array|ActiveRecord[]
     */
    public function getTheCar($userId, $putTheCarStatus)
    {
        return PutTheCar::find()
            ->where(
                ['and',
                    ['new_salesman_id' => $userId],
                    ['=', 'status', $putTheCarStatus]
                ]
            )
            ->all();
    }

    /**
     * 获取我申请到其他门店提车的信息
     * @param integer $userId 当前顾问id
     * @param integer $putTheCarStatus 分配表状态
     * @return array|ActiveRecord[]
     */
    public function getOldTheCar($userId, $putTheCarStatus)
    {
        return PutTheCar::find()
            ->where(
                ['and',
                    ['old_salesman_id' => $userId],
                    ['=', 'status', $putTheCarStatus]
                ]
            )
            ->all();
    }

    /**
     * 战败更新状态
     * @param int $clueId 线索id
     * @param int $status 状态
     * @return int
     */
    public static function updateTheCar($clueId, $status)
    {
        return PutTheCar::updateAll(['status' => $status], ['clue_id' => $clueId]);
    }

    /**
     * 处理提车顾问离职的情况
     * @param int $intConsultantId 离职顾问
     * @return int
     */
    public static function handleConsultantLeft($intConsultantId)
    {
        // 提车顾问离职，需要重新回到提车任务列表中,
        $intNumber = self::updateAll([
            'new_salesman_id' => 0,
            'new_salesman_name' => '',
            'next_handle_time' => time(),
        ], [
            'new_salesman_id' => $intConsultantId,
            'status' => self::STATUS_UNDONE
        ]);

        // 记录日志
        Helper::logs('consultant/'.date('Ym').'.log', [
            'time' => date('Y-m-d H:i:s'),
            'consultant' => $intConsultantId,
            'update_number' => $intNumber
        ]);

        return $intNumber;
    }

    /**
     * 处理订车顾问离职情况，重新分配新顾问，并通知erp 电商
     * @param  int $clueId 线索ID
     * @param int $intConsultantId 新顾问ID
     * @param int $strConsultantName 顾问姓名
     * @return bool
     */
    public static function handleOriginalConsultantLeft($clueId, $intConsultantId, $strConsultantName)
    {
        $isReturn = false;
        // 查询数据是否存在
        $one = self::find()->where(['clue_id' => $clueId, 'status' => self::STATUS_UNDONE])->one();
        if ($one) {
            // 记录日志
            $arrLogs = [
                'time' => date('Y-m-d H:i:s'),
                'clue' => $clueId,
                'consultant' => $intConsultantId,
            ];

            // 修改顾问
            /* @var $one \common\models\PutTheCar */
            $one->old_salesman_id = $intConsultantId;
            $one->old_salesman_name = $strConsultantName;
            if ($one->save()) {
                // 查询订单
                $order = Order::findOne(['order_id' => $one->order_id]);
                if ($order) {
                    // 推送ERP
                    $objApi = new JxcApi();

                    // 日志信息
                    $arrErp = [
                        'url' => rtrim($objApi->url, '/') . '/api/sale/updateSellerId',
                        'request' => [
                            'clueNo' => $one->order_id,             // 我们订单号
                            'onlineSaleNo' => $order->che_order_id, // 车城订单号
                            'sellerId' => $one->old_salesman_id,    // 下单顾问
                            'storeId' => $one->old_shop_id,         // 下单门店
                        ],

                        'response' => '',
                    ];


                    $token = '_tk' . $objApi->b_token;
                    ksort($arrErp['request']);
                    $strSign = implode('', $arrErp['request']) . $token;
                    $strSign = md5($strSign);
                    $arrErp['request']['sign'] = $strSign;

                    // 执行请求 记录日志信息
                    $arrErp['response'] = PublicMethod::http_post($arrErp['url'], $arrErp['request'], [CURLOPT_TIMEOUT => 30]);
                    $arrLogs['erp'] = $arrErp;

                    // 只有任务已经认领后才发通知给电商（以为还没有认领的话，不用通知电商）
                    if ($one->new_salesman_id > 0) {
                        // 通知电商订车和交车门店顾问信息
                        $arrLogs['cheApi'] = (new CheApi())->noticeUpdateStoreSale(
                            $order->che_order_id,
                            $one->old_salesman_id,
                            $one->new_salesman_id,
                            $one->old_shop_id,
                            $one->new_shop_id
                        );
                    }
                }
            } else {
                $arrLogs['error'] = $one->getErrors();
            }

            // 记录日志
            Helper::logs('consultant/'.date('Ym').'.log', $arrLogs);
        }

        return $isReturn;
    }
}
