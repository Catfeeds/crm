<?php
/**
 * Created by PhpStorm.
 * Date: 2017/6/14
 * Time: 14:36
 */

namespace common\helpers;


use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class Helper
{
    /**
     * timeDiffer() 处理显示两个时间差的时间信息
     * @author liujinxing
     * @param int       $start  开始时间
     * @param int       $end    结束时间
     * @param string    $format 显示格式
     * @return string   返回格式化的时间
     */
    public static function timeDiffer($start, $end, $format = 'd天h小时m分')
    {
        $time = abs($start - $end);
        $d = floor($time / 86400);
        $h = floor($time % 86400 / 3600);  // %取余
        $m = floor($time % 86400 % 3600 / 60);
        return str_replace(['d', 'h', 'm'], [$d, $h, $m], $format);
    }

    /**
     * getIpAddress() 获取IP地址
     * @return string 返回字符串
     */
    public static function getIpAddress()
    {
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $strIpAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $strIpAddress = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $strIpAddress = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $strIpAddress = getenv('HTTP_X_FORWARDED_FOR');
            } else if (getenv('HTTP_CLIENT_IP')) {
                $strIpAddress = getenv('HTTP_CLIENT_IP');
            } else {
                $strIpAddress = getenv('REMOTE_ADDR') ? getenv('REMOTE_ADDR') : '';
            }
        }

        return $strIpAddress;
    }

    /**
     * 根据文件名创建目录
     * @param $strFile
     */
    public static function createFolder($strFile)
    {
        $strPath = dirname($strFile);
        if ( ! file_exists($strPath)) mkdir($strPath, 0777, true);
    }

    /**
     * info() 记录日志信息
     * @param string $strFile 写入的文件
     * @param string $strInfo 记录日志的信息
     */
    public static function logs($strFile, $strInfo = '')
    {
        // 生成文件
        $strFile = \Yii::$app->getRuntimePath() . '/logs/' . $strFile;
        self::createFolder($strFile);

        // 处理为字符串
        if (is_array($strInfo)) $strInfo = Json::encode($strInfo);

        // 写入日志
        file_put_contents($strFile, $strInfo . PHP_EOL, FILE_APPEND);
    }

    /**
     * 根据请求参数，处理查询的where 条件信息
     * @param  array $params 请求的参数
     * @param  array $where  定义的查询处理方式
     * @return array 返回 yii 查询 where 条件
     */
    public static function handleWhere($params, $where)
    {
        $arrReturn = [];
        if ($where) {
            // 默认查询
            if (isset($where['where']) && !empty($where['where'])) {
                $arrReturn = $where['where'];
                unset($where['where']);
            }

            // 处理其他查询
            if ($where && $params) {
                foreach ($where as $key => $value) {
                    // 判断请求参数中存在定义的键值(存在,不为空)
                    if (isset($params[$key]) && $params[$key] !== '') {
                        // 判断字符串类型处理
                        if (is_string($value)) {
                            $arrReturn[] = [$value, $key, $params[$key]];
                            // 判断数组处理
                        } else if (is_array($value)) {
                            // 处理函数
                            if (isset($value['func']) && function_exists($value['func'])) {
                                $params[$key] = $value['func']($params[$key]);
                            }

                            // 对应字段
                            if (empty($value['field'])) {
                                $value['field'] = $key;
                            }

                            // 链接类型
                            if (empty($value['and'])) {
                                $value['and'] = '=';
                            }

                            $arrReturn[] = [$value['and'], $value['field'], $params[$key]];
                            // 对象处理（匿名函数）
                        } else if (is_object($value)) {
                            $arrReturn[] = $value($params[$key]);
                        }
                    }
                }
            }

            // 添加查询的 AND
            if ($arrReturn) array_unshift($arrReturn, 'and');
        }

        return $arrReturn;
    }

    /**
     * 替换掉特殊字符串
     * @param $str
     * @return mixed
     */
    public static function replace($str)
    {
        return str_replace([
            // 中文特殊字符
            '【', '】', '～', '·', '“', '”', '：', '；','？','{', '}',
            '！', '@', '#', '￥', '%', '……', '&', '*', '（', '）', '——', '+',
            '《', '》','‘', '’', '，', '。', '、', '|', '·',
            '-', '=', '、', '{', '｝',

            // 英文特殊字符串
            '!', '@', '#', '~', '`', '$', '%', '^', '&', '*', '(', ')',
            '_', '+', ',', '.', '/', ';', '|', '[', ']', '"', "'"
        ], '', $str);

    }

    /**
     * model 导出excel
     * @param string $title   excel 标题
     * @param array  $columns 列对应的字段名称 ['id' => 'ID']
     * @param $query \yii\db\Query 查询对象
     * @param array $handleParams 处理参数
     * @param null|object|string $function 处理函数
     */
    public static function  excel($title, $columns, $query, $handleParams = [], $function = null)
    {
        set_time_limit(0);

        $intCount = $query->count();

        // 判断数据是否存在
        if ($intCount > 0) {
            ob_end_clean();
            ob_start();
            $objPHPExcel = new \PHPExcel();
            if ($intCount > 3000) {
                ini_set('memory_limit','1024M');
                $cacheMethod = \PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
                $cacheSettings = array('memoryCacheSize' => '8MB');
                \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
            }
            $objPHPExcel->getProperties()->setCreator("che.com")
                ->setLastModifiedBy("che.com")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Test result file");
            $objPHPExcel->setActiveSheetIndex(0);

            // 获取显示列的信息
            $intLength = count($columns);
            $arrLetter = range('A', 'Z');
            if ($intLength > 26) {
                $arrLetters = array_slice($arrLetter, 0, $intLength - 26);
                if ($arrLetters) foreach ($arrLetters as $value) array_push($arrLetter, 'A' . $value);
            }

            $arrLetter = array_slice($arrLetter, 0, $intLength);

            $keys = array_keys($columns);
            $values = array_values($columns);

            // 确定第一行信息
            foreach ($arrLetter as $key => $value) {
                $objPHPExcel->getActiveSheet()->setCellValue($value . '1', $values[$key]);
            }

            // 写入数据信息
            $intNum = 2;
            foreach ($query->batch(1000) as $array) {
                if (is_object($function)) {
                    $function($array);
                }

                foreach ($array as $value) {
                    // 写入信息数据
                    foreach ($arrLetter as $intKey => $strValue) {
                        $tmpAttribute = $keys[$intKey];
                        if ($tmpAttribute === "serial_number") {
                            $tmpValue = $intNum - 1;
                        } else {
                            $tmpValue = ArrayHelper::getValue($value, $tmpAttribute);
                            if (isset($handleParams[$tmpAttribute])) {
                                $tmpValue = $handleParams[$tmpAttribute]($tmpValue);
                            }
                        }

                        $objPHPExcel->getActiveSheet()->setCellValue($strValue . $intNum, $tmpValue);
                    }

                    $intNum++;
                }
            }

            // 设置sheet 标题信息
            $objPHPExcel->getActiveSheet()->setTitle($title);
            $objPHPExcel->setActiveSheetIndex(0);

            // 设置头信息
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $title . '.xlsx"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');           // Date in the past
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');  // always modified
            header('Cache-Control: cache, must-revalidate');            // HTTP/1.1
            header('Pragma: public');                                   // HTTP/1.0

            // 直接输出文件
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
            \Yii::$app->end();
        }
    }
}