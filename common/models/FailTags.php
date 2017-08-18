<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_dd_fail_tags".
 *
 * @property string $id
 * @property string $type
 * @property string $name
 * @property string $des
 * @property integer $use_times
 * @property integer $status
 * @property integer $is_special
 */
class FailTags extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_dd_fail_tags';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['des'], 'string'],
            [['used_times', 'status', 'is_special'], 'integer'],
            [['type', 'name', 'group'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'name' => 'Name',
            'des' => 'Des',
            'used_times' => 'Used Times',
            'status' => 'Status',
            'group' => 'Group',
            'is_special' => 'Is Special',
        ];
    }
}
