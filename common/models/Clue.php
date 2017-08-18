<?php

namespace common\models;

use common\helpers\Helper;
use frontend\modules\sales\logic\TalkLogic;
use Yii;

/**
 * This is the model class for table "crm_clue".
 *
 * @property integer $id
 * @property integer $customer_id
 * @property string $intention_des
 * @property integer $intention_id
 * @property integer $buy_type
 * @property integer $planned_purchase_time_id
 * @property string $quoted_price
 * @property string $sales_promotion_content
 * @property integer $spare_intention_id
 * @property string $contrast_intention_id
 * @property string shop_name
 * @property integer assign_time
 * @property string salesman_name
 * @property integer $shop_id
 * @property integer $salesman_id
 * @property integer $is_assign
 * @property integer $who_assign_id
 * @property string $customer_phone
 * @property string $intention_level_des
 * @property integer $intention_level_id
 * @property integer $create_time
 * @property integer $last_view_time
 * @property integer $last_fail_time
 * @property integer $is_fail
 * @property integer $fail_tags
 * @property string $fail_reason
 * @property integer $status
 * @property string $des
 * @property string $customer_name
 * @property integer $is_star
 * @property integer $star_time
 * @property integer $clue_source
 * @property integer $clue_input_type
 * @property string $who_assign_name
 * @property integer create_type
 * @property integer create_card_time
 * @property string  create_person_name
 * @property integer view_times
 * @property integer phone_view_times
 * @property integer to_shop_view_times
 * @property integer to_home_view_times
 * @property string initial_intention_level
 */
class Clue extends \yii\db\ActiveRecord
{
    /**
     * 线索状态
     */
    const STATUS_CLUB = 0; // 线索客户
    const STATUS_WILL = 1; // 意向客户
    const STATUS_BOOK = 2; // 订车客户
    const STATUS_DEAL = 3; // 成交客户
    const STATUS_FAIL = 10; // 战败客户

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_clue';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'intention_id','buy_type', 'planned_purchase_time_id', 'spare_intention_id', 'shop_id', 'salesman_id', 'is_assign', 'who_assign_id', 'intention_level_id', 'create_time', 'last_view_time', 'is_fail', 'status', 'is_star', 'star_time', 'clue_source', 'clue_input_type'], 'integer'],
            [['sales_promotion_content', 'des','quoted_price', 'contrast_intention_id','initial_intention_level'], 'string'],
            [['intention_des', 'intention_level_des', 'fail_reason', 'customer_name', 'fail_tags', 'who_assign_name'], 'string', 'max' => 255],
            [['customer_phone'], 'string', 'max' => 15],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '自增主键',
            'customer_id' => '该条意向关联的客户id',
            'intention_des' => '意向车型',
            'intention_id' => '意向车型的id',
            'buy_type' => '购车方式id',
            'planned_purchase_time_id' => '拟购时间id',
            'quoted_price' => '报价信息',
            'sales_promotion_content' => '促销内容',
            'spare_intention_id' => '备选车型id',
            'contrast_intention_id' => '对比车型id',
            'shop_id' => '对接的门店id',
            'salesman_id' => '对接的销售人员id',
            'is_assign' => '是否分配了',
            'who_assign_id' => '谁分配的，记录分配人员的id',
            'customer_phone' => '客户的手机号',
            'intention_level_des' => '意向等级',
            'intention_level_id' => '意向等级id',
            'create_time' => '该条线索（意向）创建时间',
            'last_view_time' => '最后一次联系时间',
            'is_fail' => '是否战败：0 - 没有战败1 - 战败',
            'fail_reason' => '战败原因',
            'status' => '状态',
            'des' => '线索的描述',
            'customer_name' => '客户姓名',
            'is_star' => '是否收藏',
            'star_time' => '收藏时间',
            'clue_source' => '线索来源',
            'clue_input_type' => '线索入库的方式',
            'who_assign_name'=>'分配人名字',
            'initial_intention_level'=>'首次意向等级'
        ];
    }

    /**
     * 自动更新时间
     * @return array
     */
    //    public function behaviors()
    //    {
    //        return [
    //            [
    //                'class' => AttributeBehavior::className(),
    //                'attributes' => [
    //                    ActiveRecord::EVENT_BEFORE_INSERT => 'create_time'
    //                ],
    //                'value' => new Expression('NOW()'),
    //            ]
    //        ];
    //    }

    /**
     * 获取基础信息
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(),['id' => 'customer_id']);
    }

    /**
     * 根据意向登记，变化推送电话任务
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        /**
         * edited by liujx 2017-8-7 新增和修改的时候，修改了状态和意向等级，记录日志 start :
         *
         * 记录日志信息，方便查询问题
         */
        if ($insert ||
            isset($changedAttributes['status']) ||                  // 状态修改
            isset($changedAttributes['intention_level_id']) ||      // 意向等级修改
            isset($changedAttributes['salesman_id']) ||             // 顾问更改
            isset($changedAttributes['is_assign']) ||               // 分配状态更改
            isset($changedAttributes['is_fail'])                    // 战败状态更改
        ) {
//            Helper::logs('clue/'.date('Ymd').'-update.log', [
//                'time' => date('Y-m-d H:i:s'),
//                'clue' => $this->toArray(),
//                'changed' => $changedAttributes,
//                'controller' => Yii::$app->controller->id,
//                'action' => Yii::$app->controller->action->id,
//            ]);

            // 记录操作日志
            LogClue::create($this->toArray());
        }

        // end;

        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            if($this->intention_level_id) {
                $obj = new \common\logic\TaskLogic();
                $obj->newIntentionAddTask($this->id, $this->intention_level_id);
            }
        } else {
            if (isset($changedAttributes['intention_level_id'])) {
                $obj = new \common\logic\TaskLogic();
                if($changedAttributes['intention_level_id'] == null ) {
                    $obj->newIntentionAddTask($this->id, $this->intention_level_id);
                } else {
                    $obj->exchangeIntentionUpdateTask($this->id, $changedAttributes['intention_level_id'], $this->intention_level_id);
                }
            }
            
            if(PHP_SAPI === 'cli'){ //脚本运行没有后面的逻辑
                return ;
            }
            
            if (Yii::$app->cache->get('UPDATE_CLUE'.Yii::$app->user->getId())) {
                $data = [];
                foreach ($changedAttributes as $k => $attribute) {
                    //要存字段
                    if (in_array($k, ['intention_des', 'intention_level_des', 'customer_name', 'clue_source'])) {
                        $data[$this->label[$k]] = $attribute . ' --> ' . $this->$k;
                    }
                }
                //添加修改用户信息的商谈记录
                @TalkLogic::instance()->addNewTalk([
                    'talk_type' => 1,
                    'add_infomation' => json_encode($data),
                    'start_time' => time(),
                    'end_time' => time()
                ], $this, \Yii::$app->user->identity);
                //删除记录交谈中修改信息
                Yii::$app->cache->delete('UPDATE_CLUE'.Yii::$app->user->getId());
            }

            //商谈记录客户相关信息存入redis
            if (Yii::$app->cache->get('addTalk'.Yii::$app->user->getId())) {
                $data = json_decode(Yii::$app->cache->get('talk_change_' . Yii::$app->user->getId()), true) ?: [];
                foreach ($changedAttributes as $k => $attribute) {
                    //要存字段
                    if (in_array($k, ['intention_level_des', 'customer_name'])) {
                        $data[$this->label[$k]] = $attribute . ' --> ' . $this->$k;
                    }
                }
                if ($this->is_fail) {
                    $data['战败'] = $this->fail_reason;
                }//print_r($data);exit;
                Yii::$app->cache->set('talk_change_' . Yii::$app->user->getId(), json_encode($data));
            }
        }
    }

    /**
     * @var array
     */
    public $label = [
        'intention_des' => '意向车型',
        'intention_level_des' => "意向等级",
        'customer_name' => "客户姓名",
        'clue_source' => '客户来源'
    ];

    /**
     * 获取状态说明信息
     * @param null $status
     * @return array|mixed
     */
    public static function getStatusDesc($status = null)
    {
        $arrReturn = [
            self::STATUS_CLUB => '线索客户',
            self::STATUS_WILL => '意向客户',
            self::STATUS_BOOK => '订车客户',
            self::STATUS_DEAL => '成交客户',
            self::STATUS_FAIL => '战败客户',
        ];

        if ($status !== null && isset($arrReturn[$status])) {
            $arrReturn = $arrReturn[$status];
        }

        return $arrReturn;
    }

    /**
     * 批量导入线索信息
     * @param $array
     * array 字段信息说明
     * customer_name 客户姓名
     * customer_phone 客户手机号
     * area_id 客户地址ID
     * shop_id 门店id
     * shop_name 门店名
     * intention_id 意向车型的id
     * intention_des 意向车型 - 文字描述
     * des  线索的描述
     * clue_source 信息来源id
     * clue_input_type 客户来源id
     * @param int $createType 创建类型
     * @param string $createPersonName 创建用户名称
     * @return array ['status' => 状态, 'maxClueId' => 新增前最大线索ID]
     */
    public static function batchInsert($array, $createType = 1, $createPersonName = '')
    {
        $time = time();
        $db = Yii::$app->db;

        // 首先插入用户信息
        $arrPhone = [];
        $strSqlCustomerInsert = 'INSERT INTO `crm_customer` (`phone`, `name`, `create_time`, `area`) VALUES ';
        $strCustomerInsert = '';
        foreach ($array as &$value) {
            $value['customer_phone'] = (string)$value['customer_phone']; // 手机号为字符串

            // 特殊字符串、已经空白字符串的处理
            $value['customer_name'] = Helper::replace($value['customer_name']);     // 客户姓名
            $value['des'] = Helper::replace($value['des']);                         // 线索说明

            // 写入默认值
            if (empty($value['clue_source'])) $value['clue_source'] = 0;            // 信息来源
            if (empty($value['clue_input_type'])) $value['clue_input_type'] = 0;    // 渠道来源
            if (empty($value['area_id'])) $value['area_id'] = 0;                    // 地址信息
            if (empty($value['intention_id'])) $value['intention_id'] = 0;          // 意向车型

            // 拼接SQL
            $strCustomerInsert .= "('{$value['customer_phone']}', '{$value['customer_name']}', {$time}, {$value['area_id']}),";
            $arrPhone[] = $value['customer_phone'];
        }

        unset($value);

        // 拼接执行的SQL,并且执行
        $strInsert = $strSqlCustomerInsert.rtrim($strCustomerInsert, ','). ' ON DUPLICATE KEY UPDATE 
        `phone` = VALUES(`phone`),
        `name` = VALUES(`name`),
        `create_time` = VALUES(`create_time`)';
        $isReturn = $db->createCommand($strInsert)->execute();

        // 获取到新增之前最大的线索ID
        $intMaxId = self::find()->max('id');

        // 写入用户信息成功
        if ($isReturn > 0) {

            // 查询到新增客户的信息
            $customers = Customer::find()
                ->select(['id', 'phone'])
                ->where(['in', 'phone', $arrPhone])
                ->indexBy('phone')
                ->asArray()
                ->all();

            $strSqlClueInsert = 'INSERT INTO `crm_clue` (
              `create_type`, 
              `customer_id`, 
              `shop_id`, 
              `shop_name`, 
              `create_person_name`, 
              `customer_name`,
              `customer_phone`, 
              `intention_id`, 
              `intention_des`, 
              `des`, 
              `create_time`, 
              `clue_source`, 
              `clue_input_type`
        ) VALUES ';

            $strInsert = '';
            foreach ($array as $value) {
                if (isset($customers[$value['customer_phone']])) {
                    $customer = $customers[$value['customer_phone']];
                    $strInsert .= "(
                    {$createType},
                    {$customer['id']},
                    {$value['shop_id']},
                    '{$value['shop_name']}',
                    '{$createPersonName}',
                    '{$value['customer_name']}',
                    '{$value['customer_phone']}',
                    {$value['intention_id']},
                    '{$value['intention_des']}',
                    '{$value['des']}',
                    {$time},
                    {$value['clue_source']},
                    {$value['clue_input_type']}
                ),";
                }
            }

            if (!empty($strInsert)) {
                $strExecuteInsert = $strSqlClueInsert.rtrim($strInsert, ',');
                $isReturn = $db->createCommand($strExecuteInsert)->execute();
                if ($isReturn > 0) {
                    // 查询到刚才新增的线索信息
                    $clues = self::find()
                        ->select(['id', 'customer_id', 'customer_phone'])
                        ->where([
                            'and',
                            ['>', 'id', $intMaxId],
                            ['in', 'customer_phone', $arrPhone]
                        ])
                        ->asArray()
                        ->indexBy('customer_phone')
                        ->all();

                    if ($clues) {
                        $strSqlClueInsert = 'INSERT INTO `crm_log_clue` (
                          `create_type`, 
                          `customer_id`, 
                          `shop_id`, 
                          `shop_name`, 
                          `create_person_name`, 
                          `customer_name`,
                          `customer_phone`, 
                          `intention_id`, 
                          `intention_des`, 
                          `des`, 
                          `create_time`, 
                          `clue_source`, 
                          `clue_input_type`,
                          `created_at`,
                          `clue_id`
                    ) VALUES ';

                        $strInsert = '';

                        foreach ($array as $value) {
                            if (isset($clues[$value['customer_phone']])) {
                                $clue = $clues[$value['customer_phone']];
                                $strInsert .= "(
                                {$createType},
                                {$clue['customer_id']},
                                {$value['shop_id']},
                                '{$value['shop_name']}',
                                '{$createPersonName}',
                                '{$value['customer_name']}',
                                '{$value['customer_phone']}',
                                {$value['intention_id']},
                                '{$value['intention_des']}',
                                '{$value['des']}',
                                {$time},
                                {$value['clue_source']},
                                {$value['clue_input_type']},
                                {$time},
                                {$clue['id']}
                            ),";
                            }
                        }

                        if ($strInsert) {
                            $strExecuteInsert = $strSqlClueInsert.rtrim($strInsert, ',');
                            $isReturn = $db->createCommand($strExecuteInsert)->execute();
                        }
                    }
                }
            }
        }


        // 返回数据
        return [
            'status' => $isReturn,
            'maxClueId' => $intMaxId
        ];
    }

}
