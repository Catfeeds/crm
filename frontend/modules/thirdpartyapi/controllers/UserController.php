<?php
/**
 * 功    能：车城提供用户信息接口
 * 作    者：于凯
 * 修改日期：2017-6-8
 */
namespace frontend\modules\thirdpartyapi\controllers;


use Jasny\SSO\Exception;
use yii;
use yii\rest\Controller;
use common\models\Clue;
use common\models\Customer;
use common\models\Order;
use common\models\CheckPhone;
use common\models\ShopArea;
use common\models\UpdateXlsxLog;
use common\models\Task;
use common\logic\NoticeTemplet;
use common\logic\ClueValidate;

class UserController extends Controller
{
    private $token = 'che2017qNKb7NVurCzYBJwtqNl-1Xk3ZjkCRM';

    public function actionAddUserInfo()
    {
        $info = Yii::$app->request->post();
        //验证token
        if ($info['token'] != $this->token) {
            $this->echoData(1001, 'token验证失败', 'token->' . $info['token']);
        }
        if ($info['isType'] == 1){
            $this->clueAdd($info);
        }
        else if ($info['isType'] == 2){
            $this->penDingAdd($info);
        }

    }

    /**
     * 未上市
     */
    public function penDingAdd($info) {
        if (empty($info['data'])) {
            $this->echoData(1002, '没有录入的信息');
        }
        $list   = json_decode($info['data']);
        //去掉必填项 为空的信息
        foreach ($list as $k => $v) {
            if (empty($v->name) || empty($v->phone) || empty($v->province) || empty($v->city)) {
                unset($list[$k]);
                continue;
            }
            $newArr[$v->phone] = $v;
        }

        if (empty($newArr)){
            $this->echoData(1, '录入成功', $info['data']);
        }
        //获取车系信息
        $car        = new \common\logic\CarBrandAndType();
        $car_type   = $car->getAllCarTypeList();
        foreach ($newArr as $k => $v) {
            //查询车系是否存在 存在记录车系id  不存在清除品牌与车型名字
            $isCarId = $this->check_car($car_type, $v->car_brand_type_name);
            if ($isCarId) {
                $v->car_brand_type_id = $isCarId;
            } else {
                $v->brand_name              = null;
                $v->car_brand_son_type_name = null;
                $v->car_brand_type_name     = null;
                $v->car_brand_type_id       = 0;
            }

        }
        $newArr = $this->areaByid($newArr);
        if ($this->insertPending($newArr,1)){
            $this->echoData(1, '录入成功', $info['data']);
        }else{
            $this->echoData(0, '录入失败', $info['data']);
        }
    }

    /**
     * 线索自动分配
     */
    private function clueAdd($info) {
        if (empty($info['data'])) {
            $this->echoData(1002, '没有录入的信息');
        }
        $list   = json_decode($info['data']);
        $phone  = [];
        $newArr = [];
        //去掉必填项 为空的信息
        foreach ($list as $k => $v) {
            if (empty($v->name) || empty($v->phone) || empty($v->province) || empty($v->city)) {
                unset($list[$k]);
                continue;
            }
            //拼接手机号
            array_push($phone, $v->phone);
            $newArr[$v->phone] = $v;
        }
        //去掉必填项后验证是否还有数据
        if (empty($phone)) {
            $this->echoData(1, '录入成功', $info['data']);
        }

        //查询当天录入过的手机号
        $resItem   = CheckPhone::find()->where(['in', 'phone', $phone])->asArray()->all();
        $insertArr = [];

        if (!empty($resItem)) {//当天已经包含了一些手机号码 进行去重
            foreach ($resItem as $v) {
                //去掉录入数据中已经存在的重复数据
                unset($newArr[$v['phone']]);

                //去掉已经存在的手机号
                if (in_array($v['phone'], $phone)) {
                    unset($phone[array_search($v['phone'], $phone)]);
                }

            }
        }

        //过滤掉已经录入过的数据后 验证是否还有数据
        if (empty($phone)) {
            $this->echoData(1, '录入成功', $info['data']);
        }

        //增加当天未录入过的手机号
        foreach ($phone as $k => $v) {
            $insertArr[$k][] = $v;
        }

        $objNotice = new NoticeTemplet();
        $dbTrans= Yii::$app->db->beginTransaction();

        try {

            $insertItem = Yii::$app->db->createCommand()->batchInsert('crm_check_phone', ['phone'], $insertArr)->execute();
            if (!$insertItem) {
                $dbTrans->rollBack();
                $this->echoData(0, '录入失败', $info['data']);
            }

            //取出门店地区信息
            $shopAreaList = ShopArea::find()->asArray()->all();

            //获取车系信息
            $car        = new \common\logic\CarBrandAndType();
            $car_type   = $car->getAllCarTypeList();
            $clueArr    = [];
            $pendingArr = [];
            foreach ($newArr as $k => $v) {
                //查询车系是否存在 存在记录车系id  不存在清除品牌与车型名字
                $isCarId = $this->check_car($car_type, $v->car_brand_type_name);
                if ($isCarId) {
                    $v->car_brand_type_id = $isCarId;
                } else {
                    $v->brand_name              = null;
                    $v->car_brand_son_type_name = null;
                    $v->car_brand_type_name     = null;
                    $v->car_brand_type_id       = 0;
                }
                //验证门店地区是否存在
                $check = $this->check_array($shopAreaList, $v->city, $v->area);
                if (!empty($check)) {
                    $v->shop_id   = $check['shop_id'];
                    $v->shop_name = $check['shop_name'];
                    $clueArr[]    = $v;
                } else {
                    $pendingArr[] = $v;
                }
            }

            $new_items = [];
            // 查询线索表中已经存在的数据
            if (!empty($clueArr)){
                $mobile = '';
                foreach ($clueArr as $v) {
                    //手机号拼接
                    $mobile .= $v->phone . ',';
                }

                // 查找数据库已存在的用户
                $xlsx   = new UpdateXlsxLog();
                $mobile = rtrim($mobile, ',');
                $res    = $xlsx->get_user_clue($mobile);

                // 有数据进行数据验证
                if (!empty($res)) {
                    $phoneArr = [];
                    // 查询出来的数据进行号码分组
                    foreach ($res as $p_v) {
                        $phoneArr[$p_v['customer_phone']][] = $p_v;
                    }

                    foreach ($clueArr as $k => $v) {

                        $ischeck = false;
                        $upArr   = [];

                        // edited by liujx  只要这个用户存在线索信息那 2017-06-27 start :
                        $strPhone = strval($v->phone);

                        // 检测当前导入的号码是否存在
                        if (array_key_exists($strPhone, $phoneArr)) {
                            $upArr = $phoneArr[$strPhone][0];
                            $ischeck = true;
                        }

                        // 客户存在线索信息
                        if ($ischeck) {

                            /**
                             * edited by liujx 2017-6-28 不允许新增的直接提示错误 start:
                             *
                             * 第一步考虑排除情况： 系统内存在该客户的线索 不是战败，并且线索状态为 意向或者订车状态的线索，不允许新增线索
                             */
                            if (!ClueValidate::validateExists([
                                'and',
                                ['=', 'customer_phone', $strPhone],
                                ['=', 'is_fail', 0],
                                ['in', 'status', [1, 2]]
                            ])) {

                                /**
                                 * edited by liujx 2017-6-22: 修改需求  start:
                                 * 1、当线索状态为 线索 刷新线索到最新 status = 0 update\
                                 * 2、当线索状态为 意向 OR 订车 处理为失败 原因为 该客户在系统内已经存在 status = 1 OR status = 2 error
                                 * 3、当线索状态为 战败 OR 成交 增加一条新线索
                                 */

                                // 第二步考虑是否需要刷新线索： 优先考虑刷新线索的情况(已经存在该手机号不为战败和状态为线索的情况)
                                $clue = clue::find()->where([
                                    'customer_phone' => $strPhone,
                                    'is_fail' => 0,
                                    'status' => 0
                                ])->orderBy('id DESC')->one();

                                if ($clue) {
                                    $customer = Customer::findOne(['phone' => $v->phone]);
                                    // 更新最新客户名
                                    $customer->name = $v->name;
                                    if (!$customer->save()) {
                                        $dbTrans->rollBack();
                                        $this->res('1000', '录入失败，更新用户出错！');
                                    }

                                    // 更新客户线索信息
                                    $clue->customer_name = $v->name;
                                    $clue->des = $v->des;
                                    if (!$clue->save()) {
                                        $dbTrans->rollBack();
                                        $this->res('1000', '录入失败，更新线索出错！');
                                    }
                                } else {
                                    // 状态为战败或者订车状态，允许新增
                                    if (($upArr['is_fail'] == 1 || $upArr['status'] == 2 || $upArr['status'] == 3)) {
                                        $new_items[] = $v;

                                    // 验证当前状态是否是线索状态（更改最新客户信息）
                                    } elseif ($upArr['status'] == 0) {

                                    // 状态为 意向或者订车状态 不进行新增线索操作
                                    } elseif ($upArr['status'] == 1 || $upArr['status'] == 2) {
                                        // $v['H'] = '该客户在系统内已经存在！';
                                        // $error_data[] = $v;
                                    }
                                }
                            }

                            unset($clueArr[$k]);
                        }

                    }

                }
            }

            $items_data = array_merge_recursive($clueArr, $new_items);

            // 定义是否需要提交事务
            $insertChck = true;

            if (!empty($items_data)) {
                $items_data = $this->areaByid($items_data);
                if(!$this->insertClue($items_data)){
                    $insertChck = false;
                }
            }
            if (!empty($pendingArr)) {
                $pendingArr = $this->areaByid($pendingArr);
                if (!$this->insertPending($pendingArr,2)){
                    $insertChck = false;
                }
            }

            if ($insertChck){
                $dbTrans->commit();
                //线索数据保存成功后发送门店顾问推送
                if (!empty($items_data)) {
                    foreach ($items_data as $v){
                        $objNotice->headquartersImportClueClaimNotice($v->shop_id,1);
                    }
                }
                $this->echoData(1, '录入成功');
            }else{
                $dbTrans->rollBack();
                $this->echoData(0, '录入失败');
            }


        }catch(Exception $e){
            $dbTrans->rollBack();
            $this->echoData(0, '录入失败');
        }
    }

    /**
     * 获取地区信息
     */
    public function areaByid($data) {
        $sql = "select * from crm_dd_area";
        $area = Yii::$app->db->createCommand($sql)->queryAll();
        $newArr = [];
        foreach ($area as $k => $v) {
            $newArr[$v['name']] = $v['id'];
        }
        foreach ($data as $k => $v) {
            $data[$k]->area_id = 0;
            if (!empty($v->area)){
                $data[$k]->area_id = isset($newArr[$v->area]) ? $newArr[$v->area] : 0;
            }
            else if (!empty($v->city)){
                $data[$k]->area_id = isset($newArr[$v->city]) ? $newArr[$v->city] : 0;
            }
        }
        return $data;
    }

    private function echoData($code = 1, $msg = '', $data = null)
    {
        $this->writeErrorLog($data);
        $outString = json_encode([
            "statusCode" => intval($code),
            "content" => strval($msg),
        ]);
        // 输出结果
        header("Content-type: application/json");
        die($outString);
    }

    public function writeErrorLog($error)
    {
        $rootPath = \Yii::getAlias('@frontend/runtime/logs/');
        file_put_contents($rootPath . 'cheUser.log', date("Y-m-d H:i:s") . "\t" .
            'url=>' . \Yii::$app->request->url . "\t" .
            'mes=>' . $error . "\n"
            , FILE_APPEND);
    }

    /**
     * 检测车系中是否包含某个值
     */
    private function check_car($arr = array(), $str)
    {
        foreach ($arr as $v) {
            if ($v['car_brand_type_name'] == $str) {
                return $v['car_brand_type_id'];
            }
        }
        return false;
    }


    /**
     * 检测二维数组中是否包含某个值
     */
    private function check_array($arr, $city, $area)
    {
        foreach ($arr as $v) {
            if (($v['shiName'] == $city || mb_substr($v['shiName'],0,-1,'utf-8') == $city) && $v['quOrXian'] == $area) {//已市跟区做第一个维度
                return $v;
            }
        }
        foreach ($arr as $v) {
            if ($v['quOrXian'] == $area) {//已县或区为第二个维度检测
                return $v;
            }
        }
        $shopNames = [];
        foreach ($arr as $v) {
             if ($v['shiName'] == $city || mb_substr($v['shiName'],0,-1,'utf-8') == $city) {//已市为第三个维度
                 array_push($shopNames,$v);
            }
        }

        if (!empty($shopNames)) {
            return $shopNames[rand(0,count($shopNames)-1)];
        }

        return false;
    }

    /**
     *  线索录入
     */
    private function insertClue($res)
    {
        $array = array_map(function($value){
            return [
                'customer_name' => $value->name,
                'customer_phone' => $value->phone,
                'area_id' => $value->area_id,
                'shop_id' => $value->shop_id,
                'shop_name' => $value->shop_name,
                'intention_id' => $value->car_brand_type_id,
                'intention_des' => $value->car_brand_type_name,
                'des' => $value->des,
                'clue_source' => 16,
                'clue_input_type' => 38,
            ];
        }, $res);

        // 执行批量导入线索信息数据
        $arrReturn = Clue::batchInsert($array, 3, '车城网接口');
        return $arrReturn['status'] ? true : false;
    }

    /**
     *  未下发线索/未上市车型
     */
    private function insertPending($res,$is_type)
    {
        $customer_values = null;
        $pending_values     = null;
        $phoneItems      = [];
        $time            = time();


        //客户表
        $sql_customer_insert = "insert into crm_customer(phone,name,create_time,area)VALUES";

        foreach ($res as $v) {
            $customer_values .= "({$v->phone},'{$v->name}',{$time},'{$v->area_id}'),";
            array_push($phoneItems, $v->phone);
        }

        $sql_customer_insert .= rtrim($customer_values, ',');
        $sql_customer_insert .= "on duplicate key update 
           phone = values(phone),
            name = values(name),
            create_time =values(create_time)
        ";

        if (Yii::$app->db->createCommand($sql_customer_insert)
                ->execute() > 0
        ) {

            $clueCum = "create_time,
        customer_name,
        customer_phone,
        location,
        car_brand_son_type_name,
        intention_id,
        intention_des,
        is_type,
        customer_id";
            //查询客户信息
            $customer = Customer::find()->select('id,phone')->where(['in', 'phone', $phoneItems])->asArray()->all();
            $sqlPending = "insert into crm_clue_pending($clueCum)VALUES";
            foreach ($customer as $cv) {
                foreach ($res as $v) {
                    if ($v->phone == $cv['phone']) {
                        $location = $v->province.$v->city.$v->area;
                        $pending_values .= "(
                        {$time},
                        '{$v->name}',
                        '{$v->phone}',
                        '{$location}',
                        '{$v->car_brand_son_type_name}',
                        '{$v->car_brand_type_id}',
                        '{$v->car_brand_type_name}',
                        {$is_type},
                        {$cv['id']}
                        ),";
                        break;

                    }
                }
            }

            $sqlPending .= rtrim($pending_values, ',');
            $sqlPending .= "
            on duplicate key update 
            create_time=values(create_time),
            customer_name=values(customer_name),
            customer_phone=values(customer_phone),
            location=values(location),
            car_brand_son_type_name=values(car_brand_son_type_name),
            intention_id=values(intention_id),
            intention_des=values(intention_des),
            is_type=values(is_type),
            customer_id=values(customer_id)
            ";

            if (Yii::$app->db->createCommand($sqlPending)
                    ->execute() > 0
            ) {
                return true;
            }
        }

        return false;
    }

    private function dump($data)
    {
        echo "<pre>";
        print_r($data);
        exit;
    }
}
