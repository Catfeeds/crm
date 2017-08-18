<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%customer_error_log}}".
 *
 * @property integer $id
 * @property string $phone
 * @property integer $created_at
 */
class CustomerErrorLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_error_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone'], 'required'],
            [['created_at'], 'integer'],
            [['phone'], 'string', 'max' => 15],
            [['phone'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '记录ID',
            'phone' => '客户手机号',
            'created_at' => '创建时间',
        ];
    }

    /**
     * 查询数据，没有执行新增数据
     * @param string $phone 手机号
     * @return CustomerErrorLog|null|static
     */
    public static function findOrInsert($phone)
    {
        $one = self::findOne(['phone' => $phone]);
        if (!$one) {
            $one = new CustomerErrorLog();
            $one->phone = $one;
        }

        $one->created_at = time();
        if ($one->save()) {
            return $one;
        } else {
            return null;
        }
    }
}
