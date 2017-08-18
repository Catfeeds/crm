<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_car_brand_son_type".
 *
 * @property string $car_brand_son_type_id
 * @property string $car_brand_son_type_name
 * @property string $pic_url
 * @property integer $brand_id
 * @property integer $car_brand_type_id
 * @property integer $son_type_id
 * @property string $son_type
 * @property string $factory_price_168
 * @property string $factory_price
 * @property string $bottom_price
 * @property string $ershouche_min_price
 * @property string $car_brand_son_type_name_168
 * @property string $url_168
 * @property string $sale_state
 * @property string $csjg
 * @property string $engine
 * @property string $engine_ai_from
 * @property string $engine_capacity
 * @property string $engine_fuel_type
 * @property string $zws
 * @property string $short_name
 * @property string $driving_mode
 * @property string $pan_sky
 * @property string $elec_sky
 * @property string $main_elec_adj
 * @property string $stab_con
 * @property string $hid
 * @property string $gps
 * @property string $cruise_con
 * @property string $lea
 * @property string $rear_park_rad
 * @property string $air_con_model
 * @property string $mult_steel
 * @property string $led
 * @property string $rev_video
 * @property string $nokey_start
 * @property string $front_heat
 * @property string $day_run
 * @property string $auto_park
 * @property string $blcp
 * @property string $channel_price
 * @property string $channel_photo
 * @property string $model_number
 * @property string $vin_code
 * @property string $source_brand_son_type_id
 * @property string $source_id
 * @property string $min_delear_price
 * @property string $max_delear_price
 * @property string $specid
 * @property string $subsidies
 */
class CarBrandSonType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_car_brand_son_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['car_brand_son_type_id'], 'required'],
            [['car_brand_son_type_id', 'brand_id', 'car_brand_type_id', 'son_type_id'], 'integer'],
            [['car_brand_son_type_name', 'pic_url', 'son_type', 'factory_price_168', 'factory_price', 'bottom_price', 'ershouche_min_price', 'car_brand_son_type_name_168', 'url_168', 'sale_state', 'csjg', 'engine', 'engine_ai_from', 'engine_capacity', 'engine_fuel_type', 'zws', 'short_name', 'driving_mode', 'pan_sky', 'elec_sky', 'main_elec_adj', 'stab_con', 'hid', 'gps', 'cruise_con', 'lea', 'rear_park_rad', 'air_con_model', 'mult_steel', 'led', 'rev_video', 'nokey_start', 'front_heat', 'day_run', 'auto_park', 'blcp', 'channel_price', 'channel_photo', 'model_number', 'vin_code', 'source_brand_son_type_id', 'source_id', 'min_delear_price', 'max_delear_price', 'specid', 'subsidies'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'car_brand_son_type_id' => 'Car Brand Son Type ID',
            'car_brand_son_type_name' => 'Car Brand Son Type Name',
            'pic_url' => 'Pic Url',
            'brand_id' => 'Brand ID',
            'car_brand_type_id' => 'Car Brand Type ID',
            'son_type_id' => 'Son Type ID',
            'son_type' => 'Son Type',
            'factory_price_168' => 'Factory Price 168',
            'factory_price' => 'Factory Price',
            'bottom_price' => 'Bottom Price',
            'ershouche_min_price' => 'Ershouche Min Price',
            'car_brand_son_type_name_168' => 'Car Brand Son Type Name 168',
            'url_168' => 'Url 168',
            'sale_state' => 'Sale State',
            'csjg' => 'Csjg',
            'engine' => 'Engine',
            'engine_ai_from' => 'Engine Ai From',
            'engine_capacity' => 'Engine Capacity',
            'engine_fuel_type' => 'Engine Fuel Type',
            'zws' => 'Zws',
            'short_name' => 'Short Name',
            'driving_mode' => 'Driving Mode',
            'pan_sky' => 'Pan Sky',
            'elec_sky' => 'Elec Sky',
            'main_elec_adj' => 'Main Elec Adj',
            'stab_con' => 'Stab Con',
            'hid' => 'Hid',
            'gps' => 'Gps',
            'cruise_con' => 'Cruise Con',
            'lea' => 'Lea',
            'rear_park_rad' => 'Rear Park Rad',
            'air_con_model' => 'Air Con Model',
            'mult_steel' => 'Mult Steel',
            'led' => 'Led',
            'rev_video' => 'Rev Video',
            'nokey_start' => 'Nokey Start',
            'front_heat' => 'Front Heat',
            'day_run' => 'Day Run',
            'auto_park' => 'Auto Park',
            'blcp' => 'Blcp',
            'channel_price' => 'Channel Price',
            'channel_photo' => 'Channel Photo',
            'model_number' => 'Model Number',
            'vin_code' => 'Vin Code',
            'source_brand_son_type_id' => 'Source Brand Son Type ID',
            'source_id' => 'Source ID',
            'min_delear_price' => 'Min Delear Price',
            'max_delear_price' => 'Max Delear Price',
            'specid' => 'Specid',
            'subsidies' => 'Subsidies',
        ];
    }
}
