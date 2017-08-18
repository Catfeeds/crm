<?php

namespace common\models;

use common\logic\DataDictionary;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "crm_dd_area".
 *
 * @property string $id
 * @property string $name
 * @property integer $pid
 * @property integer $level
 */
class Area extends \yii\db\ActiveRecord
{
    /**
     * @var array 定义所有地址信息对应数据
     *
     * @link \commmon\models\Area::getAreaName
     */
    private static $arrArea = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_dd_area';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'pid', 'level'], 'required'],
            [['pid', 'level'], 'integer'],
            [['name'], 'string', 'max' => 255],
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
            'pid' => 'Pid',
            'level' => 'Level',
        ];
    }


    /**
     * 获取等级为3的ID 信息 最后到区
     * @param  int $id 省\市\区的ID
     * @return array
     */
    public static function getIds($id)
    {
        $arrReturn = [];
        $area = self::findOne($id);
        if ($area) {
            switch ($area->level) {
                case 1:
                    $all = self::find()->where(['pid' => $area->id])->asArray()->all();
                    $array = array_column($all, 'id');
                    $all = self::find()->where(['pid' => $array])->asArray()->all();
                    $arrReturn = array_column($all, 'id');
                    array_push($arrReturn, $id);
                    // 将市添加进去
                    foreach ($array as $value) {
                        array_push($arrReturn, $value);
                    }
                    break;
                case 2:
                    $all = self::find()->where(['pid' => $area->id])->asArray()->all();
                    $arrReturn = array_column($all, 'id');
                    array_push($arrReturn, $id);
                    break;
                case 3:
                    $arrReturn = [$area->id];
            }
        }

        return $arrReturn;
    }

    /**
     * 逐级向上获取地址名称
     * @param  integer $id   门店ID
     * @param  string  $and  连接符
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
     * 通过市的名称和区的名称查询到区或者市的信息(没有区用市的信息)
     * @param string $shiName 市的名称
     * @param string $quName  区的名称
     * @return static
     */
    public static function getByName($shiName, $quName)
    {
        // 先查询市
        $parent = self::findOne(['name' => $shiName, 'level' => 2]);
        if (!$parent) {
            // 没有找到，去掉市查询
            $parent = self::findOne(['name' => str_replace('市', '', $shiName), 'level' => 2]);
        }

        if ($parent) {
            $child = self::findOne(['name' => $quName, 'pid' => $parent->id]);
            // 没有区的话，直接拿市
            if (!$child) {
                $child = $parent;
            }
        } else {
            $child = self::findOne(['name' => $shiName, 'level' => 2]);
        }

        return $child;
    }

    /**
     * 通过地址ID 获取到从省到 当前ID对应ID信息
     * @param $id
     * @param string $and
     * @return string
     */
    public static function getAreaName($id, $and = '-')
    {
        $strReturn = '';

        // 先查询出所有数据
        if (empty(self::$arrArea)) {
            $all = (new DataDictionary())->getDictionaryData('area');
            self::$arrArea = ArrayHelper::index($all, 'id');
        }

        // 存在ID对应的地区信息
        if (isset(self::$arrArea[$id])) {
            $arrName = [self::$arrArea[$id]['name']];
            // 不是最上级的话，继续执行该函数
            if (self::$arrArea[$id]['pid'] != 0) {
                $tmpName = self::getAreaName(self::$arrArea[$id]['pid']);
                if ($tmpName) {
                    array_unshift($arrName, $tmpName);
                }
            }

            $strReturn = implode($and, $arrName);
        }

        return $strReturn;
    }

    /**
     * @var array 定义处理好的省市区[三维数组]
     */
    public static $arrHandleArea = [];

    /**
     * 通过省市区验证数据有效性，验证通过返回最后有效地址的ID
     * @param string $province 省名称
     * @param string $city 市名称
     * @param string $area 地区名称
     * @return int 大于1表示有效地址 -1 省错误 -2 市错误 -3 区错误
     */
    public static function validateHandleArea($province, $city, $area = '')
    {
        if (empty(self::$arrHandleArea)) {
            // 查询出全部市
            $areas = Area::find()->indexBy('id')->asArray()->all();
            $arrAreas = [];
            // 第一步取出省
            foreach ($areas as $key => $value) {
                if ($value['level'] == 1) {
                    $value['child'] = [];
                    $arrAreas[$value['name']] = $value;
                    unset($areas[$key]);
                }
            }

            // 数组剩下市和区(处理区对应市)
            $array = [];
            foreach ($areas as $key => $value) {
                if ($value['level'] == 3) {
                    if (isset($array[$value['pid']])) {
                        $array[$value['pid']]['child'][$value['name']] = $value;
                    } else {
                        $array[$value['pid']] = [
                            'child' => [$value['name'] => $value]
                        ];
                    }

                    unset($areas[$key]);
                }
            }

            // 第二步取出市
            foreach ($arrAreas as $key => $value) {
                foreach ($areas as $k => $v) {
                    if ($v['pid'] == $value['id']) {
                        if (isset($array[$v['id']])) {
                            $v['child'] = $array[$v['id']]['child'];
                        } else {
                            $v['child'] = [];
                        }

                        $arrAreas[$key]['child'][$v['name']] = $v;
                        unset($areas[$k]);
                    }
                }
            }

            self::$arrHandleArea = $arrAreas;
        }

        $intReturn = -1;

        // 验证省是否存在
        if (isset(self::$arrHandleArea[$province])) {
            // 判断市有没有问题
            $tmp = self::$arrHandleArea[$province];
            if (isset($tmp['child'][$city])) {
                $tmp = $tmp['child'][$city];
                // 判断是否存在区
                if (empty($area)) {
                    $intReturn = (int)$tmp['id'];
                } else {
                    if (isset($tmp['child'][$area])) {
                        $intReturn = (int)$tmp['child'][$area]['id'];
                    } else {
                        $intReturn = -3;
                    }
                }
            } else {
                $intReturn = -2;
            }
        }

        return $intReturn;
    }
}
