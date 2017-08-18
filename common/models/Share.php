<?php

namespace common\models;

use yii\db\ActiveRecord;


/**
 * This is the model class for table "crm_share".
 *
 * @property integer $id
 * @property string $token
 * @property integer $salesman_id
 * @property string $salesman_name
 * @property integer $shop_id
 * @property string $shop_name
 * @property string $title
 * @property string $desc
 * @property string $car_information
 * @property integer $number
 * @property integer $created_at
 */
class Share extends ActiveRecord
{
    /**
     * 分享类型
     */
    const TYPE_SHARE_CHE = 1;   // 车型分享
    const TYPE_SHARE_SHOP = 2;  // 店铺分享

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%share}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['token'], 'required'],
            [['salesman_id', 'shop_id', 'number', 'created_at', 'type', 'views_people', 'views_number'], 'integer'],
            [['car_information'], 'string'],
            [['token'], 'string', 'max' => 32],
            [['salesman_name'], 'string', 'max' => 60],
            [['shop_name', 'title', 'desc'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '分享信息ID',
            'type' => '分享类型',
            'token' => '和ID对应的token(防止客户修改分享ID -- md5(uniqid()))',
            'salesman_id' => '分享顾问ID',
            'salesman_name' => '分享顾问名称',
            'shop_id' => '分享顾问所在门店',
            'shop_name' => '分享顾问所在门店名称',
            'title' => '分享的标题',
            'desc' => '分享的副标题',
            'car_information' => '分享车型信息（json字符串）',
            'number' => '该分享生成线索数',
            'created_at' => '创建时间',
            'views_people' => '浏览人数',
            'views_number' => '浏览次数',
        ];
    }
}
