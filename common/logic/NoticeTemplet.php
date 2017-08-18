<?php
/**
 * 功    能：短信发送功能类
 * 作    者：王雕
 * 修改日期：2017-3-22
 */
namespace common\logic;
use common\models\Clue;
use common\models\Customer;
use common\models\Order;
use common\models\User;

class NoticeTemplet
{
    // 定义类型对应的发送信息
    private $arrSendTypeDesc = [
        // 客户直接在电商下单-完成支付
        'orderSuccess' => [
            'type' => 110,
            'title' => '车城电商订单需跟进-客户已支付',
            'notice_title' => '车城电商订单需跟进-客户已支付'
        ],

        // 客户直接在电商下单-未完成支付
        'orderFail' => [
            'type' => 110,
            'title' => '车城电商客户下单未支付',
            'notice_title' => '车城电商客户下单未支付'
        ],

        // 客户在CRM下单-支付超时
        'crmOrderFail' => [
            'type' => 110,
            'title' => '您的客户在电商下单超时未支付',
            'notice_title' => '您的客户在电商下单超时未支付'
        ],

        // 客服在分享页点击分享-之前有线索信息
        'shareOldClue' => [
            'type' => 112,
            'title' => '您的客户[customer_name]提交了一条新的购车意向[intention_des],请及时跟进',
            'notice_title' => '您的客户[customer_name]提交了一条新的购车意向[intention_des],请及时跟进',
        ],

        // 客服在分享页点击分享-之前没有线索信息
        'shareInsertClue' => [
            'type' => 111,
            'title' => '收到一条从您微信分享页的线索,请及时跟进',
            'notice_title' => '收到一条从您微信分享页的线索,请及时跟进',
        ],

        // 提车任务提醒
        'mentionTask' => [
            'type' => 114,
            'title' => '门店有一条提车任务（客户[customer_name]，[che_car_name]）需要顾问跟进，请及时领取',
            'notice_title' => '门店有一条提车任务（客户[customer_name]，[che_car_name]）需要顾问跟进，请及时领取',
        ],

        // 提车任务过期24小时提醒
        'mentionTaskTimeOut' => [
            'type' => 115,
            'title' => '门店有一条提车任务（客户[customer_name]，[che_car_name]）超过24小时无人跟进，请及时领取',
            'notice_title' => '门店有一条提车任务（客户[customer_name]，[che_car_name]）超过24小时无人跟进，请及时领取',
        ],

        // 电话提醒
        'phoneTaskTimeOut' => [
            'type' => 106,
            'title' => '您好，[salesman_name]，原定于[date]的电话回访已经过期，请及时回访',
            'notice_title' => '您好，[salesman_name]，原定于[date]时的电话回访已经过期，请及时回访',
        ],

        // 早上9点电话任务提醒
        'MorningNine' => [
            'type' => 116,
            'title' => '[salesman_name]，早上好，您今日有[number]条电话任务，请及时完成',
            'notice_title' => '[salesman_name]，早上好，您今日有[number]条电话任务，请及时完成',
        ],

        // 下午一点电话任务提醒
        'AfternoonOne' => [
            'type' => 117,
            'title' => '[salesman_name]，下午好，您今天还有x条电话任务未完成，请及时完成',
            'notice_title' => '[salesman_name]，下午好，您今天还有x条电话任务未完成，请及时完成',
        ],

        // 晚点6点电话任务提醒
        'NightSix' => [
            'type' => 118,
            'title' => '[salesman_name]，晚上好，您今日还有[number]条电话任务未完成，请在今天完成以免客户流失',
            'notice_title' => '[salesman_name]，晚上好，您今日还有[number]条电话任务未完成，请在今天完成以免客户流失',
        ],
    ];

    /**
     * sendNoticeByType() 通过类型给推送消息
     * @param  string  $type        推送类型
     * @param  int     $intSendId   推送人
     * @param  string  $strUsers    推送给谁的用户信息
     * @param  array   $params      内容中需要替换的信息
     * @param  string  $func        按照功能发送推送 android 的内容
     * @param  array   $other       其他配置信息
     * @return bool
     */
    public function sendNoticeByType($type, $intSendId, $strUsers, $params = [], $func = '', $other = [])
    {
        $mixReturn = false;
        if ($strUsers && !empty($this->arrSendTypeDesc[$type])) {
            $title = $this->arrSendTypeDesc[$type]['title'];
            $strNoticeTitle = $this->arrSendTypeDesc[$type]['notice_title'];
            if (!empty($params)) {
                $find = array_keys($params);
                $replace = array_values($params);
                $title = str_replace($find, $replace, $title);
                $strNoticeTitle = str_replace($find, $replace, $strNoticeTitle);
            }

            // 内容信息
            if (!isset($other['content'])) $other['content'] = '';

            // 消息的其它内容
            if (!isset($other['notice_param'])) $other['notice_param'] = '';

            $mixReturn = $this->androidPushMsg($intSendId, $strUsers, $title, $strNoticeTitle, $this->arrSendTypeDesc[$type]['type'], $other['content'], $other['notice_param'], 'sales', $func);
        }

        return $mixReturn;
    }

    /**
     * 功    能：店长分配的线索到顾问通知
     * @param $who_assign_id               int          分配人id
     * @param $salesman_id                 int          顾问id
     * @param $clue_num                    int             添加的线索的条数（一般是1）
     * @return bool                        boolen          true - 发送成功 / false - 发送失败
     * 作    者：lzx
     * 修改日期：2017-3-31
     */
    public function assignClueSendNotice($who_assign_id, $salesman_id ,$clue_num){

        $who_assign_info = User::find()->where(['=','id',$who_assign_id])->one();
        if(empty($who_assign_info->name)){
            return false;
        }
        $who_assign_name = $who_assign_info->name;

        $push_title = '收到'.$who_assign_name.'分配的'.$clue_num.'条线索';
        $notice_title = '收到'.$who_assign_name.'分配的'.$clue_num.'条线索';
        $notice_type = 101;
        return $this->androidPushMsg($who_assign_id,$salesman_id,$push_title,$notice_title,$notice_type);
    }

    /**
     * 功    能：企业激励通知
     * @param $salesman_id               int          接收顾问id
     * @param $reward                    string             奖励内容
     * @return bool                      boolen          true - 发送成功 / false - 发送失败
     * 作    者：lzx
     * 修改日期：2017-3-31
     */
    public function encourageNotice($salesman_id ,$reward){

        $who_assign_id = 0;//系统
        $push_title = '企业激励-'.$reward;
        $notice_title ='企业激励-'.$reward;
        $notice_type = 102;
        return $this->androidPushMsg($who_assign_id,$salesman_id,$push_title,$notice_title,$notice_type);
    }

    /**
     * 功    能：店长推送电话任务到顾问通知
     * @param $who_assign_id                 int          分配人id
     * @param $salesman_id                   int          顾问id
     * @param $clue_num                      int             电话任务条数（一般是1）
     * @return bool                          boolen          true - 发送成功 / false - 发送失败
     * 作    者：lzx
     * 修改日期：2017-3-31
     */
    public function telephoneTaskNotice($who_assign_id, $salesman_id ,$clue_num){

        if($who_assign_id == 0){
            $who_assign_name = '总部';
        }else{
            $who_assign_info = User::find()->where(['=','id',$who_assign_id])->one();
            if(empty($who_assign_info->name)){
                return false;
            }
            $who_assign_name = $who_assign_info->name;
        }

        $push_title = '收到'.$who_assign_name.'推送的'.$clue_num.'条电话任务';
        $notice_title = '收到'.$who_assign_name.'推送的'.$clue_num.'条电话任务';
        $notice_type = 103;
        return $this->androidPushMsg($who_assign_id,$salesman_id,$push_title,$notice_title,$notice_type);
    }

    /**
     * 功    能：客户生日提醒通知
     * @param $salesman_id                int          顾问id
     * @param $customer_name              string          客户姓名
     * @return bool                       boolen          true - 发送成功 / false - 发送失败
     * 作    者：lzx
     * 修改日期：2017-3-31
     */
    public function customerBirthdayNotice($salesman_id ,$customer_id){

        $customer_info = Customer::find()->select('name,phone')->where(['=','id',$customer_id])->one();

        $customer_name = $customer_info->name;

        $who_assign_id = 0;//系统
        //今天是用户【XX】的生日
        $push_title = '今天是用户【'.$customer_name.'】的生日';
        $notice_title = '今天是用户【'.$customer_name.'】的生日';
        $notice_param = json_encode(['phone'=>$customer_info->phone]);
        $notice_type = 104;
        return $this->androidPushMsg($who_assign_id,$salesman_id,$push_title,$notice_title,$notice_type,'',$notice_param);
    }

    /**
     * 功    能：重新分配客户通知
     * @param $who_assign_id               int          分配人id
     * @param $salesman_id                 int          顾问id
     * @param $clue_num                    int             电话任务条数（一般是1）
     * @return bool                        boolen          true - 发送成功 / false - 发送失败
     * 作    者：lzx
     * 修改日期：2017-3-31
     */
    public function reassignReminderNotice($who_assign_id, $salesman_id ,$clue_num,$clue_id_str = 1373){

        $who_assign_info = User::find()->where(['=','id',$who_assign_id])->one();
        if(empty($who_assign_info->name)){
            return false;
        }
        $who_assign_name = $who_assign_info->name;

        $clue_id_arr = explode(',',$clue_id_str);
        $clue_info_arr = Clue::find()->select('customer_name,customer_phone,status,customer_id')
            ->where(['in','id',$clue_id_arr])->asArray()->all();

        $push_title = '收到'.$who_assign_name.'重新分配的'.$clue_num.'位用户';
        $notice_title = '收到'.$who_assign_name.'重新分配的'.$clue_num.'位用户';

        if(count($clue_info_arr) > 1){
            $content = '';
            foreach ($clue_info_arr as $item){
                if($item['status'] == 0){
                    $status = '线索状态';
                }elseif ($item['status'] == 1){
                    $status = '意向客户';
                }elseif ($item['status'] == 2){
                    $status = '订车客户';
                }elseif ($item['status'] == 3){
                    $status = '成交客户';
                }
                $content .= "客户姓名：{$item['customer_name']}\n客户电话：{$item['customer_phone']}\n分配前状态：{$status}\n";
            }
            $notice_param = '';
        }else{

            $clue_info = $clue_info_arr[0];
            if($clue_info['status'] == 0){
                $status = '线索状态';
            }elseif ($clue_info['status'] == 1){
                $status = '意向客户';
            }elseif ($clue_info['status'] == 2){
                $status = '订车客户';
            }elseif ($clue_info['status'] == 3){
                $status = '成交客户';
            }
            $content = "客户姓名：{$clue_info['customer_name']}\n客户电话：{$clue_info['customer_phone']}\n分配前状态：{$status}";
            $notice_param = json_encode(['clue_id'=>intval($clue_id_arr[0])]);
        }

        $notice_type = 105;
        return $this->androidPushMsg($who_assign_id,$salesman_id,$push_title,$notice_title,$notice_type,$content,$notice_param);
    }

    /**
     * 功    能：生成预订单通知
     * @param $salesman_id               int          顾问id
     * @return bool                      boolen          true - 发送成功 / false - 发送失败
     * 作    者：lzx
     * 修改日期：2017-3-31
     */
    public function preOrderNotice($salesman_id,$order_id = 18){

        $order_info = Order::find()->select('car_owner_name,car_owner_phone,car_type_name,customer_id,clue_id')->where(['=','id',$order_id])->one();

        $who_assign_id = 0;//系统
        $push_title = '车城电商新订单需跟进-处理中';
        $notice_title = '车城电商新订单需跟进-处理中';
        $content = "客户姓名：{$order_info->car_owner_name}\n客户电话：{$order_info->car_owner_phone}\n预定车型：{$order_info->car_type_name}";
        $notice_param = json_encode(['clue_id'=>intval($order_info->clue_id)]);
        $notice_type = 106;
        return $this->androidPushMsg($who_assign_id,$salesman_id,$push_title,$notice_title,$notice_type,$content,$notice_param);
    }

    /**
     * 功    能：生成未支付订单通知
     * @param $salesman_id                int          顾问id
     * @return bool                       boolen          true - 发送成功 / false - 发送失败
     * 作    者：lzx
     * 修改日期：2017-3-31
     */
    public function unpaidOrderNotice($salesman_id,$order_id = 18){

        $order_info = Order::find()->select('car_owner_name,car_owner_phone,car_type_name,customer_id,clue_id')->where(['=','id',$order_id])->one();

        $who_assign_id = 0;//系统
        $push_title = '车城电商订单已生成-客户未支付';
        $notice_title = '车城电商订单已生成-客户未支付';
        $content = "客户姓名：{$order_info->car_owner_name}\n客户电话：{$order_info->car_owner_phone}\n预定车型：{$order_info->car_type_name}";
        $notice_param = json_encode(['clue_id'=>intval($order_info->clue_id)]);
        $notice_type = 107;
        return $this->androidPushMsg($who_assign_id,$salesman_id,$push_title,$notice_title,$notice_type,$content,$notice_param);
    }

    /**
     * 功    能：生成已支付订单通知
     * @param $salesman_id           int          顾问id
     * @return bool                  boolen          true - 发送成功 / false - 发送失败
     * 作    者：lzx
     * 修改日期：2017-3-31
     */
    public function paidOrderNotice($salesman_id,$order_id = 18){

        $order_info = Order::find()->select('car_owner_name,car_owner_phone,car_type_name,customer_id,clue_id')->where(['=','id',$order_id])->one();

        $who_assign_id = 0;//系统
        $push_title = '车城电商订单已生成-客户已支付';
        $notice_title = '车城电商订单已生成-客户已支付';

        $content = "客户姓名：{$order_info->car_owner_name}\n客户电话：{$order_info->car_owner_phone}\n预定车型：{$order_info->car_type_name}";
        $notice_param = json_encode(['clue_id'=>intval($order_info->clue_id)]);
        $notice_type = 108;
        return $this->androidPushMsg($who_assign_id,$salesman_id,$push_title,$notice_title,$notice_type,$content,$notice_param);
    }

    /**
     * 功    能：生成财务确认订单通知
     * @param $salesman_id             int          顾问id
     * @return bool                    boolen          true - 发送成功 / false - 发送失败
     * 作    者：lzx
     * 修改日期：2017-3-31
     */
    public function financialConfirmationOrderNotice($salesman_id,$order_id = 18){

        $order_info = Order::find()->select('car_owner_name,car_owner_phone,car_type_name,customer_id,clue_id')->where(['=','id',$order_id])->one();

        $who_assign_id = 0;//系统
        $push_title = '车城电商订单处理成功-财务已确认到账';
        $notice_title = '车城电商订单处理成功-财务已确认到账';
        $content = "客户姓名：{$order_info->car_owner_name}\n客户电话：{$order_info->car_owner_phone}\n预定车型：{$order_info->car_type_name}";
        $notice_param = json_encode(['clue_id'=>intval($order_info->clue_id)]);
        $notice_type = 109;
        return $this->androidPushMsg($who_assign_id,$salesman_id,$push_title,$notice_title,$notice_type,$content,$notice_param);
    }

    /**
     * 功    能：订单处理失败通知
     * @param $salesman_id                int          顾问id
     * @return bool                       boolen          true - 发送成功 / false - 发送失败
     * 作    者：lzx
     * 修改日期：2017-3-31
     */
    public function processingFailureNotice($salesman_id,$order_id = 18,$failDes = null){
        $order_info = Order::find()->select('car_owner_name,car_owner_phone,fail_reason,customer_id,clue_id')->where(['=','id',$order_id])->one();

        $who_assign_id = 0;//系统
        $push_title = '订单失败';
        $notice_title = '订单失败';
        $content = "客户姓名：{$order_info->car_owner_name}\n客户电话：{$order_info->car_owner_phone}\n失败原因：{$failDes}";
        $notice_param = json_encode(['clue_id'=>intval($order_info->clue_id)]);
        $notice_type = 110;
        return $this->androidPushMsg($who_assign_id,$salesman_id,$push_title,$notice_title,$notice_type,$content,$notice_param);
    }

    /**
     * 功    能：进销存中止通知
     * @param $salesman_id               int          顾问id
     * @return bool                      boolen          true - 发送成功 / false - 发送失败
     * 作    者：lzx
     * 修改日期：2017-3-31
     */
    public function invoicingSuspensionNotice($salesman_id,$order_id = 18){

        $order_info = Order::find()->select('car_owner_name,car_owner_phone,customer_id,clue_id')->where(['=','id',$order_id])->one();

        $who_assign_id = 0;//系统
        $push_title = '进销存系统中止订单，该意向已战败';
        $notice_title = '进销存系统中止订单，该意向已战败';

        $content = "客户姓名：{$order_info->car_owner_name}\n客户电话：{$order_info->car_owner_phone}";
        $notice_param = json_encode(['clue_id'=>intval($order_info->clue_id)]);

        $notice_type = 111;
        return $this->androidPushMsg($who_assign_id,$salesman_id,$push_title,$notice_title,$notice_type,$content,$notice_param);
    }

    /**
     * 功    能：进销存交车通知
     * @param $salesman_id        int          顾问id
     * @return bool               boolen          true - 发送成功 / false - 发送失败
     * 作    者：lzx
     * 修改日期：2017-3-31
     */
    public function invoicingDeliveryCarNotice($salesman_id,$order_id = 18){
        $order_info = Order::find()->select('car_owner_name,car_owner_phone,customer_id,clue_id')->where(['=','id',$order_id])->one();

        $who_assign_id = 0;//系统
        $push_title = '进销存系统确认交车，该客户变为交车客户';
        $notice_title = '进销存系统确认交车，该客户变为交车客户';
        $content = "客户姓名：{$order_info->car_owner_name}\n客户电话：{$order_info->car_owner_phone}";
        $notice_param = json_encode(['clue_id'=>intval($order_info->clue_id)]);
        $notice_type = 112;
        return $this->androidPushMsg($who_assign_id,$salesman_id,$push_title,$notice_title,$notice_type,$content,$notice_param);
    }

    /**
     * 功    能：总部添加线索给店长通知
     * @param $salesman_id         int        顾问id
     * @param $clue_num            int        线索数量
     * @param $clue_source         string     线索来源
     * @return bool                boolen     true - 发送成功 / false - 发送失败
     * 作    者：lzx
     * 修改日期：2017-3-31
     */
    public function headquartersAddClueNotice($salesman_id,$clue_num,$clue_source){

        $who_assign_id = 0;//系统
        $push_title = '收到集团下发的'.$clue_num.'条待分配线索，渠道来源：'.$clue_source.'，请尽快分配。';
        $notice_title = '收到集团下发的'.$clue_num.'条待分配线索。';
        $notice_type = 201;
        return $this->androidPushMsg($who_assign_id,$salesman_id,$push_title,$notice_title,$notice_type,'','','glsb');
    }

    /**
     * 功    能：总部导入线索给店长通知
     * @param $salesman_id        int          店长id
     * @param $clue_num           int          线索数量
     * @return bool               boolen       true - 发送成功 / false - 发送失败
     * 作    者：lzx
     * 修改日期：2017-3-31
     */
    public function headquartersImportClueNotice($salesman_id,$clue_num){

        $who_assign_id = 0;//系统
        $push_title = '收到集团下发的'.$clue_num.'条待分配线索，请尽快分配。';
        $notice_title = '收到集团下发的'.$clue_num.'条待分配线索。';
        $notice_type = 201;
        return $this->androidPushMsg($who_assign_id,$salesman_id,$push_title,$notice_title,$notice_type,'','','glsb');
    }

    /**
     * 功    能：总部导入线索给店员认领通知
     * @param $salesman_id        int          门店id
     * @param $clue_num           int          线索数量
     * @return bool               boolen       true - 发送成功 / false - 发送失败
     * 作    者：lzx
     * 修改日期：2017-6-6
     */
    public function headquartersImportClueClaimNotice($shop_id,$clue_num){

        //根据shop_id查询店员id
        $salesman_list = User::find()->select('id')->where(['=','shop_id',$shop_id])->andWhere(['=','is_delete',0])->asArray()->all();
        $salesman_id_arr = array_column($salesman_list,'id');
        $salesman_id_str = implode(',',$salesman_id_arr);

        $who_assign_id = 0;//系统
        $push_title = '收到集团下发的'.$clue_num.'条线索，请及时认领';
        $notice_title = '收到集团下发的'.$clue_num.'条线索，请及时认领';
        $notice_type = 203;
        return $this->androidPushMsg($who_assign_id,$salesman_id_str,$push_title,$notice_title,$notice_type,'','','sales','clue_claim');
    }

    /**
     * 功    能：逾期线索提醒
     * @param $who_assign_id      int          分配人id
     * @param $salesman_id        int          顾问id
     * @return bool               boolen       true - 发送成功 / false - 发送失败
     * 作    者：lzx
     * 修改日期：2017-3-31
     */
    public function overdueClueRemind($who_assign_id,$salesman_id){

        $push_title = '您有即将逾期的线索待跟进，请及时跟进。';
        $notice_title = '您有即将逾期的线索待跟进，请及时跟进。';
        $notice_type = 113;   //暂定  待确认
        return $this->androidPushMsg($who_assign_id,$salesman_id,$push_title,$notice_title,$notice_type);
    }

    public function androidPushMsg($who_assign_id,$salesman_id,$push_title,$notice_title,$notice_type,$content = '',$notice_param = '',$app_type = 'sales',$func = ''){
        $hwPush = new HWPushLogic();
        return $hwPush->androidPushMsg($who_assign_id,$salesman_id,$push_title,$notice_title,$notice_type,$app_type,$content,$notice_param,$func);
    }

}
