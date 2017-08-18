<?php
/**
 * 销售指标数据表
 */
namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_tj_sales_target".
 *
 * @property string $id
 * @property integer $company_id
 * @property integer $area_id
 * @property integer $shop_id
 * @property string $year_and_month
 * @property integer $target_num
 * @property integer $finish_num
 * @property string $create_time
 * @property string $create_person
 * @property integer $create_person_id
 */
class TjSalesTarget extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_tj_sales_target';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'area_id', 'shop_id', 'target_num', 'finish_num', 'create_person_id'], 'integer'],
            [['create_time'], 'safe'],
            [['year_and_month'], 'string', 'max' => 7],
            [['create_person'], 'string', 'max' => 255],
            [['shop_id', 'year_and_month'], 'unique', 'targetAttribute' => ['shop_id', 'year_and_month'], 'message' => 'The combination of Shop ID and Year And Month has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Company ID',
            'area_id' => 'Area ID',
            'shop_id' => 'Shop ID',
            'year_and_month' => 'Year And Month',
            'target_num' => 'Target Num',
            'finish_num' => 'Finish Num',
            'create_time' => 'Create Time',
            'create_person' => 'Create Person',
            'create_person_id' => 'Create Person ID',
        ];
    }
}
