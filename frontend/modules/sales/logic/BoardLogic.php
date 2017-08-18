<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/4/12
 * Time: 16:36
 */

namespace frontend\modules\sales\logic;


use common\models\Order;
use common\models\PutTheCar;
use common\models\TjClueGenjinzhong;
use common\models\TjIntentionGenjinzhong;
use common\models\TjIntentionLevelCount;
use common\models\TjIntentionTalkTagCount;
use common\models\TjJichushuju;
use common\models\TjWeijiaoche;
use common\models\User;
use yii\helpers\ArrayHelper;

/**
 * 看板相关逻辑
 *
 * Class BoardLogic
 * @package frontend\modules\sales\logic
 */
class BoardLogic extends BaseLogic
{

    public function getData($pData, $user)
    {
        if ($pData['type'] == 'day') {
            $dateTime = date('Y-m-d', strtotime($pData['date_time']));
            return $this->getDayData($dateTime, $user);
        } else {
            $dateTime = date('Y-m', strtotime($pData['date_time']));
            return $this->getMonthData($dateTime, $user);
        }
    }

    /**
     * 看板首页（天）
     *
     * @param $dateTime
     * @param $user
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getDayData($dateTime, $user)
    {
        $model= TjJichushuju::find()->where([
            'create_date' => $dateTime,
            'shop_id' => $user->shop_id,
            'salesman_id' => $user->id
        ])->one();
        $intent = TjIntentionGenjinzhong::find()->where([
            'shop_id' => $user->shop_id,
            'salesman_id' => $user->id
        ])->one();
        $weiJiaoche = TjWeijiaoche::find()->where([
            'shop_id' => $user->shop_id,
            'salesman_id' => $user->id
        ])->one();
        $clueGengJin = TjClueGenjinzhong::find()->where([
            'shop_id' => $user->shop_id,
            'salesman_id' => $user->id
        ])->one();
        $clueGengJinNum = !empty($clueGengJin) ? $clueGengJin->num : 0;

        // edited by liujx 2017-07-26 添加未分配提车任务记录数据 start :
        $intNotAssignMentionCarTask = $this->getNotAssignMentionCarTask($user->shop_id, $dateTime, 'day');
        // end

        if(empty($model)) {
            $data = [
                'clue' => [
                    'add' => 0,
                    'not_follow' => 0
                ],
                'phone_task' => 0,
                'intent' => [
                    'add' => 0,
                    'follow' => !empty($intent) ? intval($intent->num) : 0
                ],
                'talk' => 0,
                'trade' => 0,
                'not_trade' => !empty($weiJiaoche) ? intval($weiJiaoche->num) : 0,
                'is_failed' => 0,
                'not_assign_mention_car' => $intNotAssignMentionCarTask,
            ];
        } else {
            $data = [
                'clue' => [
                    'add' => intval($model->new_clue_num),
                    'not_follow' => intval($clueGengJinNum)
                ],
                'phone_task' => @round($model->finish_phone_task_num / $model->phone_task_num * 100),
                'intent' => [
                    'add' => intval($model->new_intention_num),
                    'follow' => !empty($intent) ? intval($intent->num) : 0
                ],
                'talk' => intval($model->talk_num),
                'trade' => intval($model->chengjiao_num),
                'not_trade' => !empty($weiJiaoche) ? intval($weiJiaoche->num) : 0,
                'is_failed' => intval($model->fail_num),
                'not_assign_mention_car' => $intNotAssignMentionCarTask,
            ];
        }
        return $data;
    }

    /**
     * 看板首页（月）
     *
     * @param $dateTime
     * @param $user
     * @return array
     */
    public function getMonthData($dateTime, $user)
    {
        $model= TjJichushuju::find()->select([
            'sum(new_clue_num) as clue_num',
            'sum(finish_phone_task_num)/sum(phone_task_num) as rate',
            'sum(new_intention_num) as intention_num',
            'sum(talk_num) as talk_num_total',
            'sum(chengjiao_num) as chengjiao_num_total',
            'sum(fail_num) as fail_num_total',
        ])->where([
            'salesman_id' => $user->id,
            'shop_id' => $user->shop_id
        ])->andWhere([
            'like', 'create_date', $dateTime
        ])->asArray()->one();
        $intent = TjIntentionGenjinzhong::find()->where([
            'salesman_id' => $user->id,
            'shop_id' => $user->shop_id
        ])->one();
        $weiJiaoche = TjWeijiaoche::find()->where([
            'salesman_id' => $user->id,
            'shop_id' => $user->shop_id
        ])->one();
        $clueGengJin = TjClueGenjinzhong::find()->where([
            'salesman_id' => $user->id,
            'shop_id' => $user->shop_id
        ])->one();
        $clueGengJinNum = !empty($clueGengJin) ? $clueGengJin->num : 0;

        // edited by liujx 2017-07-26 添加未分配提车任务记录数据 start :
        $intNotAssignMentionCarTask = $this->getNotAssignMentionCarTask($user->shop_id, $dateTime, 'month');
        // end

        $data = [
            'clue' => [
                'add' => intval($model['clue_num']),
                'not_follow' => $clueGengJinNum
            ],
            'phone_task' => round($model['rate'] * 100),
            'intent' => [
                'add' => intval($model['intention_num']),
                'follow' => !empty($intent) ? intval($intent->num) : 0
            ],
            'talk' => intval($model['talk_num_total']),
            'trade' => intval($model['chengjiao_num_total']),
            'not_trade' => !empty($weiJiaoche) ? intval($weiJiaoche->num) : 0,
            'is_failed' => intval($model['fail_num_total']),
            'not_assign_mention_car' => $intNotAssignMentionCarTask,
        ];
        return $data;
    }

    /**
     * 根据门店查询未分配的任务
     *
     * @param integer $shopId 查询门店
     * @param string $time 查询时间
     * @param string $type day|month
     * @return int
     */
    public function getNotAssignMentionCarTask($shopId, $time, $type)
    {
        // 开始时间
        $start = strtotime($time);

        // 根据类型查询月报和日报
        $end = $type === 'day' ? $start + 86400 : strtotime(date('Y-m-01', $start) . ' +1 month');

        return PutTheCar::getNotMentionCount((int)$shopId, $start, $end);
    }

    /**
     * 电话完成率
     *
     * @param $pData
     * @param User $user
     *
     * @return array
     */
    public function getTaskRate($pData, $user)
    {
        if ($pData['type'] == 'day') {
            $dateTime = date('Y-m', strtotime($pData['date_time']));
            return $this->getMonthTaskRate($dateTime, $user);
        } else {
            $dateTime = date('Y', strtotime($pData['date_time']));
            return $this->getYearTaskRate($dateTime, $user);
        }
    }

    /**
     * 月电话任务
     *
     * @param $dateTime
     * @param User $user
     * @return array
     */
    public function getMonthTaskRate($dateTime, $user)
    {
        $data = TjJichushuju::find()->select([
            'create_date','salesman_id', '(finish_phone_task_num/phone_task_num) as rate'
        ])->where([
            'shop_id' => $user->shop_id,
        ])->andWhere([
            'like', 'create_date', $dateTime
        ])->asArray()->all();
        $self = [];
        $other =[];
        foreach ($data as $v) {
            if ($v['salesman_id'] == $user->id) {
                $self[$v['create_date']] = round($v['rate'] * 100) ? : 0;
            }
            $other[$v['create_date']][] = round($v['rate'] * 100) ? : 0;
        }
        //return $self;
        if($dateTime == date('Y-m')) {
            $endDate = date('d');
        } else {
            $endDate = date('t', strtotime($dateTime));
        }
        $return = [];
        $return['chart']['legend'] = ['我的完成率', '店平均完成率', '店最高完成率'];
        for($i = 1; $i <= $endDate; $i++) {
            $time = strval($dateTime.'-'.str_pad($i,2,"0",STR_PAD_LEFT));

            $selfRate = isset($self[$time]) ? $self[$time] : 0;
            if (isset($other[$time])) {
                $shop = intval(round(array_sum($other[$time]) / count($other[$time])));
                $most = intval(max($other[$time]));
            } else {
                $shop = 0;
                $most = 0;
            }
            $return['list'][] = [
                'date' => $time,
                'self' => $selfRate,
                'shop' => $shop,
                'most' => $most
            ];
            $return['chart']['data'][0][] = $selfRate;
            $return['chart']['data'][1][] = $shop;
            $return['chart']['data'][2][] = $most;
            $return['chart']['x'][] = $time;
        }
        return $return;
    }

    /**
     * 月电话任务
     *
     * @param $dateTime
     * @param $user
     * @return array
     */
    public function getYearTaskRate($dateTime, $user)
    {
        $data = TjJichushuju::find()->select([
            'DATE_FORMAT(create_date, "%Y-%m") as date','salesman_id', '(sum(finish_phone_task_num)/sum(phone_task_num)) as rate'
        ])->where([
            'shop_id' => $user->shop_id,
        ])->andWhere([
            'like', 'create_date', $dateTime
        ])->groupBy(['date', 'salesman_id'])->asArray()->all();
        $self = [];
        $other =[];
        foreach ($data as $v) {
            if ($v['salesman_id'] == $user->id) {
                $self[$v['date']] = round($v['rate'] * 100) ? : 0;
            }
            $other[$v['date']][] = round($v['rate'] * 100) ? : 0;
        }
        if($dateTime == date('Y')) {
            $endDate = date('m');
        } else {
            $endDate = date('12', strtotime($dateTime));
        }
        $return = [];
        $return['chart']['legend'] = ['我的完成率', '店平均完成率', '店最高完成率'];
        for($i = 1; $i <= $endDate; $i++) {
            $time = $dateTime.'-'.str_pad($i,2,"0",STR_PAD_LEFT);
            $selfRate = isset($self[$time]) ? intval($self[$time]) : 0;
            if (isset($other[$time])) {
                $shop = intval(round(array_sum($other[$time]) / count($other[$time])));
                $most = intval(max($other[$time]));
            } else {
                $shop = 0;
                $most = 0;
            }
            $return['list'][] = [
                'date' => $time,
                'self' => $selfRate,
                'shop' => $shop,
                'most' => $most
            ];
            $return['chart']['data'][0][] = $selfRate;
            $return['chart']['data'][1][] = $shop;
            $return['chart']['data'][2][] = $most;
            $return['chart']['x'][] = $time;
        }

        return $return;
    }

    /**
     * 意向
     *
     * @param $pData
     * @param $user
     * @return array
     */
    public function getIntent($pData, $user)
    {
        if ($pData['type'] == 'day') {
            $dateTime = date('Y-m', strtotime($pData['date_time']));
            return [
                'intent' => $this->getMonthIntent($dateTime, $user),
                'intentLevel' => $this->getIntentLevel($user),
                'tags' => $this->getTags($user)
            ];
        } else {
            $dateTime = date('Y', strtotime($pData['date_time']));
            return [
                'intent' => $this->getYearIntent($dateTime, $user),
                'intentLevel' => $this->getIntentLevel($user),
                'tags' => $this->getTags( $user)
            ];
        }
    }

    /**
     * 按天显示
     *
     * @param $dateTime
     * @param $user
     * @return array
     */
    public function getMonthIntent($dateTime, $user)
    {
        $data = TjJichushuju::find()->select([
            'create_date','salesman_id', 'new_intention_num'
        ])->where([
            'shop_id' => $user->shop_id,
        ])->andWhere([
            'like', 'create_date', $dateTime
        ])->asArray()->all();
        $self = [];
        $other =[];
        foreach ($data as $v) {
            if ($v['salesman_id'] == $user->id) {
                $self[$v['create_date']] = $v['new_intention_num'] ? : 0;
            }
            $other[$v['create_date']][] = $v['new_intention_num'] ? : 0;
        }
        if($dateTime == date('Y-m')) {
            $endDate = date('d');
        } else {
            $endDate = date('t', strtotime($dateTime));
        }
        $return = [];
        $return['chart']['legend'] = ['我的新增客户', '店平均新增客户', '店最高新增客户'];
        for($i = 1; $i <= $endDate; $i++) {
            $time = $dateTime.'-'.str_pad($i,2,"0",STR_PAD_LEFT);
            $selfRate = isset($self[$time]) ? intval($self[$time]) : 0;
            if (isset($other[$time])) {
                $shop = intval(round(array_sum($other[$time]) / count($other[$time])));
                $most = intval(max($other[$time]));
            } else {
                $shop = 0;
                $most = 0;
            }
            $return['list'][] = [
                'date' => $time,
                'self' => $selfRate,
                'shop' => $shop,
                'most' => $most
            ];
            $return['chart']['data'][0][] = $selfRate;
            $return['chart']['data'][1][] = $shop;
            $return['chart']['data'][2][] = $most;
            $return['chart']['x'][] = $time;
        }

        return $return;
    }

    /**
     * 按月显示意向
     *
     * @param $dateTime
     * @param $user
     * @return array
     */
    public function getYearIntent($dateTime, $user)
    {
        $data = TjJichushuju::find()->select([
            'DATE_FORMAT(create_date, "%Y-%m") as date','salesman_id', 'sum(new_intention_num) as num'
        ])->where([
            'shop_id' => $user->shop_id,
        ])->andWhere([
            'like', 'create_date', $dateTime
        ])->groupBy(['date', 'salesman_id'])->asArray()->all();
        $self = [];
        $other =[];
        foreach ($data as $v) {
            if ($v['salesman_id'] == $user->id) {
                $self[$v['date']] = $v['num'] ? : 0;
            }
            $other[$v['date']][] = $v['num'] ? : 0;
        }
        if($dateTime == date('Y')) {
            $endDate = date('m');
        } else {
            $endDate = date('12', strtotime($dateTime));
        }
        $return = [];
        $return['chart']['legend'] = ['我的新增客户', '店平均新增客户', '店最高新增客户'];
        for($i = 1; $i <= $endDate; $i++) {
            $time = $dateTime.'-'.str_pad($i,2,"0",STR_PAD_LEFT);
            $selfRate = isset($self[$time]) ? intval($self[$time]) : 0;
            if (isset($other[$time])) {
                $shop = intval(round(array_sum($other[$time]) / count($other[$time])));
                $most = intval(max($other[$time]));
            } else {
                $shop = 0;
                $most = 0;
            }
            $return['list'][] = [
                'date' => $time,
                'self' => $selfRate,
                'shop' => $shop,
                'most' => $most
            ];
            $return['chart']['data'][0][] = $selfRate;
            $return['chart']['data'][1][] = $shop;
            $return['chart']['data'][2][] = $most;
            $return['chart']['x'][] = $time;
        }

        return $return;
    }

    /**
     * 意向等级
     *
     * @param $user
     * @return array
     */
    public function getIntentLevel($user)
    {
        $data = TjIntentionLevelCount::find()->select([
           'name' , 'sum(num) as count'
        ])->innerJoin('crm_dd_intention', 'crm_dd_intention.id = crm_tj_intention_level_count.intention_level_id')->where([
            'shop_id' => $user->shop_id,
            'salesman_id' => $user->id,
         ])->groupBy('intention_level_id')->asArray()->all();
        $total = array_sum(ArrayHelper::getColumn($data, 'count'));
        $return = [];
        foreach ($data as $k => $v) {
            $data[$k]['rate'] = round($v['count'] / $total * 100);
            $return['chart'][] = [
                'name' => $v['name'],
                'value' => $v['count']
            ];
        }
        $return['list'] = $data;
        return $return;
    }

    /**
     * 标签
     *
     * @param  User $user
     * @return array
     */
    public function getTags($user)
    {
        $data = TjIntentionTalkTagCount::find()->select([
            'name' , 'sum(num) as count'
        ])->innerJoin('crm_tags', 'crm_tags.id = crm_tj_intention_talk_tag_count.tag_id')->where([
            'shop_id' => $user->shop_id,
            'salesman_id' => $user->id,
        ])->groupBy('tag_id')->orderBy('count DESC')->limit(7)->asArray()->all();
        $total = array_sum(ArrayHelper::getColumn($data, 'count'));
        $return = [];
        foreach ($data as $k => $v) {
            $data[$k]['rate'] = round($v['count'] / $total * 100);
            $return['chart'][] = [
                'name' => $v['name'],
                'value' => intval($v['count'])
            ];
        }
        $return['list'] = $data;
        return $return;
    }

    /**
     * 交车
     *
     * @param $pData
     * @param $user
     * @return array|bool
     */
    public function getGiveCar($pData, $user)
    {
        $dateTime = date('Y', strtotime($pData['date_time']));
        $data = TjJichushuju::find()->select([
            'DATE_FORMAT(create_date, "%Y-%m") as date', 'sum(chengjiao_num) as num'
        ])->where([
            'shop_id' => $user->shop_id,
            'salesman_id' => $user->id,
        ])->andWhere([
            'like', 'create_date', $dateTime
        ])->groupBy('date')->asArray()->all();
        if($dateTime == date('Y')) {
            $endDate = date('m');
        } elseif($dateTime < date('Y')) {
            $endDate = 12;
        } else {
            return false;
        }
        $return = [];
        $dataMap = ArrayHelper::map($data, 'date', 'num');
        for($i = 1; $i <= $endDate; $i++) {
            $time = $dateTime.'-'.str_pad($i,2,"0",STR_PAD_LEFT);
            $self = isset($dataMap[$time]) ? intval($dataMap[$time]) : 0;
            $return['chart']['data'][0][] = $self;
            $return['chart']['x'][] = $time;
            $return['list'][] = [
                'date' => $time,
                'num' => $self
            ];
        }
        $return['history_give_car'] = $this->getHistoryGiveCar($user);
        return $return;
    }

    /**
     * 本月交车客户
     *
     * @param $user
     * @return array
     */
    public function getHistoryGiveCar($user)
    {
        $order = Order::find()->where([
            'salesman_id' => $user->id,
            'shop_id' => $user->shop_id,
            'status' => 6
        ])->andWhere([
            'like', 'from_unixtime(last_pudate_time, "%Y-%m")', date('Y-m')
        ])->orderBy('last_pudate_time DESC')->all();
        $data = [];
        foreach ($order as $v) {
            $data[] = [
                'customer_name' => $v->car_owner_name,
                'car_type_name' => $v->car_type_name,
                'delivery_price' => $v->delivery_price
            ];
        }
        return $data;
    }
}