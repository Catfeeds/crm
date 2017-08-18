<?php

namespace console\controllers;

use yii\console\Controller;
use common\models\PutTheCar;
use common\logic\CompanyUserCenter;
use common\logic\PhoneLetter;
use common\logic\NoticeTemplet;

/**
 * Class MentionTaskController 提车任务过期24小时提示
 * @package console\controllers
 */
class MentionTaskController extends Controller
{
    /**
     * 提车任务24小时没有顾问任务提醒这个门店所有顾问
     */
    public function actionIndex()
    {
        // 24小时之前
        $time = time() -  86400;

        // 查询没有认领的信息
        $arrMentionTask = PutTheCar::find()->where([
            'and',
            ['status' => PutTheCar::STATUS_UNDONE],
            ['new_salesman_id' => 0],
            ['<', 'next_handle_time', $time]
        ])->groupBy('new_shop_id')->all();

        echo PutTheCar::find()->where([
            'and',
            ['status' => PutTheCar::STATUS_UNDONE],
            ['new_salesman_id' => 0],
            ['<', 'next_handle_time', $time]
        ])->groupBy('new_shop_id')->createCommand()->getRawSql();

        echo ' number is: '.count($arrMentionTask). ' ';

        // 存在数据
        if ($arrMentionTask) {
            // 通过门店获取到这个门店的所以顾问信息
            $common = new CompanyUserCenter();

            $phoneObject = new PhoneLetter();

            $appObject = new NoticeTemplet();

            foreach ($arrMentionTask as $value) {
                // 修改时间，防止重复处理
                //
                /* @var $value \common\models\PutTheCar */
                $value->next_handle_time = time();
                if ($value->save()) {
                    // 获取门店下所有顾问
                    $users = $common->getShopSales($value->new_shop_id);
                    if ($users) {

                        $arrUserIds = [];

                        // 客户没有名字使用手机号
                        $strCustomerName = $value->customer_name ? $value->customer_name : $value->customer_phone;

                        foreach ($users as $val) {
                            // 推送语言短信
                            $phoneObject->sendMentionTaskShopVoice($val['phone'], $strCustomerName, $value->yu_ding_che_xing);

                            // 推送文字短信
                            $phoneObject->sendMentionTaskShopSMS($val['phone'], $strCustomerName, $value->yu_ding_che_xing);

                            $arrUserIds[] = $val['id'];

                            // 后台任务发送语音短信 暂停一秒
                            sleep(1);
                        }

                        // 推送APP通知
                        $appObject->sendNoticeByType(
                            'mentionTaskTimeOut',
                            0,
                            implode(',', $arrUserIds),
                            [
                                '[customer_name]' => $strCustomerName,
                                '[che_car_name]' => $value->yu_ding_che_xing
                            ],
                            'mention_task'
                        );
                    }
                }
            }
        }

        echo date('Y-m-d H:i:s').' OK'.PHP_EOL;
    }

}