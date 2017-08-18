<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "crm_user".
 *
 * @property integer $id
 * @property string $nickname
 * @property string $name
 * @property string $phone
 * @property string $birthday
 * @property string $profession
 * @property string $email
 * @property string $access_token
 * @property string $auth_key
 * @property string $password_hash
 * @property string $avatar
 * @property integer $shop_id
 * @property string $money
 * @property string $ice_money
 * @property string $role_info
 * @property integer $sex
 * @property integer $is_delete
 * @property string $last_login_time
 * @property string $huawei_push_token
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'role_info'], 'required'],
            [['id', 'shop_id', 'sex', 'is_delete'], 'integer'],
            [['money', 'ice_money'], 'number'],
            [['last_login_time'], 'safe'],
            [['nickname', 'name', 'profession', 'email', 'avatar', 'role_info'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 15],
            [['birthday'], 'string', 'max' => 10],
            [['access_token'], 'string', 'max' => 1000],
            [['auth_key', 'password_hash', 'huawei_push_token'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nickname' => 'Nickname',
            'name' => 'Name',
            'phone' => 'Phone',
            'birthday' => 'Birthday',
            'profession' => 'Profession',
            'email' => 'Email',
            'access_token' => 'Access Token',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'avatar' => 'Avatar',
            'shop_id' => 'Shop ID',
            'money' => 'Money',
            'role_info' => 'Role Info',
            'sex' => 'Sex',
            'is_delete' => 'Is Delete',
            'last_login_time' => 'Last Login Time',
            'huawei_push_token' => 'Huawei Push Token',
        ];
    }
    
    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * Finds user by phone
     *
     * @param string $phoneOrEmail
     * @return static|null
     */
    public static function findByPhone($phoneOrEmail)
    {
        $arrWhere = [
            'or',
            ['=', 'phone', $phoneOrEmail],
            ['=', 'email', $phoneOrEmail]
        ];
        return static::find()->where($arrWhere)->one();
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
}
