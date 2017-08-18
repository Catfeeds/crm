<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_announcement_salesman_read_log".
 *
 * @property integer $salesman_id
 * @property integer $announcement_id
 * @property integer $read_time
 */
class AnnouncementSalesmanReadLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_announcement_salesman_read_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['salesman_id', 'announcement_id', 'read_time'], 'required'],
            [['salesman_id', 'announcement_id', 'read_time'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'salesman_id' => 'Salesman ID',
            'announcement_id' => 'Announcement ID',
            'read_time' => 'Read Time',
        ];
    }
}
