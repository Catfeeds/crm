<?php

namespace frontend\modules\sales\logic;

use common\models\Customer;
use common\models\GongHai;
use common\models\Area;
use common\logic\GongHaiLogic;
use common\models\GongHaiLog;
use common\models\CarBrandSonType;
use common\logic\CarBrandAndType;
use Yii;

/**
 * 公海业务逻辑
 * 于凯
 */
class GongHaiGic
{
    /**
     * 新增公海信息
     * @param \common\models\Clue $data 线索信息
     * @param  integer $intReasonId     原因信息ID 默认是原顾问战败
     * @return bool
     * @throws \Exception
     */
    public static function addGongHai($data, $intReasonId = 1)
    {
        // 查询客户地址
        $area = Customer::find()->select('area')->where(['id' => $data->customer_id])->asArray()->one();

        // 默认值
        $area_id   = 0;
        $area_name = '';

        if (!empty($area['area'])) {
            $area_id = $area['area'];
            $area_name = Area::getParentNamesAll($area['area']);
        }


        // 获取进入公海战败次数
        $defeatNum = GongHaiLog::find()->where(['reason_id'=>1,'customer_phone'=>$data->customer_phone])->count();

        // 需要查询之前公海信息是否存在(存在更新下进入时间和次数)
        $gonghai = GongHai::findOne(['customer_phone' => $data->customer_phone]);

        // 新增数据
        if (!$gonghai) {
            $gonghai = new GongHai();
            $gonghai->customer_id    = $data->customer_id;   // 客户id
            $gonghai->customer_name  = $data->customer_name; // 客户名
            $gonghai->customer_phone = $data->customer_phone;// 客户手机号
            $gonghai->chexing_id     = 0;                    // 车型id
            $gonghai->chexing_des    = '';                   // 车型说明
        }

        // 其他信息不管是新增还是修改，都要处理
        $gonghai->create_time    = time();                  // 进入时间
        $gonghai->follow_up      = $defeatNum > 0 ? 1 : 0;  // 是否联系过
        $gonghai->defeat_num     = $defeatNum;              // 战败次数

        // 原因信息
        $gonghai->reason_id      = $intReasonId;            // 进入原因id
        $gonghai->reason_des     = GongHaiLogic::getGongHaiReasonName($intReasonId); // 进入原因说明

        // 查询车系信息
        $intention = (new CarBrandAndType())->getBrandAndFactoryInfoByTypeId($data->intention_id);
        if (isset($intention[$gonghai->intention_id])) {
            $strName = $intention[$data->intention_id]['brand_name'].'-'.$intention[$data->intention_id]['car_brand_type_name'];
        } else {
            $strName = $data->intention_des;
        }

        // 车系信息
        $gonghai->intention_id   = $data->intention_id;     // 车系id
        $gonghai->intention_des  = $strName;                // 车系说明

        // 地区信息
        $gonghai->area_id        = $area_id;                // 地区id
        $gonghai->area_name      = $area_name;              // 地区

        // 判断修改是否成功
        if (!$gonghai->save()) {
            throw new \Exception('公海录入失败:'.json_encode($gonghai->errors, 320));
        }
        return true;
    }

    /**
     * 新增公海信息
     * $data  新增的数据
     * $reason_id  原因id
     */
    public static function addGongHai1($data, $reason_id)
    {
        $falg = true;
        $db          = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {

            foreach ($data as $v) {
                // 手机号为空不处理
                if (empty($v['customer_phone'])) {
                    continue;
                }

                $area_id   = 0;
                $area_name = '';
                if (!empty($v['area_id'])) {
                    $area_id = $v['area_id'];
                    $area_name = Area::getParentNamesAll($v['area_id']);
                }

                $car = null;
                if (!empty($v['car_brand_son_type_name'])){
                    $car = CarBrandSonType::find()->select('car_brand_son_type_id')->where("car_brand_son_type_name='{$v['car_brand_son_type_name']}'")->asArray()->one();
                }

                // 获取进入公海战败次数
                $defeatNum = GongHaiLog::find()->where(['reason_id' => 1, 'customer_phone' => $v['customer_phone']])->count();
                $gonghai = GongHai::findOne(['customer_phone' => $v['customer_phone']]);

                // 没有数据进行新增
                if (!$gonghai) {
                    $gonghai = new GongHai();
                    $gonghai->customer_id    = $v['customer_id'];//客户id
                    $gonghai->customer_name  = $v['customer_name'];//客户名
                    $gonghai->customer_phone = $v['customer_phone'];//客户手机号
                }

                $gonghai->create_time   = time();                  // 进入时间
                $gonghai->defeat_num    = $defeatNum;              // 战败次数
                $gonghai->follow_up     = $defeatNum > 0 ? 1 : 0;  // 是否联系过

                // 进入原因
                $gonghai->reason_id     = $reason_id;              // 进入原因id
                $gonghai->reason_des    = GongHaiLogic::getGongHaiReasonName($reason_id); // 进入原因说明

                // 查询车系信息
                $intention = (new CarBrandAndType())->getBrandAndFactoryInfoByTypeId($v['intention_id']);
                if (isset($intention[$v['intention_id']])) {
                    $strName = $intention[$v['intention_id']]['brand_name'] . '-'.$intention[$v['intention_id']]['car_brand_type_name'];
                } else {
                    $strName = $v['intention_des'];
                }

                // 车系信息
                $gonghai->intention_id  = $v['intention_id'];      // 车型id
                $gonghai->intention_des = $strName;                // 车系说明

                // 地址信息
                $gonghai->area_id       = $area_id;                // 地区id
                $gonghai->area_name     = $area_name;              // 地区

                // 车型信息
                $gonghai->chexing_id    = empty($car['car_brand_son_type_id']) ? 0 : $car['car_brand_son_type_id']; // 车型id
                $gonghai->chexing_des   = empty($v['car_brand_son_type_name']) ? '' :$v['car_brand_son_type_name']; // 车型说明
                if (!$gonghai->save()) {
                    $falg = false;
                    break;
                }
            }

            if ($falg) {
                $transaction->commit();
                return true;
            } else {
                $transaction->rollBack();
                throw new \Exception('公海录入失败');
            }
        }catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
