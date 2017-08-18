<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/9
 * Time: 19:06
 */

namespace common\server;


class Excel extends \moonland\phpexcel\Excel
{
    /**
     * saving the xls file to download or to path
     */
    public function writeFile($sheet)
    {
        if (!isset($this->format))
            $this->format = 'Excel2007';
        $objectwriter = \PHPExcel_IOFactory::createWriter($sheet, $this->format);
        $path = 'php://output';
        if (isset($this->savePath) && $this->savePath != null) {
            $path = $this->savePath . '/' . $this->getFileName();
        }
        $objectwriter->save($path);
    }

}