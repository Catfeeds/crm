<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_car_brand_type".
 *
 * @property integer $car_brand_type_id
 * @property string $car_brand_type_name
 * @property string $pic_url
 * @property integer $brand_id
 * @property string $brand_name
 * @property string $brand_type_id
 * @property string $brand_type
 * @property string $scfs
 * @property string $first_num
 * @property string $tj
 * @property string $sortid
 * @property string $isused
 * @property string $isshow
 * @property string $level
 * @property string $sale_state
 * @property string $pic_count
 * @property string $show_name
 * @property string $zhidao_price
 * @property string $car_model_name
 * @property string $car_model
 * @property string $url
 * @property string $body_structure
 * @property string $engine
 * @property string $transmission
 * @property string $color
 * @property string $color_name
 * @property string $color_inner
 * @property string $color_name_inner
 * @property string $ename
 * @property string $source_brand_type_id
 * @property string $source_id
 * @property string $source_pic_url
 * @property string $min_price
 * @property string $max_price
 * @property string $tags
 * @property string $kb_average
 * @property string $gearbox
 * @property string $min_delear_price
 * @property string $max_delear_price
 * @property string $Is_display
 * @property string $autohome_brand_id
 * @property string $autohome_brand_type_id
 * @property string $factory_id
 */
class CarBrandType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_car_brand_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['car_brand_type_id'], 'required'],
            [['car_brand_type_id', 'brand_id'], 'integer'],
            [['car_brand_type_name', 'pic_url', 'brand_name', 'brand_type_id', 'brand_type', 'scfs', 'first_num', 'tj', 'sortid', 'isused', 'isshow', 'level', 'sale_state', 'pic_count', 'show_name', 'zhidao_price', 'car_model_name', 'car_model', 'url', 'body_structure', 'engine', 'transmission', 'color', 'color_name', 'color_inner', 'color_name_inner', 'ename', 'source_brand_type_id', 'source_id', 'source_pic_url', 'min_price', 'max_price', 'tags', 'kb_average', 'gearbox', 'min_delear_price', 'max_delear_price', 'Is_display', 'autohome_brand_id', 'autohome_brand_type_id', 'factory_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'car_brand_type_id' => 'Car Brand Type ID',
            'car_brand_type_name' => 'Car Brand Type Name',
            'pic_url' => 'Pic Url',
            'brand_id' => 'Brand ID',
            'brand_name' => 'Brand Name',
            'brand_type_id' => 'Brand Type ID',
            'brand_type' => 'Brand Type',
            'scfs' => 'Scfs',
            'first_num' => 'First Num',
            'tj' => 'Tj',
            'sortid' => 'Sortid',
            'isused' => 'Isused',
            'isshow' => 'Isshow',
            'level' => 'Level',
            'sale_state' => 'Sale State',
            'pic_count' => 'Pic Count',
            'show_name' => 'Show Name',
            'zhidao_price' => 'Zhidao Price',
            'car_model_name' => 'Car Model Name',
            'car_model' => 'Car Model',
            'url' => 'Url',
            'body_structure' => 'Body Structure',
            'engine' => 'Engine',
            'transmission' => 'Transmission',
            'color' => 'Color',
            'color_name' => 'Color Name',
            'color_inner' => 'Color Inner',
            'color_name_inner' => 'Color Name Inner',
            'ename' => 'Ename',
            'source_brand_type_id' => 'Source Brand Type ID',
            'source_id' => 'Source ID',
            'source_pic_url' => 'Source Pic Url',
            'min_price' => 'Min Price',
            'max_price' => 'Max Price',
            'tags' => 'Tags',
            'kb_average' => 'Kb Average',
            'gearbox' => 'Gearbox',
            'min_delear_price' => 'Min Delear Price',
            'max_delear_price' => 'Max Delear Price',
            'Is_display' => 'Is Display',
            'autohome_brand_id' => 'Autohome Brand ID',
            'autohome_brand_type_id' => 'Autohome Brand Type ID',
            'factory_id' => 'Factory ID',
        ];
    }
}
