<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_dd_intention".
 *
 * @property string $id
 * @property string $name
 * @property string $des
 * @property integer $frequency_day
 * @property integer $total_times
 * @property integer $has_today_task
 * @property integer $is_special
 * @property integer $status
 */
class Intention extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_dd_intention';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['frequency_day', 'total_times', 'has_today_task', 'is_special', 'status'], 'integer'],
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
            'frequency_day' => 'Frequency Day',
            'total_times' => 'Total Times',
            'has_today_task' => 'Has Today Task',
            'is_special' => 'Is Special',
            'status' => 'Status',
        ];
    }
}
