<?php

namespace common\models;

use common\helpers\Helper;
use frontend\modules\sales\logic\MemberLogic;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "crm_customer".
 *
 * @property integer $id
 * @property string $phone
 * @property string $name
 * @property string $spare_phone
 * @property string $weixin
 * @property integer $sex
 * @property integer $profession
 * @property integer $area
 * @property string $address
 * @property string $birthday
 * @property integer $create_time
 * @property integer $is_keep
 * @property integer $age_group_level_id
 * @property integer $member_id
 */
class Customer extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_customer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone', 'create_time'], 'required'],
            [['sex', 'profession', 'area', 'create_time', 'is_keep', 'age_group_level_id'], 'integer'],
            [['phone', 'spare_phone'], 'string', 'max' => 15],
            [['name', 'weixin'], 'string', 'max' => 255],
            [['address'], 'string', 'max' => 1000],
            [['birthday'], 'string', 'max' => 10],
            [['phone'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '自增主键',
            'phone' => '客户的手机号，做唯一索引',
            'name' => '客户姓名',
            'spare_phone' => '客户的备用手机号，可以为空',
            'weixin' => '客户的微信账号',
            'sex' => '性别： 1 - 男，2 - 女',
            'profession' => '职位id',
            'area' => '客户所在的地区 地区的id',
            'address' => '客户居住地址',
            'birthday' => '客户生日',
            'create_time' => '客户收录时间',
            'is_keep' => '是否是保有客户： 0 - 不是 1 - 是',
            'age_group_level_id' => '年龄段（对应年龄段数据字典中的id）',
            'member_id' => '用户ID'
        ];
    }


    /**
     * findByMemberIdOrInsert() 通过会员ID查询客服信息(不存在执行新增)
     * @param int $intMemberId 客户手机号
     * @param array $params 新增的其它数据
     * @return Customer|null|static
     */
    public static function findByMemberIdOrInsert($intMemberId, $params = [])
    {
        $customer = self::findOne(['member_id' => $intMemberId]);
        if (!$customer) {
            $customer = null;
            // 请求验证用户是否存在
            $response = self::validateUser($intMemberId);
            if ($response && !empty($response['status']) && !empty($response['phone'])) {
                // 查询这个手机号我们是否已经注册
                $customer = self::findOne(['phone' => $response['phone']]);
                if ($customer) {
                    // 会员手机号注册过,并且没有member，那么更新 member_id
                    if (empty($customer->member_id)) {
                        $customer->member_id = $intMemberId;
                        $customer->save();
                    } else {
                        // 我们的member id 和 传递的member id 不一样
                        if ($customer->member_id != $intMemberId) {
                            // 记录日志
                            Helper::logs('error/'.date('Ymd').'-customer-error.log', [
                                'time' => date('Y-m-d H:i:s'),
                                'customer' => $customer->toArray(),
                                'response' => $response,
                                'member_id' => $intMemberId,
                                'params' => $params,
                                'error' => '传递用户intMemberId和查询到数据不一致',
                            ]);

                            // 返回数据为空
                            $customer = null;
                        }
                    }
                } else {
                    // 没有注册那么注册这个客户
                    $customer = new Customer();
                    if ($params) $customer->attributes = $params;
                    $customer->phone = $response['phone'];
                    $customer->member_id = $intMemberId;
                    $customer->create_time = time();
                    if (!$customer->save(false)) {
                        $customer = null;
                    }
                }
            }
        }

        return $customer;
    }

    /**
     * validateUser() 验证用户是否存在
     * @param  int $intUid
     * @return array 返回数组
     */
    public static function  validateUser($intUid)
    {
        $member = new MemberLogic();
        $response = $member->get('/inside/user/check', ['uid' => $intUid]);

        // 记录请求信息
        Helper::logs('curl/'.date('Ymd').'-customer.log', array_merge(
            ['time' => date('Y-m-d H:i:s'), 'ip' => Helper::getIpAddress()],
            $member->getRequestInfo()
        ));

        // 请求成功
        $mixReturn = null;
        if ($response && $response['err_code'] === 0 && !empty($response['data'])) {
            $mixReturn = $response['data'];
        }

        return $mixReturn;
    }

    /**
     * 通过手机号查询这个客户，没有的话，注册这个客户手机号
     * @param string $strPhone 客户手机号
     * @param array $params    注册时候的其它信息
     * @return Customer|null|static
     */
    public static function findByPhoneOrInsert($strPhone, $params = [])
    {
        $customer = self::findOne(['phone' => $strPhone]);
        if (!$customer) {
            // 没有注册那么注册这个客户
            $customer = new Customer();
            if ($params) $customer->attributes = $params;
            $customer->phone = $strPhone;
            $customer->create_time = time();
            if (!$customer->save(false)) {
                $customer = null;
            } else {
                // 去用户中心注册
                (new MemberLogic())->addMember($customer->id);
            }
        }

        return $customer;
    }
}
