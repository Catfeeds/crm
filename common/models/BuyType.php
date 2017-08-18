<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_dd_buy_type".
 *
 * @property string $id
 * @property string $name
 * @property string $des
 * @property integer $status
 */
class BuyType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_dd_buy_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status'], 'integer'],
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
        ];
    }
}
