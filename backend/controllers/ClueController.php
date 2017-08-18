<?php
/**
 * 线索处理
 */

namespace backend\controllers;

use common\logic\ClueValidate;
use Yii;

use common\logic\DataDictionary;
use common\models\Clue;
use common\models\Customer;
use yii\data\Pagination;
use common\logic\PhoneLetter;
use common\models\UpdateXlsxLog;
use common\logic\CompanyUserCenter;
use common\logic\NoticeTemplet;

class ClueController extends BaseController
{
    public $enableCsrfValidation = false;

    public $arrCustomer = [
        'name',//客户姓名
        'phone',//客户的手机号，做唯一索引
        'spare_phone',//spare_phone
        'weixin',
        'sex',//性别： 1 - 男，2 - 女
        'area',//客户所在的地区
        'profession',//职位id
        'create_time' //客户收录时间
    ];

    public $arrClue = [
        'clue_input_type',//客户来源
        //'intention_des',//意向车型 - 文字描述
        'intention_id',//意向车型的id
        'planned_purchase_time_id',//拟购时间id
        'quoted_price',//报价信息
        'sales_promotion_content',//促销内容
        'create_time',//该条线索（意向）创建时间
        'shop_id',//对接的门店id
        'shop_name',//店铺名称
        'des',//描述
        'clue_source',//线索来源
        'buy_type' //购车方式id
    ];


    /**
     * 新增线索处理页面
     * @return string
     */
    public function actionCreate()
    {

        $arrDict = new DataDictionary();

//        //职业
//        $data['profession'] = $arrDict->getDictionaryData('profession');

        //渠道来源
        $data['input_type'] = $arrDict->getDictionaryData('input_type');

        //信息来源
        $data['source'] = $arrDict->getDictionaryData('source');

//        //地区
//        $data['area']     = $arrDict->getDictionaryData('area');
//        $data['areaJson'] = json_encode($data['area'], JSON_UNESCAPED_UNICODE);


//        $car = new \common\logic\CarBrandAndType();
//        //获取品牌信息
//        $data['brand'] = $car->getCarBrandList();

//        //获取车型信息
//        $data['car']     = $car->getAllCarTypeList();
//        $data['carJson'] = json_encode($data['car'], JSON_UNESCAPED_UNICODE);

        //拟购时间
        $data['planned_purchase_time'] = $arrDict->getDictionaryData('planned_purchase_time');

        //购买方式
        $data['buy_type'] = $arrDict->getDictionaryData('buy_type');

        // 获取门店信息
//        $os             = new \common\logic\CompanyUserCenter();
//        $data['os']     = $os->getLocalOrganizationalStructure();
//        $data['osJson'] = json_encode($data['os'], JSON_UNESCAPED_UNICODE);


        //print_r($data);exit;

        return $this->renderPartial('create', ['model' => $data]);
    }

    /**
     * 线索录入
     */
    public function actionSave()
    {
        $session = Yii::$app->getSession();
        $post = Yii::$app->request->post();
        if (empty($post['area'])) {
            $this->res(300, '请填写地址');
        }

        $areas = explode(',', $post['area']);
        $area = 0;

        //地址
        if (!empty($areas) && count($areas) > 1) {
            $area = $areas[count($areas) - 1];
        }

        $shop = new \common\logic\CompanyUserCenter();
        // edited by liujx 修改必须选到门店 start:
        $arrShop = explode(',', $post['shop_id']);
        if (count($arrShop) <= 2) {
            $this->res(300, '请选择到门店');
        }
        // end;

        $post['shop_id'] = $arrShop[2];
        $post['shop_name'] = $shop->getShopName($post['shop_id']);

        $post['intention_des'] = null;
        if (!empty($post['intention_id'])) {
            $car = new \common\logic\CarBrandAndType();
            $post['intention_id'] = explode(',', $post['intention_id'])[1];
            $post['intention_des'] = $car->getCarTypeNameByTypeId($post['intention_id']);

        }

        //新增线索行为记录log
        $this->arrLogParam = [
            'customer_name' => trim($post['name']),
            'customer_phone' => trim($post['phone'])
        ];

        //获取当前最大的线索id
        $id = Clue::find()->max('id');
        $id = empty($id) ? 0 : $id;
        $log = new UpdateXlsxLog();

        $clue = new Clue();
        $time = time();
        $date = date('Y-m-d H:i:s', $time);

        /**
         * edited by liujx 2017-6-28 不允许新增的直接提示错误 start:
         *
         * 系统内存在该客户的线索 不是战败，并且线索状态为 意向或者订车状态的线索，不允许新增线索
         */
        if (ClueValidate::validateExists([
            'and',
            ['=', 'customer_phone', $post['phone']],
            ['=', 'is_fail', 0],
            ['in', 'status', [1, 2]]
        ])
        ) {
            $this->res('300', '该客户在系统内已经存在！');
        }

        // end;

        // 查询线索是否存在
        $res = Clue::find()->select('id,customer_phone,customer_id,status,shop_id,is_fail,salesman_id')
            // edited by liujx 2017-6-22 修改需求 同一个客户不能在不同门店添加线索 old: "customer_phone ={$post['phone']} and shop_id={$post['shop_id']}" start:
            ->where(['customer_phone' => $post['phone']])
            // end;
            ->orderBy('id desc')
            ->asArray()
            ->one();

        $objNotice = new NoticeTemplet();

        $db = Yii::$app->db;

        // 获取门店店长信息
//        $company = new CompanyUserCenter();
//        $shop    = $company->getShopownerByShopId($post['shop_id']);

        if (empty($res)) {//没有线索新增信息
            $transaction = $db->beginTransaction();
            //客户信息
            $sqlCustomer = "insert into crm_customer(name,sex,phone,area,weixin,create_time,spare_phone)value(
                               '{$post['name']}',
                                {$post['sex']},
                               '{$post['phone']}',
                               '{$area}',
                               '{$post['weixin']}',
                               {$time},
                               '{$post['spare_phone']}'
                            )";
            $sqlCustomer .= " 
            on duplicate key update 
            name=values(name),
            sex=values(sex),
            phone=values(phone),
            area=values(area),
            weixin=values(weixin),
            create_time=values(create_time),
            spare_phone=values(spare_phone)";

            try {

                if (Yii::$app->db->createCommand($sqlCustomer)->execute() > 0) {
                    $customerId = Yii::$app->db->getLastInsertId();

                    //客户购车信息
                    foreach ($post as $k => $v) {
                        if (in_array($k, $this->arrClue)) {

                            $clue->$k = $v;
                        }
                    }

                    $clue->create_person_name = $session['userinfo']['name'];
                    $clue->customer_id = $customerId;
                    $clue->customer_name = $post['name'];
                    $clue->create_time = $time;
                    $clue->customer_phone = $post['phone'];
                    $clue->intention_des = $post['intention_des'];
                    $clue->create_type = 2;//手动创建

                    if ($clue->save()) {
                        $objNotice->headquartersImportClueClaimNotice($post['shop_id'], 1);
                        if ($log->insertYuQi($date, $time, $id)) {
                            $transaction->commit();
                            $this->res();
                        } else {
                            $transaction->rollBack();
                            $this->res('300', 'yuqi录入失败');
                        }

                    } else {
                        $transaction->rollBack();
                        $this->res('300', 'clue录入失败');
                    }

                } else {

                    $transaction->rollBack();
//                    手机号码位数错误 手机号11位
                    $msg = (strlen($post['phone']) > 11 ? '手机号码位数错误' : 'customer录入失败');
                    $this->res('300', $msg);

                }
            } catch (\Exception $e) {

                $transaction->rollBack();

                throw $e;
            }
        } else {

            // 优先考虑刷新线索的情况(已经存在该手机号不为战败和状态为线索的情况)
            $clue = clue::find()->where([
                'customer_phone' => $post['phone'],
                'is_fail' => 0,
                'status' => 0
            ])->asArray()->orderBy('id DESC')->one();
            if (!empty($clue)) {
                //如果存在线索 不更新门店信息
                unset($post['shop_id']);
                unset($post['shop_name']);
                if ($this->insertOrupdate($post, $res, false)) {
                    $this->res();
                } else {

                    $this->res('300', '更新客户信息失败！');
                }
            }

            /**
             * edited by liujx 2017-6-22: 修改需求  start:
             * 1、当线索状态为 线索 刷新线索到最新 status = 0 update\
             * 2、当线索状态为 意向 OR 订车 处理为失败 原因为 该客户在系统内已经存在 status = 1 OR status = 2 error
             * 3、当线索状态为 战败 OR 成交 增加一条新线索
             */

            // 先处理新增一条线索的(状态为 战败 OR 成交 )
            if ($res['is_fail'] == 1 || $res['status'] == 3) {
                if ($this->insertOrupdate($post, $res)) {
                    $this->res();
                } else {
                    $this->res('300', '生成新客户数据失败！');
                }

                // 处理直接失败 意向 OR 订车
            } elseif ($res['status'] == 1 || $res['status'] == 2) {
                $this->res('300', '该客户在系统内已经存在！');

                // 处理线索状态的刷新数据
            } elseif ($res['status'] == 0) {
                if ($this->insertOrupdate($post, $res, false)) {
                    $this->res();
                } else {

                    $this->res('300', '更新客户信息失败！');
                }
            }
        }

    }

    /**
     * 数据操作
     * @param  array $post 操作的数据集合
     * @param  array $res 检测的数据
     * @param  bool $ischeck true增加 false 修改
     * @return bool
     */
    public function insertOrupdate($post, $res, $ischeck = true)
    {
        $session = Yii::$app->getSession();
        $time = time();

        //获取当前最大的线索id
        $id = Clue::find()->max('id');
        $id = empty($id) ? 0 : $id;
        $log = new UpdateXlsxLog();
        $date = date('Y-m-d H:i:s', $time);

        if ($ischeck) {
            $clue = new Clue();
            $clue->create_time = $time;
        } else {
            $clue = Clue::findOne($res['id']);
        }

        //客户购车信息
        foreach ($post as $k => $v) {
            if (in_array($k, $this->arrClue)) {

                $clue->$k = $v;
            }
        }

        $clue->create_person_name = $session['userinfo']['name'];
        $clue->customer_id = $res['customer_id'];
        $clue->customer_phone = $post['phone'];
        $clue->customer_name = $post['name'];
        $clue->intention_des = $post['intention_des'];
        $clue->create_type = 2;//手动创建

        if ($clue->save()) {
            if ($log->insertYuQi($date, $time, $id)) {
                return true;
            } else {
                false;
            }

        } else {

            return false;
        }


    }


    /**
     * 列表
     */
    public function actionIndex()
    {
        //总部才有权限
        $this->checkPermission('/clue/index', 0);

        $get = Yii::$app->request->get();

        $params = ' 1=1';

        $keyword = null;
        $addtime = null;

        if (!empty($get['keyword'])) {

            $params .= " and (customer_name like '%{$get['keyword']}%'";
            $params .= " or customer_phone like '%{$get['keyword']}%'";
            $params .= " or intention_des like '%{$get['keyword']}%'";
            $params .= " or shop_name like '%{$get['keyword']}%'";
            $params .= " or who_assign_name like '%{$get['keyword']}%')";

            $keyword = $get['keyword'];
        }

        if (!empty($get['addtime'])) {
            list($startDate, $endDate) = explode(' - ', trim($get['addtime']));
            $params .= "  and FROM_UNIXTIME(create_time, '%Y-%m-%d')  >= '{$startDate}'";
            $params .= " and FROM_UNIXTIME(create_time, '%Y-%m-%d') <=  '{$endDate}'";
            $addtime = $get['addtime'];
        } else {
            $date = date('Y-m-d');
            $params .= " and FROM_UNIXTIME(create_time, '%Y-%m-%d') <=  '{$date}'";
        }

        //渠道来源筛选
        if (!empty($get['input_type'])) {
            $input_type = implode(',', $get['input_type']);
            $params .= " and clue_input_type in ({$input_type})";
            $arr['input_types'] = json_encode($get['input_type']);
        } else {
            $arr['input_types'] = null;
        }

        //信息来源筛选
        if (!empty($get['source'])) {
            $source = implode(',', $get['source']);
            $params .= " and clue_source in ({$source})";
            $arr['sources'] = json_encode($get['source']);
        } else {
            $arr['sources'] = null;
        }

        //创建方式筛选
        if (!empty($get['create_type'])) {
            $create_type = implode(',', $get['create_type']);
            $params .= " and create_type in ({$create_type})";
            $arr['create_types'] = json_encode($get['create_type']);
        } else {
            $params .= ' and create_type > 0 and create_type < 3';
            $arr['create_types'] = null;
        }

        $arrDict = new DataDictionary();

        //渠道来源
        $input_type = $arrDict->getDictionaryData('input_type');

        //拟购时间
        $planned_purchase_time = $arrDict->getDictionaryData('planned_purchase_time');

        //获取车型信息
        $cartype = new \common\logic\CarBrandAndType();
        $car = $cartype->getAllCarTypeList();

        //信息来源
        $source = $arrDict->getDictionaryData('source');

        // 获取门店信息
        $os = new \common\logic\CompanyUserCenter();
        $os_list = $os->getLocalOrganizationalStructure();
        $arr['input_type'] = $input_type;
        $arr['source'] = $source;

        $sql1 = "select * from crm_clue where {$params}";
        $sql2 = "select * from crm_clue_wuxiao where {$params}";
        $sql = " SELECT * from ( {$sql1} union {$sql2}) tmp ";

        $query1 = "select count(*)count from crm_clue where {$params}";
        $query2 = "select count(*)count from crm_clue_wuxiao where {$params}";
        $query = " SELECT sum(count)count from ( {$query1} union {$query2}) tmp ";
        $queryList = Yii::$app->db->createCommand($query)->queryOne();
        $count = $queryList['count'];

        //分页
        $pagination = new Pagination([
            'defaultPageSize' => 20,
            'totalCount' => $count,
        ]);

        $sql .= "order by create_time desc limit $pagination->offset,$pagination->limit";
        $list = Yii::$app->db->createCommand($sql)->queryAll();
        foreach ($list as $k => $v) {

            //拼接拟购时间
            foreach ($planned_purchase_time as $val) {
                if ($val['id'] == $v['planned_purchase_time_id']) {
                    $list[$k]['planned_purchase_time_name'] = $val['name'];
                    break;
                }
            }

            //拼接渠道来源
            foreach ($input_type as $val) {
                if ($val['id'] == $v['clue_input_type']) {
                    $list[$k]['clue_input_type_name'] = $val['name'];
                    break;
                }
            }

            //拼接信息来源
            foreach ($source as $val) {
                if ($val['id'] == $v['clue_source']) {
                    $list[$k]['source_name'] = $val['name'];
                    break;
                }
            }

            //拼接车型
            foreach ($car as $val) {
                if ($val['car_brand_type_id'] == $v['intention_id']) {
                    $list[$k]['intention_id_name'] = $val['car_brand_type_name'];
                    break;
                }
            }

            //拼接门店
            foreach ($os_list as $val) {
                if ($val['id'] == $v['shop_id']) {
                    $list[$k]['shop_name'] = $val['name'];
                    break;
                }
            }

        }

        return $this->render('index', ['list' => $list, 'arr' => $arr, 'pagination' => $pagination, 'keyword' => $keyword, 'addtime' => $addtime]);
    }

}
