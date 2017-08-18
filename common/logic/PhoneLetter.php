<?php
/**
 * 功    能：短信发送功能类
 * 作    者：王雕
 * 修改日期：2017-3-22
 */
namespace common\logic;

use Yii;
use common\common\PublicMethod;
use common\models\SmsLogs;
use common\models\PhoneLetterTmp;

class PhoneLetter 
{
    /**
     * 短信相关的配置
     */
    private static $url = 'http://139.196.250.134:8090/smsApi!mt';//接口地址
    private static $user = '17798999998';//接口账号
    private static $appKey = '14f6bf67387c417087898d2a106c120d';//接口token
    private static $extCode = '362928';//扩展码
    
    /**
     * 语音短信相关的配置
     */
    private static $yy_url = 'http://139.224.34.60:8099/api/playSoundMsg';
    private static $yy_appId = '4e1dfdf2a9c2459e9c5d0b3e8c0b85ef';
    private static $yy_apptoken = 'e6cd6def0fcf4c71942c810aff1561ef';

    /**
     * 发送的类型
     */
    const SENT_TYPE_TEXT = 1;           // 文字短信
    const SENT_TYPE_APP = 2;            // APP短信
    const SENT_TYPE_VOICE = 3;          // 语音短信

    /**
     * 功    能：总部添加线索给店长时发送短信通知
     * 参    数：$strPhone              string          要发送短信的手机号
     *         ：$strShopownerName      string          接受短信的店长的姓名
     *         ：$intClueNum            int             添加的线索的条数（一般是1）
     *         ：$strFrom               int             这条线索的渠道来源（eg: 400电话）
     * 返    回：                       boolen          true - 发送成功 / false - 发送失败
     * 作    者：王雕
     * 修改日期：2017-3-22
     */
    public function addClueToShopSendSms($strPhone, $strShopownerName, $intClueNum, $strFrom)
    {
        if(!$this->checkPhoneLetterTmpIsOk(5))
        {
            return false;
        }
        $strContentTpl = '【管理速报】您好，[shopowner]，您收到总部下发的[num]条线索，渠道来源：[from]，请尽快分配。退订回T';
        $strContent = str_replace(['[shopowner]', '[num]', '[from]'], [$strShopownerName, $intClueNum, $strFrom], $strContentTpl);
        return $this->sendSms($strPhone, $strContent);
    }

    /**
     * 功    能：总部导入线索给店长时发送短信通知
     * 参    数：$strPhone              string          要发送短信的手机号
     *         ：$strShopownerName      string          接受短信的店长的姓名
     *         ：$intClueNum            int             添加的线索的条数（一般是1）
     * 返    回：                       boolen          true - 发送成功 / false - 发送失败
     * 作    者：王雕
     * 修改日期：2017-3-22
     */
    public function uploadClueToShopowner($strPhone, $strShopownerName, $intClueNum)
    {
        if(!$this->checkPhoneLetterTmpIsOk(6))
        {
            return false;
        }
        $strContentTpl = '【管理速报】您好，[shopowner]，您收到总部下发的[num]条线索，请尽快分配。退订回T';
        $strContent = str_replace(['[shopowner]', '[num]'], [$strShopownerName, $intClueNum], $strContentTpl);
        return $this->sendSms($strPhone, $strContent);
    }
    
    /**
     * 功    能：店长分配线索给销售时发送短信通知 - 单条
     * 参    数：$strPhone              string          要发送短信的手机号
     *         ：$salesName             string          接收该短信的销售人员的姓名
     *         ：$strShopownerName      string          接受短信的店长的姓名
     *         ：$intClueNum            int             添加的线索的条数（一般是1）
     *         ：$strFrom               int             这条线索的渠道来源（eg: 400电话）
     * 返    回：                       boolen          true - 发送成功 / false - 发送失败
     * 作    者：王雕
     * 修改日期：2017-3-22
     */
    public function shopownerAssignClueToSales($strPhone, $salesName, $strShopownerName, $intClueNum, $strFrom)
    {
        if(!$this->checkPhoneLetterTmpIsOk(7))
        {
            return false;
        }
        $strContentTpl = '【销售助手】您好，[sales]，您收到店长：[shopowner]分配的[num]条线索，渠道来源：[from]，请尽快跟进。退订回T';
        $strContent = str_replace(['[sales]', '[shopowner]', '[num]', '[from]'], [$salesName, $strShopownerName, $intClueNum, $strFrom], $strContentTpl);
        return $this->sendSms($strPhone, $strContent);
    }
    
    
    /**
     * 功    能：店长分配线索给销售时发送短信通知 - 多条
     * 参    数：$strPhone              string          要发送短信的手机号
     *         ：$salesName             string          接收该短信的销售人员的姓名
     *         ：$strShopownerName      string          接受短信的店长的姓名
     *         ：$intClueNum            int             添加的线索的条数
     * 返    回：                       boolen          true - 发送成功 / false - 发送失败
     * 作    者：王雕
     * 修改日期：2017-3-22
     */
    public function shopownerAssignClues($strPhone, $salesName, $strShopownerName, $intClueNum)
    {
        if(!$this->checkPhoneLetterTmpIsOk(9))
        {
            return false;
        }
        $strContentTpl = '【销售助手】您好，[sales]，您收到店长：[shopowner]分配的[num]条线索，请尽快跟进。退订回T';
        $strContent = str_replace(['[sales]', '[shopowner]', '[num]'], [$salesName, $strShopownerName, $intClueNum], $strContentTpl);
        return $this->sendSms($strPhone, $strContent);
    }
    
    

    /**
     * 功    能：销售当日电话任务播报（脚本中触发?）
     * 参    数：$strPhone              string          要发送短信的手机号
     *         ：$salesName             string          接收该短信的销售人员的姓名
     *         ：$intTotalNum           int             今天的电话任务数
     *         ：$intFinishNum          int             今天已完成的电话任务数
     * 返    回：                       boolen          true - 发送成功 / false - 发送失败
     * 作    者：王雕
     * 修改日期：2017-3-22
     * edited by liujx 2017-08-07 start : 改功能现在禁用 end;
     */
    public function salesTodayPhoneTaskNotice($strPhone, $salesName, $intTotalNum, $intFinishNum)
    {
//        if(!$this->checkPhoneLetterTmpIsOk(8))
//        {
//            return false;
//        }
//        $intNnfinishNum = $intTotalNum - $intFinishNum;
//        $strContentTpl = '【销售助手】您好，[sales]，您今天有[total]个电话任务，已回访：[finish]个，未回访：[unfinish]个。退订回T';
//        $strContent = str_replace(['[sales]', '[total]', '[finish]', '[unfinish]'], [$salesName, $intTotalNum, $intFinishNum, $intNnfinishNum], $strContentTpl);
//        return $this->sendSms($strPhone, $strContent);
    }
    
    /**
     * 功    能：发送手机验证码进行密码重置 - 忘记密码的时候用 - 目前已暂停使用
     * 参    数：$strPhone              string          要发送短信的手机号
     *         ：$strContent            string          发送的短信内容
     * 返    回：                       boolen          true - 发送成功 / false - 发送失败
     * 作    者：王雕
     * 修改日期：2017-3-22
     */
    public function sendPhoneCodeToResetPwd($strPhone, $strCode)
    {
//        $strContentTpl = '【销售助手】您的验证码为：[code]，该验证码用于密码修改验证，请在[minute]分钟内按页面提示提交验证码。退订回T';
//        $strContent = str_replace(['[code]', '[minute]'], [$strCode, 10], $strContentTpl);
//        return $this->sendSms($strPhone, $strContent);
    }
    

    /**
     * 功    能：查看短信模板是否有效，无效的时候不能发送短信
     * 参    数：$tmpId     int     短信模板id
     * 返    回： true/false
     * 作    者：王雕
     * 修改日期：2017-3-22
     */
    private function checkPhoneLetterTmpIsOk($tmpId)
    {
        $model = PhoneLetterTmp::findOne(['id' => $tmpId]);
        if($model && $model->status == 1)//短信模板存在且没有被禁用
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    
    /**
     * 功    能：发送短信功能 - post发送到短信平台
     * 参    数：$strPhone              string          要发送短信的手机号
     *         ：$strContent            string          发送的短信内容
     * 返    回：                       boolen          true - 发送成功 / false - 发送失败
     * 作    者：王雕
     * 修改日期：2017-3-22
     */
    private function sendSms($strPhone, $strContent)
    {

        $arrPost = [
            'userAccount' => self::$user,
            'appKey' => self::$appKey,
            'extCode' => self::$extCode,
            'cpmId' => date('YmdHis') . rand(10000,99999),
            'mobile' => $strPhone,
            'message' => $strContent
        ]; 
        $jsonRes = PublicMethod::http_post(self::$url, $arrPost);
        $arrRes = json_decode($jsonRes, true);
        $this->saveSmsLogs($strPhone, $strContent, $arrRes['respCode'], $arrRes['respMsg']);
        return ($arrRes['respCode'] == 200 ? true : false);
    }


    /**
     * 功    能：发送短信后记录发送日志，以便后面查数据
     * 参    数：$strPhone              string          要发送短信的手机号
     *         ：$strContent            string          发送的短信内容
     *         ：$resCode               int             发送请求到短信平台后得到的返回结果 - 状态码
     *         ：$resMsg                string          发送请求到短信平台后得到的返回结果 - 状态描述
     * 返    回：                       boolen          true - 发送成功 / false - 发送失败
     * 作    者：王雕
     * 修改日期：2017-3-22
     */
    private function saveSmsLogs($strPhone, $strContent, $resCode, $resMsg)
    {
        $arrSave = [
            'phones' => $strPhone,
            'content' => $strContent,
            'respcode' => $resCode,
            'resmsg' => $resMsg,
            'create_time' => date('Y-m-d H:i:s')
        ];
        $objSms = new SmsLogs();
        $objSms->setAttributes($arrSave);
        $objSms->save();
    }


    /**
     * 功    能：店长提醒逾期线索语音短信
     * 参    数：$strPhone              string          要发送短信的手机号
     * 返    回：                       boolen          true - 发送成功 / false - 发送失败
     * 作    者：lzx
     * 修改日期：2017-05-11
     * edited by liujx 2017-08-07 该功能不用 end;
     */
    public function shopownerRemindOverdueClue($strPhone)
    {
//        if(!$this->checkPhoneLetterTmpIsOk(22))
//        {
//            return false;
//        }
//        $strContentTpl = '您有即将逾期的线索待跟进，请及时跟进';
//        return $this->yuyindianhua($strPhone, $strContentTpl);
    }

    /**
     * 功    能：店长分配线索给顾问语音短信  单条
     * 参    数：$strPhone              string          要发送短信的手机号
     *         ：$salesName             string          接收该短信的销售人员的姓名
     *         ：$strShopownerName      string          店长的姓名
     *         ：$intTotalNum           int             今天的电话任务数
     * 返    回：                       boolen          true - 发送成功 / false - 发送失败
     * 作    者：lzx
     * 修改日期：2017-05-11
     */
    public function shopOwnerAssignClueToSalesman($strPhone, $salesName, $strShopownerName,$intTotalNum,$input_type_name)
    {
        if(!$this->checkPhoneLetterTmpIsOk(21))
        {
            return false;
        }
        $strContentTpl = '您好，[sales]，您收到店长：[shopowner]分配的[total]条线索，渠道来源：[input_type_name]，请尽快跟进。';
        $strContent = str_replace(['[sales]', '[shopowner]', '[total]','[input_type_name]'], [$salesName, $strShopownerName, $intTotalNum ,$input_type_name], $strContentTpl);
        return $this->yuyindianhua($strPhone, $strContent);
    }

    /**
     * 功    能：店长分配线索给顾问语音短信   多条
     * 参    数：$strPhone              string          要发送短信的手机号
     *         ：$salesName             string          接收该短信的销售人员的姓名
     *         ：$strShopownerName      string          店长的姓名
     *         ：$intTotalNum           int             今天的电话任务数
     * 返    回：                       boolen          true - 发送成功 / false - 发送失败
     * 作    者：lzx
     * 修改日期：2017-05-11
     */
    public function shopOwnerAssignCluesToSalesman($strPhone, $salesName, $strShopownerName,$intTotalNum)
    {
        if(!$this->checkPhoneLetterTmpIsOk(21))
        {
            return false;
        }
        $strContentTpl = '您好，[sales]，您收到店长：[shopowner]分配的[total]条线索，请尽快跟进。';
        $strContent = str_replace(['[sales]', '[shopowner]', '[total]'], [$salesName, $strShopownerName, $intTotalNum], $strContentTpl);
        return $this->yuyindianhua($strPhone, $strContent);
    }

    /**
     * 功    能：总部分配线索给门店语音短信
     * 参    数：$strPhone              string          要发送短信的手机号
     *         ：$strShopownerName      string          店长的姓名
     *         ：$intTotalNum           int             今天的电话任务数
     * 返    回：                       boolen          true - 发送成功 / false - 发送失败
     * 作    者：lzx
     * 修改日期：2017-05-11
     */
    public function headquartersAssignClueToShop($strPhone, $strShopownerName,$intTotalNum)
    {
        if(!$this->checkPhoneLetterTmpIsOk(23))
        {
            return false;
        }
        $strContentTpl = '您好，[shopowner]，您收到总部下发的[total]条线索，请尽快分配。';
        $strContent = str_replace(['[shopowner]', '[total]'], [ $strShopownerName, $intTotalNum], $strContentTpl);
        return $this->yuyindianhua($strPhone, $strContent);
    }



    /**
     * @功能：语音短信
     * @param string $strPhone 接收人手机号
     * @param string $strContent 语音播报内容
     * @return bool
     */
    public function yuyindianhua($strPhone, $strContent)
    {
        $arrPost = [
            'appId' => self::$yy_appId,
            'callee' => '86' . $strPhone,
            'playtimes' => 2,//播报两次
            'attemptInterval' => 2,//每次间隔2秒
            'msg' => $strContent,
            'ti' => time()
        ]; 
        $arrPost['au'] = strtoupper(md5(self::$yy_appId . self::$yy_apptoken . $arrPost['ti']));
        $jsonRes = PublicMethod::http_post(self::$yy_url, $arrPost);
        $arrRes = json_decode($jsonRes, true);
        $this->saveSmsLogs($strPhone, '【语音短信】' . $strContent, $arrRes[0]['resultCode'], $arrRes[0]['resultMsg']);
        return ($arrRes[0]['resultCode'] == 0 ? true : false);
    }

    /**
     * 客户点击分享页面 发送文字短信给这个客户的顾问（原顾问-客户已经存在顾问跟进）
     *
     * @param string $strPhone          顾问手机号
     * @param string $strSalesmanName   顾问名称
     * @param string $strCustomerName   客户名字
     * @param string $strIntentionDes   意向车系名称
     * @return bool
     */
    public function sendShareUpdateClueSMS($strPhone, $strSalesmanName, $strCustomerName, $strIntentionDes)
    {
        // 验证短信可以用
        $isReturn = $this->checkPhoneLetterTmpIsOk(35); // id = 35
        if ($isReturn) {
            // 替换内容
            $strContent = '【销售助手】您好,[salesman_name],你的客户[customer_name]提交了一条新的购车意向[intention_des],请及时跟进。退订回T';
            $strContent = str_replace([
                '[salesman_name]',
                '[customer_name]',
                '[intention_des]'
            ], [
                $strSalesmanName,
                $strCustomerName,
                $strIntentionDes
            ], $strContent);

            // 发送短信
            $isReturn = $this->sendSms($strPhone, $strContent);
        }

        return $isReturn;
    }

    /**
     * 客户点击分享页面 发送文字短信给这个客户的顾问
     *
     * @param string $strPhone          顾问手机号
     * @param string $strSalesmanName   顾问名称
     * @return bool
     */
    public function sendShareInsertClueSMS($strPhone, $strSalesmanName)
    {
        // 验证短信可以用
        $isReturn = $this->checkPhoneLetterTmpIsOk(36); // id = 36
        if ($isReturn) {
            // 替换内容
            $strContent = '【销售助手】您好,[salesman_name],你收到一条来自微信分享页收到的线索，请及时跟进。退订回T';
            $strContent = str_replace('[salesman_name]', $strSalesmanName, $strContent);

            // 发送短信
            $isReturn = $this->sendSms($strPhone, $strContent);
        }

        return $isReturn;
    }

    /**
     * 客户点击分享页面 发送语音短信给这个客户的顾问（原顾问-客户已经存在顾问跟进）
     *
     * @param string $strPhone          顾问手机号
     * @param string $strSalesmanName   顾问名称
     * @param string $strCustomerName   客户名字
     * @param string $strIntentionDes   意向车系名称
     * @return bool
     */
    public function sendShareUpdateClueVoice($strPhone, $strSalesmanName, $strCustomerName, $strIntentionDes)
    {
        // 验证短信可以用
        $isReturn = $this->checkPhoneLetterTmpIsOk(37); // id = 37
        if ($isReturn) {
            // 替换内容
            $strContent = '您好,[salesman_name],你的客户[customer_name]提交了一条新的购车意向[intention_des],请及时跟进';
            $strContent = str_replace([
                '[salesman_name]',
                '[customer_name]',
                '[intention_des]'
            ], [
                $strSalesmanName,
                $strCustomerName,
                $strIntentionDes
            ], $strContent);

            // 发送短信
            $isReturn = $this->yuyindianhua($strPhone, $strContent);
        }

        return $isReturn;
    }

    /**
     * 客户点击分享页面 发送语音短信给这个客户的顾问
     *
     * @param string $strPhone          顾问手机号
     * @param string $strSalesmanName   顾问名称
     * @return bool
     */
    public function sendShareInsertClueVoice($strPhone, $strSalesmanName)
    {
        // 验证短信可以用
        $isReturn = $this->checkPhoneLetterTmpIsOk(38); // id = 38
        if ($isReturn) {
            // 替换内容
            $strContent = '您好,[salesman_name],你收到一条来自微信分享页收到的线索，请及时跟进';
            $strContent = str_replace('[salesman_name]', $strSalesmanName, $strContent);

            // 发送短信
            $isReturn = $this->yuyindianhua($strPhone, $strContent);
        }

        return $isReturn;
    }

    /**
     * 提车任务进入门店任务池，给这个门店所有顾问发送短信
     *
     * @param string $strPhone          顾问手机号
     * @param string $strCustomerName   客户姓名
     * @param string $strCheCarName     车型
     * @return bool
     */
    public function sendMentionTaskAllSMS($strPhone, $strCustomerName, $strCheCarName)
    {
        // 验证短信
        $isReturn = $this->checkPhoneLetterTmpIsOk(39);
        if ($isReturn) {
            // 替换内容
            $strContent = '【销售助手】门店有一条提车任务（客户[customer_name]，[che_car_name]）需要顾问跟进，请及时领取。退订回T';
            $strContent = str_replace([
                '[customer_name]',
                '[che_car_name]'
            ],
            [
                $strCustomerName,
                $strCheCarName
            ], $strContent);

            // 发送短信
            $isReturn = $this->sendSms($strPhone, $strContent);
        }

        return $isReturn;
    }

    /**
     * 提车任务任务池中 24小时没有顾问认领，给这个门店所有顾问发送短信
     *
     * @param string $strPhone          顾问手机号
     * @param string $strCustomerName   客户姓名
     * @param string $strCheCarName     车型
     * @return bool
     */
    public function sendMentionTaskShopSMS($strPhone, $strCustomerName, $strCheCarName)
    {
        // 验证短信
        $isReturn = $this->checkPhoneLetterTmpIsOk(40);
        if ($isReturn) {
            // 替换内容
            $strContent = '【销售助手】门店有一条提车任务（客户[customer_name]，[che_car_name]）超过24小时无人跟进，请及时领取。退订回T';
            $strContent = str_replace([
                '[customer_name]',
                '[che_car_name]'],
                [
                    $strCustomerName,
                    $strCheCarName
                ], $strContent);

            // 发送短信
            $isReturn = $this->sendSms($strPhone, $strContent);
        }

        return $isReturn;
    }

    /**
     * 电话任务下次提醒时间，提醒时间超过14小时没有打电话给顾问发送提醒短信
     *
     * @param string $strPhone          顾问手机号
     * @param string $strSalesmanName   顾问姓名
     * @param string $date              时间
     * @return bool
     */
    public function sendPhoneReminderSMS($strPhone, $strSalesmanName, $date)
    {
        // 验证短信
        $isReturn = $this->checkPhoneLetterTmpIsOk(41);
        if ($isReturn) {
            // 替换内容
            $strContent = '【销售助手】您好，[salesman_name]，原定于[date]的电话回访已经过期，请及时回访。退订回T';
            $strContent = str_replace([
                '[salesman_name]',
                '[date]'
            ], [$strSalesmanName, $date], $strContent);

            // 发送短信
            $isReturn = $this->sendSms($strPhone, $strContent);
        }

        return $isReturn;
    }

    /**
     * 提车任务进入门店任务池，给这个门店所有顾问发送语音短信
     *
     * @param string $strPhone          顾问手机号
     * @param string $strCustomerName   客户姓名
     * @param string $strCheCarName     车型
     * @return bool
     */
    public function sendMentionTaskAllVoice($strPhone, $strCustomerName, $strCheCarName)
    {
        // 验证短信
        $isReturn = $this->checkPhoneLetterTmpIsOk(42);
        if ($isReturn) {
            // 替换内容
            $strContent = '门店有一条提车任务（客户[customer_name]，[che_car_name]）需要顾问跟进，请及时领取';
            $strContent = str_replace([
                '[customer_name]',
                '[che_car_name]'
            ],
                [
                    $strCustomerName,
                    $strCheCarName
                ], $strContent);

            // 发送短信
            $isReturn = $this->yuyindianhua($strPhone, $strContent);
        }

        return $isReturn;
    }

    /**
     * 提车任务任务池中 24小时没有顾问认领，给这个门店所有顾问发送短信
     *
     * @param string $strPhone          顾问手机号
     * @param string $strCustomerName   客户姓名
     * @param string $strCheCarName     车型
     * @return bool
     */
    public function sendMentionTaskShopVoice($strPhone, $strCustomerName, $strCheCarName)
    {
        // 验证短信
        $isReturn = $this->checkPhoneLetterTmpIsOk(43);
        if ($isReturn) {
            // 替换内容
            $strContent = '门店有一条提车任务（客户[customer_name]，[che_car_name]）超过24小时无人跟进，请及时领取';
            $strContent = str_replace([
                '[customer_name]',
                '[che_car_name]'],
                [
                    $strCustomerName,
                    $strCheCarName
                ], $strContent);

            // 发送短信
            $isReturn = $this->yuyindianhua($strPhone, $strContent);
        }

        return $isReturn;
    }

    /**
     * 电话任务下次提醒时间，提醒时间超过14小时没有打电话给顾问发送提醒短信
     *
     * @param string $strPhone          顾问手机号
     * @param string $strSalesmanName   顾问姓名
     * @param string $date              时间
     * @return bool
     */
    public function sendPhoneReminderVoice($strPhone, $strSalesmanName, $date)
    {
        // 验证短信
        $isReturn = $this->checkPhoneLetterTmpIsOk(44);
        if ($isReturn) {
            // 替换内容
            $strContent = '您好，[salesman_name]，原定于[date]的电话回访已经过期，请及时回访';
            $strContent = str_replace([
                '[salesman_name]',
                '[date]'
            ], [$strSalesmanName, $date], $strContent);

            // 发送短信
            $isReturn = $this->yuyindianhua($strPhone, $strContent);
        }

        return $isReturn;
    }

    /**
     * 通过短信模板ID推送短信信息 (模板ID 大于 44 的 使用，之前的模板可以不兼容)
     * @param string $phone 推送手机号
     * @param integer $tmpId 模板ID
     * @param array $params 需要替换的参数
     *        ['search' => 'replace'] key 查找的目标值，也就是 needle 'replace' search 的替换值
     * @return bool
     */
    public function sendMessageByTmpId($phone, $tmpId, $params = [])
    {
        $isReturn = false;

        // 查询模板是否存在,并且状态为开启状态
        $one = PhoneLetterTmp::findOne(['id' => $tmpId]);
        if ($one && $one->status == PhoneLetterTmp::STATUS_ENABLED) {
            $content = $one->content;

            // 存在需要替换的参数
            if ($params) {
                $search = array_keys($params);
                $replace = array_values($params);
                $content = str_replace($search, $replace, $content);
            }

            // 处理发送类型
            switch ($one->type) {
                // 文字短信
                case PhoneLetterTmp::TYPE_TEXT_MESSAGE:
                    $isReturn = $this->sendSms($phone, $content);
                    break;
                // 语音短信
                case PhoneLetterTmp::TYPE_VOICE_MESSAGE:
                    $isReturn = $this->yuyindianhua($phone, $content);
                    break;
                default:
                    $isReturn = false;
            }
        }

        return $isReturn;
    }
}
