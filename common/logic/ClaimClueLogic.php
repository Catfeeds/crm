<?php
/**
 * 检测超时的线索
 */
namespace common\logic;


use common\models\Clue;
use common\models\Talk;
use common\models\Task;
use common\models\User;
use Yii;

class ClaimClueLogic
{

    /**
     * 获取门店未分配线索  并重置 过期认领线索
     * @param $shop_id 门店id
     * $param $isType 1返回数组  2返回bool
     */
    public function getClaimClue($shop_id){

        //当前时间前30分钟
        $time = time() - (30*60);
        $where = "shop_id={$shop_id} 
        and status=0 
        and (
            salesman_id=0 
            or (assign_time < {$time} 
                and last_view_time=0 
                and who_assign_name = '个人认领')
        )";
        //查询数据
        $query = \common\models\Clue::find()->select('id as clue_id,customer_name,customer_phone,clue_source,create_time,intention_id,intention_des,des,clue_input_type')
            ->where($where);
//echo $query->createCommand()->getRawSql();exit;
        $list = $query->asArray()->all();

        $clue_id_str = implode(',',array_column($list,'clue_id'));

        if (!empty($clue_id_str)){
            //认领30分钟内未联系的线索初始化
            $sql = "update crm_clue set 
                    salesman_id = 0,
                    salesman_name = '',
                    who_assign_name = '',
                    who_assign_id = 0,
                    assign_time = 0,
                    is_assign = 0
                    where id IN ($clue_id_str)";
            if(\Yii::$app->db->createCommand($sql)->execute() === false) {
                return false;
            }else{
                return $list;
            }
        }else{
            return $list;
        }

    }

    /**
     * 判断当前线索是否可以操作
     * @param $clue_id
     * @return bool
     */
    public function checkHandleClue($clue_id,$salesman_id,$shop_id){

        $this->getClaimClue($shop_id);

        //认领线索
        $clue = \common\models\Clue::findOne($clue_id);
        if (!empty($clue)){
            //判断当前线索是否已被认领 如果已被认领返回提示信息
            if($clue->salesman_id == $salesman_id){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * 判断当前线索是否已被认领
     * @param $clue_id
     * @return bool
     */
    public function checkClaimClue($clue_id,$shop_id)
    {
        //重置认领线索
        $this->getClaimClue($shop_id);
        //认领线索
        $clue = \common\models\Clue::findOne($clue_id);

        //判断当前线索是否已被认领 如果已被认领返回提示信息
        if($clue->who_assign_name == '个人认领'){
            return true; //已被认领
        }else{
            return false;
        }
    }

    public function getClaimClues(){

        //当前时间前30分钟
        $time = time() - (30*60);
        $where = " status=0 
        and (
            salesman_id=0 
            or (assign_time < {$time} 
                and last_view_time=0 
                and who_assign_name = '个人认领')
        )";
        //查询数据
        $query = \common\models\Clue::find()->select('id as clue_id,customer_name,customer_phone,clue_source,create_time,intention_id,intention_des,des,clue_input_type')
            ->where($where);
        //echo $query->createCommand()->getRawSql();exit;
        $list = $query->asArray()->all();

        $clue_id_str = implode(',',array_column($list,'clue_id'));

        if (!empty($clue_id_str)){
            //认领30分钟内未联系的线索初始化
            $sql = "update crm_clue set 
                    salesman_id = 0,
                    salesman_name = '',
                    who_assign_name = '',
                    who_assign_id = 0,
                    assign_time = 0,
                    is_assign = 0
                    where id IN ($clue_id_str)";
            \Yii::$app->db->createCommand($sql)->execute();
        }

    }
}
?>