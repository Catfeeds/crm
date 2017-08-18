<?php
//  汽车品牌表
namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_car_brand".
 *
 * @property string $brand_id
 * @property string $brand_name
 * @property string $pic_url
 * @property string $first_num
 * @property string $py
 * @property string $pyem
 * @property integer $isused
 * @property string $ename
 * @property integer $sortid
 * @property integer $recommend
 * @property string $national
 * @property integer $national_id
 * @property string $create_date
 * @property string $done_date
 * @property integer $brand_story
 * @property string $tags
 * @property integer $source_brand_id
 * @property integer $source_id
 * @property string $source_pic_url
 * @property integer $isdisplay
 * @property integer $autohome_brand_id
 */
class CarBrand extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_car_brand';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['brand_id'], 'required'],
            [['brand_id', 'isused', 'sortid', 'recommend', 'national_id', 'brand_story', 'source_brand_id', 'source_id', 'isdisplay', 'autohome_brand_id'], 'integer'],
            [['create_date', 'done_date'], 'safe'],
            [['brand_name', 'pic_url', 'py', 'pyem', 'ename', 'national', 'tags', 'source_pic_url'], 'string', 'max' => 255],
            [['first_num'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'brand_id' => 'Brand ID',
            'brand_name' => 'Brand Name',
            'pic_url' => 'Pic Url',
            'first_num' => 'First Num',
            'py' => 'Py',
            'pyem' => 'Pyem',
            'isused' => 'Isused',
            'ename' => 'Ename',
            'sortid' => 'Sortid',
            'recommend' => 'Recommend',
            'national' => 'National',
            'national_id' => 'National ID',
            'create_date' => 'Create Date',
            'done_date' => 'Done Date',
            'brand_story' => 'Brand Story',
            'tags' => 'Tags',
            'source_brand_id' => 'Source Brand ID',
            'source_id' => 'Source ID',
            'source_pic_url' => 'Source Pic Url',
            'isdisplay' => 'Isdisplay',
            'autohome_brand_id' => 'Autohome Brand ID',
        ];
    }
}
