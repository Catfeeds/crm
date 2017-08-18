<?php
namespace backend\controllers;

use common\models\Area;
use common\models\OrganizationalStructure;
use common\models\PutTheCar;
use yii;
use \common\helpers\Helper;
use common\logic\DataDictionary;
use common\models\Clue;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use common\logic\JsSelectDataLogic;
use common\models\Order;

/**
 * 客户搜索功能
 *
 * Class CustomerSearchController
 * @package backend\controllers
 */
class CustomerSearchController extends BaseController
{
    /**
     * 引入json 返回处理
     */
    use \common\traits\Json;

    /**
     * 显示页面
     */
    public function actionIndex()
    {
        // 验证权限
        $this->checkPermission('/customer-search/index');

        $session = Yii::$app->getSession();

        // 获取到组织架构信息
        $arrOrgIds = $session['userinfo']['permisson_org_ids'];
        $objSelectDataLogic = new JsSelectDataLogic();
        $arrSelectOrgList = $objSelectDataLogic->getSelectOrgNew($arrOrgIds, $session['userinfo']['role_level'], true);

        // 获取到渠道来源和信息来源信息
        $data = new DataDictionary();
        $arrSource = $data->getDictionaryData('source');
        $arrSources = ArrayHelper::map($arrSource, 'id', 'name');
        $arrInputType = $data->getDictionaryData('input_type');
        $arrInputTypes = ArrayHelper::map($arrInputType, 'id', 'name');

        // 获取到意向等级信息
        $arrIntention = $data->getDictionaryData('intention');
        $arrIntentions = ArrayHelper::map($arrIntention, 'id', 'name');

        // 查询到全部地址信息
        $areas = $data->getDictionaryData('area');
        $areas = ArrayHelper::index($areas, 'id');

       // 查询到全部组织构架信息
        $shop = OrganizationalStructure::find()->indexBy('id')->where(['level' => [
            OrganizationalStructure::LEVEL_REGION,
            OrganizationalStructure::LEVEL_STORE
        ]])->asArray()->all();

        // 线索状态
        $arrStatus = Clue::getStatusDesc();
        $arrStatus['All'] = '请选择客户状态';
//        unset($arrStatus[Clue::STATUS_CLUB]);

        // 战败类型
        $arrFailStatus = [
            1 => '意向战败',
            2 => '订车战败'
        ];

        // 获取到战败原因
        $arrFailReason = $data->getDictionaryData('fail_tags');
        $arrFailReasons = $arrFailReason['intention_fail']; // 意向战败
        $orderFail = $arrFailReason['order_fail'];          // 订车战败
        foreach ($orderFail as $value) {
            foreach ($value as $val) {
                array_push($arrFailReasons, $val);
            }
        }

        // 所有意向和订单战败原因
        $arrFailReasons = ArrayHelper::map($arrFailReasons, 'id', 'name');

        // 订单状态
        $arrOrderStatus = Order::getOrderStatusDesc();

        // 载入视图
        return $this->render('index', [
            'sources' => Json::encode($arrSources),
            'arrSources' => $arrSources,        // 信息来源
            'arrInputType' => $arrInputTypes,   // 渠道来源
            'inputTypes' => Json::encode($arrInputTypes),
            'orgList' => Json::encode($arrSelectOrgList),
            'areas' => Json::encode($areas),
            'shops' => Json::encode($shop),
            'clueStatus' => $arrStatus,
            'intentions' => $arrIntentions,                   // 意向等级
            'failStatus' => $arrFailStatus,                   // 战败类型
            'clueStatusJson' => Json::encode($arrStatus),     // 线索信息
            'orderStatus' => $arrOrderStatus,                 // 订单状态
            'failReasons' => $arrFailReasons,                 // 所有意向战败和订单战败原因
        ]);
    }

    /**
     * 执行搜索
     */
    public function actionSearch()
    {
        // 接收请求参数
        $request = \Yii::$app->request;

        // 接收参数
        $params = $request->post('params');  // 查询参数
        $intStart   = (int)$request->post('iDisplayStart',  0);   // 开始位置
        $intLength  = (int)$request->post('iDisplayLength', 10);  // 查询长度

        // 获取到查询类
        $query = $this->handleWhere($params);
        $total = $query->count();
        $this->arrJson['sql'] = $query->createCommand()->getRawSql();

        if ($total > 0) {
            // 查询数据
            $data = $query->orderBy(['a.id' => SORT_DESC])
                ->offset($intStart)
                ->limit($intLength)
                ->all();
            if ($data) {
                $ids = [];
                foreach ($data as &$value) {
                    if ($value['status'] >= Clue::STATUS_BOOK) {
                        $ids[] = $value['id'];
                    }

                    // 添加提车门店和提车顾问
                    $value['new_shop_name'] = '--';
                    $value['new_salesman_name'] = '--';
                }

                unset($value);

                if ($ids) {
                    $tasks = PutTheCar::find()->where(['clue_id' => $ids])->indexBy('clue_id')->all();
                    if ($tasks) {
                        foreach ($data as &$value) {
                            $tmpKey = $value['id'];
                            if (isset($tasks[$tmpKey])) {
                                $value['new_shop_name'] = $tasks[$tmpKey]['new_shop_name'];
                                $value['new_salesman_name'] = $tasks[$tmpKey]['new_salesman_name'];
                            }
                        }

                        unset($value);
                    }
                }
            }
        } else {
            $data = [];
        }

        $this->handleJson([
            'sEcho' => (int)$request->post('sEcho'),  // 请求次数
            'iTotalRecords' => count($data),                // 当前页条数
            'iTotalDisplayRecords' => (int)$total,          // 数据总条数
            'aaData' => $data,                              // 数据信息
        ], 0, 'success');

        // 返回数据
        return $this->returnJson();
    }

    /**
     * 查询客户导出问题
     */
    public function actionExport()
    {
        // 接收到请求参数
        $params = Yii::$app->request->post('params');
        $query = $this->handleWhere($params);

        // 获取到渠道来源和信息来源信息
        $data = new DataDictionary();
        $arrSource = $data->getDictionaryData('source');
        $arrSources = ArrayHelper::map($arrSource, 'id', 'name');
        $arrInputType = $data->getDictionaryData('input_type');
        $arrInputTypes = ArrayHelper::map($arrInputType, 'id', 'name');

        // 线索状态
        $arrStatus = Clue::getStatusDesc();

        // 执行导出
        Helper::excel('查询客户', [
            'serial_number' => '序号',
            'customer_name' => '客户姓名',
            'customer_phone' => '手机号码',
            'area' => '所在地',
            'clue_input_type' => '渠道来源',
            'clue_source' => '信息来源',
            'intention_level_des' => '意向等级',
            'intention_des' => '意向车系',
            'salesman_name' => '顾问',
            'shop_id' => '门店',
            'new_shop_name' => '提车门店',
            'new_salesman_name' => '提车顾问',
            'status' => '状态',
        ], $query->orderBy(['a.id' => SORT_DESC]), [
            'area' => function($value) {
                return Area::getAreaName($value);
            },
            'clue_input_type' => function($value) use ($arrInputTypes) {
                return isset($arrInputTypes[$value]) ? $arrInputTypes[$value] : $value;
            },
            'clue_source' => function($value) use ($arrSources) {
                return isset($arrSources[$value]) ? $arrSources[$value] : $value;
            },
            'shop_id' => function($value) {
                return OrganizationalStructure::getShopName($value, OrganizationalStructure::LEVEL_REGION);
            },
            'status' => function($value) use ($arrStatus) {
                return isset($arrStatus[$value]) ? $arrStatus[$value] : $value;
            }
        ], function(&$array) {
            $ids = [];
            foreach ($array as &$value) {
                if ($value['status'] >= Clue::STATUS_BOOK) {
                    $ids[] = $value['id'];
                }

                // 添加提车门店和提车顾问
                $value['new_shop_name'] = '';
                $value['new_salesman_name'] = '';
            }

            unset($value);

            if ($ids) {
                $tasks = PutTheCar::find()->where(['clue_id' => $ids])->indexBy('clue_id')->all();
                if ($tasks) {
                    foreach ($array as &$value) {
                        $tmpKey = $value['id'];
                        if (isset($tasks[$tmpKey])) {
                            $value['new_shop_name'] = $tasks[$tmpKey]['new_shop_name'];
                            $value['new_salesman_name'] = $tasks[$tmpKey]['new_salesman_name'];
                        }
                    }

                    unset($value);
                }
            }
        });
    }

    /**
     * 处理请求的查询，返回查询对象信息
     *
     * @param array $params 请求参数
     * @return yii\db\Query
     */
    private function handleWhere($params)
    {
        // 判断是否存在
        $defaultWhere = [];

        $session = Yii::$app->getSession();
        // 获取到组织架构信息
        $arrOrgIds = $session['userinfo']['permisson_org_ids'];

        // 没有选择门店，查询该顾问的权限所拥有的门店
        if (empty($params['shop_id'])) {
            array_push($defaultWhere, ['in', 'a.shop_id', $arrOrgIds]);
        }

        // 接收处理排序信息
        $where = Helper::handleWhere($params, [
            'where' => $defaultWhere,
            // 查询参数处理
            'keyword' => function($value) {
                return [
                    'or',
                    ['like', 'a.customer_name', $value],
                    ['like', 'a.customer_phone', $value],
                    ['like', 'a.salesman_name', $value]
                ];
            },

            // 线索创建时间
            'create_time' => function($value) {
                $value = explode(' - ', $value);
                // 容错处理时间
                if (empty($value[1])) $value[1] = date('Y-m-d H:i:s');
                return ['between', 'a.create_time', strtotime($value[0]), strtotime($value[1].' 23:59:59')];
            },

            // 门店信息
            'shop_id' => function($value) use ($arrOrgIds) {
                $arrValues = explode(',', $value);
                $value = array_pop($arrValues);
                if ($value == -1) $value = array_pop($arrValues);
                $value = OrganizationalStructure::getChildIds($value);
                // 处理数组
                if ($value) {
                    foreach ($value as &$val) {
                        $val = (int)$val;
                    }
                    unset($val);
                }

                $value = array_intersect($value, $arrOrgIds);

                return ['in', 'a.shop_id', $value];
            },

            // 客户状态
            'clue_status' => function($value) {
                $value = explode(',', $value);
                foreach ($value as &$val) {
                    $val = (int)$val;
                }

                unset($val);

                // 默认不包括战败的
                $tmpWhere = ['and', ['in', 'a.status', $value], ['=', 'is_fail', 0]];

                // 包括战败的
                if (in_array(Clue::STATUS_FAIL, $value)) {
                    $key = array_search(Clue::STATUS_FAIL, $value);
                    unset($value[$key]);
                    if (count($value) > 0) {
                        $tmpWhere = ['or', ['in', 'a.status', $value], ['=', 'is_fail', 1]];
                    } else {
                        $tmpWhere = ['=', 'is_fail', 1];
                    }
                }

                return $tmpWhere;
            },

            // 渠道来源
            'clue_input_type' => function($value) {
                $value = explode(',', $value);
                foreach ($value as &$val) {
                    $val = (int)$val;
                }

                unset($val);
                return ['in', 'a.clue_input_type', $value];
            },

            // 信息来源
            'clue_source' => function($value) {
                $value = explode(',', $value);
                foreach ($value as &$val) {
                    $val = (int)$val;
                }

                unset($val);
                return ['in', 'a.clue_source', $value];
            },

            // 转为意向时间
            'create_card_time' => function($value) {
                $value = explode(' - ', $value);
                // 容错处理时间
                if (empty($value[1])) $value[1] = date('Y-m-d H:i:s');
                return ['between', 'a.create_card_time', strtotime($value[0]), strtotime($value[1].' 23:59:59')];
            },

            // 意向等级
            'intention_level_id' => function($value) {
                $value = explode(',', $value);
                foreach ($value as &$val) {
                    $val = (int)$val;
                }

                unset($val);
                return ['in', 'a.intention_level_id', $value];
            },

            // 战败类型
            'fail_status' => function($value) {
                $value = explode(',', $value);
                foreach ($value as &$val) {
                    $val = (int)$val;
                }

                unset($val);
                return ['and', ['in', 'a.status', $value], ['=', 'is_fail', 1]];
            },

            // 战败原因
            'fail_reason' => function($value) {
                $value = explode(',', $value);
                foreach ($value as &$val) {
                    $val = (int)$val;
                }

                unset($val);
                return ['in', 'fail_tags', $value];
            }
        ]);

        // 查询数据
        /* @var $query \yii\db\Query */
        $query = (new yii\db\Query())->select([
            'a.customer_name', 'a.customer_phone', 'a.salesman_name',
            'b.area', 'a.clue_source', 'a.clue_input_type',
            'a.intention_level_des', 'a.intention_des', 'a.intention_id',
            'a.shop_id', 'a.status', 'a.id'
        ])->from('{{%clue}} a')
            ->leftJoin('{{%customer}} b', 'a.customer_id = b.id')
            ->where($where);

        // 需要联表查询(关联订单表 交车时间、订车时间、订单状态 )
        if (!empty($params['jiao_time']) || !empty($params['ding_time']) || !empty($params['order_status'])) {
            $query = $query->innerJoin('{{%order}} o', 'o.clue_id = a.id');
            $arrWhere = [];
            // 交车时间
            if (!empty($params['jiao_time'])) {
                $value = explode(' - ', $params['jiao_time']);
                // 容错处理时间
                if (empty($value[1])) $value[1] = date('Y-m-d H:i:s');
                $arrWhere[] = ['between', 'o.car_delivery_time', strtotime($value[0]), strtotime($value[1].' 23:59:59')];
            }

            // 订车时间
            if (!empty($params['ding_time'])) {
                $value = explode(' - ', $params['ding_time']);
                // 容错处理时间
                if (empty($value[1])) $value[1] = date('Y-m-d H:i:s');
                $arrWhere[] = ['between', 'o.create_time', strtotime($value[0]), strtotime($value[1].' 23:59:59')];
            }

            // 订单状态
            if (!empty($params['order_status'])) {
                $value = explode(',', $params['order_status']);
                $tmpWhere = [];
                // 客户未支付
                if (in_array(Order::ORDER_STATUS_PAYMENT_WAIT, $value)) {
                    $tmpWhere[] = ['and', ['=', 'o.cai_wu_dao_zhang_time', 0], ['<', 'o.status', 3]];
                }

                // 客户已经支付
                if (in_array(Order::ORDER_STATUS_PAYMENT_CARRY_OUT, $value)) {
                    $tmpWhere[] = ['and', ['>', 'o.cai_wu_dao_zhang_time', 0], ['=', 'o.status', 3]];
                }

                if (count($tmpWhere) > 0) array_unshift($tmpWhere, 'or');
                $arrWhere[] = $tmpWhere;
            }

            if (count($arrWhere)) array_unshift($arrWhere, 'and');
            /* @var $query \yii\db\Query */
            $query = $query->andWhere($arrWhere);
        }

        return $query;
    }
}