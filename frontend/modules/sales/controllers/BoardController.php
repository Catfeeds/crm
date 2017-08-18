<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/3/31
 * Time: 13:53
 */

namespace frontend\modules\sales\controllers;

use common\common\PublicMethod;
use frontend\modules\sales\logic\BoardLogic;
use frontend\modules\sales\logic\ClueLogic;
use frontend\modules\sales\logic\TalkLogic;
use Yii;

/**
 * 看板相关接口
 *
 * Class BoardController
 * @package frontend\modules\sales\controllers
 */
class BoardController extends AuthController
{
    /**
     * 看板首页@todo
     *
     * @return  array
     */
    public function actionIndex()
    {
        $pData = $this->getPData();

        if (empty($pData) || !isset($pData['type']) || !isset($pData['date_time'])) {
            return $this->paramError();
        }
        $user = Yii::$app->user->identity;
        $clue = BoardLogic::instance()->getData($pData, $user);

        $time = PublicMethod::data_update_time();
        $clue['update_time'] = $time;
        if ($clue) {
            return $clue;
        }
        return $this->paramError(ClueLogic::instance()->errorCode, ClueLogic::instance()->getError());
    }

    /**
     * 线索跟进记录
     *
     * @请求参数 p type 请求类型 month | day
     * @请求参数 p date_time 请求时间 2017-04 | 2017-04-06
     */
    public function actionClue()
    {
        $pData = $this->getPData();
        if (empty($pData) || !isset($pData['type']) || !isset($pData['date_time'])) {
            return $this->paramError();
        }
        $user = Yii::$app->user->identity;
        $clue = ClueLogic::instance()->getClueListByTime($pData, $user);
        if ($clue) {
            return $clue;
        }
        return $this->paramError(ClueLogic::instance()->errorCode, ClueLogic::instance()->getError());
    }

    /**
     * 电话任务完成率
     *
     * @return array
     */
    public function actionTaskPhone()
    {
        $user = Yii::$app->user->identity;
        $pData = $this->getPData();
        if (empty($pData) || !isset($pData['type']) || !isset($pData['date_time'])) {
            return $this->paramError();
        }
        $task = BoardLogic::instance()->getTaskRate($pData, $user);
        return $task;
    }

    /**
     * 商谈记录
     *
     * @return array
     */
    public function actionTalkLog()
    {
        $user = Yii::$app->user->identity;
        $pData = $this->getPData();
        if (empty($pData) || !isset($pData['type']) || !isset($pData['date_time'])) {
            return $this->paramError();
        }
        $data = TalkLogic::instance()->getTalkLog($pData, $user);
        if ($data) {
            return $data;
        }
        return $this->paramError(TalkLogic::instance()->errorCode, TalkLogic::instance()->getError());
    }

    /**
     * 意向客户相关统计
     * @return array
     */
    public function actionIntent()
    {
        $pData = $this->getPData();
        if (empty($pData) || !isset($pData['type']) || !isset($pData['date_time'])) {
            return $this->paramError();
        }
        $user = Yii::$app->user->identity;
        $data = BoardLogic::instance()->getIntent($pData, $user);
        if ($data) {
            return $data;
        }
        return $this->paramError(ClueLogic::instance()->errorCode, ClueLogic::instance()->getError());
    }

    /**
     * 交车统计
     */
    public function actionGiveCar()
    {
        $pData = $this->getPData();
        if (empty($pData) && !isset($pData['date_time'])) {
            return $this->paramError();
        }
        $user = Yii::$app->user->identity;
        $data = BoardLogic::instance()->getGiveCar($pData, $user);
        if ($data) {
            return $data;
        }
        return $this->paramError(ClueLogic::instance()->errorCode, ClueLogic::instance()->getError());
    }


}