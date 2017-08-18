<?php
/**
 * Created by PhpStorm.
 * User: liujx
 * Date: 2017/6/29
 * Time: 11:43
 */

namespace backend\controllers;

use common\helpers\Helper;
use common\logic\GongHaiLogic;
use common\logic\JsSelectDataLogic;
use common\models\OrganizationalStructure;
use common\models\Area;
use yii;
use common\models\GongHai;

/**
 * Class GonghaiController
 * 公海信息处理类
 * @package backend\controllers
 */
class GonghaiController extends BaseController
{
    /**
     * 引入json 返回处理
     */
    use \common\traits\Json;

    /**
     * 公海线索 - 线索列表
     * @return string
     */
    public function actionIndex()
    {
        // 验证权限
        $this->checkPermission('/gonghai/index');

        $areas = (new JsSelectDataLogic())->getShengShiQuNew(true);
        // 载入视图
        return $this->render('index', [
            'areas' => yii\helpers\Json::encode($areas)
        ]);
    }

    /**
     * 公海线索 - 线索搜索
     * @return mixed|string
     */
    public function actionSearch()
    {
        // 接收请求参数
        $request = \Yii::$app->request;

        // 接收参数
        $params = $request->post('params');  // 查询参数
        $intStart   = (int)$request->post('iDisplayStart',  0);   // 开始位置
        $intLength  = (int)$request->post('iDisplayLength', 10);  // 查询长度

        // 接收处理排序信息
        $sort  = $request->post('sSortDir_0', 'desc'); // 排序类型
        $where = Helper::handleWhere($params, [
            // 查询参数处理
            'keyword' => function($value) {
                return [
                    'or',
                    ['like', 'customer_name', $value],
                    ['like', 'customer_phone', $value],
                    ['like', 'chexing_des', $value]
                ];
            },

            // 时间处理
            'create_time' => function($value) {
                $value = explode(' - ', $value);
                // 容错处理时间
                if (empty($value[1])) $value[1] = date('Y-m-d H:i:s');
                return ['between', 'create_time', strtotime($value[0]), strtotime($value[1].' 23:59:59')];
            },

            // 所在地
            'area' => function($value) {
                $value = explode(',', $value);
                $value = array_pop($value);
                // 查询ID
                $ids = Area::getIds((int)$value);
                return ['in', 'area_id', $ids];
            },

            // 车系
            'intention_id' => function($value) {
                return ['=', 'intention_id', intval(trim(strrchr($value, ','), ','))];
            },

            // 战败次数 处理为int 查询
            'defeat_num' => ['func' => 'intval'],
        ]);

        // 处理排序字段信息
        if (isset($params['orderBy']) && !empty($params['orderBy'])) {
            $field = $params['orderBy'];
            unset($params['orderBy']);
        } else {
            $field = 'id';
        }

        // 查询数据
        $query = GongHai::find()->where($where);
        $total = $query->count();
//        $this->arrJson['sql'] = $query->createCommand()->getRawSql();

        if ($total > 0) {
            // 查询数据
            $data = $query->orderBy([$field => $sort === 'asc' ? SORT_ASC : SORT_DESC])
                ->offset($intStart)
                ->limit($intLength)
                ->all();
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
     * 公海线索-删除公海线索
     * @return mixed|string
     */
    public function actionDeleteAll()
    {
        // 先验证请求
        $request = Yii::$app->request;
        if ($request->isAjax) {

            // 获取数据
            $ids = $request->post('ids');
            if ($ids) {
                // 删除数据
                if (GongHai::deleteAll(['id' => explode(',', $ids)])) {
                    $this->handleJson($ids, 0, '删除成功');
                } else {
                    $this->arrJson['errMsg'] = '删除失败,请稍后再试';
                }
            }
        }

        return $this->returnJson();
    }

    /**
     * 公海线索 - 下发到门店
     * @return mixed|string
     */
    public function actionIssued()
    {
        // 先验证请求
        $request = Yii::$app->request;
        if ($request->isAjax) {

            // 获取数据
            $ids = $request->post('ids');       // 公海线索信息
            $shops = $request->post('shops');   // 门店信息

            if ($ids && $shops) {
                $arrIds = explode(',', $ids);
                $arrShop = explode(',', $shops);
                $this->arrJson['errMsg'] = '门店信息不存在';
                if (count($arrShop) >= 3 && $arrIds) {
                    // 查询门店信息
                    $shop = OrganizationalStructure::findOne((int)$arrShop[2]);
                    if ($shop) {
                        // 查询公海信息
                        $this->arrJson['errMsg'] = '公海信息不存在';
                        $arrGongHai = GongHai::findAll($arrIds);
                        if ($arrGongHai) {
                            $user = Yii::$app->session->get('userinfo');
                            $intSuccess = $intError = 0;
                            $error = [];

                            // 处理
                            foreach ($arrGongHai as $value) {
                                $tmp = GongHaiLogic::highSeasGoOut($value, $shop, null, $user);
                                if ($tmp['status'] === true && $tmp['message'] === 'success') {
                                    $intSuccess ++;
                                } else {
                                    $error[] = $value->id.' 处理失败，错误原因： ' . $tmp['message'] . '<br/>';
                                    $intError ++;
                                }
                            }

                            // 返回数据
                            $this->handleJson([
                                'success' => $intSuccess,
                                'error_number' => $intError,
                                'error_info' => implode(',', $error),
                            ], 0, '处理响应成功');
                        }
                    }
                }
            }
        }

        return $this->returnJson();
    }
}