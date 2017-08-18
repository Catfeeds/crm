<?php

namespace common\models;

use common\logic\CompanyUserCenter;
use common\logic\DataDictionary;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "{{%organizational_structure}}".
 *
 * @property string $id
 * @property string $name
 * @property integer $level
 * @property integer $pid
 */
class OrganizationalStructure extends \yii\db\ActiveRecord
{
    /**
     * 组织构架层级
     */
    const LEVEL_ALL = 10;       // 总部
    const LEVEL_COMPANY = 15;   // 公司
    const LEVEL_REGION = 20;    // 大区
    const LEVEL_STORE = 30;     // 门店

    /**
     * @var array 所有组织构架信息 key 为 ID
     */
    private static $arrOrganizationalStructure = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_organizational_structure';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'level'], 'required'],
            [['level', 'pid'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '自增id',
            'name' => '组织名称',
            'level' => '在组织架构中的层级 ：
1 - 总部；
2 - 大区；
3 - 门店；',
            'pid' => '组织架构中，其父层级的id
1、总部的为0；',
        ];
    }

    /**
     * 获取门店信息
     * @return array
     */
    public function getOrganizationalStructure() {
       return  (new \yii\db\Query())
            ->select(['*'])
            ->from('crm_organizational_structure')
            ->where('level > 1')
            ->all();
    }

    /**
     * 逐级向上获取门店名称
     * @param  int    $id   门店ID
     * @param  string $and  连接符
     * @return string
     */
    public static function getParentNamesAll($id, $and = '-')
    {
        $strName = '';
        $shop = self::findOne($id);
        if ($shop) {
            $arrName = [$shop->name];
            if ($shop->pid != 0) {
                array_unshift($arrName, self::getParentNamesAll($shop->pid, $and));
            }

            $strName = implode($and, $arrName);
        }

        return $strName;
    }

    /**
     * 获取自己和上一级的名称
     * @param int    $id  门店ID
     * @param string $and 连接字符串
     * @return string
     */
    public static function getParentNames($id, $and = '-')
    {
        $strName = '';
        $shop = self::findOne($id);
        if ($shop) {
            $strName = $shop->name;
            $parent = self::findOne($shop->pid);
            if ($parent) {
                $strName = $parent->name.$and.$strName;
            }
        }

        return $strName;
    }

    /**
     * 通过门店ID获取属于这个门店的随机一个顾问信息
     * @param $intShopId
     * @return null|array
     */
    public static function getRandomUser($intShopId)
    {
        $mixReturn = null;
        // 通过门店获取到这个门店的所以顾问信息
        $common = new CompanyUserCenter();
        $users = $common->getShopSales($intShopId);
        if ($users) {
            // 在数组中随机一个元素出来，返回的是这个数组的key
            $key = array_rand($users, 1);
            $mixReturn = $users[$key];
        }

        return $mixReturn;
    }

    /**
     * 获取组织名称 通过组织ID 从下往上找
     *
     * 例如 组织$id = 16, $level = self::LEVEL_REGION
     * 返回 中规车一区-南京店
     *
     * @param int $id       组织ID
     * @param int $level    指定找到最上级的层级
     * @param string $and   不同层级名称之间的链接符号
     * @return string
     */
    public static function getShopName($id, $level, $and = '-')
    {
        $strReturn = '';

        // 先查询出所有数据
        if (empty(self::$arrOrganizationalStructure)) {
            self::$arrOrganizationalStructure = self::find()->indexBy('id')->asArray()->all();
        }

        // 存在ID对应的地区信息
        if (isset(self::$arrOrganizationalStructure[$id])) {
            $arrName = [self::$arrOrganizationalStructure[$id]['name']];
            // 不是最上级的话，继续执行该函数
            if (self::$arrOrganizationalStructure[$id]['pid'] != 0 &&
                self::$arrOrganizationalStructure[$id]['level'] > $level) {
                $tmpName = self::getShopName(self::$arrOrganizationalStructure[$id]['pid'], $level, $and);
                if ($tmpName) {
                    array_unshift($arrName, $tmpName);
                }
            }

            $strReturn = implode($and, $arrName);
        }

        return $strReturn;
    }

    /**
     * 通过组织ID 向下查询到门店ID
     * @param  int $id 组织ID
     * @return array
     */
    public static function getChildIds($id)
    {
        $arrReturn = [];
        $one = self::findOne(['id' => $id]);
        if ($one) {
            if ($one->level < self::LEVEL_STORE) {
                $arrReturn = self::getChildAll($id);
            } else {
                $arrReturn = [$id];
            }
        }

        return $arrReturn;
    }

    /**
     * 通过父级ID 查询到所有的门店ID
     * @param integer|array $id 父级ID
     * @return array
     */
    public static function getChildAll($id)
    {
        $arrReturn = [];
        $all = self::find()->where(['pid' => $id, 'is_delete' => 0])->asArray()->all();
        if ($all) {
            $arrIds = array_column($all, 'id');
            $one = array_shift($all);
            if ($one['level'] < self::LEVEL_STORE) {
                $arrReturn = self::getChildAll($arrIds);
            } else {
                $arrReturn = $arrIds;
            }
        }

        return $arrReturn;
    }
}
