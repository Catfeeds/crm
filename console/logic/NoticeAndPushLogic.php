<?php
/**
 * 跟进中的线索统计逻辑层
 * 作    者：王雕
 * 功    能：跟进中的线索统计逻辑层
 * 修改日期：2017-4-6
 */
namespace console\logic;
use common\logic\NoticeTemplet;
use common\models\Clue;
use common\models\Customer;
class NoticeAndPushLogic
{
    /**
     * 功    能：发送顾客生日提醒
     * 作    者：lzx
     * 修改日期：2017-04-26
     */
    public function CustomerBirthdayNotice()
    {
        //获取当天日期
        $date = date('m-d');
        //查询数据库 获取当前客户id列表
        $customer_list = Customer::find()->select('id')->where("birthday like '%{$date}'")->asArray()->all();

        //取出顾客id列表
        $customer_id_arr = array_column($customer_list,'id');

        //查询当前客户所属顾问id
        $clue_list = Clue::find()->select('id,customer_id,salesman_id')->where(['in','customer_id',$customer_id_arr])->asArray()->all();

        //循环推送消息
        $notice_templet = new NoticeTemplet();
        foreach ($clue_list as $item){

            //如果当前客户已被分配发送消息
            if(!empty($item['salesman_id'])){
                $notice_templet->customerBirthdayNotice($item['salesman_id'],$item['customer_id']);
            }
        }
    }
}
