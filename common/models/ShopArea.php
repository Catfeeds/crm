<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%shop_area}}".
 *
 * @property string $shengName
 * @property string $shiName
 * @property string $quOrXian
 * @property integer $shop_id
 * @property string $shop_name
 */
class ShopArea extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_area}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id'], 'integer'],
            [['shengName', 'shiName', 'quOrXian'], 'string', 'max' => 50],
            [['shop_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'shengName' => 'Sheng Name',
            'shiName' => 'Shi Name',
            'quOrXian' => 'Qu Or Xian',
            'shop_id' => 'Shop ID',
            'shop_name' => 'Shop Name',
        ];
    }
}
