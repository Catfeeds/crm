<?php
/**
 * Created by PhpStorm.
 * User: yukai
 * Date: 2017/1/13
 */

namespace frontend\modules\sales\logic;

use Yii;
use common\models\Share;
/**
 * 分享相关逻辑
 */
class ShareLogic extends BaseLogic
{

    /**
     * 增加分享信息
     * @param $guid  唯一标识符
     * @return bool  返回结果
     */
    public function add($id = null)
    {
        if (!empty($id)) {
            $count = Share::find()->where(['=','id',$id])->count();
            if ($count > 0) {
                return $id;
            }
        }
        $share = new Share();
        $share->token =  md5(uniqid(mt_rand(), true));
         if($share->save()) {
             return $share->id;
         } else {
             return false;
         }

    }
}
