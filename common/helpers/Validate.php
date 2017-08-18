<?php
/**
 * Created by PhpStorm.
 * User: Think
 * Date: 2017/6/26
 * Time: 16:26
 */

namespace common\helpers;


class Validate
{
    /**
     * 验证数据是否存在并且不为空
     * @param  array $params 验证的数组数据
     * @param  array $keys   验证存在的必须不为空的key
     * @return bool  验证通过返回true
     */
    public static function validateParams($params, $keys)
    {
        // 验证数据必须存在
        if ($params) {
            foreach ($keys as $value) {
                if (!isset($params[$value]) || empty($params[$value])) return false;
            }

            return true;
        }

        return false;
    }

    /**
     * 验证是否为手机号或者电话号码，只是验证格式没有验证号段
     * @param string $phone   手机号
     * @param string $pattern 验证规则 默认使用手机号
     * @return int
     */
    public static function validatePhone($phone, $pattern = '/^\d{6,12}$/')
    {
        return preg_match($pattern, $phone);
    }
}