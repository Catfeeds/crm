<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_user_role_orgids".
 *
 * @property integer $user_id
 * @property integer $role_id
 * @property string $org_ids
 */
class UserRoleOrgids extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_user_role_orgids';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'role_id'], 'required'],
            [['user_id', 'role_id'], 'integer'],
            [['org_ids'], 'string'],
            [['user_id', 'role_id'], 'unique', 'targetAttribute' => ['user_id', 'role_id'], 'message' => 'The combination of User ID and Role ID has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'role_id' => 'Role ID',
            'org_ids' => 'Org Ids',
        ];
    }
}
