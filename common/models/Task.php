<?php

namespace common\models;

use common\logic\ExcitationLogic;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%task}}".
 *
 * @property integer $id
 * @property integer $shop_id
 * @property integer $clue_id
 * @property integer $customer_id
 * @property integer $salesman_id
 * @property string $task_date
 * @property integer $task_time
 * @property integer $is_finish
 * @property string $task_from
 * @property string $task_des
 * @property integer $start_time
 * @property integer $end_time
 * @property string $cancel_reason
 * @property integer $is_cancel
 * @property integer $task_type
 * @property integer $is_visit
 * @property integer $visit_time
 * @property integer $next_handle_time
 */
class Task extends ActiveRecord
{
    /**
     * 是否回访
     */
    const IS_VISIT_YES = 1; // 是
    const IS_VISIT_NOT = 0; // 否 默认值

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%task}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                'shop_id', 'clue_id', 'customer_id',
                'salesman_id', 'task_time', 'is_finish',
                'start_time', 'end_time', 'is_cancel',
                'task_type', 'is_visit', 'visit_time',
                'next_handle_time'
            ], 'integer'],
            [['task_date'], 'string', 'max' => 10],
            [['task_from', 'cancel_reason','task_des'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '自增主键',
            'shop_id' => '任务关联的门店的id',
            'clue_id' => '关联的线索id',
            'customer_id' => '关联的客户id',
            'salesman_id' => '关联的销售id',
            'task_date' => '任务触发日期',
            'task_time' => '任务触发时间',
            'is_finish' => '是否完成',
            'task_from' => '任务来源',
            'start_time' => '做任务的开始时间',
            'end_time' => '完成任务时的结束时间',
            'cancel_reason' => '取消任务的原因',
            'is_cancel' => '是否取消该任务：0 - 不取消1 - 取消',
            'task_type' => '任务类型：1 - 电话任务2 - 到店任务3 - 上门任务',
            'is_visit' => '是否回访',
            'visit_time' => '回访时间',
            'next_handle_time' => '下一次处理时间',
        ];
    }

    /**
     * 下次预约
     *
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert){
            if (Yii::$app->cache->get('addTalk'.Yii::$app->user->getId())) {
                $data = json_decode(Yii::$app->cache->get('talk_change_' . Yii::$app->user->getId()), true) ?: [];
                //要存字段
                if ($this->task_type == 2) {
                    $data['下次到店'] = $this->task_date;
                }
                if ($this->task_type == 3) {
                    $data['下次上门'] = $this->task_date;
                }
                Yii::$app->cache->set('talk_change_' . Yii::$app->user->getId(), json_encode($data));
            }
        }
    }
}
