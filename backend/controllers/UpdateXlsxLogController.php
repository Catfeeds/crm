<?php

namespace backend\controllers;


use common\models\Area;
use Yii;
use yii\data\Pagination;
use backend\models\UploadForm;
use common\models\Clue;
use common\models\Customer;
use common\logic\PhoneLetter;
use common\logic\CompanyUserCenter;
use common\logic\NoticeTemplet;
use common\logic\ClueValidate;
use common\models\UpdateXlsxLog;


/**
 * UpdateXlsxLogController implements the CRUD actions for UpdateXlsxLog model.
 */
class UpdateXlsxLogController extends BaseController
{
    /**
     * @var bool 关闭 csrf 验证
     */
    public $enableCsrfValidation = false;

    /**
     * @var array 定义excel信息
     */
    private $excelName = [
        'A' => '客户姓名',
        'B' => '手机号码',
        'C' => '门店',
        'D' => '渠道来源',
        'E' => '信息来源',
        'F' => '省',
        'G' => '市',
        'H' => '区',
        'I' => '意向车型',
        'J' => '说明'
    ];

    /**
     * @var array
     */
    private $arrSend = [];

    /**
     * Lists all UpdateXlsxLog models.
     * @return mixed
     */
    public function actionIndex()
    {
        //权限控制 - 只有总部人员有权限，且其角色需要勾选
        $this->checkPermission('/update-xlsx-log/index');

        $get = Yii::$app->request->get();
        $get['update_time'] = empty($get['update_time']) ? 'desc' : $get['update_time'];
        //总数
        $count = UpdateXlsxLog::find()->select('id')->count();
        //分页
        $pagination = new Pagination([
            'defaultPageSize' => 20,
            'totalCount' => $count,
        ]);
        $list = UpdateXlsxLog::find()
            ->orderBy("update_time {$get['update_time']}")
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();;


        return $this->render('index', [
            'list' => $list,
            'pagination' => $pagination,
            'get' => $get,
        ]);
    }

    /**
     * add excel file
     */
    public function actionAdd_file()
    {
        $session         = Yii::$app->getSession();
        $items_data      = [];//接受数据结果集
        $error_data      = [];//错误数据
        $mobile          = null;//多个手机号码
        $file_url        = null;//文件上传路径
        $error_file_name = null;//错误文件名
        $new_items       = [];
        $count = null;
        $ok_count = null;
        $form = new UploadForm();

        if (empty($_FILES)) {
            $this->res('300', '文件不可为空!');
        }

        // 检测文件是否合法
        $is_excel = $form->is_check_file_data($this->excelName, $_FILES);

        if ($is_excel == 'no') {
            $error = '导入数据格式不正确！';
            $this->res('300', $error);
        }

        // 查询出全部市
        $areas = Area::find()->indexBy('id')->asArray()->all();
        $arrAreas = [];
        // 第一步取出省
        foreach ($areas as $key => $value) {
            if ($value['level'] == 1) {
                $value['child'] = [];
                $arrAreas[$value['name']] = $value;
                unset($areas[$key]);
            }
        }

        // 数组剩下市和区(处理区对应市)
        $array = [];
        foreach ($areas as $key => $value) {
            if ($value['level'] == 3) {
                if (isset($array[$value['pid']])) {
                    $array[$value['pid']]['child'][$value['name']] = $value;
                } else {
                    $array[$value['pid']] = [
                        'child' => [$value['name'] => $value]
                    ];
                }

                unset($areas[$key]);
            }
        }

        // 第二步取出市
        foreach ($arrAreas as $key => $value) {
            foreach ($areas as $k => $v) {
                if ($v['pid'] == $value['id']) {
                    if (isset($array[$v['id']])) {
                        $v['child'] = $array[$v['id']]['child'];
                    } else {
                        $v['child'] = [];
                    }

                    $arrAreas[$key]['child'][$v['name']] = $v;
                    unset($areas[$k]);
                }
            }
        }

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {

            //上传文件
            $res = $form->upload($_FILES);
            if ($res['suc'] == 'ok') {
                // 上传文件路径
                $file_url = $res['mes'];

                // 录入的excel信息
                $items_data = $is_excel['mes'];

                // 数据条数
                $count = count($items_data);

                // 去掉excel头部 说明信息，不是有用数据
                unset($items_data[0], $items_data[1]);

                // 没有数据
                if (empty($items_data)) {
                    $transaction->rollBack();
                    $error = '没有导入的数据！';
                    $this->res('300', $error);
                }
                //去掉EXCEL空行
                for ($i = 2; $i < $count; $i++) {
                    if (
                        empty($items_data[$i]['A']) &&
                        empty($items_data[$i]['B']) &&
                        empty($items_data[$i]['C']) &&
                        empty($items_data[$i]['D']) &&
                        empty($items_data[$i]['E']) &&
                        empty($items_data[$i]['F']) &&

                        // 市区必须要有一个
                        (empty($items_data[$i]['G']) && empty($items_data[$i]['H']))

                    ){
                        unset($items_data[$i]);
                    }
                }
                $count = count($items_data);
                // 1.检测不可为空值信息
                for ($i = 2; $i < $count; $i++) {
                    if (
                        empty($items_data[$i]['A']) ||
                        empty($items_data[$i]['B']) ||
                        empty($items_data[$i]['C']) ||
                        empty($items_data[$i]['D']) ||
                        empty($items_data[$i]['E']) ||
                        empty($items_data[$i]['F']) ||

                        // 市区必须要有一个
                        (empty($items_data[$i]['G']) && empty($items_data[$i]['H']))
                    ) {
                        $items_data[$i]['K'] = '信息内容有空值！';
                        $error_data[] = $items_data[$i];
                        unset($items_data[$i]);
                    }else {
                        //去掉空格
                        $items_data[$i]['A'] = trim($items_data[$i]['A']);
                        $items_data[$i]['B'] = trim($items_data[$i]['B']);
                        $items_data[$i]['C'] = trim($items_data[$i]['C']);
                        $items_data[$i]['D'] = trim($items_data[$i]['D']);
                        $items_data[$i]['E'] = trim($items_data[$i]['E']);
                        $items_data[$i]['F'] = trim($items_data[$i]['F']);
                        $items_data[$i]['G'] = trim($items_data[$i]['G']);
                        $items_data[$i]['H'] = trim($items_data[$i]['H']);
                    }
                }

                $xlsx = new UpdateXlsxLog();

                // 获取门店信息
                $company  = new \common\logic\CompanyUserCenter();
                $str_list = $company->getLocalOrganizationalStructure(3);

                // 获取信息来源信息
                $data   = new \common\logic\DataDictionary();
                $source = $data->getDictionaryData('source');

                // 获取渠道来源信息
                $input_type = $data->getDictionaryData('input_type');

                // 获取车型信息
                $car      = new \common\logic\CarBrandAndType();
                $car_type = $car->getAllCarTypeList();

                // 定义没有重复和重复了的手机号数组
                $phoneArr = $dubArr = [];

                // 去掉重复手机号
                foreach ($items_data as $k => $v) {
                    if (!in_array($v['B'], $phoneArr)) {
                        $phoneArr[] = $v['B'];
                    } else {
                        $v['K'] = 'EXCEL表中多个客户手机号码重复！';
                        $error_data[] = $v;
                        unset($items_data[$k]);
                        // 记录重复的手机号码
                        $dubArr[] = $v['B'];
                    }
                }

                // 去掉重复手机号
                foreach ($items_data as $k => $v) {
                    if (in_array($v['B'], $dubArr)) {
                        $v['K'] = 'EXCEL表中多个客户手机号码重复！';
                        $error_data[] = $v;
                        unset($items_data[$k]);
                    }
                }

                // 处理地址信息
                foreach ($items_data as $key => $value) {
                    $value['F'] = trim($value['F']);
                    $value['G'] = trim($value['G']);
                    if (!empty($value['H'])) {
                        $value['H'] = trim($value['H']);
                    }

                    // 验证省市区并且写入地址信息
                    $intReturn = Area::validateHandleArea($value['F'], $value['G'], $value['H']);
                    if ($intReturn > 0) {
                        $items_data[$key]['area'] = $intReturn;
                    } else {
                        switch ($intReturn) {
                            case -1:
                                $value['K'] = '地址信息省填写错误';
                                break;
                            case -2:
                                $value['K'] = '地址信息市填写错误';
                                break;
                            default:
                                $value['K'] = '地址信息区填写错误';
                        }

                        $error_data[] = $value;
                        unset($items_data[$key]);
                    }
                }

                //2.检测数据字典不对应的数据
                foreach ($items_data as $k => $v) {

                    // 检测门店信息是否正确
                    $c = $this->check_array($str_list, $v['C']);

                    // 检测渠道源信息是否正确
                    $d = $this->check_array($input_type, $v['D']);

                    // 检测信息来源信息是否正确
                    $e = $this->check_array($source, $v['E']);

                    // 检测意向车型信息是否正确
                    $f = $this->check_array($car_type, $v['I'], true);

                    // 添加其他信息
                    $items_data[$k]['md'] = $c;//门店id
                    $items_data[$k]['kh'] = $d;//渠道来源id
                    $items_data[$k]['xf'] = $e;//信息来源id
                    $items_data[$k]['yx'] = $f;//意向车型id

                    // 不存在 移除当前合法数组数据 增加错误数据数组

                    // 门店信息为空
                    if (!$c) {
                        $v['K']       = '门店信息不符合！';
                        $error_data[] = $v;
                        unset($items_data[$k]);
                    // 渠道信息为空
                    } else if (!$d) {

                        $v['K']       = '渠道来源信息不符合！';
                        $error_data[] = $v;
                        unset($items_data[$k]);

                    // 来源信息为空
                    } elseif (!$e) {
                        $v['K']       = '信息来源信息不符合！';
                        $error_data[] = $v;
                        unset($items_data[$k]);
                    } elseif (!empty($v['I'])) {
                        // 意向车型存在问题
                        if (!$f) {
                            $v['K']       = '意向车型信息不符合！';
                            $error_data[] = $v;
                            unset($items_data[$k]);
                        }
                    }
                }

                // 3.检测数据库中已经存在的手机号
                $res = null;
                if (!empty($items_data)) {
                    $mobile = '';

                    // 手机号拼接
                    foreach ($items_data as $val) {
                        $mobile .= $val['B'] . ',';
                    }

                    $mobile = rtrim($mobile, ',');

                    // 查找数据库已存在的用户
                    $res = $xlsx->get_user_clue($mobile);
                }

                // 存在已经有的线索信息
                if (!empty($res)) {
                    $phoneArr = [];
                    // 查询出来的数据进行号码分组
                    foreach ($res as $p_v) {
                        $phoneArr[$p_v['customer_phone']][] = $p_v;
                    }

                    // 处理已经有线索信息的客户手机号
                    foreach ($items_data as $k => $v) {
                        $ischeck = false;
                        $upArr = [];
                        // 检测当前导入的号码是否存在
                        // edited by liujx  只要这个用户存在线索信息那 start :
                        $strPhone = strval($v['B']);
                        if (array_key_exists($strPhone, $phoneArr)) {
                            $upArr = $phoneArr[$strPhone][0];
                            $ischeck = true;

//                            // 手机号码转换字符串 整形位数不够
//                            foreach ($phoneArr[strval($v['B'])] as $mv) {
//                                //检测手机号是否在导入数据门店中出现
//                                if($mv['shop_id'] == $v['md']) {
//                                    $ischeck = true;
//                                    $upArr = $mv;
//                                }
//                            }
                        }

                        // 存在客户线索数据
                        if ($ischeck) {

                            /**
                             * edited by liujx 2017-6-28 不允许新增的直接提示错误 start:
                             *
                             * 第一步判断: 系统内存在该客户的线索 不是战败，并且线索状态为 意向或者订车状态的线索，不允许新增线索
                             */
                            if (ClueValidate::validateExists([
                                'and',
                                ['=', 'customer_phone', $strPhone],
                                ['=', 'is_fail', 0],
                                ['in', 'status', [1, 2]]
                            ])) {
                                $v['K'] = '该客户在系统内已经存在！';
                                $error_data[] = $v;
                            } else {

                                // 第二步判断：优先考虑刷新线索的情况(已经存在该手机号不为战败和状态为线索的情况)
                                $clue = clue::find()->where([
                                    'customer_phone' => $strPhone,
                                    'is_fail' => 0,
                                    'status' => 0
                                ])->orderBy('id DESC')->one();

                                // 存在之前的线索刷新这个线索信息
                                if ($clue) {
                                    $customer = Customer::findOne(['phone' => $v['B']]);

                                    // 更新最新客户名
                                    $customer->name = $v['A'];

                                    if (!$customer->save()) {
                                        $transaction->rollBack();
                                        $this->res('300', '导入失败，更新用户出错！');
                                    }

                                    // 更新最新客户线索信息
                                    $clue->clue_input_type = $v['kh'];
                                    $clue->customer_name = $v['A'];

                                    // 来源信息
                                    if (!empty($v['xf'])) $clue->clue_source = $v['xf'];

                                    // 意向车型信息
                                    if (!empty($v['yx'])) $clue->intention_id = $v['yx'];
                                    if (!empty($v['I'])) $clue->intention_des = $v['I'];

                                    $clue->des = $v['J'];
                                    if (!$clue->save()) {
                                        $transaction->rollBack();
                                        $this->res('300', '导入失败，更新线索出错！');
                                    }

                                    $v['K'] = '该客户在系统有一条线索，并将其刷新到最新';
                                    $error_data[] = $v;
                                } else {
                                    // 状态==订单 或者完成 或者是战败
                                    if ($upArr['is_fail'] == 1 || $upArr['status'] == 3) {
                                        $v['customer_id'] = $upArr['customer_id'];
                                        $new_items[] = $v;
                                        // 3.3 验证当前状态是否是线索状态（更改最新客户信息）
                                    } elseif ($upArr['status'] == 1 || $upArr['status'] == 2) {
                                        $v['K'] = '该客户在系统内已经存在！';
                                        $error_data[] = $v;
                                    }
                                }
                            }

                            // 删除这个元素
                            unset($items_data[$k]);
                        }
                    }
                }

                // 生成错误信息excel
                if (!empty($error_data)) {
                    $error_file_name = $form->set_error_info_excel($error_data);
                }

                // 合并新增加的数据，与线索表状态=战败或者交车新生成的线索信息
                $items_data = array_merge_recursive($items_data, $new_items);

                // 插入导入文件表
                $xlsx->success_num        = count($items_data);
                $xlsx->error_num          = count($error_data);
                $xlsx->update_time        = time();
                $xlsx->update_person_id   = $session['userinfo']['id'];
                $xlsx->update_person_name = $session['userinfo']['name'];
                $xlsx->update_type        = 1;
                $xlsx->update_from        = 1;
                $xlsx->update_file        = $file_url;
                $xlsx->error_file         = $error_file_name;
                $ok_count = count($items_data);

                // 保存数据失败
                if (!$xlsx->save()) {
                    $transaction->rollBack();
                    $this->res('300', '导入失败，插入文件表失败！');
                }

                // 新增线索行为记录log
                $this->arrLogParam = [
                    'total_num' => $count - 2,
                    'success_num' => $ok_count,
                    'fail_num' => count($error_data)
                ];

                // 插入最新数据  保存客户信息与线索信息
                if (!empty($items_data)) {
                    if ($xlsx->insert_info($items_data)) {
                        $arrNewSend = $this->group_array($items_data);

                        $this->smsSend($arrNewSend);
                        $transaction->commit();
                        $this->res();
                    } else {
                        $transaction->rollBack();
                        $this->res('300', '导入失败，保存客户信息与线索信息！');
                    }
                } else {
                    $transaction->commit();
                    $this->res();
                }

            } else {
                $transaction->rollBack();
                $error = $res['mes'];
                $this->res(300, $error);
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * 检测二维数组中是否包含某个值
     */
    private function check_array($arr = array(), $str, $ischeck = false)
    {
        foreach ($arr as $v) {
            if (!$ischeck) {
                if ($v['name'] ==  $str) {
                    return $v['id'];
                }
            }else{
                if ($v['car_brand_type_name'] ==  $str) {
                    return $v['car_brand_type_id'];
                }
            }
        }
        return false;
    }

    /**
     *相同数据分组
     */
    private function group_array($arr)
    {
        //取出导入所有的门店id

        foreach ($arr as $v) {
            array_push($this->arrSend, $v['md']);
        }

        //门店id重复分组
        $arrNewSend = array_count_values($this->arrSend);
        return $arrNewSend;
    }

    private function smsSend($arrNewSend)
    {
        //短信类
        $sms = new PhoneLetter();

        //获取门店店长类
        $company = new CompanyUserCenter();
        $objNotice = new NoticeTemplet();
        foreach ($arrNewSend as $k => $v) {

            //获取相关门店店长信息
//            $shop = $company->getShopownerByShopId($k);
//            foreach($shop as $val)//一个门店可以有多个店长
//            {
//                if (!empty($val['phone'])) {
//                    //给店长发送文字短信
//                    $sms->uploadClueToShopowner($val['phone'], $val['name'], $v);
//                    //给店长发语音短信
//                    $sms->headquartersAssignClueToShop($val['phone'],$val['name'],$v);
//                }
//            }
            //推送 门店顾问
            $objNotice->headquartersImportClueClaimNotice($k, $v);
        }
    }
}

