<?php
/**
 * 激励表
 */
namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_excitation".
 *
 * @property string $id
 * @property string $create_person
 * @property integer $create_person_id
 * @property string $end_person
 * @property integer $end_person_id
 * @property string $name
 * @property string $start_time
 * @property string $end_time
 * @property string $money
 * @property integer $status
 * @property string $active_shop_ids
 * @property string $clue_price
 * @property string $clue_to_intention_price
 * @property string $new_intention_price
 * @property string $finish_phone_task_price
 * @property string $to_shop_price
 * @property string $to_home_price
 * @property string $dingche_price
 * @property string $jiaoche_price
 */
class Excitation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_excitation';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_person_id', 'end_person_id', 'status'], 'integer'],
            [['start_time', 'end_time'], 'safe'],
            [['money', 'clue_price', 'clue_to_intention_price', 'new_intention_price', 'finish_phone_task_price', 'to_shop_price', 'to_home_price', 'dingche_price', 'jiaoche_price'], 'number'],
            [['create_person', 'end_person', 'name'], 'string', 'max' => 255],
            [['active_shop_ids'], 'string', 'max' => 1000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'create_person' => 'Create Person',
            'create_person_id' => 'Create Person ID',
            'end_person' => 'End Person',
            'end_person_id' => 'End Person ID',
            'name' => 'Name',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'money' => 'Money',
            'status' => 'Status',
            'active_shop_ids' => 'Active Shop Ids',
            'clue_price' => 'Clue Price',
            'clue_to_intention_price' => 'Clue To Intention Price',
            'new_intention_price' => 'New Intention Price',
            'finish_phone_task_price' => 'Finish Phone Task Price',
            'to_shop_price' => 'To Shop Price',
            'to_home_price' => 'To Home Price',
            'dingche_price' => 'Dingche Price',
            'jiaoche_price' => 'Jiaoche Price',
        ];
    }
}
