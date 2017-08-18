<?php
namespace backend\controllers;

use common\logic\NoticeTemplet;
use common\models\Clue;
use common\models\CluePending;
use common\models\Customer;
use common\models\GongHai;
use common\models\OrganizationalStructure;
use common\models\Task;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use frontend\modules\sales\logic\GongHaiGic;

class PendingClueController extends BaseController
{
    use \common\traits\Json;

    //未上市车型线索列表
    public function actionUnlistedList()
    {
        //接收查询参数
        $search_time = Yii::$app->request->get('addtime');
        $search_key  = Yii::$app->request->get('search_key');

        $info = $this->getlist($search_key, $search_time, 1);


        $search_data['search_key'] = $search_key;
        $search_data['addtime']    = $search_time;
        return $this->render('unlisted', [
            'list' => $info['list'],
            'pagination' => $info['pagination'],
            'search_data' => $search_data
        ]);

    }

    //未下发线索

    private function getlist($search_key, $search_time, $type)
    {

        //如果有搜索条件，拼接查询条件
        $or_where = [];
        if ($search_key) {
            $or_where = [
                'or',
                ['like', 'customer_name', $search_key],
                ['like', 'customer_phone', $search_key],
                ['like', 'intention_des', $search_key],
            ];
        }
        $and_where = [];
        if ($search_time) {
            list($strStartDate, $strEndDate) = explode(' - ', $search_time);
            $and_where = [
                'and',
                ['>=', "FROM_UNIXTIME(create_time, '%Y-%m-%d')", $strStartDate],
                ['<=', "FROM_UNIXTIME(create_time, '%Y-%m-%d')", $strEndDate],
            ];
        }

        //查询数据
        $query = CluePending::find()->where(['=', 'is_type', $type]);

        //拼接查询条件
        $or_where && $query->andWhere($or_where);
        $and_where && $query->andWhere($and_where);

        //计算总数
        $intTotal   = $query->count();
        $pagination = new Pagination([
            'defaultPageSize' => 20,
            'totalCount' => $intTotal,
        ]);
        $list       = $query->orderBy('create_time desc')->asArray()->all();

        //处理数据
        foreach ($list as $key => $value) {
            $list[$key]['create_time_fomat'] = date('Y-m-d H:i', $value['create_time']);
        }

        $info['list']       = $list;
        $info['pagination'] = $pagination;
        return $info;
    }

    public function actionUnassignList()
    {
        //接收查询参数
        $search_time = Yii::$app->request->get('addtime');
        $search_key  = Yii::$app->request->get('search_key');


        $info = $this->getlist($search_key, $search_time, 2);


        $search_data['search_key'] = $search_key;
        $search_data['addtime']    = $search_time;

        return $this->render('unassign', [
            'list' => $info['list'],
            'pagination' => $info['pagination'],
            'search_data' => $search_data
        ]);

    }

    //下发到门店

    public function actionAssign()
    {
        // 接收参数
        $id_arr   = Yii::$app->request->post('id_arr');
        $org_info = Yii::$app->request->post('org_info');

        // 验证数据的有效性
        if ($id_arr && $org_info) {
            // 必须要选择到门店
            $org_arr = explode(',', $org_info);
            $this->arrJson['errMsg'] = '请选择门店信息';
            if (count($org_arr) > 2) {

                // 查询门店名称
                $shop_id = end($org_arr);
                $shop_info = OrganizationalStructure::findOne($shop_id);
                $shop_name = empty($shop_info->name) ? '' : $shop_info->name;

                // 取出数据
                $list = CluePending::find()->where(['in', 'id', $id_arr])->asArray()->all();
                $intNumber = count($list);
                $list_new = $this->handleRepeat($list, $shop_id);

                $intHave = count($list_new);

                // 提示信息
                $this->arrJson['errCode'] = 0;
                $this->arrJson['errMsg'] = '处理成功! <br/>总数据:'.$intNumber.'条;处理成功:'.
                    $intHave.'条;重复数据:'.($intNumber - $intHave).'条';

                // 有数据就才处理
                if (!empty($list_new)) {
                    $insert = [];
                    foreach ($list_new as $item) {
                        $insert[] = [
                            'customer_id' => empty($item['customer_id']) ? 0 : $item['customer_id'],
                            'customer_name' => empty($item['customer_name']) ? '' : $item['customer_name'],
                            'customer_phone' => empty($item['customer_phone']) ? '' : $item['customer_phone'],
                            'intention_des' => empty($item['intention_des']) ? '' : $item['intention_des'],
                            'intention_id' => empty($item['intention_id']) ? '' : $item['intention_id'],
                            'shop_id' => $shop_id,
                            'shop_name' => $shop_name,
                            'create_time' => time(),
                            'create_type' => 3,
                            'clue_input_type' => 38,
                            'clue_source' => 16,
                        ];
                    }

                    // 开启事务
                    $db = Yii::$app->db;
                    $transaction = $db->beginTransaction();
                    try {

                        // 多条插入
                        $db->createCommand()
                            ->batchInsert(Clue::tableName(), [
                                'customer_id', 'customer_name', 'customer_phone',
                                'intention_des', 'intention_id', 'shop_id',
                                'shop_name', 'create_time', 'create_type',
                                'clue_input_type', 'clue_source'
                            ], $insert)
                            ->execute();

                        // 删除数据
                        $db->createCommand()->delete(CluePending::tableName(), ['in', 'id', $id_arr])->execute();
                        $transaction->commit();

                    } catch (\Exception $e) {
                        $transaction->rollBack();
                        $this->arrJson['errCode'] = 1;
                        $this->arrJson['errMsg'] = $e->getMessage();
                    }
                }
            }
        }

        // 返回数据
        return $this->returnJson();
    }

    /**
     * 检查这个客户信息是否有效
     * liujx edited by 2017-07-04 下发时候检测客户之前是否有线索信息
     * @param  array $list
     * @return array
     */
    public function handleRepeat($list, $shop_id)
    {
        /**
         * edited by liujx 2017-07-04 下发到门店需要验证这个客户是否有存在的线索信息 start:
         *
         * 如果客户有活的的线索、意向、订车线索，不允许下发到门店中去，并且要删除这条没有的数据
         */
        // 获取到所以手机号
        $arrPhone = array_column($list, 'customer_phone');

        // 查询这些客户是否有活的线索信息，线索、意向、订车
        $clues = Clue::find()->select('customer_phone')->where([
            'customer_phone' => $arrPhone,
            'is_fail' => 0,
            'status' => [0, 1, 2]
        ])->groupBy('customer_phone')->indexBy('customer_phone')->asArray()->all();

        // 查询存在数据
        if ($clues) {
            // 定义需要删除的数据ID
            $arrDeleteIds = [];

            // 处理现在的数据
            foreach ($list as $key => $value) {
                if (isset($clues[$value['customer_phone']])) {
                    unset($list[$key]);             // 删除数组这个元素
                    $arrDeleteIds[] = $value['id']; // 添加需要删除的下发线索信息ID
                }
            }

            // 执行数据的删除(因为客户有线索在跑,这条线索没有用了)
            CluePending::deleteAll(['id' => $arrDeleteIds]);
        }

        // end;

        return $list;
    }

    /**
     * 投入公海
     */
    public function actionGongHai()
    {
        $arrs = Yii::$app->request->post('arrs');
        $reason_id = Yii::$app->request->post('reason_id');
        //查询顾问id
        $cluePending  = CluePending::find()->where(['in', 'id', $arrs])->asArray()->all();
        $customer_ids = [];
        foreach ($cluePending as $v) {
            array_push($customer_ids, $v['customer_id']);
        }
        //查询客户地址
        $customer = Customer::find()->select('id,area')->where(['in', 'id', $customer_ids])->asArray()->all();

        foreach ($cluePending as $k => $v) {
            foreach ($customer as $val) {
                if ($v['customer_id'] == $val['id']) {
                    $cluePending[$k]['area_id'] = $val['area'];
                    break;
                }
            }
        }
        $json = [];
        if (GongHaiGic::addGongHai1($cluePending, $reason_id)) {
            CluePending::deleteAll(['in','id',$arrs]);
            $json['code'] = 200;
        } else {
            $json['code'] = 300;
            $json['msg']  = '投入失败';
        }
        return json_encode($json);
    }
}

