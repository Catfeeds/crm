<?php
/**
 * 销售目标中的完成数的统计（完成数等于订车数，财务到账的，结果以统计的订车数表中的值为准）
 * 作    者：王雕
 * 功    能：销售目标中的完成数的统计
 * 修改日期：2017-4-26
 */
namespace console\logic;
use common\models\TjSalesTarget;
use common\models\TjDingcheNum;
class SalesTargetLogic extends BaseLogic
{
    /**
     * 功    能：销售指标完成数
     * 作    者：王雕
     * 修改日期：2017-4-26
     */
    public function finishNum()
    {
        //从order表中实时统计所有的订单，财务到账，未战败按照店铺和时间聚合
        $strSql = "SELECT
                        count(1) AS num,
                        from_unixtime(cai_wu_dao_zhang_time,'%Y-%c') AS year_and_month,
                        shop_id
                    FROM
                        crm_order
                    WHERE
                        cai_wu_dao_zhang_time > 0
                        and `status` <> 4
                    GROUP BY
                        shop_id,
                        year_and_month";
        $list = \Yii::$app->db->createCommand($strSql)->queryAll();
        //先把所有点的所有指标完成数据全部置为空
        TjSalesTarget::updateAll(['finish_num'=> 0]);
        if($list)
        {
            foreach($list as $val)
            {
                $arrCondition = [
                    'year_and_month' => $val['year_and_month'],
                    'shop_id' => $val['shop_id'],
                ];
                $objSalesTarget = TjSalesTarget::findOne($arrCondition);
                if($objSalesTarget)
                {
                    $objSalesTarget->setAttribute('finish_num', $val['num']);
                    $objSalesTarget->save();
                    unset($objSalesTarget);
                }
            }
        }
    }
}
