<?php
/**
 * Created by PhpStorm.
 * User: liujx
 * Date: 2017/7/5
 * Time: 11:05
 */

namespace console\controllers;

use common\models\GongHai;
use yii;
use common\helpers\Helper;
use common\models\Clue;
use frontend\modules\sales\logic\GongHaiGic;
use yii\console\Controller;
use common\models\Area;
use common\models\Customer;

/**
 * Class GonghaiControllers 公海认领的线索的定时任务
 * @package console\controllers
 */
class GonghaiController extends Controller
{
    /**
     * @var int 定义需要查询多少分钟之前的线索
     */
    protected $intMinute = 30;

    /**
     * 公海认领30分钟后，没有跟进的继续投入公海
     */
    public function actionIndex()
    {
        $time = time() - $this->intMinute * 60;

        // 查询公海认领线索,状态为0，并且不是战败的线索,创建时间为指定时间之前的线索
        $all = Clue::find()->where([
            'and',
            ['=', 'create_type', 6],
            ['=', 'status', 0],
            ['=', 'is_fail', 0],
            ['or',
                ['and', ['=', 'last_view_time', 0], ['<=', 'create_time', $time]],
                ['and', ['>', 'last_view_time', 0], ['<=', 'last_view_time', $time]]
            ]
        ])->all();

        // 查询到了数据
        if ($all) {
            $error = [];
            // 存在数据
            foreach ($all as $value) {
                $transaction = Yii::$app->db->beginTransaction();
                $isSubmit = false;
                try {
                    /* @var $value \common\models\Clue */
                    if (GongHaiGic::addGongHai($value, 7)) {
                        // 删除当前的线索
                        if ($value->delete()) {
                            $isSubmit = true;
                        }
                    }

                    // 处理成功提交这个事务
                    if ($isSubmit) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                    }

                } catch (\Exception $e) {
                    $error[] = [
                        'error' => $e->getMessage(),
                        'clue' => $value->toArray(),
                    ];

                    $transaction->rollBack();
                }
            }

            // 存在错误信息，添加日志
            if (!empty($error)) {
                Helper::logs('error/'.date('Ymd').'-gonghai-index-error.log', [
                    'time' => date('Y-m-d H:i:s'),
                    'number' => count($error),
                    'errors' => $error
                ]);
            }
        }


        // 执行输出下
        echo date('Y-m-d H:i:s', $time). ' - '. date('Y-m-d H:i:s'). ' OK'.PHP_EOL;
    }

    /**
     * 线索表有活跃线索 删除公海线索
     */
    public function actionClueGongHai() {
        //1.查询活跃线索
        $query = Clue::find()->select('customer_phone')
            ->where(
                [
                    'and',
                    ['status' => [0,1,2]],
                    ['is_fail'=>0],
                ]
            );
          $clue = $query->asArray()->all();
          if (!empty($clue)) {
              $phoneArr = [];
              foreach ($clue as $v) {
                  array_push($phoneArr,$v['customer_phone']);
              }

             GongHai::deleteAll(['customer_phone'=>$phoneArr]);
          }

    }

    /**
     * 处理公海线索列表中没有客户没有地区信息
     */
    public function actionAddress()
    {
        // 查询全部没有地区信息的公海线索(通过手机号作为key)
        $all = GongHai::find()->where(['area_id' => 0])->all();
        $intSuccess = 0;
        if ($all) {
            // 获取到手机号
            $arrPhone = yii\helpers\ArrayHelper::getColumn($all, 'customer_phone');
            // 查询客户信息(地址不为空的用户)
            $customers = Customer::find()->select('area,phone')->where([
                'and',
                ['in', 'phone', $arrPhone],
                ['>', 'area', 0]
            ])->indexBy('phone')->asArray()->all();

            if ($customers) {
                foreach ($all as $value) {
                    /* @var $value \common\models\GongHai */
                    // 这个客户存在地址信息，修改公海这个客户的地址信息
                    if (isset($customers[$value->customer_phone])) {
                        $value->area_id = $customers[$value->customer_phone]['area'];
                        $value->area_name = Area::getParentNamesAll($value->area_id);
                        if ($value->save()) {
                            $intSuccess ++;
                        }
                    }
                }
            }
        }

        echo date('Y-m-d H:i:s').' OK Gonghai number: '.count($all). '; SUCCESS number: '.$intSuccess;
    }
}