<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_dd_planned_purchase_time".
 *
 * @property string $id
 * @property string $name
 * @property string $des
 * @property integer $status
 * @property integer $is_special
 */
class PlannedPurchaseTime extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_dd_planned_purchase_time';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'is_special'], 'integer'],
            [['name', 'des'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'des' => 'Des',
            'status' => 'Status',
            'is_special' => 'Is Special',
        ];
    }
}
