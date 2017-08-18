<?php
/**
 * 脚本控制器 - 本文件只是脚本入口，具体实现逻辑不要放在本文件中
 * 执行示例 ： php yii cron\jiao-che
 */
namespace console\controllers;
use common\models\Clue;
use console\logic\NoticeAndPushLogic;
use console\logic\UnfinishedTelTaskLogic;
use Yii;
use yii\console\Controller;
use console\logic\JichushujuLogic;
use console\logic\WeijiaocheLogic;
use console\logic\ClueGjzLogic;
use console\logic\IntentionGjzLogic;
use console\logic\DingcheDateCountLogic;
use console\logic\DingcheNumLogic;
use console\logic\ThisMonthIntentionLogic;
use console\logic\LastMonthIntentionLogic;
use console\logic\ThisMonthNewClueLogic;
use console\logic\DingcheDateInputtypeLogic;
use console\logic\InputTypeClueLogic;
use console\logic\IntentionFailLogic;
use console\logic\IntentionLevelCountLogic;
use console\logic\ZhuanhualoudouLogic;
use console\logic\IntentionTalkTagCountLogic;
use console\logic\TaskLogic;
use common\logic\CompanyUserCenter;
use console\logic\SalesTargetLogic;
use console\logic\CheckPhoneLogic;
use common\logic\ClaimClueLogic;
use console\logic\FailClueBugDataLogic;
use console\logic\GongHaiJbLogic;
class CronController extends Controller
{
    private $intStartTime = 0;//脚本开始运行的时间

    private $intResult = 0;

    /**
     * 功    能：构造函数，记录脚本运行的开始时间，析构函数中记录日志的时候用到
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function __construct($id, $module, $config = array()) {
        parent::__construct($id, $module, $config);
        $this->intStartTime = time();
    }

    //##############################################################################
    // 统计销售顾问的基础业务能力相关的数据，
    // 包含：新增线索数、新增意向数、电话任务数、已完成电话任务数、商谈数
    //       、来电数、去电数、上门数、到店数、战败数、交车数等
    //##############################################################################
    /**
     * 功    能：顾问基础业务数据 - 新增线索客户数统计脚本
     * 运行配置：yii cron/new-clue
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionNewClue()
    {
        $objJcLogic = new JichushujuLogic();
        $this->intResult = $objJcLogic->newClue();
    }

    /**
     * 功    能：顾问基础业务数据 - 新增意向客户数统计脚本
     * 运行配置：yii cron/new-intention-clue
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionNewIntentionClue()
    {
        $objJcLogic = new JichushujuLogic();
        $this->intResult = $objJcLogic->newIntentionClue();
    }

    /**
     * 功    能：顾问基础业务数据 - 电话任务数统计脚本
     * 运行配置：yii cron/phone-task
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionPhoneTask()
    {
        $objJcLogic = new JichushujuLogic();
        $this->intResult = $objJcLogic->phoneTask();
    }

    /**
     * 功    能：顾问基础业务数据 - 已取消的电话任务数统计脚本
     * 运行配置：yii cron/cancel-phone-task
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionCancelPhoneTask()
    {
        $objJcLogic = new JichushujuLogic();
        $this->intResult = $objJcLogic->cancelPhoneTask();
    }

    /**
     * 功    能：顾问基础业务数据 - 电话任务完成数统计脚本
     * 运行配置：yii cron/phone-task-finish
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionPhoneTaskFinish()
    {
        $objJcLogic = new JichushujuLogic();
        $this->intResult = $objJcLogic->finishPhoneTask();
    }

    /**
     * 功    能：顾问基础业务数据 - 商谈记录数统计脚本
     * 运行配置：yii cron/talk
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionTalk()
    {
        $objJcLogic = new JichushujuLogic();
        $this->intResult = $objJcLogic->talkNum();
    }

    /**
     * 功    能：顾问基础业务数据 - 来电数统计脚本
     * 运行配置：yii cron/lai-dian
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionLaiDian()
    {
        $objJcLogic = new JichushujuLogic();
        $this->intResult = $objJcLogic->laiDianNum();
    }

    /**
     * 功    能：顾问基础业务数据 - 去电数统计脚本
     * 运行配置：yii cron/qu-dian
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionQuDian()
    {
        $objJcLogic = new JichushujuLogic();
        $objJcLogic->quDianNum();
    }

    /**
     * 功    能：顾问基础业务数据 - 到店数统计脚本
     * 运行配置：yii cron/to-shop
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionToShop()
    {
        $objJcLogic = new JichushujuLogic();
        $objJcLogic->toShopNum();
    }

    /**
     * 功    能：顾问基础业务数据 - 上门数统计脚本
     * 运行配置：yii cron/to-home
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionToHome()
    {
        $objJcLogic = new JichushujuLogic();
        $objJcLogic->toHomeNum();
    }

    /**
     * 功    能：顾问基础业务数据 - 战败客户数统计脚本
     * 运行配置：yii cron/fail-clue
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionFailClue()
    {
        $objJcLogic = new JichushujuLogic();
        $objJcLogic->failCustomerNum();
    }

    /**
     * 功    能：顾问基础业务数据 - 交车数统计脚本
     * 运行配置：yii cron/jiao-che
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionJiaoChe()
    {
        $objJcLogic = new JichushujuLogic();
        $objJcLogic->jiaoChe();
    }

    /**
     * 功    能：顾问基础业务数据 - 订车数统计脚本
     * 运行配置：yii cron/ding-che
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionDingChe()
    {
        $objJcLogic = new JichushujuLogic();
        $objJcLogic->dingche();
    }


    //##############################################################################
    // 统计正在跟进中的意向与线索还有未交车信息，这些数据没有时间维度
    //##############################################################################
    /**
     * 功    能：跟进中的线索 - 按照销售顾问的维度统计
     * 运行配置：yii cron/gen-jin-zhong-clue
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionGenJinZhongClue()
    {
        $objGjzClue = new ClueGjzLogic();
        $objGjzClue->gjzClue();
    }

    /**
     * 功    能：跟进中的意向 - 按照销售顾问的维度统计
     * 运行配置：yii cron/gen-jin-zhong-intention
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionGenJinZhongIntention()
    {
        $objGjzIntention = new IntentionGjzLogic();
        $objGjzIntention->gjzIntention();
    }

    /**
     * 功    能：未交车数据统计，没有时间维度
     * 运行配置：yii cron/wei-jiao-che
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionWeiJiaoChe()
    {
        $objWjc = new WeijiaocheLogic();
        $objWjc->weijiaoche();
    }


    //##############################################################################
    // 统计订车数、订车成交周期、上月结余意向、本月新增意向、本月新增线索、等信息的脚本
    //##############################################################################
    /**
     * 功    能：订车成交周期统计
     * 运行配置：yii cron/ding-che-date-count
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionDingCheDateCount()
    {
        $objDcDc = new DingcheDateCountLogic();
        $objDcDc->dateCount();
    }

    /**
     * 功    能：订车数统计
     * 运行配置：yii cron/ding-che-num
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionDingCheNum()
    {
        $objDcn = new DingcheNumLogic();
        $objDcn->dingche();
    }

    /**
     * 功    能：本月新增意向数统计
     * 运行配置：yii cron/this-month-new-intention
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionThisMonthNewIntention()
    {
        $objThisMonthNewIntention = new ThisMonthIntentionLogic();
        $objThisMonthNewIntention->thisMonthIntention();
    }

    /**
     * 功    能：上月结余意向数统计 - 每月底统计一次
     * 运行配置：yii cron/last-month-intention
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionLastMonthIntention()
    {
        $objLastMonthIntention = new LastMonthIntentionLogic();
        $objLastMonthIntention->lastMonthIntention();
    }

    /**
     * 功    能：本月新增线索数统计
     * 运行配置：yii cron/this-month-new-clue
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionThisMonthNewClue()
    {
        $objThisMonthNewClue= new ThisMonthNewClueLogic();
        $objThisMonthNewClue->countThisMonthNewClue();
    }

    /**
     * 功    能：按照渠道号+顾问的形式统计订车数
     * 运行配置：yii cron/ding-che-input-type-count
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionDingCheInputTypeCount()
    {
        $objDingcheDateInputtype = new DingcheDateInputtypeLogic();
        $objDingcheDateInputtype->countData();
    }

    /**
     * 功    能：线索 - 总线索 - 分配时间等于今天的
     * 运行配置：yii cron/clue-input-type-all
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionClueInputTypeAll()
    {
        $objInputTypeClue = new InputTypeClueLogic();
        $objInputTypeClue->allClue();
    }

    /**
     * 功    能：无效线索 - 战败时间等于今天的线索
     * 运行配置：yii cron/clue-input-type-fail
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionClueInputTypeFail()
    {
        $objInputTypeClue = new InputTypeClueLogic();
        $objInputTypeClue->failClue();
    }

    /**
     * 功    能：已转化线索 - 建卡时间是今天
     * 运行配置：yii cron/clue-input-type-zhuan-hua
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionClueInputTypeZhuanHua()
    {
        $objInputTypeClue = new InputTypeClueLogic();
        $objInputTypeClue->zhuanhuaClue();
    }

    /**
     * 功    能：意向战败 - 战败标签统计
     * 运行配置：yii cron/intention-fail-tag
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionIntentionFailTag()
    {
        $objIntentionFail = new IntentionFailLogic();
        $objIntentionFail->countIntentionFailTag();
    }

    /**
     * 功    能：订车战败 - 战败标签统计
     * 运行配置：yii cron/ding-che-fail-tag
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionDingCheFailTag()
    {
        $objIntentionFail = new IntentionFailLogic();
        $objIntentionFail->countDingcheFailTag();
    }

    /**
     * 功    能：意向客户的意向等级使用状况
     * 运行配置：yii cron/intention-level-count
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionIntentionLevelCount()
    {
        $objIntentionLevelCount = new IntentionLevelCountLogic();
        $objIntentionLevelCount->intentionLevel();
    }

    /**
     * 功    能：转化漏斗数据统计 - 新增线索数
     * 运行配置：yii cron/zhuan-hua-lou-dou-new-clue
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionZhuanHuaLouDouNewClue()
    {
        $objZhuanhualoudou = new ZhuanhualoudouLogic();
        $objZhuanhualoudou->newClueNum();
    }

    /**
     * 功    能：转化漏斗数据统计 - 新增意向数
     * 运行配置：yii cron/zhuan-hua-lou-dou-new-intention
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionZhuanHuaLouDouNewIntention()
    {
        $objZhuanhualoudou = new ZhuanhualoudouLogic();
        $objZhuanhualoudou->newIntentionNum();
    }
    /**
     * 功    能：转化漏斗数据统计 - 到店数
     * 运行配置：yii cron/zhuan-hua-lou-dou-to-shop
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionZhuanHuaLouDouToShop()
    {
        $objZhuanhualoudou = new ZhuanhualoudouLogic();
        $objZhuanhualoudou->toShopNum();
    }

    /**
     * 功    能：转化漏斗数据统计 - 订车数
     * 运行配置：yii cron/zhuan-hua-lou-dou-ding-che
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionZhuanHuaLouDouDingChe()
    {
        $objZhuanhualoudou = new ZhuanhualoudouLogic();
        $objZhuanhualoudou->dingcheNum();
    }

    /**
     * 功    能：当前的意向客户的交谈记录中的标签的使用状况的统计脚本
     * 运行配置：yii cron/intention-talk-tag-count
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function actionIntentionTalkTagCount()
    {
        $objIntentionTalkTagCount = new IntentionTalkTagCountLogic();
        $objIntentionTalkTagCount->countTalkTags();
    }


    /**
     * 功    能：每天定时提醒销售顾问的电话任务完成进度
     * 运行配置：yii cron/sales-today-phone-task-notice 1
     * 作    者：王雕
     * 修改日期：2017-4-6
     * edited by liujx edited by liujx 2017-08-07  早上 9 点, 下午1点, 晚上6点 都要推送通知短信
     * 判断推送类型通过时间判断的
     */
    public function actionSalesTodayPhoneTaskNotice()
    {
        // 获取到参数，确定操作类型
        $time = time();
        // 减去600 是可以允许误差 10分钟
        // 下午一点
        $intAfternoonOne = strtotime(date('Y-m-d 13:00:00')) - 600;

        // 晚上6点
        $intNightSix = strtotime(date('Y-m-d 18:00:00')) - 600;

        // 先判断是否为晚上6点
        if ($time >= $intNightSix) {
            $intType = 3;
        } elseif ($time >= $intAfternoonOne) {
            $intType = 2;
        } else {
            $intType = 1;
        }

        $objTask = new TaskLogic();

        // 判断处理类型
        switch ($intType) {
            case 2:
                $objTask->handleAfternoonOne();
                break;
            case 3:
                $objTask->handleNightSix();
                break;
            default:
                $objTask->handleMorningNine();

        }

        echo date('Y-m-d H:i:s').' OK'.PHP_EOL;

//        $objTask->toldSalesTodayPhoneTaskInfo();
    }

###################################################################
#########   组织架构相关功能 脚本##################################
###################################################################
    /**
     * 功    能：更新组织架构
     * 运行配置：yii cron/update-org
     * 作    者：王雕
     * 修改日期：2017-4-25
     */
    public function actionUpdateOrg()
    {
        $objCompanyUserCenter = new CompanyUserCenter();
        $objCompanyUserCenter->curlUpdateOrganizationalStructure();
    }

    /**
     * 功    能：更新组织里面的人员信息
     * 运行配置：yii cron/update-user
     * 作    者：王雕
     * 修改日期：2017-4-25
     */
    public function actionUpdateUser()
    {
        $objCompanyUserCenter = new CompanyUserCenter();
        $objCompanyUserCenter->curlUpdateProjectUserList();
    }

    /**
     * 功    能：更新组织里面的角色及权限信息
     * 运行配置：yii cron/update-role
     * 作    者：王雕
     * 修改日期：2017-4-25
     */
    public function actionUpdateRole()
    {
        $objCompanyUserCenter = new CompanyUserCenter();
        $objCompanyUserCenter->curlUpdateRoleInfo();
    }

    /**
     * 功    能：更新销售指标中的完成数
     * 运行配置：yii cron/sales-target-finish-num
     * 作    者：王雕
     * 修改日期：2017-4-25
     */
    public function actionSalesTargetFinishNum()
    {
        $objSalesTarget = new SalesTargetLogic();
        $objSalesTarget->finishNum();
    }

    /**
     * 功    能：发送顾客生日提醒  每天上午九点执行一次
     * 运行配置：yii cron/customer_birthday_notice
     * 作    者：lzx
     * 修改日期：2017-04-26
     */
    public function actionCustomerBirthdayNotice()
    {
        $logic = new NoticeAndPushLogic();
        $logic->CustomerBirthdayNotice();
    }

    /**
     * 功    能：把昨天未完成的电话任务转接到今天  每天凌晨12点以后执行一次
     * 运行配置：yii cron/unfinished-tel-task
     * 作    者：lzx
     * 修改日期：2017-05-11
     */
    public function actionUnfinishedTelTask()
    {
        $logic = new UnfinishedTelTaskLogic();
        $logic->index();
    }
    /**
     * 功    能：清空每天验证首次录入的手机号码  每天凌晨12点以后执行一次
     * 运行配置：yii cron/delete-check-phone
     * 作    者：于凯
     * 修改日期：2017-06-12
     */
    public function actionDeleteCheckPhone()
    {
        $logic = new CheckPhoneLogic();
        $logic->deleteCheckPhone();
    }
    /**
     * 功    能：每一分钟检测还原抢购超过30分钟的信息
     * 运行配置：yii cron/claim-clue
     * 作    者：于凯
     * 修改日期：2017-06-19
     */
    public function actionClaimClue()
    {
        $logic = new ClaimClueLogic();
        $logic->getClaimClues();
    }
    
    
    /**
     * 功    能：战败线索在线索表中存储的意向等级状态不是战败级的bug做个定时脚本去清除异常数据
     * 作    者：王雕
     * 修改日期：2017-6-23
     */
    public function actionFailClueBugData()
    {
        $objFailClueBugDataLogic = new FailClueBugDataLogic();
        $objFailClueBugDataLogic->clearFailClueTask();
    }
    
    
    
    
    /**
     * 功    能：析构函数，在脚本执行结束的时候统计脚本的运行情况
     * 作    者：王雕
     * 修改日期：2017-4-6
     */
    public function __destruct()
    {
        //记录运行log
        $strLog = '[' . date('Y-m-d H:i:s') . ']:' . Yii::$app->controller->id . '/' . Yii::$app->controller->action->id . "\n";
        $strLog .= '耗时：' . (time() - $this->intStartTime) . " 秒\n\n";
        $strFile = Yii::$app->runtimePath . '/cron_run_log' . date('Y-m-d') . '.log';
        file_put_contents($strFile, $strLog, FILE_APPEND);
    }

    /****************************************进入公海脚本  yukai*********************************************/
    /**
     *功  能 ： 两个月无人跟进投放到公海
     */
    public function actionTwoMonth() {
        $gonghai = new GongHaiJbLogic();
        $gonghai->twoMonth();
    }

    /**
     *功  能 ：24个小时无人认领的门店线索自动进入公海线索
     */
    public function actionTwentyFour() {
        $gonghai = new GongHaiJbLogic();
        $gonghai->twentyFour();
    }
}

