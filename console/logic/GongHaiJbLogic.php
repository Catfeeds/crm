<?php
/**
 * 脚本进入公海逻辑
 * 作    者：于凯
 */
namespace console\logic;
use common\helpers\Helper;
use common\models\Clue;
use common\models\Customer;
use common\models\Talk;
use Yii;
use frontend\modules\sales\logic\GongHaiGic;
class GongHaiJbLogic extends BaseLogic
{
    /**
     * 两个月无人跟进的客户 投放公海
     */
   public function twoMonth() {

       // 状态status=1 最后联系时间 last_view_time小于2个月前的数据 不是战败
       $time = time()-5184000;
       $clue = Clue::find()
           ->where("`status` = 1 AND `is_fail` = 0 AND ((`last_view_time` > 0 AND `last_view_time` <= {$time}) OR (`last_view_time` = 0 AND `create_time` <= {$time}))")
           ->asArray()
           ->all();

       if (!empty($clue)){

           $customer_ids = [];
           $ids = [];
           foreach ($clue as $v) {
               array_push($customer_ids, $v['customer_id']);
               array_push($ids, $v['id']);
           }
           //查询客户地址
           $customer = Customer::find()->select('id,area')->where(['in', 'id', $customer_ids])->asArray()->all();

           foreach ($clue as $k => $v) {
               foreach ($customer as $val) {
                   if ($v['customer_id'] == $val['id']) {
                       $clue[$k]['area_id'] = $val['area'];
                       break;
                   }
               }
           }

           if(GongHaiGic::addGongHai1($clue, 2)){
               //更新2个月前的数据为战败状态
               $data['is_fail'] = 1;
               $data['fail_reason'] = '2个月无人跟进';
               $data['intention_level_id'] = 7;
               $data['intention_level_des'] = \common\models\Intention::findOne(7)->name;
               Clue::updateAll($data,['in','id',$ids]);
               //增加商谈记录
               foreach ($clue as $v) {
                   $talk = new Talk();
                   $talk->castomer_id = $v['customer_id'];
                   $talk->clue_id = $v['id'];
                   $talk->salesman_id = $v['salesman_id'];
                   $talk->shop_id = $v['shop_id'];
                   $talk->create_time = time();
                   $talk->talk_date = date('Y-m-d');
                   $talk->start_time = time();
                   $talk->talk_type = 13;
                   $talk->add_infomation = json_encode(['战败原因'=>'2月无人跟进']);
                   $talk->save();
               }

           }
       }

   }

    /**
     * 24个小时无人认领的门店线索自动进入公海线索
     */
    public function twentyFour() {

        //状态status=0 销售人员salesman_id=0 未分配is_assign=0 创建时间<当前时间-24小时
        $time = time()-3600*24;

        $clue = Clue::find()
            ->where("status=0 and salesman_id=0 and is_assign=0 and create_time <= {$time}")
            ->asArray()
            ->all();
        if (!empty($clue)){

            $customer_ids = [];
            $ids = [];
            foreach ($clue as $v) {
                array_push($customer_ids, $v['customer_id']);
                array_push($ids, $v['id']);
            }
            //查询客户地址
            $customer = Customer::find()->select('id,area')->where(['in', 'id', $customer_ids])->asArray()->all();

            foreach ($clue as $k => $v) {
                foreach ($customer as $val) {
                    if ($v['customer_id'] == $val['id']) {
                        $clue[$k]['area_id'] = $val['area'];
                        break;
                    }
                }
            }
            foreach ($clue as $k => $v) {
                if (!isset($v['area_id']))
                    unset($clue[$k]);
            }
//$this->dump($clue);

            try {
                if(GongHaiGic::addGongHai1($clue, 3)){
                    //删除24小时门店无人认领的数据
                    Clue::deleteAll(['in','id',$ids]);
                }
            } catch (\Exception $e) {
                Helper::logs('/error/'.date('Ymd').'-gonghai-twentyFour-error.log', [
                    'time' => date('Y-m-d H:i:s'),
                    'error' => $e->getMessage(),
                ]);
            }

        }
    }
}
