<?php
/**
 * 后台客户列表
 */
namespace backend\controllers;

use common\logic\ActiveClueLogic;
use common\logic\NoticeTemplet;
use common\models\Intention;
use Yii;
use common\models\Clue;
use common\models\Customer;
use common\models\Order;
use common\models\Talk;
use common\models\Task;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\Pagination;
use common\logic\DataDictionary;
use moonland\phpexcel\Excel;
use common\logic\CompanyUserCenter;
use common\logic\JsSelectDataLogic;
use common\logic\CarBrandAndType;
class CustomerController extends BaseController
{
    private $intPageSize = 20;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                ],
            ],
        ];
    }

    public function actionGetClueCustomer()
    {
        //权限控制 - 门店
        $this->checkPermission('/customer/get-clue-customer', 3);

        $arrOutPut   = [];
        $session     = Yii::$app->getSession();

        //获取门店销售人员信息
        $userCen           = new CompanyUserCenter();
        $get = Yii::$app->request->get();
       //获取当前用户id、shop_id
        if(isset($get['shop_id']) && !empty($get['shop_id']))
        {
            $arrGetShopIds = explode(',', $get['shop_id']);
            $shopId = $arrGetShopIds[count($arrGetShopIds)-1];
        }
        else
        {
            $get['shop_id'] = $shopId = $this->getDefaultShopId();
        }
        $arrOutPut['user'] = $userCen->getShopSales($shopId);

        $arrAndWhere = "shop_id={$shopId}";
        $arrAndWhere .= " and status = 0";

        if (isset($get['searchTime']) && preg_match('/^\d{4}\-\d{2}\-\d{2} \- \d{4}\-\d{2}\-\d{2}$/', trim($get['searchTime'])))//输入了时间搜索条件 且时间条件格式正确
        {
            list($startDate, $endDate) = explode(' - ', trim($get['searchTime']));
            $arrAndWhere .= " and FROM_UNIXTIME(create_time, '%Y-%m-%d')  >= '{$startDate}'";
            $arrAndWhere .= " and FROM_UNIXTIME(create_time, '%Y-%m-%d') <=  '{$endDate}'";

            $arrOutPut['startDate'] = $startDate;
            $arrOutPut['endDate']   = $endDate;
        } else {
            $date = date('Y-m-d');
            $arrAndWhere .= " and FROM_UNIXTIME(create_time, '%Y-%m-%d') <=  '{$date}'";
            $arrOutPut['startDate'] = $arrOutPut['endDate'] = '';
        }


        //顾问列表查询
        if (!empty($get['user_name'])) {
            $salesman_id = implode(',',$get['user_name']);
            $arrAndWhere  .= " and salesman_id in ({$salesman_id})";
            $arrOutPut['username'] = json_encode($get['user_name']);
        } else {
            $arrOutPut['username'] = null;
        }


        //状态筛选
        if (!empty($get['status'])) {

            if (count($get['status']) != 2) {

                foreach ($get['status'] as $v) {

                    if ($v == 'is_fail') {

                        $arrAndWhere .= ' and is_fail = 1';
                    }

                    if ($v == 'status') {
                        $arrAndWhere .= ' and is_fail = 0';
                    }
                }
            }

            $arrOutPut['status'] = json_encode($get['status']);

        } else {
            $arrOutPut['status'] = null;
        }

        //信息来源筛选
        if (!empty($get['sourve'])) {
            $sourve = implode(',',$get['sourve']);
            $arrAndWhere  .= " and clue_source in ({$sourve})";
            $arrOutPut['sourve'] = json_encode($get['sourve']);
        } else {
            $arrOutPut['sourve'] = null;
        }

        $arrOutPut['so'] = (isset($get['so']) ? trim($get['so']) : '');
        //搜索的时候才会用到查询条件
        if ($arrOutPut['so']) {
            $arrAndWhere .= " and (customer_name like '%{$arrOutPut['so']}%'";
            $arrAndWhere .= " or customer_phone like '%{$arrOutPut['so']}%'";
            $arrAndWhere .= " or intention_des like '%{$arrOutPut['so']}%'";
            $arrAndWhere .= " or des like '%{$arrOutPut['so']}%'";
            $arrAndWhere .= " or create_person_name like '%{$arrOutPut['so']}%'";
            $arrAndWhere .= " or salesman_name like '%{$arrOutPut['so']}%' )";

        }

        if (empty($get['create_time']) && empty($get['last_view_time'])) {

            $orderby                  = 'create_time desc';
            $arrOutPut['create_time'] = 'desc';

        } else {

            if (!empty($get['create_time'])) {

                $orderby                  = "create_time {$get['create_time']}";
                $arrOutPut['create_time'] = $get['create_time'];

            } else {
                $arrOutPut['create_time'] = null;
            }

        }
        if (!empty($get['last_view_time'])) {

            $orderby                     = "last_view_time  {$get['last_view_time']}";
            $arrOutPut['last_view_time'] = $get['last_view_time'];

        } else {

            $arrOutPut['last_view_time'] = null;
        }

        $strSelect = 'id,customer_id,customer_name,customer_phone,intention_des,clue_source,des,create_time,last_view_time,salesman_id,salesman_name,create_person_name,is_fail,shop_id,STATUS';
        $sql1 = "select {$strSelect} from crm_clue";
        $sql2 = "select {$strSelect} from crm_clue_wuxiao";
        $sql = " select {$strSelect} from ( {$sql1} union {$sql2}) as tmp where {$arrAndWhere} order by {$orderby}";


        if (isset($get['isDownload']) && intval($get['isDownload']) == 1)//下载时不需要分页,直接下载全部
        {
            $arrList = Yii::$app->db->createCommand($sql)->queryAll();
        } else {
            $countQuery = Yii::$app->db->createCommand($sql)->queryAll();
            $intTotal   = count($countQuery);
            //分页
            $pages = new Pagination([
                'defaultPageSize' => $this->intPageSize,
                'totalCount' => $intTotal,
            ]);

            $sql .= "  limit $pages->offset,$pages->limit ";
            $arrList              = Yii::$app->db->createCommand($sql)->queryAll();
            $arrOutPut['list']    = $arrList;
            $arrOutPut['count']   = $intTotal;
            $arrOutPut['objPage'] = $pages;
        }

        $arrOutPut['objDataDic'] = $objDataDic = new DataDictionary();//数据字典操作

        //信息来源
        $arrOutPut['source'] = $objDataDic->getDictionaryData('source');

        if (isset($get['isDownload']) && intval($get['isDownload']) == 1) {
            //列表导出的时候记录日志
            $this->arrLogParam = [
                'date_1' => ($arrOutPut['startDate'] ? $arrOutPut['startDate'] : '-'),
                'date_2' => ($arrOutPut['endDate'] ? $arrOutPut['endDate'] : '-')
            ];

            //输出excel数据
            $arrColumns = ['序号', '姓名', '手机号码', '客户来源', '意向车型', '说明', '创建日', '创建人', '归属顾问', '最近联系', '状态'];
            $arrModels  = [];
            foreach ($arrList as $k => $items) {
                $arrModels[$k] = [
                    $k + 1,//序号
                    $items['customer_name'],//姓名
                    $items['customer_phone'],//手机号码
                    $objDataDic->getSourceName($items['clue_source']),//客户来源
                    $items['intention_des'],//意向车型
                    $items['des'],//说明
                    empty($items['create_time']) ? '' :date('Y-m-d', $items['create_time']),//创建日
                    $items['create_person_name'],//创建人
                    $items['salesman_name'],//归属顾问
                    empty($items['last_view_time']) ? '' :date('Y-m-d', $items['last_view_time']),//最近联系
                    ($items['is_fail'] == 1 ? '已战败' : '跟进中'),//状态
                ];
            }


            $this->outPutExcel('线索客户', $arrColumns, $arrModels);
        } else {

            //获取门店 - 该界面是门店数据，等级传入固定的30 - 门店
            $objSelectDataLogic = new JsSelectDataLogic();
//            $arrSelectorgList = $objSelectDataLogic->getSelectOrgNew($session['userinfo']['permisson_org_ids'], 30, true);
            $arrSelectorgList = $objSelectDataLogic->getSelectOrgNew($session['userinfo']['permisson_org_ids'], $session['userinfo']['role_level']);
            $arrOutPut['selectOrgJson'] = json_encode($arrSelectorgList);
            $arrOutPut['get'] = $get;
            //显示页面
            return $this->render('clueList', $arrOutPut);
        }
    }

    public function actionGetIntentionCustomer()
    {
        //权限控制 - 门店
        $this->checkPermission('/customer/get-intention-customer', 3);

        //意向客户  status = 1
        $arrOutPut     = [];
        $arrAndWhere   = [
            'and',
            ['is_fail' => 0], ['status' => 1],
            ['>', 'salesman_id', 0],//有销售正在对接
        ];
        $session       = Yii::$app->getSession();
        $get = Yii::$app->request->get();
       //获取当前用户id、shop_id
        if(isset($get['shop_id']) && !empty($get['shop_id']))
        {
            $arrGetShopIds = explode(',', $get['shop_id']);
            $shopId = $arrGetShopIds[count($arrGetShopIds)-1];
        }
        else
        {
            $get['shop_id'] = $shopId = $this->getDefaultShopId();
        }
        $arrAndWhere[] = ['=', 'shop_id', $shopId];
        
        //建卡时间
        if (isset($get['createCardTime']) && preg_match('/^\d{4}\-\d{2}\-\d{2} \- \d{4}\-\d{2}\-\d{2}$/', trim($get['createCardTime']))) {
            //输入了时间搜索条件 且时间条件格式正确
            list($startCreateCardDate, $endCreateCardDate) = explode(' - ', trim($get['createCardTime']));
            $arrAndWhere[]                    = ['>=', 'create_card_time', strtotime($startCreateCardDate)];//建卡时间限制
            $arrAndWhere[]                    = ['<', 'create_card_time', strtotime($endCreateCardDate) + 86399];//建卡时间限制
            $arrOutPut['startCreateCardDate'] = $startCreateCardDate;
            $arrOutPut['endCreateCardDate']   = $endCreateCardDate;
        } else {
            $arrOutPut['startCreateCardDate'] = $arrOutPut['endCreateCardDate'] = '';
        }
        $arrOutPut['so'] = (isset($get['so']) ? trim($get['so']) : '');

        //搜索的时候才会用到查询条件
        if ($arrOutPut['so']) {
            $arrOrWhere = [
                'or',
                ['like', 'customer_name', $arrOutPut['so']],//姓名
                ['like', 'customer_phone', $arrOutPut['so']],//手机
                ['like', 'intention_des', $arrOutPut['so']],//车型
                ['like', 'salesman_name', $arrOutPut['so']],//顾问
            ];
        }
        $strSelect = 'id,customer_id,customer_name,customer_phone,intention_level_des,intention_des,clue_source,create_card_time,last_view_time,salesman_id,salesman_name';

        //信息来源筛选
        if (!empty($get['sourve'])) {
            $arrAndWhere[]       = ['in', 'clue_source', $get['sourve']];
            $arrOutPut['sourve'] = json_encode($get['sourve']);
        } else {
            $arrOutPut['sourve'] = null;
        }

        //意向等级筛选
        if (!empty($get['intention'])) {
            $arrAndWhere[]           = ['in', 'intention_level_id', $get['intention']];
            $arrOutPut['intentions'] = json_encode($get['intention']);
        } else {
            $arrOutPut['intentions'] = null;
        }

        //排序
        if (empty($get['create_card_time']) && empty($get['last_view_time'])) {

            $orderby                       = 'create_card_time desc';
            $arrOutPut['create_card_time'] = 'desc';

        } else {

            if (!empty($get['create_card_time'])) {

                $orderby                       = "create_card_time {$get['create_card_time']}";
                $arrOutPut['create_card_time'] = $get['create_card_time'];

            } else {
                $arrOutPut['create_card_time'] = null;
            }


        }
        if (!empty($get['last_view_time'])) {

            $orderby                     = "last_view_time  {$get['last_view_time']}";
            $arrOutPut['last_view_time'] = $get['last_view_time'];

        } else {

            $arrOutPut['last_view_time'] = null;
        }

        $query = Clue::find()->select($strSelect)->where($arrAndWhere)->orderBy($orderby);
        //用到了搜索功能
        !empty($arrOutPut['so']) && $query->andWhere($arrOrWhere);
        if (isset($get['isDownload']) && intval($get['isDownload']) == 1) //下载模式  数据不分页   直接全部导出
        {
            $arrList = $query->asArray()->all();
        } else {
            $countQuery = clone $query;
            $intTotal   = $countQuery->count();
            $pages      = new Pagination(['totalCount' => $intTotal]);//分页信息
            $pages->setPageSize($this->intPageSize);
            $arrList              = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();
            $arrOutPut['list']    = $arrList;
            $arrOutPut['count']   = $intTotal;
            $arrOutPut['objPage'] = $pages;
        }
        //       die($query->createCommand()->getRawSql()); // 查看拼接的sql语句
        $arrOutPut['objDataDic'] = $objDataDic = new DataDictionary();//数据字典操作

        //信息来源
        $arrOutPut['source'] = $objDataDic->getDictionaryData('source');

        //意向等级
        $arrOutPut['intention'] = $objDataDic->getDictionaryData('intention');

        if (isset($get['isDownload']) && intval($get['isDownload']) == 1) {
            //列表导出的时候记录日志
            $this->arrLogParam = [
                'date_1' => ($arrOutPut['startCreateCardDate'] ? $arrOutPut['startCreateCardDate'] : '-'),
                'date_2' => ($arrOutPut['endCreateCardDate'] ? $arrOutPut['endCreateCardDate'] : '-')
            ];

            //输出excel数据
            $arrColumns = ['序号', '姓名', '手机号码', '客户来源', '意向等级', '意向车型', '建卡日期', '最近联系', '归属顾问'];
            $arrModels  = [];
            foreach ($arrList as $k => $items) {
                $arrModels[$k] = [
                    $k + 1,//序号
                    $items['customer_name'],//姓名
                    $items['customer_phone'],//手机号码
                    $objDataDic->getSourceName($items['clue_source']),//客户来源
                    $items['intention_level_des'],//意向车型
                    $items['intention_des'],//意向车型
                    date('Y-m-d H:i:s', $items['create_card_time']),//建卡日期
                    empty($items['last_view_time']) ? '' :date('Y-m-d H:i:s', $items['last_view_time']),//最近联系
                    $items['salesman_name'],//归属顾问
                ];
            }
            $this->outPutExcel('意向客户', $arrColumns, $arrModels);
        } else {

            //获取门店 - 该界面是门店数据，等级传入固定的30 - 门店
            $objSelectDataLogic = new JsSelectDataLogic();
            $arrSelectorgList = $objSelectDataLogic->getSelectOrgNew($session['userinfo']['permisson_org_ids'], $session['userinfo']['role_level']);
            $arrOutPut['selectOrgJson'] = json_encode($arrSelectorgList);
            $arrOutPut['get'] = $get;
            //显示页面
            return $this->render('intentionList', $arrOutPut);
        }
    }

    public function actionGetOrderCustomer()
    {
        //权限控制 - 门店
        $this->checkPermission('/customer/get-order-customer', 3);

        $arrOutPut     = [];
        $arrAndWhere   = [
            'and',
            ['c.is_fail' => 0],
            ['<>', 'o.status', '6'],
            ['>', 'c.salesman_id', 0],//有销售正在对接
        ];
        $session = Yii::$app->getSession();
        $get = Yii::$app->request->get();
       //获取当前用户id、shop_id
        if(isset($get['shop_id']) && !empty($get['shop_id']))
        {
            $arrGetShopIds = explode(',', $get['shop_id']);
            $shopId = $arrGetShopIds[count($arrGetShopIds)-1];
        }
        else
        {
            $get['shop_id'] = $shopId = $this->getDefaultShopId();
        }
        $arrAndWhere[] = ['=', 'c.shop_id', $shopId];
        //订车客户 status = 2 
        //建卡时间 输入了时间搜索条件 且时间条件格式正确
        if (isset($get['createCardTime']) && preg_match('/^\d{4}\-\d{2}\-\d{2} \- \d{4}\-\d{2}\-\d{2}$/', trim($get['createCardTime']))) {
            list($startCreateCardDate, $endCreateCardDate) = explode(' - ', trim($get['createCardTime']));
            $arrAndWhere[]                    = ['>=', 'c.create_card_time', strtotime($startCreateCardDate)]; //建卡时间限制
            $arrAndWhere[]                    = ['<', 'c.create_card_time', strtotime($endCreateCardDate) + 86399]; //建卡时间限制
            $arrOutPut['startCreateCardDate'] = $startCreateCardDate;
            $arrOutPut['endCreateCardDate']   = $endCreateCardDate;
        } else {
            $arrOutPut['startCreateCardDate'] = $arrOutPut['endCreateCardDate'] = '';
        }
        //订车时间
        if (isset($get['orderTime']) && preg_match('/^\d{4}\-\d{2}\-\d{2} \- \d{4}\-\d{2}\-\d{2}$/', trim($get['orderTime']))) {
            list($startOrderDate, $endOrderDate) = explode(' - ', trim($get['orderTime']));
            $arrAndWhere[]               = ['>=', 'o.create_time', strtotime($startOrderDate)]; //订车时间限制
            $arrAndWhere[]               = ['<', 'o.create_time', strtotime($endOrderDate) + 86399]; //订车时间限制
            $arrOutPut['startOrderDate'] = $startOrderDate;
            $arrOutPut['endOrderDate']   = $endOrderDate;
        } else {
            $arrOutPut['startOrderDate'] = $arrOutPut['endOrderDate'] = '';
        }
        //搜索的时候才会用到查询条件
        $arrOutPut['so'] = (isset($get['so']) ? trim($get['so']) : '');
        if ($arrOutPut['so']) {
            $arrOrWhere = [
                'or',
                ['like', 'c.customer_name', $arrOutPut['so']],//姓名
                ['like', 'c.customer_phone', $arrOutPut['so']],//手机
                ['like', 'c.intention_des', $arrOutPut['so']],//车型
            ];
        }
        //本次查询用到两边联查  crm_clue 和 crm_order  原因是搜索条件在两张表中都有
        $strSelect = 'c.id, c.customer_id, c.customer_name, c.customer_phone,'
            . 'c.create_card_time, o.create_time, o.predict_car_delivery_time, o.car_type_id,o.car_type_name,'
            . 'o.color_configure, o.deposit,o.buy_type, o.is_insurance, o.status';
        $query     = Clue::find()->select($strSelect)->from('crm_clue as c')->join('INNER JOIN', 'crm_order as o', 'c.id = o.clue_id');

        $arrOutPut['create_time']               = null;
        $arrOutPut['create_card_time']          = null;
        $arrOutPut['predict_car_delivery_time'] = null;
        $arrOutPut['is_insurance']              = null;
        $arrOutPut['buy_types']                 = null;
        $arrOutPut['status']                    = null;

        //列表筛选  	本店投保
        if (!empty($get['is_insurance'])) {
            $arrAndWhere[]             = ['in', 'is_insurance', $get['is_insurance']];
            $arrOutPut['is_insurance'] = json_encode($get['is_insurance']);
        }

        //列表筛选  	购买方式
        if (!empty($get['buy_type'])) {
            $arrAndWhere[]          = ['in', 'o.buy_type', $get['buy_type']];
            $arrOutPut['buy_types'] = json_encode($get['buy_type']);
        }

        //列表筛选  	状态
        if (!empty($get['status'])) {
            $arrAndWhere[]       = ['in', 'o.status', $get['status']];
            $arrOutPut['status'] = json_encode($get['status']);
        }

        //列表排序
        if (empty($get['create_time']) &&
            empty($get['create_card_time']) &&
            empty($get['predict_car_delivery_time'])
        ) {

            $orderby                  = 'o.create_time desc';
            $arrOutPut['create_time'] = 'desc';

        } else {

            if (!empty($get['create_time'])) {

                $orderby                  = "o.create_time  {$get['create_time']}";
                $arrOutPut['create_time'] = $get['create_time'];

            } else if (!empty($get['create_card_time'])) {

                $orderby                       = "c.create_card_time  {$get['create_card_time']}";
                $arrOutPut['create_card_time'] = $get['create_card_time'];

            } else if (!empty($get['predict_car_delivery_time'])) {

                $orderby                                = "o.predict_car_delivery_time  {$get['predict_car_delivery_time']}";
                $arrOutPut['predict_car_delivery_time'] = $get['predict_car_delivery_time'];
            }


        }

        $query->where($arrAndWhere)->orderBy($orderby);
        //用到了搜索功能
        !empty($arrOutPut['so']) && $query->andWhere($arrOrWhere);

        if (isset($get['isDownload']) && intval($get['isDownload']) == 1) //下载模式  数据不分页   直接全部导出
        {
            $arrList = $query->asArray()->all();
        } else {
            $countQuery = clone $query;
            $intTotal   = $countQuery->count();
            $pages      = new Pagination(['totalCount' => $intTotal]);//分页信息
            $pages->setPageSize($this->intPageSize);
            $arrList              = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();
            $arrOutPut['list']    = $arrList;
            $arrOutPut['count']   = $intTotal;
            $arrOutPut['objPage'] = $pages;
        }
        // die($query->createCommand()->getRawSql()); // 查看拼接的sql语句
        $arrOutPut['objDataDic'] = $objDataDic = new DataDictionary();//数据字典操作
        //购买方式
        $arrOutPut['buy_type'] = $objDataDic->getDictionaryData('buy_type');
        if (isset($get['isDownload']) && intval($get['isDownload']) == 1) {
            //列表导出的时候记录日志
            $this->arrLogParam = [
                'date_1' => ($arrOutPut['startCreateCardDate'] ? $arrOutPut['startCreateCardDate'] : '-'),
                'date_2' => ($arrOutPut['endCreateCardDate'] ? $arrOutPut['endCreateCardDate'] : '-'),
                'date_3' => ($arrOutPut['startOrderDate'] ? $arrOutPut['startOrderDate'] : '-'),
                'date_4' => ($arrOutPut['endOrderDate'] ? $arrOutPut['endOrderDate'] : '-')
            ];

            //输出 excel 数据
            $arrColumns = ['序号', '姓名', '手机号码', '建卡日期', '订车日期', '预计交车日期', '车型（车系）', '颜色', '订金', '购买方式', '本店投保', '状态'];
            $arrModels  = [];
            $statusDes = null;
            foreach ($arrList as $k => $items) {
                $predict_car_delivery_time = empty($items['predict_car_delivery_time']) ? '--' : date('Y-m-d',$items['predict_car_delivery_time']);
                if ($items['status'] == 1) $statusDes = '处理中';
                else if ($items['status'] == 2) $statusDes = '客户未支付';
                else if ($items['status'] == 3) $statusDes = '财务到账';
                else if ($items['status'] == 4) $statusDes = '战败';
                else if ($items['status'] == 5) $statusDes = '客户已支付';
                $arrModels[$k] = [
                    ($k + 1),//序号
                    $items['customer_name'],//姓名
                    $items['customer_phone'],//手机号码
                    date('Y-m-d', $items['create_card_time']),//建卡日期
                    date('Y-m-d', $items['create_time']),//订车日期
                    $predict_car_delivery_time,//预计交车日期
                    $items['car_type_name'],//车型（车系）
                    $items['color_configure'],//颜色配置
                    $items['deposit'],//订金
                    $objDataDic->getBuyTypeName($items['buy_type']),//购买方式
                    ($items['is_insurance'] == 1 ? '是' : '否'),//本店投保
                    ($statusDes),//状态
                ];
            }
            $this->outPutExcel('订车客户', $arrColumns, $arrModels);
        } else {
            //获取门店 - 该界面是门店数据，等级传入固定的30 - 门店
            $objSelectDataLogic = new JsSelectDataLogic();
            $arrSelectorgList = $objSelectDataLogic->getSelectOrgNew($session['userinfo']['permisson_org_ids'], $session['userinfo']['role_level']);
            $arrOutPut['selectOrgJson'] = json_encode($arrSelectorgList);
            $arrOutPut['get'] = $get;
            //显示页面
            return $this->render('orderList', $arrOutPut);
        }
    }

    public function actionGetSuccessCustomer()
    {
        //权限控制 - 门店
        $this->checkPermission('/customer/get-success-customer', 3);
        
        $arrOutPut     = [];
        $arrAndWhere   = [
            'and',
            ['c.is_fail' => 0],
            ['o.status' => 6],
            ['>', 'c.salesman_id', 0],//有销售正在对接
        ]; //交车客户 status = 3
        $session = Yii::$app->getSession();
        $get = Yii::$app->request->get();
       //获取当前用户id、shop_id
        if(isset($get['shop_id']) && !empty($get['shop_id']))
        {
            $arrGetShopIds = explode(',', $get['shop_id']);
            $shopId = $arrGetShopIds[count($arrGetShopIds)-1];
        }
        else
        {
            $get['shop_id'] = $shopId = $this->getDefaultShopId();
        }

        $arrAndWhere[] = ['=', 'c.shop_id', $shopId];
        //建卡时间 输入了时间搜索条件 且时间条件格式正确
        if (isset($get['createCardTime']) && preg_match('/^\d{4}\-\d{2}\-\d{2} \- \d{4}\-\d{2}\-\d{2}$/', trim($get['createCardTime']))) {
            list($startCreateCardDate, $endCreateCardDate) = explode(' - ', trim($get['createCardTime']));
            $arrAndWhere[]                    = ['>=', 'c.create_card_time', strtotime($startCreateCardDate)]; //建卡时间限制
            $arrAndWhere[]                    = ['<', 'c.create_card_time', strtotime($endCreateCardDate) + 86399]; //建卡时间限制
            $arrOutPut['startCreateCardDate'] = $startCreateCardDate;
            $arrOutPut['endCreateCardDate']   = $endCreateCardDate;
        } else {
            $arrOutPut['startCreateCardDate'] = $arrOutPut['endCreateCardDate'] = '';
        }
        //交车时间
        if (isset($get['deliveryTime']) && preg_match('/^\d{4}\-\d{2}\-\d{2} \- \d{4}\-\d{2}\-\d{2}$/', trim($get['deliveryTime']))) {
            list($startDeliveryDate, $endDeliveryDate) = explode(' - ', trim($get['deliveryTime']));
            $arrAndWhere[]                  = ['>=', 'o.car_delivery_time', strtotime($startDeliveryDate)]; //预计交车时间限制
            $arrAndWhere[]                  = ['<', 'o.car_delivery_time', strtotime($endDeliveryDate) + 86399];//预计交车时间限制
            $arrOutPut['startDeliveryDate'] = $startDeliveryDate;
            $arrOutPut['endDeliveryDate']   = $endDeliveryDate;
        } else {
            $arrOutPut['startDeliveryDate'] = $arrOutPut['endDeliveryDate'] = '';
        }

        //搜索的时候才会用到查询条件
        $arrOutPut['so'] = (isset($get['so']) ? trim($get['so']) : '');
        if ($arrOutPut['so']) {
            $arrOrWhere = [
                'or',
                ['like', 'c.customer_name', $arrOutPut['so']],//姓名
                ['like', 'c.customer_phone', $arrOutPut['so']],//手机
                ['like', 'c.intention_des', $arrOutPut['so']],//车型
                ['like', 'c.salesman_name', $arrOutPut['so']],//顾问
            ];
        }

        //本次查询用到两边联查  crm_clue 和 crm_order  原因是搜索条件在两张表中都有
        $strSelect = 'c.id, c.customer_id, c.customer_name, c.customer_phone, c.salesman_name,'
            . 'c.create_card_time, o.create_time, o.predict_car_delivery_time, o.car_type_id,'
            . 'o.color_configure,o.car_delivery_time,o.buy_type, o.is_insurance, o.is_add, o.status';
        $query     = Clue::find()->select($strSelect)->from('crm_clue as c')->join('INNER JOIN', 'crm_order as o', 'c.id = o.clue_id');

        $arrOutPut['is_insurance']      = null;
        $arrOutPut['buy_types']         = null;
        $arrOutPut['is_add']            = null;
        $arrOutPut['is_insurance']      = null;
        $arrOutPut['create_card_time']  = null;
        $arrOutPut['car_delivery_time'] = null;

        //列表筛选  	本店投保
        if (!empty($get['is_insurance'])) {
            $arrAndWhere[]             = ['in', 'is_insurance', $get['is_insurance']];
            $arrOutPut['is_insurance'] = json_encode($get['is_insurance']);
        }

        //列表筛选  	购买方式
        if (!empty($get['buy_type'])) {
            $arrAndWhere[]          = ['in', 'o.buy_type', $get['buy_type']];
            $arrOutPut['buy_types'] = json_encode($get['buy_type']);
        }

        //列表筛选  	加装
        if (!empty($get['is_add'])) {
            $arrAndWhere[]       = ['in', 'o.is_add', $get['is_add']];
            $arrOutPut['is_add'] = json_encode($get['is_add']);
        }


        //列表排序
        if (
            empty($get['create_card_time']) &&
            empty($get['car_delivery_time'])
        ) {

            $orderby                        = 'o.car_delivery_time desc';
            $arrOutPut['car_delivery_time'] = 'desc';

        } else {

            if (!empty($get['create_card_time'])) {

                $orderby                       = "c.create_card_time  {$get['create_card_time']}";
                $arrOutPut['create_card_time'] = $get['create_card_time'];

            } else if (!empty($get['car_delivery_time'])) {

                $orderby                        = "o.car_delivery_time  {$get['car_delivery_time']}";
                $arrOutPut['car_delivery_time'] = $get['car_delivery_time'];
            }


        }
        $query->where($arrAndWhere)->orderBy($orderby);
        //用到了搜索功能
        !empty($arrOutPut['so']) && $query->andWhere($arrOrWhere);
        if (isset($get['isDownload']) && intval($get['isDownload']) == 1) //下载模式  数据不分页   直接全部导出
        {
            $arrList = $query->asArray()->all();
        } else {
            $countQuery = clone $query;
            $intTotal   = $countQuery->count();
            $pages      = new Pagination(['totalCount' => $intTotal]);//分页信息
            $pages->setPageSize($this->intPageSize);
            $arrList              = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();
            $arrOutPut['list']    = $arrList;
            $arrOutPut['count']   = $intTotal;
            $arrOutPut['objPage'] = $pages;
        }
        //       die($query->createCommand()->getRawSql()); // 查看拼接的sql语句
        $arrOutPut['objDataDic'] = $objDataDic = new DataDictionary();//数据字典操作
        $arrOutPut['carDataDic'] = $carObj = new CarBrandAndType();//车数据
        //购买方式
        $arrOutPut['buy_type'] = $objDataDic->getDictionaryData('buy_type');
        if (isset($get['isDownload']) && intval($get['isDownload']) == 1) {
            //列表导出的时候记录日志
            $this->arrLogParam = [
                'date_1' => ($arrOutPut['startCreateCardDate'] ? $arrOutPut['startCreateCardDate'] : '-'),
                'date_2' => ($arrOutPut['endCreateCardDate'] ? $arrOutPut['endCreateCardDate'] : '-'),
                'date_3' => ($arrOutPut['startDeliveryDate'] ? $arrOutPut['startDeliveryDate'] : '-'),
                'date_4' => ($arrOutPut['endDeliveryDate'] ? $arrOutPut['endDeliveryDate'] : '-')
            ];

            //输出excel数据
            $arrColumns = ['序号', '姓名', '手机号码', '建卡日期', '购车日期', '车型（车系）', '颜色', '购买方式', '本店投保', '是否加装', '交车顾问'];
            $arrModels  = [];
            foreach ($arrList as $k => $items) {
                $arrModels[$k] = [
                    $k + 1,//序号
                    $items['customer_name'],//姓名
                    $items['customer_phone'],//手机号码
                    empty($items['create_card_time']) ? '' :date('Y-m-d', $items['create_card_time']),//建卡日期
                    empty($items['car_delivery_time']) ? '' :date('Y-m-d', $items['car_delivery_time']),//购车日期
                    $objDataDic->getCarName($items['car_type_id']),//车型（车系）
                    $items['color_configure'],//颜色配置
                    $objDataDic->getBuyTypeName($items['buy_type']),//购买方式
                    ($items['is_insurance'] == 1 ? '是' : '否'),//本店投保
                    ($items['is_add'] == 1 ? '是' : '否'),//是否加装
                    $items['salesman_name'],//交车顾问
                ];
            }

            $this->outPutExcel('交车客户', $arrColumns, $arrModels);
        } else {
            //获取门店 - 该界面是门店数据，等级传入固定的30 - 门店
            $objSelectDataLogic = new JsSelectDataLogic();
            $arrSelectorgList = $objSelectDataLogic->getSelectOrgNew($session['userinfo']['permisson_org_ids'], $session['userinfo']['role_level']);
            $arrOutPut['selectOrgJson'] = json_encode($arrSelectorgList);
            $arrOutPut['get'] = $get;

            //显示页面
            return $this->render('successList', $arrOutPut);
        }
    }

    /**
     * 保有客户重新分配
     */
    public function actionAjaxKeepCustomerReset()
    {
        $arrCustomerIds = Yii::$app->request->post('customer_ids');
        $intSalemanId   = Yii::$app->request->post('saleman_id');
        $intSalemanName = Yii::$app->request->post('saleman_name');

        //获取session
        $user_info = Yii::$app->session->get('userinfo');
        //获取当前用户id
        $operatorId   = $user_info['id'];
        $operatorName = $user_info['name'];
        foreach ($arrCustomerIds as $customerId) {
            $arrWhere = ['customer_id' => $customerId];
            $objClue  = Clue::find()->where($arrWhere)->one();
            if ($objClue) {
                //添加保有客户
                \common\logic\UserHistoryLogic::instance()->addUserHistory($objClue, '保有客户', $operatorId, $operatorName);
                $objClue->salesman_id   = $intSalemanId;
                $objClue->salesman_name = $intSalemanName;
                $objClue->save();
            }
        }
        $arrRtn = ['code' => 0, 'errMsg' => ''];

        //发布推送
        $noticeTemplet = new NoticeTemplet();
        //线索总数
        $clue_num = count($arrCustomerIds);
        //查询线索并处理id
        $clue_list = Clue::find()->select('id')->where(['in','customer_id',$arrCustomerIds])->asArray()->all();
        $clue_id_list = array_column($clue_list,'id');

        $clue_id_str = implode(',',$clue_id_list);
        $noticeTemplet->reassignReminderNotice($operatorId, $intSalemanId ,$clue_num,$clue_id_str);

        die(json_encode($arrRtn));
    }

    /**
     * 意向客户推送任务
     */
    public function actionAjaxIntentionCustomerAddTask()
    {
        $arrRtn     = ['code' => 1, 'errMsg' => '参数错误'];
        $arrClueIds = Yii::$app->request->post('clue_ids');
        $session = Yii::$app->getSession();

        if (!empty($arrClueIds) && is_array($arrClueIds)) {
            //拿去线索对应的销售型
            $arrWhere  = ['in', 'id', $arrClueIds];
            $strSelect = 'shop_id, id, customer_id, salesman_id';
            $list      = Clue::find()->select($strSelect)->where($arrWhere)->asArray()->all();
            if ($list) {
                $arrTasks = [];

                //获取session
                $user_info = Yii::$app->session->get('userinfo');
                //获取当前用户id
                $who_assign_id = $user_info['id'];
                $notice_templet = new NoticeTemplet();

                $push_list = [];
                foreach ($list as $val) {
                	if(empty($val['shop_id']) || empty($val['customer_id']) || empty($val['salesman_id'])){//无顾问  无门店  无客户的 不生成电话任务
                		continue;
                	}
                    $arrTasks[] = [
                        'shop_id' => $val['shop_id'],
                        'clue_id' => $val['id'],
                        'customer_id' => $val['customer_id'],
                        'salesman_id' => $val['salesman_id'],
                        'task_date' => date('Y-m-d'),
                        'task_time' => time(),
                        'task_from' => '店长主动分配',
                        'task_type' => 1,//电话任务
                        'is_cancel' => 0,
                        'task_des' => date('m月d日').$session['userinfo']['name'].'分配',
                    ];
                    $push_list[$val['salesman_id']][] = $val;
                    //推送电话任务
//                    $notice_templet->telephoneTaskNotice($who_assign_id, $val['salesman_id'] ,1);
                }

                //防止给同一顾问推送多任务时  多个推送  改为一条推送
                //推送电话任务
                foreach ($push_list as $key=>$value){
                    $notice_templet->telephoneTaskNotice($who_assign_id, $key ,count($value));
                }

                $arrItems = array_keys($arrTasks[0]);
                //批量插入电话任务
                $rtn = Yii::$app->db->createCommand()->batchInsert(Task::tableName(), $arrItems, $arrTasks)->execute();
                if ($rtn) {
                    $arrRtn = ['code' => 0, 'errMsg' => ''];
                } else {
                    $arrRtn = ['code' => 3, 'errMsg' => '数据库操作失败'];
                }
            } else {
                $arrRtn = ['code' => 2, 'errMsg' => '数据为空'];
            }
        }
        die(json_encode($arrRtn));
    }


    public function actionGetKeepCustomer()
    {
        //权限控制 - 门店
        $this->checkPermission('/customer/get-keep-customer', 3);

        //保有客户 is_keep  = 1;  customer 表和订单表联查
        $get           = Yii::$app->request->get();
        $arrOutPut     = [];
        $arrAndWhere   = [
            'and',
            ['cu.is_keep' => 1]
        ];//线索
        $session       = Yii::$app->getSession();
       //获取当前用户id、shop_id
        if(isset($get['shop_id']) && !empty($get['shop_id']) && in_array($get['shop_id'], $session['userinfo']['permisson_org_ids']))
        {
            $shopId = $get['shop_id'];
        }
        else
        {
            $get['shop_id'] = $shopId = $this->getDefaultShopId();
        }
        $arrAndWhere[] = ['=', 'o.shop_id', $shopId];
        //交车时间（购车时间）
        if (isset($get['deliveryTime']) && preg_match('/^\d{4}\-\d{2}\-\d{2} \- \d{4}\-\d{2}\-\d{2}$/', trim($get['deliveryTime']))) {
            list($startDeliveryDate, $endDeliveryDate) = explode(' - ', trim($get['deliveryTime']));
            $arrAndWhere[]                  = ['>=', 'o.car_delivery_time', strtotime($startDeliveryDate)]; //预计交车时间限制
            $arrAndWhere[]                  = ['<', 'o.car_delivery_time', strtotime($endDeliveryDate) + 86399];//预计交车时间限制
            $arrOutPut['startDeliveryDate'] = $startDeliveryDate;
            $arrOutPut['endDeliveryDate']   = $endDeliveryDate;
        } else {
            $arrOutPut['startDeliveryDate'] = $arrOutPut['endDeliveryDate'] = '';
        }
        $arrOutPut['so'] = (isset($get['so']) ? trim($get['so']) : '');
        if ($arrOutPut['so']) {
            $arrOrWhere = [
                'or',
                ['like', 'cu.name', $arrOutPut['so']],//姓名
                ['like', 'cu.phone', $arrOutPut['so']],//手机号
                ['like', 'o.car_type_name', $arrOutPut['so']],//车型
                ['like', 'o.frame_number', $arrOutPut['so']],//车架号
                ['like', 'o.car_number', $arrOutPut['so']],//车牌号
            ];
        }

        //本次查询用到两边联查  crm_clue 和 crm_order  原因是搜索条件在两张表中都有
        $strSelect = 'cu.id, cu.name, cu.phone, o.car_number, o.frame_number, o.clue_id, '
            . 'o.car_type_name, o.car_delivery_time, o.buy_type, o.is_insurance';
        $query     = Customer::find()->select($strSelect)->from('crm_customer as cu')->join('INNER JOIN', 'crm_order as o', 'cu.id = o.customer_id');
        $query->where($arrAndWhere)->orderBy('o.car_delivery_time desc');
        //用到了搜索功能
        !empty($arrOutPut['so']) && $query->andWhere($arrOrWhere);
        if (isset($get['isDownload']) && intval($get['isDownload']) == 1) //下载模式  数据不分页   直接全部导出
        {
            $arrList = $query->asArray()->all();
        } else {
            $countQuery = clone $query;
            $intTotal   = $countQuery->count();
            $pages      = new Pagination(['totalCount' => $intTotal]);//分页信息
            $pages->setPageSize($this->intPageSize);
            $arrList              = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();
            $arrOutPut['objPage'] = $pages;
        }
        //       die($query->createCommand()->getRawSql()); // 查看拼接的sql语句
        $arrClueIds = array_map(function ($v) {
            return $v['clue_id'];
        }, $arrList);
        //销售人员列表 [ '线索id' => '销售顾问', ......]
        $salesmanList = $this->getSalesNameFromClueByClueId($arrClueIds);
        $objDataDic   = new DataDictionary();//数据字典操作
        foreach ($arrList as &$val) {
            $val['salesman_name'] = $salesmanList[$val['clue_id']];
            $val['buy_type_name'] = $objDataDic->getBuyTypeName($val['buy_type']);
        }
        if (isset($get['isDownload']) && intval($get['isDownload']) == 1) {
            //列表导出的时候记录日志
            $this->arrLogParam = [
                'date_1' => ($arrOutPut['startDeliveryDate'] ? $arrOutPut['startDeliveryDate'] : '-'),
                'date_2' => ($arrOutPut['endDeliveryDate'] ? $arrOutPut['endDeliveryDate'] : '-'),
            ];

            //输出excel数据
            $arrColumns = ['序号', '姓名', '手机号码', '车牌号', '车架号', '车型（车系）', '购车日期', '归属顾问', '购买方式', '本店投保'];
            $arrModels  = [];
            foreach ($arrList as $k => $items) {
                $arrModels[$k] = [
                    $k + 1,//序号
                    $items['name'],//姓名
                    $items['phone'],//手机号码
                    $items['car_number'],//车牌号
                    $items['frame_number'],//车架号
                    $items['frame_number'],//车型（车系）
                    date('Y-m-d', $items['car_delivery_time']),//购车日期
                    $items['salesman_name'],//交车顾问
                    $items['buy_type_name'],//购买方式
                    ($items['is_insurance'] == 1 ? '是' : '否'),//本店投保
                ];
            }
            $this->outPutExcel('保有客户', $arrColumns, $arrModels);
        } else {
            //显示页面
            $arrOutPut['list']  = $arrList;
            $arrOutPut['count'] = $intTotal;
            //把门店下面的销售人员列举出来
            $objUserCenter              = new \common\logic\CompanyUserCenter();
            $userlist                   = $objUserCenter->getShopSales($shopId);
            $arrOutPut['shop_userlist'] = $userlist;
            //获取门店 - 该界面是门店数据，等级传入固定的30 - 门店
            $objSelectDataLogic = new JsSelectDataLogic();
            $arrSelectorgList = $objSelectDataLogic->getSelectOrgNew($session['userinfo']['permisson_org_ids'], 30, true);
            $arrOutPut['selectOrgJson'] = json_encode($arrSelectorgList);
            $arrOutPut['get'] = $get;
            return $this->render('keepList', $arrOutPut);
        }
    }

    //根据线索id获取对接的销售人员的名称
    private function getSalesNameFromClueByClueId($mixClueIds)
    {
        $rtn = [];
        if ($mixClueIds) {
            $arrWhere   = ['and'];
            $arrWhere[] = (is_array($mixClueIds) ? ['in', 'id', $mixClueIds] : ['=', 'id', $mixClueIds]);
            $query      = Clue::find()->select('id,salesman_name')->from('crm_clue')->where($arrWhere);
            $list       = $query->asArray()->all();
            if (!empty($list)) {
                foreach ($list as $v) {
                    $rtn[$v['id']] = $v['salesman_name'];
                }
            }
        }
        return $rtn;
    }

    public function actionGetFailCustomer()
    {
        //权限控制 - 门店
        $this->checkPermission('/customer/get-fail-customer', 3);
        
        //战败客户 is_fail = 1;
        $arrOutPut     = [];
        $arrAndWhere   = [
            'and',
            ['is_fail' => 1],
            ['>', 'status', 0],
            ['>', 'salesman_id', 0],//有销售正在对接
        ];// 0 - 表示线索客户
       //获取当前用户id、shop_id
        $get = Yii::$app->request->get();
        $session = Yii::$app->getSession();
        if(isset($get['shop_id']) && !empty($get['shop_id']))
        {
            $arrGetShopIds = explode(',', $get['shop_id']);
            $shopId = $arrGetShopIds[count($arrGetShopIds)-1];
        }
        else
        {
            $get['shop_id'] = $shopId = $this->getDefaultShopId();
        }
        $arrAndWhere[] = ['=', 'shop_id', $shopId];
        //创建时间条件
        if (isset($get['searchTime']) && preg_match('/^\d{4}\-\d{2}\-\d{2} \- \d{4}\-\d{2}\-\d{2}$/', trim($get['searchTime']))) {
            list($startDate, $endDate) = explode(' - ', trim($get['searchTime']));
            $arrAndWhere[]          = ['>=', 'create_time', strtotime($startDate)];//创建时间限制
            $arrAndWhere[]          = ['<', 'create_time', strtotime($endDate) + 86399]; //创建时间限制
            $arrOutPut['startDate'] = $startDate;
            $arrOutPut['endDate']   = $endDate;
        } else {
            $arrOutPut['startDate'] = $arrOutPut['endDate'] = '';
        }

        $arrOutPut['so'] = (isset($get['so']) ? trim($get['so']) : '');//关键词
        //搜索的时候才会用到查询条件
        if ($arrOutPut['so']) {
            $arrOrWhere = [
                'or',
                ['like', 'customer_name', $arrOutPut['so']],//姓名
                ['like', 'customer_phone', $arrOutPut['so']],//手机
                ['like', 'des', $arrOutPut['so']],//说明
                ['like', 'salesman_name', $arrOutPut['so']],//顾问
            ];
        }
        $strSelect = 'id,customer_id,status,customer_name,customer_phone,create_card_time,salesman_name,is_fail,fail_tags,fail_reason,last_fail_time,des';
        $query     = Clue::find()->select($strSelect)->where($arrAndWhere)->orderBy('last_fail_time desc');
        !empty($arrOutPut['so']) && $query->andWhere($arrOrWhere);//搜索词条件
        if (isset($get['isDownload']) && intval($get['isDownload']) == 1)//下载时不需要分页,直接下载全部
        {
            $arrList = $query->asArray()->all();
        } else {
            $countQuery = clone $query;
            $intTotal   = $countQuery->count();
            $pages      = new Pagination(['totalCount' => $intTotal]);//分页信息
            $pages->setPageSize($this->intPageSize);
            $arrList              = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();
            $arrOutPut['list']    = $arrList;
            $arrOutPut['count']   = $intTotal;
            $arrOutPut['objPage'] = $pages;
        }
        //       die($query->createCommand()->getRawSql()); // 查看拼接的sql语句
        $objDataDic              = new DataDictionary();//数据字典操作
        $arrOutPut['objDataDic'] = $objDataDic;
        if (isset($get['isDownload']) && intval($get['isDownload']) == 1) {
            //列表导出的时候记录日志
            $this->arrLogParam = [
                'date_1' => ($arrOutPut['startDate'] ? $arrOutPut['startDate'] : '-'),
                'date_2' => ($arrOutPut['endDate'] ? $arrOutPut['endDate'] : '-'),
            ];
            //输出excel数据
            $arrColumns = ['序号', '姓名', '手机号码', '战败来源', '建卡日期', '战败日期', '战败原因', '说明', '战败顾问'];
            $arrModels  = [];
            foreach ($arrList as $k => $items) {
                $failName = '';//战败来源
                switch ($items['status']) {
                    case 0 :
                        $failName = '线索战败';
                        break;
                    case 1 :
                        $failName = '意向战败';
                        break;
                    case 2 :
                        $failName = '订车战败';
                        break;
                }
                $arrModels[$k] = [
                    $k + 1,//序号
                    $items['customer_name'],//姓名
                    $items['customer_phone'],//手机号码
                    $failName,//战败来源
                    date('Y-m-d', $items['create_card_time']),//创建日
                    date('Y-m-d', $items['last_fail_time']),//战败日期
                    $items['fail_reason'],//战败原因  （说明）
                    $items['des'],//说明
                    $items['salesman_name'],//最近联系
                ];
            }
            $this->outPutExcel('失败客户', $arrColumns, $arrModels);

        }
        else
        {
            //查询所有意向等级
            $obj = new DataDictionary();
            $intention_list = $obj->getDictionaryData('intention');
            $arrOutPut['intention_list'] = $intention_list;
            //获取门店 - 该界面是门店数据，等级传入固定的30 - 门店
            $objSelectDataLogic = new JsSelectDataLogic();
            $arrSelectorgList = $objSelectDataLogic->getSelectOrgNew($session['userinfo']['permisson_org_ids'], $session['userinfo']['role_level']);
            $arrOutPut['selectOrgJson'] = json_encode($arrSelectorgList);
            $arrOutPut['get'] = $get;

            //显示页面
            return $this->render('failList', $arrOutPut);
        }

    }

    //根据线索id获取用户基本信息
    public function actionCustomerDetail($ischeck = 0, $id)
    {
        $arrOutPut = [];
        //获取线索中的信息
        $arrClues = Clue::find()->select('*')->where(['id' => $id])->asArray()->one();
        if ($arrClues)//线索有效
        {
            $arrOutPut['clueInfo'] = $arrClues;
            //获取客户信息
            $arrCustomer               = Customer::find()->where(['id' => $arrClues['customer_id']])->asArray()->one();
            $arrOutPut['customerInfo'] = $arrCustomer;
            //订车客户或者成交客户，有订单信息
            if ($arrClues['status'] == 2 || $arrClues['status'] == 3) {
                $arrOrderInfo           = Order::find()->where(['clue_id' => $arrClues['id']])->asArray()->one();
                $arrOutPut['orderInfo'] = $arrOrderInfo;
            }
            //商谈记录列表
            $arrOutPut['talkList'] = Talk::find()->where(['clue_id' => $arrClues['id']])->orderBy('create_time desc')->asArray()->all();
            //任务列表
            $arrWhere                = ['and', ['clue_id' => $arrClues['id']], ['task_type' => 1]];
            $arrOutPut['taskList']   = Task::find()->where($arrWhere)->orderBy('task_time desc')->asArray()->all();
            $arrOutPut['objDataDic'] = new DataDictionary;
            $arrOutPut['isCheck']    = $ischeck;

            return $this->render('customerDetail', $arrOutPut);
        }
    }

    /**
     * 保有客户根据客户id显示客户详情
     */
    public function actionCustomerDetailByCustomerId($id)
    {
        $arrOutPut = [];
        //获取线索中的信息
        $arrClues = Clue::find()->select('*')->where(['customer_id' => $id])->asArray()->one();
        if ($arrClues)//线索有效
        {
            $arrOutPut['clueInfo'] = $arrClues;
            //获取客户信息
            $arrCustomer               = Customer::find()->where(['id' => $arrClues['customer_id']])->asArray()->one();
            $arrOutPut['customerInfo'] = $arrCustomer;
            //订车客户或者成交客户，有订单信息
            if ($arrClues['status'] == 2 || $arrClues['status'] == 3) {
                $arrOrderInfo           = Order::find()->where(['clue_id' => $arrClues['id']])->asArray()->one();
                $arrOutPut['orderInfo'] = $arrOrderInfo;
            }
            //商谈记录列表
            $arrOutPut['talkList'] = Talk::find()->where(['clue_id' => $arrClues['id']])->orderBy('create_time desc')->asArray()->all();
            //任务列表
            $arrWhere                = ['and', ['clue_id' => $arrClues['id']], ['task_type' => 1]];
            $arrOutPut['taskList']   = Task::find()->where($arrWhere)->orderBy('task_time desc')->asArray()->all();
            $arrOutPut['objDataDic'] = new DataDictionary;
            $arrOutPut['isCheck']    = 0;
            return $this->render('customerDetail', $arrOutPut);
        }
    }

    /**激活到原顾问  lzx
     * @param $clue_id_list
     * @return bool
     */
    public function actionActive()
    {
        //获取session
        $user_info = Yii::$app->session->get('userinfo');
        //获取当前用户id、shop_id
        $shop_id         = $this->getDefaultShopId();//
        $who_assign_id   = $user_info['id'];
        $who_assign_name = $user_info['name'];

        $clue_id_arr = Yii::$app->request->post('id_arr');
        $intention_level_id = Yii::$app->request->post('intention_level');
//        var_dump($intention_level_id);die;
        if($intention_level_id != ''){
            $intention_level_des = Intention::findOne($intention_level_id)->name;
        }

        //循环激活
        foreach ($clue_id_arr as $clue_id) {
            $clue                  = Clue::find()->where(['=', 'id', $clue_id])->andWhere(['=', 'shop_id', $shop_id])->one();
            $clue->is_fail         = 0;
            $clue->status          = 1;
            $clue->who_assign_id   = $who_assign_id;

            $clue = Clue::find()->where(['=','id',$clue_id])->andWhere(['=','shop_id',$shop_id])->one();

            if($intention_level_id != ''){
                $clue->intention_level_id = $intention_level_id;
                $clue->intention_level_des = $intention_level_des;
            }

            $clue->is_fail = 0;
            $clue->status = 1;
            $clue->who_assign_id = $who_assign_id;

            $clue->who_assign_name = $who_assign_name;

            $rtn = $clue->save();

            if (!$rtn || empty($clue->shop_id) ||  empty($clue->customer_id) ||  empty($clue->salesman_id)) {
            	continue;//无顾问或者线索保存失败或者无门店或者无客户的 不生成电话任务
            }

            //获取新任务所需数据
            $customer_id = $clue->customer_id;
            $salesman_id = $clue->salesman_id;
            $date        = date("Y-m-d");
            $time        = strtotime($date);
            //新建电话任务
            $task              = new Task();
            $task->shop_id     = $shop_id;
            $task->clue_id     = $clue_id;
            $task->customer_id = $customer_id;
            $task->salesman_id = $salesman_id;
            $task->task_date   = $date;
            $task->task_time   = $time;
            $task->task_from   = '店长主动分配';
            $task->task_type   = 1;
            $task->is_cancel   = 0;
            $task->is_finish   = 1;
            $task->task_des = date('m月d日').$who_assign_name.'分配';
            $task->save();
        }

        return true;
    }
}
