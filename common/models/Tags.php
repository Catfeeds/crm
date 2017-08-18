<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_tags".
 *
 * @property string $id
 * @property string $name
 * @property integer $type
 * @property integer $status
 * @property integer $used_times
 */
class Tags extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_tags';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'used_times'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 20],
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
            'type' => 'Type',
            'status' => 'Status',
            'used_times' => 'Used Times',
        ];
    }
}
