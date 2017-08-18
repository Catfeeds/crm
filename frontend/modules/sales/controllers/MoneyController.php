<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/4/6
 * Time: 10:03
 */

namespace frontend\modules\sales\controllers;



use frontend\modules\sales\logic\MoneyLogic;


/**
 * 钱包相关接口
 *
 * Class MoneyController
 * @package frontend\modules\sales\controllers
 */
class MoneyController extends AuthController
{
    /**
     * 收入明细
     */
    public function actionIncome()
    {
        $pData = $this->getPData();
        return MoneyLogic::instance()->income(\Yii::$app->user->identity, $pData);
    }

    /**
     * 支出明细
     */
    public function actionExpenditure()
    {
        $pData = $this->getPData();
        return MoneyLogic::instance()->expenditure(\Yii::$app->user->identity, $pData);
    }

    /**
     * 体现申请
     */
    public function actionCashApply()
    {
        $pData = $this->getPData();
        if (!isset($pData['money'])) {
            return $this->paramError();
        }
        return MoneyLogic::instance()->cashApply(\Yii::$app->user->identity, $pData);
    }
}