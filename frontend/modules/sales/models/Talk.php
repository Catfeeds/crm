<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/4/13
 * Time: 14:09
 */

namespace frontend\modules\sales\models;

use Yii;
use common\logic\ExcitationLogic;

/**
 * 销售助手激励相关
 *
 * Class Talk
 * @package frontend\modules\sales\models
 */
class Talk extends \common\models\Talk
{
    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            if (in_array($this->talk_type, [5,6,7])) {
                //到店激励
                ExcitationLogic::instance()->addExcitationLog(Yii::$app->user->identity, 5);
            }
            if (in_array($this->talk_type, [8, 9, 10])) {
                //上门激励
                ExcitationLogic::instance()->addExcitationLog(Yii::$app->user->identity, 6);
            }
        }
        return parent::afterSave($insert, $changedAttributes);
    }
}