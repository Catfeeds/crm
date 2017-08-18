<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/3/15
 * Time: 14:12
 */

namespace frontend\modules\sales\controllers;

use common\models\Clue;
use frontend\modules\sales\models\Talk;
use Yii;
use frontend\modules\sales\logic\TalkLogic;

/**
 * 交谈记录
 *
 * Class TalkController
 * @package frontend\modules\sales\controllers
 */
class TalkController extends AuthController
{
    /**
     * 添加交谈记录
     * @return array | null |boolean
     */
    public function actionAdd()
    {
        $pData = $this->getPData();

        $customer = isset($pData['customer']) ? $pData['customer'] : [];
        $talk     = isset($pData['talk']) ? $pData['talk'] : [];
        $order    = isset($pData['order']) ? $pData['order'] : [];
        $theCarOrder = isset($pData['theCarOrder']) ? $pData['theCarOrder'] : [];

        if (isset($customer['status'])) {
            unset($customer['status']);
        }

        if (!isset($customer['clue_id']) || !$talk['talk_type']) {
            return $this->paramError(4000,'clue_id或者talk_type不能为空！');
        }

        if (isset($talk['talk_type']) && $talk['talk_type']) {
            if (in_array($talk['talk_type'], [2, 3, 23])) {
                $talk['start_time'] = time();
                $talk['end_time']   = time();
            } else {
//                if (!$talk['start_time'] || !$talk['end_time']) {  //17-06-09
                if (!$talk['start_time']) {
                    return $this->paramError();
                }
            }
        }
        if ($talk['talk_type'] == 7 || $talk['talk_type'] == 10) {
            if (empty($order) && $order['order_id']) {
                return $this->paramError(400, '交车需要包含订单信息');
            }
        }else if ($talk['talk_type'] == 6 || $talk['talk_type'] == 9) {
            if (empty($theCarOrder)) {
                return $this->paramError(400, '需要包含订车信息');
            }
        }

        //战败数据转到$customer里
        if (isset($talk['fail_tags'])) {
            $customer['fail_tags'] = $talk['fail_tags'];
        }
        //给客户添加交谈记录
        $taskId = null;
        if (isset($pData['task_id']) && $pData['task_id'])
            $taskId = $pData['task_id'];
        $talk = TalkLogic::instance()->talkAdd($customer, $talk, \Yii::$app->user->identity, $order, $taskId,$theCarOrder);
        if ($talk) {
            return true;
        } else {
            Yii::$app->params['code']    = TalkLogic::instance()->getErrorCode();
            Yii::$app->params['message'] = TalkLogic::instance()->getError();
            return false;
        }
    }

    public function actionGetTalk()
    {

        $data = $this->getPData();
        return TalkLogic::instance()->getTalk($data['clue_id']);
    }

    /**
     * 来电接口， 记录来电次数
     *
     *
     */
    public function actionAddCall()
    {
        $pData = $this->getPData();
        if (!isset($pData['phone']) || !$pData['phone']) {
            return $this->paramError();
        }
        $user = \Yii::$app->user->identity;
        //查找线索信息
        $list = Clue::find()->where("is_fail=0 and status != 3 and customer_phone={$pData['phone']} and shop_id={$user->shop_id} ")->asArray()->one();
        $data = (object)array();
        if (!empty($list)) {
            foreach ($list as $v) {
                $data->clue_id                  = intval($list['id']);
                $data->customer_id              = strval($list['customer_id']);
                $data->intention_des            = strval($list['intention_des']);
                $data->intention_id             = intval($list['intention_id']);
                $data->buy_type                 = intval($list['buy_type']);
                $data->planned_purchase_time_id = intval($list['planned_purchase_time_id']);
                $data->quoted_price             = strval($list['quoted_price']);
                $data->sales_promotion_content  = strval($list['sales_promotion_content']);
                $data->spare_intention_id     = intval($list['spare_intention_id']);
                $data->contrast_intention_id    = intval($list['contrast_intention_id']);
                $data->shop_name                = strval($list['shop_name']);
                $data->shop_id                  = intval($list['shop_id']);
                $data->salesman_name            = strval($list['salesman_name']);
                $data->salesman_id              = intval($list['salesman_name']);
                $data->is_assign                = intval($list['salesman_id']);
                $data->assign_time              = intval($list['assign_time']);
                $data->who_assign_name          = strval($list['who_assign_name']);
                $data->who_assign_id            = intval($list['who_assign_id']);
                $data->customer_phone           = strval($list['customer_phone']);
                $data->intention_level_des      = strval($list['intention_level_des']);
                $data->intention_level_id       = intval($list['intention_level_id']);
                $data->create_card_time         = intval($list['create_card_time']);
                $data->create_type              = intval($list['create_type']);
                $data->create_person_name       = strval($list['create_person_name']);
                $data->create_time              = intval($list['create_time']);
                $data->last_view_time           = intval($list['last_view_time']);
                $data->last_fail_time           = intval($list['last_fail_time']);
                $data->is_fail                  = intval($list['is_fail']);
                $data->fail_tags                = strval($list['fail_tags']);
                $data->fail_reason              = strval($list['fail_reason']);
                $data->status                   = intval($list['status']);
                $data->des                      = strval($list['des']);
                $data->customer_name            = strval($list['customer_name']);
                $data->star_time                = intval($list['star_time']);
                $data->clue_source              = intval($list['clue_source']);
                $data->clue_input_type          = intval($list['clue_input_type']);
                $data->view_times               = intval($list['view_times']);
                $data->phone_view_times         = intval($list['phone_view_times']);
                $data->to_home_view_times       = intval($list['to_home_view_times']);
                $data->to_shop_view_times       = intval($list['to_shop_view_times']);
            }
        }
        return $data;
    }

}
