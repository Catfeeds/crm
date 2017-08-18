<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/4/13
 * Time: 14:11
 */

namespace frontend\modules\sales\models;

use Yii;
use common\logic\ExcitationLogic;

/**
 * 销售助手激励相关
 *
 * Class Task
 * @package frontend\modules\sales\models
 */
class Task extends \common\models\Task
{
    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        if(!empty($changedAttributes)) {
            if (isset($changedAttributes['is_finish']) && $changedAttributes['is_finish'] == 1
                && $this->is_finish == 2 && $this->task_type == 1) {
                //完成电话任务
                ExcitationLogic::instance()->addExcitationLog(Yii::$app->user->identity, 4);
            }
        }
        return parent::afterSave($insert, $changedAttributes);
    }
}