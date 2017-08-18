<?php

namespace backend\models;

use moonland\phpexcel\Excel;


class UploadForm
{


    /**
     * @inheritdoc
     */
    public function upload($files)
    {


        if ($files['file']['error'] > 0) {
            $error = '';
            switch ($files['file']['error']) {
                case 1:
                    $error = '文件大小超过服务器限制';
                    break;
                case 2:
                    $error = '文件太大！';
                    break;
                case 3:
                    $error = '文件只加载了一部分！';
                    break;
                case 4:
                    $error = '文件加载失败！';
                    break;
            }
            $this->check_file($error);

        }

        $fileName = $_FILES["file"]["name"];
        $type     = strstr($fileName, '.');  // 文件后缀


        if ($type != ".xls" && $type != ".xlsx") {
            return $this->check_file('上传格式不正确！');
        }


        $today = date("YmdHis");

        $upfile = $today . $type;

        $path = '../web/uploads';
        if (! file_exists ( $path )) {
            mkdir ("$path", 0777, true );
        }

        $path = '../web/uploads/clue';
        if (! file_exists ( $path )) {
            mkdir ("$path", 0777, true );
        }

        $path = '../web/uploads/errorclue';

        if (! file_exists ( $path )) {
            mkdir ("$path", 0777, true );
        }

        if (!move_uploaded_file($_FILES['file']['tmp_name'], '../web/uploads/clue/' . $upfile)) {
            return $this->check_file('上传失败！');

        } else {
            return $this->check_file($upfile, 'ok');
        }


    }

    /**
     * @param $mes 错误信息
     * @param string $suc 状态
     * @return mixed
     */
    private function check_file($mes, $suc = 'no')
    {
        $data['suc'] = $suc;
        $data['mes'] = $mes;
        return $data;
    }

    /**
     * 检测exce文件 是否符合规则
     * @param array $excelName 所要验证的内容
     * @param array $file  上传的文件
     * @return bool
     */
    public function is_check_file_data($excelName,$file)
    {
        //检测导出的excel的头格式是否正确
        $ischeck = false;

        $fileName = $_FILES["file"]["name"];
        $type     = strstr($fileName, '.');  // 文件后缀


        if ($type != ".xls" && $type != ".xlsx") {
             return 'no';
        }

        $filePath = $file['file']['tmp_name']; // 要读取的文件的路径

        //导出excel内容
        $excel = Excel::import($filePath, [
            'setFirstRecordAsKeys' => false,
        ]);

        foreach ($excel[2] as $k => $v) {
            if ($k == 'K'){
                break;
            }
            if ($v != $excelName[$k]) {
                $ischeck = true;
                break;
            }
        }
        if ($ischeck) return 'no';

        //更改下标从0开始
        $excel = array_values($excel);

        return $this->check_file($excel);


    }

    public function set_error_info_excel($error_data) {

        $file_name = date('YmdHis').'error.xlsx';

        
        //生成错误excel
         \common\server\Excel::export([
            'asAttachment' => false,
            'savePath' => '../web/uploads/errorclue/',
            'models' => $error_data,
            'columns' => ['A','B','C','D','E','F','G','H', 'I', 'J', 'K'],
            'fileName' => $file_name,
            'headers' => [
                'A' => '客户姓名', 'B' => '手机号码', 'C' => '门店',
                'D' => '渠道来源', 'E' => '信息来源', 'F' => '省',
                'G' => '市', 'H' => '区', 'I' => '意向车型',
                'J' => '说明', 'K' => '错误原因'
            ],

        ]);

        return $file_name;
    }

}
