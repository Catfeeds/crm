<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%check_phone}}".
 *
 * @property integer $phone
 */
class CheckPhone extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%check_phone}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'phone' => 'Phone',
        ];
    }
}
