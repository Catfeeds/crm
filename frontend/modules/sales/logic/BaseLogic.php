<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/3/10
 * Time: 17:04
 */

namespace frontend\modules\sales\logic;


use common\server\Logic;
use Yii;
use yii\data\Pagination;
use yii\helpers\FileHelper;

class BaseLogic extends Logic
{
    /**
     * 分页数据处理
     *
     * @param Pagination $pagination
     * @return array
     */
    public function pageFix($pagination)
    {
        return [
            'totalCount' => intval($pagination->totalCount),
            'pageCount' => intval($pagination->getPageCount()),
            'currentPage' => intval($pagination->getPage() + 1),
            'perPage' => intval($pagination->getPageSize()),
        ];
    }

    /**
     * 文件保存
     * @param $files
     * @param $key
     * @return string
     */
    public function saveFiles($files, $key, $aacToMp3 = false)
    {
        $datas = date('Y/m/d');
        $rootPath = \Yii::getAlias("@frontend/web/upload/clue/$key/".$datas);
        if (!is_dir($rootPath)) {
            @FileHelper::createDirectory($rootPath, 0777);
        }
        if(is_array($files['tmp_name'])){
            $data = [];
            foreach ($files['tmp_name'] as $k => $v) {
                $saveName = date('YmdHis_') . rand(10000, 99999) . '.' . pathinfo($files['name'][$k], PATHINFO_EXTENSION);
                @move_uploaded_file($v, $rootPath .'/'. $saveName);
                $data[] = 'http://'.$_SERVER['HTTP_HOST'].'/upload/clue/' .$key . '/' .$datas . '/' . $saveName;
            }
            return implode(',', $data);
        } else {
            $saveName = date('YmdHis_') . rand(10000, 99999) . '.' . pathinfo($files['name'], PATHINFO_EXTENSION);
            @move_uploaded_file($files['tmp_name'], $rootPath . '/' . $saveName);
            //将aac格式(安卓只是后缀改为MP3了实际还是aac)的音频文件转换为MP3格式的 - add by 王雕
            $strLog = '[' . date('Y-m-d H:i:s') . ']:上传音频：' . $rootPath .'/'. $files['name'] . "\n";
            if($aacToMp3)//商谈时上传语音的时候将aac格式转码成MP3格式的
            {
                $accFile = $rootPath .'/'. $saveName;
                //保存文件的后缀名强制为MP3格式的
                $saveName = str_replace(pathinfo($saveName, PATHINFO_EXTENSION), 'mp3', $saveName);
                $mp3File = $rootPath . '/' . $saveName;
                $strLog .= "mp3File: $mp3File \n";
                
                //现在上传的文件有可能是aac  也有可能是amr格式的
                if(substr($accFile, -3) == 'aac')
                {
                    $strLog .= "\n  faad -o - {$accFile} | lame --preset extreme - {$mp3File} \n";
                    shell_exec("faad -o - {$accFile} | lame --preset extreme - {$mp3File}");
                }
                else //amr
                {
                    $strLog .= "\n  ffmpeg -i {$accFile} {$mp3File} \n";
                    shell_exec("ffmpeg -i {$accFile} {$mp3File}");
                }
            }
            $strLogFile = Yii::$app->getRuntimePath() . '/logs/aac_to_mp3.log';
            file_put_contents($strLogFile, $strLog , FILE_APPEND);
            return 'http://'.$_SERVER['HTTP_HOST'].'/upload/clue/' .$key . '/' . $datas . '/' . $saveName;
        }

    }

    /**
     * 检查必填字段
     *
     * @param $data
     * @param $checkData
     * @return bool
     */
    public function checkRequire($data, $checkData)
    {
        foreach ($checkData as $v) {
            if (!isset($data[$v]) || !$data[$v]) {
                return false;
            }
            continue;
        }
        return true;
    }

    /**
     * 获取字段
     *
     * @param $data
     * @param $attributeData
     * @return array|bool
     */
    public function getAttributeData($data, $attributeData)
    {
        $return = [];
        foreach ($attributeData as $v) {
            if (isset($data[$v]) && $data[$v]) {
                $return[$v] = $data[$v];
            } else{
                continue;
            }
        }
        return $return;
    }

    public function dump($arr) {
        echo "<pre>";
        print_r($arr);
        exit;
    }
}