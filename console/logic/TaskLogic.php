<?php
/**
 * 任务相关脚本逻辑层
 * 作    者：王雕
 * 功    能：任务相关脚本逻辑层
 * 修改日期：2017-4-6
 */
namespace console\logic;
use common\logic\NoticeTemplet;
use common\models\Task;
use common\models\User;
use common\logic\PhoneLetter;
class TaskLogic extends BaseLogic
{
    /**
     * 功    能：每天提示销售顾问的具体电话任务完成进度
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function toldSalesTodayPhoneTaskInfo()
    {
        //有效顾问，有电话任务
        $arrWhere = [
            'and',
            ['=', 'u.is_delete', 0],//顾问有效
            ['=', 't.task_type', 1],//电话任务
            ['=', 't.task_date', date('Y-m-d')],//任务是今天
            ['>', 'u.phone', 0],//手机号为空的不通知，无法通知呀
        ];
        $arrHaving = [
            'and',
            ['>', 'phoneTaskNum', 0],
        ];
        $strSelect = 'count(1) as phoneTaskNum, t.salesman_id,u.name,u.phone';
        $query = Task::find()->select($strSelect)
                            ->from('crm_task as t')
                            ->leftJoin('crm_user as u', 't.salesman_id = u.id')
                            ->where($arrWhere)
                            ->groupBy('t.salesman_id')
                            ->having($arrHaving)
                            ->orderBy('phoneTaskNum desc');
//        echo $query->createCommand()->getRawSql();
        $arrList = $query->asArray()->all();
        //查找这些顾问的电话任务完成情况
        $arrSalesmanIds = array_map(function($v){return $v['salesman_id'];}, $arrList);
        $arrFinishWhere = [
                    'and',
                    ['in', 'salesman_id', $arrSalesmanIds],
                    ['=', 'is_finish', 2],//2 为已完成
                    ['=', 'task_type', 1],//电话任务
                    ['=', 'task_date', date('Y-m-d')],//任务是今天
        ];
        $arrFinishListTmp = Task::find()->select('count(1) as finishNum, salesman_id')
                ->where($arrFinishWhere)
                ->groupBy('salesman_id')
                ->asArray()
                ->all();
        foreach($arrFinishListTmp as $v)
        {
            $arrFinishList[$v['salesman_id']] = $v['finishNum'];
        }
        //一个一个的发送短信
        $objPhoneLetter = new PhoneLetter();
        foreach($arrList as $val)
        {
            $strPhone = trim($val['phone']);
            $salesName = trim($val['name']);
            $intTotalNum = intval($val['phoneTaskNum']);
            $intFinishNum = isset($arrFinishList[$val['salesman_id']]) ? intval($arrFinishList[$val['salesman_id']]) : 0;
            $objPhoneLetter->salesTodayPhoneTaskNotice($strPhone, $salesName, $intTotalNum, $intFinishNum);
        }
    }

    /**
     * 查询到顾问今天没有完成的电话任务数
     * @return array 数组中包含 为完成数，顾问信息
     * [
     *    'number' => $value['number'],
     *   'uid' => $value['salesman_id'],
     *   'salesman_id' => $value['salesman_id'],
     *   'name' => $arrSalesman['name'],
     *   'phone' => $arrSalesman['phone']
     * ]
     */
    private function getTaskToUsers()
    {
        $arrReturn = [];

        // 查询顾问没有完成的电话任务数
        $array = Task::find()
            ->select('COUNT(*) AS `number`, `salesman_id`')
            ->where([
                'task_type' => 1,  // 电话任务
                'is_finish' => 1,  // 没有完成
                'is_cancel' => 0,  // 没有取消
                'task_date' => date('Y-m-d')   // 今天的
            ])
            ->asArray()
            ->groupBy('salesman_id')
            ->all();

        if ($array) {
            $arrSalesmanIds = array_column($array, 'salesman_id');
            // 查询用户表
            $users = User::find()
                ->select(['id', 'name', 'phone'])
                ->where([
                    'and',
                    ['id' => $arrSalesmanIds],
                    ['!=', 'phone', ''],
                    ['=', 'is_delete', 0]
                ])
                ->indexBy('id')
                ->asArray()
                ->all();

            // 处理返回数据
            foreach ($array as $value) {
                if (isset($users[$value['salesman_id']])) {
                    $arrSalesman = $users[$value['salesman_id']];
                    $arrReturn[] = [
                        'number' => $value['number'],
                        'uid' => $value['salesman_id'],
                        'salesman_id' => $value['salesman_id'],
                        'name' => $arrSalesman['name'],
                        'phone' => $arrSalesman['phone']
                    ];
                }
            }
        }

        return $arrReturn;
    }

    /**
     * 处理用户信息
     * @param $funName
     */
    private function handleUsers($funName)
    {
        $arrUsers = $this->getTaskToUsers();
        if ($arrUsers) {
            array_map($funName, $arrUsers);
        }
    }

    /**
     * 早上9点，给还有没有完成的电话任务的顾问推送app通知
     */
    public function handleMorningNine()
    {
        $message = new NoticeTemplet();
        $this->handleUsers(function($value) use ($message) {
            // 发送推送
            $message->sendNoticeByType('MorningNine', 0, (string)$value['salesman_id'], [
                '[salesman_name]' => $value['name'],
                '[number]' => $value['number']
            ], 'client_order_fail');
        });
    }

    /**
     * 下午一点，给还有没有完成的电话任务的顾问推送文字短信
     */
    public function handleAfternoonOne()
    {
        $objPhone = new PhoneLetter();
        $message = new NoticeTemplet();
        $this->handleUsers(function($value) use ($objPhone, $message) {
            $params = [
                '[salesman_name]' => $value['name'],
                '[number]' => $value['number']
            ];

            // 发送文字短信
            $objPhone->sendMessageByTmpId($value['phone'], 45, $params);

            // 发送推送
            $message->sendNoticeByType('AfternoonOne', 0, (string)$value['salesman_id'], $params, 'client_order_fail');
        });
    }

    /**
     * 晚上6点，给还有没有完成的电话任务的顾问推送文字和语音短信
     */
    public function handleNightSix()
    {
        // 发送短信类
        $objPhone = new PhoneLetter();
        $message = new NoticeTemplet();

        $this->handleUsers(function($value) use ($objPhone, $message) {
            $params = [
                '[salesman_name]' => $value['name'],
                '[number]' => $value['number']
            ];

            // 发送文字短信
            $objPhone->sendMessageByTmpId($value['phone'], 46, $params);

            sleep(1);
            // 发送语言短信
            $objPhone->sendMessageByTmpId($value['phone'], 47, $params);

            // 发送推送
            $message->sendNoticeByType('NightSix', 0, (string)$value['salesman_id'], $params, 'client_order_fail');
        });
    }
}
