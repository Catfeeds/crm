<?php
namespace frontend\controllers;

use common\logic\ClueValidate;
use common\logic\PhoneLetter;
use common\models\Customer;
use common\models\Share;
use frontend\modules\sales\models\Clue;
use \yii\web\Controller;
use common\traits\Json;
use common\logic\NoticeTemplet;
use common\models\User;
use common\logic\CarBrandAndType;
use common\common\PublicMethod;
use yii;

/**
 * v1.4.4 添加车型分享页面
 * Class ShareController
 * @package frontend\controllers
 */
class ShareController extends Controller
{
    /**
     * @var bool 定义不需要使用布局文件
     */
    public $layout = false;

    /**
     * 引入json 返回处理
     */
    use Json;

    /**
     * 分享页面显示
     * @param integer $id
     * @return string
     */
    public function actionIndex($id)
    {
        if ($id) {
            $share = Share::findOne($id);
            if ($share) {
                $info = $share->car_information ? yii\helpers\Json::decode($share->car_information) : null;
                if ($info) {

                    // 查询车系信息
                    $arrIntentionId = array_column($info, 'intention_id');
                    $intentions = (new CarBrandAndType())->getBrandAndFactoryInfoByTypeId($arrIntentionId);

                    foreach ($info as &$value) {
                        $key = $value['intention_id'];
                        if (isset($intentions[$key])) {
                            $title = $intentions[$key]['brand_name'].' ';
                            $title .= $intentions[$key]['car_brand_type_name'].' ';
                            $value['title'] = $title . $value['title'];
                        }
                    }

                    unset($value);
                }

                // 查询顾问信息
                $user = User::findOne($share->salesman_id);

                // 返回视图信息
                return $this->render('index', [
                    'user' => $user,
                    'share' => $share,
                    'info' => $info,
                ]);
            }
        }

        return false;
    }

    /**
     * 分享页面-车系详情信息
     * @param integer $id
     * @return string
     */
    public function actionDetail($id)
    {
        /* @var $request \yii\web\Request */
        $request = Yii::$app->request;
        $key = $request->get('key');
        if ($id && $key) {
            $share = Share::findOne($id);
            if ($share) {
                // 解析JSON数据
                $result = empty($share->car_information) ? null : yii\helpers\Json::decode($share->car_information);
                if ($result) {

                    // 处理数据，防止客户故意修改意向车系信息
                    $intKey = 0;
                    foreach ($result as $k => $value) {
                        if (md5($k) === $key) {
                            $intKey = $k;
                        }
                    }

                    // 车系信息
                    $info = $result[$intKey];
                    if ($info) {
                        $intentions = (new CarBrandAndType())->getBrandAndFactoryInfoByTypeId($info['intention_id']);
                        $key = $info['intention_id'];
                        if (isset($intentions[$key])) {
                            $title = $intentions[$key]['brand_name'].' ';
                            $title .= $intentions[$key]['car_brand_type_name'].' ';
                            $info['title'] = $title . $info['title'];
                        }
                    }

                    // 请求电商获取信息
                    $url = Yii::$app->params['che_com']['cheApi'];
                    $url = rtrim($url, '/').'/api/crm/cheProduct/productDetail';
                    $url .= '?productId='.$info['productId'];
                    $content = PublicMethod::http_get($url);
                    $html = '';
                    if ($content) {
                        $result = yii\helpers\Json::decode($content, true);
                        if ($result && isset($result['detail'])) {
                            $html = $result['detail'];
                        }
                    }

                    // 载入视图
                    return $this->render('detail', [
                        'share' => $share,
                        'info' => $info, // 车型信息
                        'html' => $html, // 商品详情html,
                    ]);
                }
            }
        }

        return false;
    }

    /**
     * 处理线索信息
     */
    public function actionClue()
    {
        // 接收请求参数
        $request = Yii::$app->request;
        $intShareId = (int)$request->post('share_id');   // 分享ID
        $strToken = $request->post('token');        // 分享token
        $strPhone = $request->post('phone');        // 客户手机号
        $intIntentionId = (int)$request->post('intention_id'); // 车系信息

        // 验证请求数据的有效性
        if ($intShareId && $strToken && $strPhone && $intIntentionId) {
            // 验证手机号的有效性
            $this->arrJson['errMsg'] = '手机号输入错误';
            if (preg_match('/^\d{11}$/', $strPhone)) {
                // 查询分享信息,并验证数据
                $share = Share::findOne($intShareId);
                $this->arrJson['errMsg'] = '分享信息不存在';
                if ($share && $share->token === $strToken) {
                    // 查询客户信息(手机号不存在,那么执行注册)
                    $customer = Customer::findByPhoneOrInsert($strPhone);
                    $this->arrJson['errMsg'] = '抱歉！注册用户失败';
                    if ($customer) {
                        /**
                         * edited by liujx 2017-07-17 添加验证，一个手机号每天只能分享一个车型 start:
                         */
                        $key = 'CRM:share:user_' . $customer->id . ':share_' . $intShareId;
                        /* @var $redis \yii\redis\Connection */
                        $redis = Yii::$app->redis;
                        // 之前处理过，现在不处理，直接返回
                        if ($redis->get($key)) {
                            $this->handleJson([], 0, '收到您的需求，稍后我会和您联系');
                        } else {
                            // end;
                            $redis->setex($key, 86400, time());
                            $this->arrJson['errMsg'] = '抱歉,处理出现问题';

                            // 查询车系信息
                            $intention = (new CarBrandAndType())->getBrandAndFactoryInfoByTypeId($intIntentionId);
                            if ($intention) {
                                $strName = $intention[$intIntentionId]['car_brand_type_name'];
                            } else {
                                $strName = '';
                            }

                            // 新增和修改线索信息
                            $clue = ClueValidate::shareValidateOrInsertClue($customer, $share, [
                                'intention_id' => $intIntentionId,
                                'intention_des' => $strName,
                            ]);

                            if ($clue) {

                                // 根据类型处理发送消息
                                switch ($clue->status) {
                                    // 线索状态是 意向和订车时候发送的内容一样
                                    case Clue::STATUS_WILL:
                                    case Clue::STATUS_BOOK:

                                        // 发送文字短信
                                        $strCustomerName = $customer->name ? $customer->name : $customer->phone;
                                        $strName = $strName ? '('.$strName.')' : '';

                                        // 推送消息
                                        (new NoticeTemplet())->sendNoticeByType(
                                            'shareOldClue',
                                            0,
                                            (string)$clue->salesman_id,
                                            [
                                                '[customer_name]' => $strCustomerName,
                                                '[intention_des]' => $strName
                                            ],
                                            'client_order_fail',
                                            [
                                                'content' => "客户姓名：{$customer->name}\n客户电话：{$clue->customer_phone}\n预定车型：{$strName}\n",
                                                'notice_param' => json_encode(['clue_id' => $clue->id], 320),
                                            ]
                                        );

                                        // 发送短信
                                        $user = User::findOne($clue->salesman_id);
                                        if ($user && $user->phone) {
                                            $phoneObject = new PhoneLetter();

                                            // 发送文字短信
                                            $phoneObject->sendShareUpdateClueSMS(
                                                $user->phone,
                                                $clue->salesman_name,
                                                $strCustomerName,
                                                $strName
                                            );

                                            // 发送语音短信
                                            $phoneObject->sendShareUpdateClueVoice(
                                                $user->phone,
                                                $clue->salesman_name,
                                                $strCustomerName,
                                                $strName
                                            );
                                        }
                                        break;

                                    // 其他状态发送消息内容
                                    default:
                                        // 推送消息
                                        (new NoticeTemplet())->sendNoticeByType(
                                            'shareInsertClue',
                                            0,
                                            (string)$clue->salesman_id,
                                            [],
                                            'client_order_fail'
                                        );

                                        // 发送短信
                                        $user = User::findOne($clue->salesman_id);
                                        if ($user && $user->phone) {
                                            $phoneObject = new PhoneLetter();

                                            // 发送文字短信
                                            $phoneObject->sendShareInsertClueSMS($user->phone, $clue->salesman_name);

                                            // 发送语音短信
                                            $phoneObject->sendShareInsertClueVoice($user->phone, $clue->salesman_name);
                                        }
                                }

                                $this->handleJson([], 0, '收到您的需求，稍后我会和您联系');
                            } else {
                                $redis->del($key);
                            }
                        }
                    }
                }
            }
        }

        return $this->returnJson();
    }
}