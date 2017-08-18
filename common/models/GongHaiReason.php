<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_gonghai_reason".
 *
 * @property integer $id
 * @property string $reason_name
 */
class GongHaiReason extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_gonghai_reason';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['reason_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'reason_name' => 'Reason Name',
        ];
    }
}
