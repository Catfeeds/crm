<?php
namespace backend\controllers;
use Yii;
use yii\web\Controller;
use common\logic\CompanyUserCenter;
use common\models\LogPcCaozuo;
use common\common\PublicMethod;
use common\models\OrganizationalStructure;
use backend\logic\LoginLogic;
class BaseController extends Controller
{
    //记录后台操作日志用 - 记录操作内容的时候需要的一些变量值
    protected $arrLogParam = [];
    
    //记录后台操作日志用 - 标记是什么操作，如果该变量不赋值则默认从url中获取uri
    protected $strRoute = '';

    /**
     * 校验是否登录以及是否选择过门店
     */
    public function beforeAction($action)
    {
        $objLoginLogic = LoginLogic::instance();
        $objLoginLogic->checkIsLogin();
        return true;
    }
    
     //输出excel表格文件
    public function outPutExcel($fileName = '', $itemsName = [], $list = [])
    {
        //目前最多支持26个列
        $intAscii = 65;// ascii A
        $columns = $headers = $models = [];
        foreach($itemsName as $name)
        {
            $strAscii = chr($intAscii);
            $columns[] = $strAscii;
            $headers[$strAscii] = $name;
            $intAscii++; // A B C D E......
        }
        foreach($list as $k => $arrLine)
        {
            $intAscii = 65;
            foreach($arrLine as $item)
            {
                $strAscii = chr($intAscii);
                $models[$k][$strAscii] = $item;
                $intAscii++;
            }
        }
        set_time_limit(0);
        ini_set('memory_limit',-1);
        //生成excel
        \moonland\phpexcel\Excel::export([
            'models' => $models,
            'columns' => $columns,
            'fileName' => $fileName.'.xlsx',
            'headers' => $headers,
        ]);

    }
    
    //返回页面方法
    public function res($statusCode = 200,$message =  null) {
        $list = array(
            "statusCode"    => $statusCode,
            "message"       => $message
        );

        echo json_encode($list);
        exit();
    }

    public function dump($arr)
    {
        echo "<pre>";
        print_r($arr);
        exit;
    }
    
    /**
     * @功能：  权限控制
     * @作者：  王雕
     * @param string        $strUrlOrSlug       请求的uri,或者在权限系统中配置的目录别名
     * @return boolean      成功的时候返回true，不通过的时候直接跳走
     */
    public function checkPermission($strUrlOrSlug)
    {
        //判断数据权限是否ok
        $session = Yii::$app->getSession();
        //角色目录权限
        $arrUrlList = $session['userinfo']['menu_url'];
        $arrSlugList = $session['userinfo']['slug'];
        if(!in_array($strUrlOrSlug, $arrUrlList) && !in_array($strUrlOrSlug, $arrSlugList))
        {
            if($strUrlOrSlug == '/index/index')
            {
                //首页直接跳
                PublicMethod::noticeJump('/index/welcome', '', 0);
            }
            else
            {
                PublicMethod::noticeJump('/index/welcome', '您无权限请求该页面', 3);
            }
        }
        return true;
    }
    
    /**
     * 功    能：保存一些操作的log记录，在该类的析构函数中调用
     * 作    者：王雕
     * 修改日期：2017-4-13
     */
    private function saveUseLog()
    {
        $session = Yii::$app->getSession();
        $strRoute = empty($this->strRoute) ? Yii::$app->requestedRoute : $this->strRoute;
        $arrConfig = $this->saveLogConfigAndTemplate();
        if(isset($session['userinfo']) && isset($arrConfig[$strRoute]))//登录过且配置过日志格式的才记录日志
        {
            //构造content模板替换信息的find数组与replace数组
            $arrKeys = $arrFind = $arrValue = [];
            foreach($this->arrLogParam as $k => $v)
            {
                $arrKeys[] = $k;
                $arrFind[] = "【{$k}】";
                $arrValue[] = $v;
            }
            //设置的参数不满足模板替换的时候不记录日志
            if(empty(array_diff($arrConfig[$strRoute]['replace_keys'],$arrKeys)))
            {
                
                //要插入表格的数据
                $arrInsert = [
                    'create_time' => time(),
                    'user_id' => $session['userinfo']['id'],
                    'user' => $session['userinfo']['name'],
                    'phone' => $session['userinfo']['phone'],
                    'ip' => Yii::$app->request->userIP,
                    'org_id' => $session['userinfo']['org_id'],
                    'org_name' => @OrganizationalStructure::findOne($session['userinfo']['org_id'])->name,
                ];
                $arrLogInfo = array_merge($arrConfig[$strRoute], $arrInsert);
                unset($arrLogInfo['replace_keys']);
                //开始替换构造content内容了
                $arrLogInfo['content'] = str_replace($arrFind, $arrValue, $arrLogInfo['content']);
                //入库
                $objLog = new LogPcCaozuo();
                $objLog->setAttributes($arrLogInfo);
                $objLog->save();
            }
        }
    }
    
    /**
     * 功    能：配置后台记录的日志以及日志模板,从保存日志函数中独立出来,便于折叠，该代码段会很长
     * 作    者：王雕
     * 修改日期：2017-4-13
     */
    private function saveLogConfigAndTemplate()
    {
        return [
            //新增线索
            'clue/save' => [
                'type_id' => 1, 
                'type_name' => '线索管理', 
                'content' => '新增线索：【customer_name】（【customer_phone】）',
                'replace_keys' => ['customer_name', 'customer_phone'],
            ],
            //导入线索
            'update-xlsx-log/add_file' => [
                'type_id' => 2, 
                'type_name' => '线索管理', 
                'content' => '导入线索：【total_num】条，成功【success_num】条，失败【fail_num】条',
                'replace_keys' => ['total_num', 'success_num', 'fail_num'],
            ],
            //休眠客户重新分配,
            "休眠客户重新分配" => [
                'type_id' => 3,
                'type_name' => '异常客户重分配',
                'content' => '休眠客户重分配：【customer_name_and_phone】顾问【salesman_name_a】——》顾问【salesman_name_a】',
                //customer_name_and_phone => 张明（18565896512）, 多个用逗号连接  例如 customer_name_and_phone => 张明（18565896512）,张明（18565896512）......
                'replace_keys' => ['customer_name_and_phone', 'salesman_name_a', 'salesman_name_a'],
            ],
            //休眠客户激活
            '休眠客户激活' => [
                'type_id' => 4,
                'type_name' => '异常客户重分配',
                'content' => '休眠客户激活：【customer_name_and_phone】',
                'replace_keys' => ['customer_name_and_phone'],
            ],
            //无人跟进客户从重分配
            'active-clue/reassign' => [
                'type_id' => 5,
                'type_name' => '异常客户重分配',
                'content' => '无人跟进客户重分配：分配给顾问【salesman_name_a】',
                'replace_keys' => ['salesman_name_a'],
            ],
            //休眠客户列表
            'active-clue/unconnect-list' => [
                'type_id' => 6,
                'type_name' => '导出列表',
                'content' => '休眠客户列表，【day】天未联系的客户',
                'replace_keys' => ['day'],
            ],
            //无人跟进客户从重分配
            'active-clue/nofollow' => [
                'type_id' => 7,
                'type_name' => '导出列表',
                'content' => '无人跟进客户列表，时间：【date_1】到【date_2】',
                'replace_keys' => ['date_1', 'date_2'],
            ],
            //线索客户列表
            'customer/get-clue-customer' => [
                'type_id' => 8,
                'type_name' => '导出列表',
                'content' => '线索客户列表，创建时间：【date_1】到【date_2】',
                'replace_keys' => ['date_1', 'date_2'],
            ],
            //意向客户列表
            'customer/get-intention-customer' => [
                'type_id' => 9,
                'type_name' => '导出列表',
                'content' => '意向客户列表，建卡时间：【date_1】到【date_2】',
                'replace_keys' => ['date_1', 'date_2'],
            ],
            //订车客户列表
            'customer/get-order-customer' => [
                'type_id' => 10,
                'type_name' => '导出列表',
                'content' => '订车客户列表，建卡时间：【date_1】到【date_2】，订车时间：【date_3】到【date_4】',
                'replace_keys' => ['date_1', 'date_2', 'date_3', 'date_4'],
            ],
            //交车客户列表
            'customer/get-success-customer' => [
                'type_id' => 11,
                'type_name' => '导出列表',
                'content' => '交车客户列表，建卡时间：【date_1】到【date_2】，交车时间：【date_3】到【date_4】',
                'replace_keys' => ['date_1', 'date_2', 'date_3', 'date_4'],
            ],
            //保有客户列表
            'customer/get-keep-customer' => [
                'type_id' => 12,
                'type_name' => '导出列表',
                'content' => '保有客户列表，购车日期：【date_1】到【date_2】',
                'replace_keys' => ['date_1', 'date_2'],
            ],
            //战败客户列表
            'customer/get-fail-customer' => [
                'type_id' => 13,
                'type_name' => '导出列表',
                'content' => '战败客户列表，创建时间：【date_1】到【date_2】',
                'replace_keys' => ['date_1', 'date_2'],
            ],
            //明细查询列表
            '明细查询列表' => [
                'type_id' => 14,
                'type_name' => '导出列表',
                'content' => '明细查询列表，时间：【date_1】到【date_2】',
                'replace_keys' => ['date_1', 'date_2'],
            ],
            //商谈记录列表
            '商谈记录列表' => [
                'type_id' => 15,
                'type_name' => '导出列表',
                'content' => '商谈记录列表，时间：【date_1】到【date_2】',
                'replace_keys' => ['date_1', 'date_2'],
            ],
            //报表查询列表
            '报表查询列表' => [
                'type_id' => 16,
                'type_name' => '导出列表',
                'content' => '报表查询列表，时间：【date_1】到【date_2】',
                'replace_keys' => ['date_1', 'date_2'],
            ],
            //使用日志列表
            'logs/show-logs' => [
                'type_id' => 17,
                'type_name' => '导出列表',
                'content' => '使用日志列表，时间：【date_1】到【date_2】',
                'replace_keys' => ['date_1', 'date_2'],
            ],
            //新增激励
            'excitation/create' => [
                'type_id' => 18,
                'type_name' => '激励管理',
                'content' => '新增激励，激励名称：【e_name】',
                'replace_keys' => ['e_name'],
            ],
            //手动结束激励
            'excitation/end' => [
                'type_id' => 19,
                'type_name' => '激励管理',
                'content' => '手动结束激励，激励名称：【e_name】',
                'replace_keys' => ['e_name'],
            ],
            //新增公告
            'announcement-send/create' => [
                'type_id' => 20,
                'type_name' => '公告管理',
                'content' => '新增公告，公告标题：【title】',
                'replace_keys' => ['title'],
            ],
            //新增安卓版本
            'addAndroidApp' => [
                'type_id' => 21,
                'type_name' => 'APP版本管理',
                'content' => '新增Android版本，版本：V【version_name】',
                'replace_keys' => ['version_name'],
            ],
            //新增ios版本
            'addiOSApp' => [
                'type_id' => 22,
                'type_name' => 'APP版本管理',
                'content' => '新增iOS版本，版本：V【version_name】',
                'replace_keys' => ['version_name'],
            ],
            //提现申请通过
            '提现申请通过' => [
                'type_id' => 23,
                'type_name' => '提现处理',
                'content' => '提现申请通过，金额：¥【money】',
                'replace_keys' => ['money'],
            ],
            //提现申请驳回
            '提现申请驳回' => [
                'type_id' => 24,
                'type_name' => '提现处理',
                'content' => '提现申请驳回，金额：¥【money】',
                'replace_keys' => ['money'],
            ],
            //新增意向等级
            'intention/update-or-create/create' => [
                'type_id' => 25,
                'type_name' => '意向等级管理',
                'content' => '新增意向等级，等级名称：【intention_level_name】',
                'replace_keys' => ['intention_level_name'],
            ],
            //编辑意向等级
            'intention/update-or-create/update' => [
                'type_id' => 26,
                'type_name' => '意向等级管理',
                'content' => '编辑意向等级，等级名称：【intention_level_name】',
                'replace_keys' => ['intention_level_name'],
            ],
            //客户信息管理 - 新增 - 客户信息年龄段、职业。。。等
            'customerinfo/update-or-create/create' => [
                'type_id' => 27,
                'type_name' => '客户信息管理',
                'content' => '新增【type_name】，名称：【tag_name】',
                'replace_keys' => ['type_name', 'tag_name'],
            ],
            //客户信息管理 - 编辑
            'customerinfo/update-or-create/update' => [
                'type_id' => 28,
                'type_name' => '客户信息管理',
                'content' => '编辑【type_name】，名称：【tag_name】',
                'replace_keys' => ['type_name', 'tag_name'],
            ],
            //到店上门商谈管理 - 新增
            '到店上门商谈管理 - 新增' => [
                'type_id' => 29,
                'type_name' => '到店/上门商谈管理',
                'content' => '新增【type_name】，名称：【tag_name】',
                'replace_keys' => ['type_name', 'tag_name'],
            ],
            //到店上门商谈管理 - 编辑
            '到店上门商谈管理 - 编辑' => [
                'type_id' => 30,
                'type_name' => '到店/上门商谈管理',
                'content' => '编辑【type_name】，名称：【tag_name】',
                'replace_keys' => ['type_name', 'tag_name'],
            ],
            //战败原因管理 - 新增
            '战败原因管理 - 新增' => [
                'type_id' => 31,
                'type_name' => '战败原因管理',
                'content' => '新增【type_name】，名称：【tag_name】',
                'replace_keys' => ['type_name', 'tag_name'],
            ],
            //战败原因管理- 编辑
            '战败原因管理- 编辑' => [
                'type_id' => 32,
                'type_name' => '战败原因管理',
                'content' => '编辑【type_name】，名称：【tag_name】',
                'replace_keys' => ['type_name', 'tag_name'],
            ],
            //短信模板管理 - 新增
            '短信模板管理 - 新增' => [
                'type_id' => 33,
                'type_name' => '短信模板管理',
                'content' => '新增XX，类型：APP用，名称：XX',
                'replace_keys' => ['type_name', 'tag_name'],
            ],
            //短信模板管理 - 编辑
            '短信模板管理 - 编辑' => [
                'type_id' => 34,
                'type_name' => '短信模板管理',
                'content' => '编辑XX，类型：APP用，名称：XX',
                'replace_keys' => ['type_name', 'tag_name'],
            ],
        ];
    }
    
    /**
     * 功    能：析构函数，调用日志保存函数，记录用户的操作日志
     * 作    者：王雕
     * 修改日期：2017-4-13
     */
    public function __destruct()
    {
        $this->saveUseLog();
    }

    /**
     * 店长登录的时候有可能店长用够多个店铺的权限，此处默认给一个
     * @return type
     */
    public function getDefaultShopId()
    {
        $shopId = 0;
        $session = Yii::$app->getSession();
        $orgIds = $session['userinfo']['permisson_org_ids'];
        $arrWhere = [
            'and',
            ['=', 'is_delete', 0],
            ['=', 'level', 30],
            ['in', 'id', $orgIds],
        ];
        $shopIdTmp = @OrganizationalStructure::find()->select('id')->where($arrWhere)->asArray()->one();
        if($shopIdTmp)
        {
            $shopId = $shopIdTmp['id'];
        }
        return $shopId;
    }
    
    
    /**
     * 通过区返回门店id
     */
    public function getRole($id)
    {
        $session = Yii::$app->getSession();
        $orgIds = $session['userinfo']['permisson_org_ids'];
        $arrWhere = [
            'and',
            ['=', 'is_delete', 0],
            ['in', 'id', $orgIds],
            ['=', 'pid', $id]
        ];
        $shopListTmp = OrganizationalStructure::find()->where($arrWhere)->asArray()->all();
        $shopList = array_column($shopListTmp, 'id');
        return (empty($shopList) ? '0' : implode(',', $shopList));
    }
    /**
     * 通过公司获取门店id
     * $id 公司id
     */
    public function getRoles($id)
    {
        $session = Yii::$app->getSession();
        $orgIds = $session['userinfo']['permisson_org_ids'];
        $arrWhere = [
            'and',
            ['=', 'is_delete', 0],
            ['in', 'id', $orgIds],
            ['=', 'pid', $id]
        ];
        $areaListTmp = OrganizationalStructure::find()->where($arrWhere)->asArray()->all();
        $areaList = array_column($areaListTmp, 'id');
        $arrWhereShop = [
            'and',
            ['=', 'is_delete', 0],
            ['in', 'id', $orgIds],
            ['in', 'pid', $areaList]
        ];
        $shopListTmp = OrganizationalStructure::find()->where($arrWhereShop)->asArray()->all();
        $shopList = array_column($shopListTmp, 'id');
        return (empty($shopList) ? '0' : implode(',', $shopList));
    }

    /**
     * 公司人员登录 返回所有门店id
     */
    public function getCompanyReturnShopId() {
        $session = Yii::$app->getSession();
        $company = new CompanyUserCenter();
        $area    = $company->getCanSelectAreaOrShopListByPhoneOrEmail($session['userinfo']['email']);
        $shop_id = null;

        foreach ($area['children'] as $v) {

            if (!empty($v['children'])) {
                foreach ($v['children'] as $val) {

                    $shop_id .= $val['id'].',';

                }
            }
        }
        return empty($shop_id) ? 0 :rtrim($shop_id, ',');
    }

    /**
     * 大区人员登录 返回所有门店id
     */
    public function getAreaReturnShopId() {

        $session = Yii::$app->getSession();
        $company = new CompanyUserCenter();
        $area    = $company->getCanSelectAreaOrShopListByPhoneOrEmail($session['userinfo']['email']);
        $shop_id = null;

        foreach ($area['children'] as $v) {

            $shop_id .= $v['id'].',';

        }
        return empty($shop_id) ? 0 :rtrim($shop_id, ',');
    }





    /**
     * 总部登陆 返回门店id
     */
    public function getShopIdByCompanyId($companyId)
    {

        $session = Yii::$app->getSession();
        $company = new CompanyUserCenter();
        $area    = $company->getCanSelectAreaOrShopListByPhoneOrEmail($session['userinfo']['email']);
        $shopIds = [];

        //所有公司
        foreach ($area['children'] as $v) {
            if ($v['pid'] == $companyId) {
                //所有区
                if (!empty($v['children'])) {
                    foreach ($v['children'] as $area_val) {
                        $shopIds[] = $area_val['id'];
                    }
                }
            }
        }
        return $shopIds;
    }


    /**
     * @param $areaId
     * @return  array
     * 总部登陆 返回门店id
     */
    public function getShopIdByAreaId($areaId)
    {

        $session = Yii::$app->getSession();
        $company = new CompanyUserCenter();
        $area    = $company->getCanSelectAreaOrShopListByPhoneOrEmail($session['userinfo']['email']);
        $shopIds = [];

        //所有公司
        foreach ($area['children'] as $v) {
            if ($v['pid'] == $areaId) {
                $shopIds[] = $v['id'];
            }
        }
        return $shopIds;
    }
}
