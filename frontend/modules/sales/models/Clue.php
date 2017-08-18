<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/4/13
 * Time: 14:05
 */

namespace frontend\modules\sales\models;

use common\models\Customer;
use common\models\Intention;
use Yii;
use common\logic\ExcitationLogic;


/**
 * 销售助手激励相关
 *
 * Class Clue
 * @package frontend\modules\sales\models
 */
class Clue extends \common\models\Clue
{
    /**
     * 激励
     *
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            if($this->status == 0) {
                //新增线索激励
                ExcitationLogic::instance()->addExcitationLog(Yii::$app->user->identity, 1);
            }
            if($this->status == 1) {
                //新增意向客户激励
                ExcitationLogic::instance()->addExcitationLog(Yii::$app->user->identity, 3);
            }
        } else {
            if (isset($changedAttributes['status'])) {
                //线索转意向客户激励
                if ($changedAttributes['status'] == 0 && $this->status == 1) {
                    $this->create_card_time = $_SERVER['REQUEST_TIME'];
                    $this->save();
                    ExcitationLogic::instance()->addExcitationLog(Yii::$app->user->identity, 2);
                }
                if ($changedAttributes['status'] == 1 && $this->status == 2) {
                    //意向转订车
                    $this->intention_level_id = 6;
                    $this->intention_level_des = Intention::findOne(6)->name;
                    $this->save();
                    ExcitationLogic::instance()->addExcitationLog(Yii::$app->user->identity, 7);
                }
                if ($changedAttributes['status'] == 2 && $this->status == 3) {
                    //保有客户
                    //订车转交车
                    $this->intention_level_id = 8;
                    $this->intention_level_des = Intention::findOne(8)->name;
                    $this->save();
                    Customer::updateAll(['is_keep' => 1], ['id' => $this->customer_id]);
                    ExcitationLogic::instance()->addExcitationLog(Yii::$app->user->identity, 8);
                }
            }
            if(isset($changedAttributes['is_fail']) && $this->is_fail == 1) {
                //战败改变意向登记
                $this->intention_level_id = 7;
                $this->intention_level_des = Intention::findOne(7)->name;
                $this->save();
            }

        }
        return parent::afterSave($insert, $changedAttributes);
    }
}