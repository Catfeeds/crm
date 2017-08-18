<?php

namespace console\controllers;

use common\models\Task;
use common\models\User;
use \yii\console\Controller;
use common\logic\PhoneLetter;
use common\logic\NoticeTemplet;
use yii\helpers\ArrayHelper;

/**
 * Class TaskController 电话回访提醒
 * @package console\controllers
 */
class PhoneVisitController extends Controller
{
    /**
     * 电话回访任务，没有完成，过时1小时提醒
     */
    public function actionIndex()
    {
        // 时间一个小时之前
        $time = time() - 3600;

        // 查询到设置回访时间小于指定时间的电话任务(没有完成、没有取消)
        $all = Task::find()->where([
            'and',
            ['is_finish' => 1], // 未完成
            ['is_cancel' => 0],  // 没有取消
            ['task_type' => 1],  // 电话任务
            ['is_visit' => 1],   // 设置回访
            ['task_date' => date('Y-m-d')], // 当天的电话任务
            ['<', 'next_handle_time', $time], // (上一次处理时间)小于指定时间
        ])->all();

        echo Task::find()->where([
            'and',
            ['is_finish' => 1], // 未完成
            ['is_cancel' => 0],  // 没有取消
            ['task_type' => 1],  // 电话任务
            ['is_visit' => 1],   // 设置回访
            ['task_date' => date('Y-m-d')], // 当天的电话任务
            ['<', 'next_handle_time', $time], // (上一次处理时间)小于指定时间
        ])->createCommand()->getRawSql();

        echo ' number is : '.count($all).' ';

        // 存在数据
        if ($all) {
            // 查询销售信息
            $arrUserIds = ArrayHelper::getColumn($all, 'salesman_id');
            $users = User::find()->select(['name', 'phone', 'id'])->where(['id' => $arrUserIds])->indexBy('id')->all();
            // 实例化短信发送类
            $phoneObject = new PhoneLetter();

            // 实例化APP推送类
            $appObject = new NoticeTemplet();

            foreach ($all as $value) {
                /* @var $value \common\models\Task */
                $value->next_handle_time = time();

                // 数据修改成功，并且顾问存在
                if ($value->save() && isset($users[$value->salesman_id])) {
                    /* @var $salesman \common\models\User */
                    $salesman = $users[$value->salesman_id];

                    // 格式化时间
                    $date = date('Y年m月d日H时i分', $value->visit_time);

                    // 发送文字短信
                    $phoneObject->sendPhoneReminderSMS($salesman->phone, $salesman->name, $date);
                    // 发送语言短信
                    $phoneObject->sendPhoneReminderVoice($salesman->phone, $salesman->name, $date);
                    // APP推送
                    $appObject->sendNoticeByType('phoneTaskTimeOut', 0, (string)$value->salesman_id, [
                        '[salesman_name]' => $salesman->name,
                        '[date]' => $date
                    ]);
                }
            }
        }

        echo date('Y-m-d H:i:s').' OK'.PHP_EOL;
    }
}