<?php
/**
 * Created by PhpStorm.
 * User:
 * Date: 2017/3/17
 * Time: 17:04
 */

namespace frontend\modules\glsb\logic;


use common\server\Logic;
use yii\data\Pagination;

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
     * 处理列表返回数据格式
     * @param $list
     * @param int $pageCount
     * @param int $currentPage
     * @return array
     */
    public function excute_list($list,$totalCount ,$perPage ,$pageCount = 1,$currentPage = 1){

        if($list){
            $data['models'] = $list;
            $msg = '获取成功';
        }else{
            $data['models'] = array();
            $msg = '数据为空';
            $pageCount = 0;
            $currentPage = 0;
        }

        $data['pages'] = [
            'totalCount' => $totalCount,
            'pageCount' => $pageCount,
            'currentPage' => $currentPage,
            'perPage' => $perPage,
        ];

        return ['msg' => $msg,'data' => $data];
    }
}