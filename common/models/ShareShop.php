<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%share_shop}}".
 *
 * @property integer $id
 * @property integer $shop_id
 * @property string $shop_name
 * @property string $banner
 * @property string $main_che_info
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class ShareShop extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%share_shop}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id', 'shop_name'], 'required'],
            [['shop_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['banner', 'main_che_info'], 'string'],
            [['shop_name'], 'string', 'max' => 64],
            [['shop_id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '唯一ID',
            'shop_id' => '店铺ID',
            'shop_name' => '店铺名称',
            'banner' => 'banner 图片信息（json字符串）',
            'main_che_info' => '主推车型（json字符串）',
            'status' => '状态[1 启用 0 不启用]',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
        ];
    }
}
