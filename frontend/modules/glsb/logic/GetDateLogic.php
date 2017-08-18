<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/3/7
 * Time: 17:07
 */

namespace frontend\modules\glsb\logic;

use common\models\OrganizationalStructure;

/**
 * 公告相关逻辑
 * Class AnnouncementInboxLogic
 * @package frontend\modules\glsb\logic
 */
class GetDateLogic extends BaseLogic
{

    public function get_shop_list($organizational_structure_level,$area_id,$shop_id){

        //实例化组织结构模型
        $organizational_structure_model = new OrganizationalStructure();

        $info_list = array();

        //根据等级id判断需要展示的店列表
        if($organizational_structure_level == 1){

            $info_list = $organizational_structure_model->find()->select('id,name,pid')->asArray()->all();

        }elseif($organizational_structure_level == 2){

            $info_list1 = $organizational_structure_model->find()->select('id,name,pid')->where(['=','level',2])->andWhere(['=','id',$area_id])->asArray()->all();
            $info_list2 = $organizational_structure_model->find()->select('id,name,pid')->where(['=','level',3])->andWhere(['=','pid',$area_id])->asArray()->all();
            $info_list = array_merge($info_list1,$info_list2);

        }elseif($organizational_structure_level == 3){

            $info_list = $organizational_structure_model->find()->select('id,name,pid')->where(['=','level',3])->andWhere(['=','id',$shop_id])->asArray()->all();

        }

        return $info_list;
    }

}