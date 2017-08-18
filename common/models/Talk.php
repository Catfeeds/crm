<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%talk}}".
 *
 * @property integer $id
 * @property integer $castomer_id
 * @property integer $clue_id
 * @property integer $salesman_id
 * @property integer $shop_id
 * @property integer $create_time
 * @property string $talk_date
 * @property integer $start_time
 * @property integer $end_time
 * @property integer $talk_type
 * @property string $select_tags
 * @property string $content
 * @property string $imgs
 * @property string $voices
 * @property string $voices_times
 * @property string $vedios
 * @property string $order_id
 * @property string $add_infomation
 * @property integer $is_intention_change
 * @property integer $is_type
 */
class Talk extends \yii\db\ActiveRecord
{
    /**
     *  交谈方式的定义
     */
    const TALK_TYPE_ERP_TERMINATION = 25;   // ERP 终止合同-客户转为意向
    const TALK_TYPE_ERP_DELIVERY = 26;      // ERP 确认交车
    const TALK_TYPE_ORDER_CAR = 27;         // 电商订车


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%talk}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['castomer_id', 'clue_id', 'salesman_id', 'shop_id', 'create_time', 'start_time', 'end_time', 'talk_type', 'is_intention_change','is_type'], 'integer'],
            [['content', 'add_infomation'], 'string'],
            [['talk_date'], 'string', 'max' => 10],
            [['select_tags'], 'string', 'max' => 255],
            [['imgs', 'voices', 'vedios'], 'string', 'max' => 500],
            [['order_id'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '自增主键',
            'castomer_id' => '客户id',
            'clue_id' => '关联的意向id',
            'salesman_id' => '对接的销售人员的id',
            'shop_id' => '关联的门店id',
            'talk_date' => '交谈的日期 格式  年月日（2017-03-14）',
            'start_time' => '交谈开始时间',
            'end_time' => '交谈结束时间',
            'talk_type' => '交谈方式:
1. 修改客户信息
2. 来电
3. 去电
4. 短信
5. 到店-商谈 
6. 到店-订车
7. 到店-交车
8. 上门-商谈
9. 上门-订车
10. 上门-交车
11. 取消电话任务审批
12. 取消电话任务审批结果
13. 意向客户战败
14. 意向客户战败审批
15. 意向客户战败审批结果
16. 订车客户战败
17. 订车客户战败审批
18. 订车客户战败审批结果
19. 试驾
20. 战败客户激活
21. 休眠客户激活
22. 订车客户换车
23. 添加备注
24. 顾问重新分配',
            'select_tags' => '选中的标签id，多个用逗号分隔',
            'content' => '交谈内容',
            'imgs' => '图片附件地址，多个逗号分隔',
            'voices' => '音频附件地址，多个逗号分隔',
            'vedios' => '视频附件，多个逗号分隔',
            'order_id' => '关联订单表中的id',
        ];
    }

    /**
     * 交谈信息
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        // edited by liujx 当 add_infomation 有值的话，不用自动添加 end;
        if (empty($this->add_infomation) && $insert) {
            if (Yii::$app->cache->get('talk_change_'.Yii::$app->user->getId())) {
                $this->add_infomation = Yii::$app->cache->get('talk_change_' . Yii::$app->user->getId());
            }
        }

        return parent::beforeSave($insert);
    }
}
