<?php
namespace backend\controllers;

use common\logic\DataDictionary;
use common\models\Clue;
use common\models\OrganizationalStructure;
use common\models\Task;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class UnfinishedTelTaskController extends BaseController
{
    /**
     * 未完成电话任务列表
     */
    public function actionList()
    {
        //权限控制 - 所有
        $this->checkPermission('/yu-qi/index');//???

        $query = Task::find()
            ->select('crm_task.clue_id,crm_task.shop_id,crm_task.task_date,crm_clue.intention_des,crm_clue.intention_level_des,
            crm_clue.customer_name,crm_clue.customer_phone,crm_clue.salesman_name,crm_clue.intention_level_des')
            ->from('crm_task')->leftJoin('crm_clue','crm_task.clue_id = crm_clue.id');


        //组织查询条件
        $arrWhereAnd1 = [];
        $strSo = trim(Yii::$app->request->post('so'));
        if($strSo)
        {
            $arrWhereAnd1 = [
                'or',
                ['like','crm_clue.salesman_name',$strSo],
                ['like','crm_clue.customer_name',$strSo],
                ['like','crm_clue.customer_phone',$strSo],
                ['like','crm_clue.intention_des',$strSo],
                ['like','crm_clue.shop_name',$strSo],
            ];
        }

        $arrWhereAnd2 = [];
        $strSearchTime = trim(Yii::$app->request->post('search_time'));
        //输入了时间搜索条件 且时间条件格式正确
        if($strSearchTime && preg_match('/^\d{4}\-\d{2}\-\d{2} \- \d{4}\-\d{2}\-\d{2}$/', $strSearchTime))
        {
            list($strStartDate, $strEndDate) = explode(' - ', $strSearchTime);
            $arrWhereAnd2 = [
                'and',
                ['>=', 'crm_task.task_date', $strStartDate],
                ['<=', 'crm_task.task_date', $strEndDate],
            ];
        }
        else
        {
            $strStartDate = $strEndDate = '';
        }

        //意向等级查询条件
        $arrWhereAnd3 = [];
        $intention_post = Yii::$app->request->post('intention');
        if($intention_post)
        {
            $arrWhereAnd3 = ['in','crm_clue.intention_level_des',$intention_post];
        }else{
            $intention_post = [];
        }

        //所属门店查询条件
        $arrWhereAnd4 = [];
        $shop_id_post = Yii::$app->request->post('shop_id');
        if($shop_id_post)
        {
            $arrWhereAnd4 = ['in','crm_task.shop_id',$shop_id_post];
        }else{
            $shop_id_post = [];
        }


        $arrWhereAnd1 && $query->where($arrWhereAnd1);
        $arrWhereAnd2 && $query->andWhere($arrWhereAnd2);
        $arrWhereAnd3 && $query->andWhere($arrWhereAnd3);
        $arrWhereAnd4 && $query->andWhere($arrWhereAnd4);

        //获取session
        $user_info = Yii::$app->session->get('userinfo');

        //按照当前用户组织架构id查询下级shop_id
        $org_id = $user_info['org_id'];
        $shop_ids = $this->getChildsIds($org_id);

        //考虑当前用户为店长的情况
        $shop_ids[] = $org_id;
        //按照shop_id查询未完成电话任务列表
        $query->andWhere(['in','crm_task.shop_id',$shop_ids])
            ->andWhere(['=','crm_task.is_finish',1])
            ->andWhere(['<','crm_task.task_date',date('Y-m-d')])
            ->andWhere(['=','crm_task.is_cancel',0])
            ->andWhere(['=','crm_task.task_type',1])
            ->andWhere(['not in','crm_clue.intention_level_id',[7]])
        ;
//        die($query->createCommand()->getRawSql());
        //页面显示
        $intPageSize = 20;
        $countQuery = clone $query;
        $intTotal = $countQuery->count();
        $pages = new Pagination(['totalCount' => $intTotal]);//分页信息
        $pages->setPageSize($intPageSize);
        $arrList = $query->orderBy('task_time desc')
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()->all();

        //取出shop_idlist
        $shop_id_arr = array_column($arrList,'shop_id');
        //取出组织架构信息
        $org_list = OrganizationalStructure::find()->asArray()->all();

        $org_list_new = ArrayHelper::index($org_list,'id');

        //对每个shop取大区信息 公司信息
        $shop_info = [];
        foreach ($shop_id_arr as $shop_id){
            $shop_name = $org_list_new[$shop_id]['name'];
            $area_id = $org_list_new[$shop_id]['pid'];
            $area_name = $org_list_new[$area_id]['name'];
            $company_id = $org_list_new[$area_id]['pid'];
            $company_name = $org_list_new[$company_id]['name'];
            $info = $company_name.'-'.$area_name.'-'.$shop_name;
            $shop_info[$shop_id] = $info;
        }

        foreach ($arrList as $key=>$value){
            $arrList[$key]['shop_name_show'] =$shop_info[$value['shop_id']];
        }

        $obj = new DataDictionary();
        $intention_list = $obj->getDictionaryData('intention');

        //添加查询条件
        foreach ($intention_list as $key=>$value){
            if(in_array($value['name'],$intention_post)){
                $intention_list[$key]['is_check'] = 'checked';
            }else{
                $intention_list[$key]['is_check'] = '';
            }
        }

        $shop_info_check = [];
        foreach ($shop_info as $k=>$v){
            $info = [];
            $info['id'] = $k;
            $info['name'] = $v;
            if(in_array($k,$shop_id_post)){
                $info['is_check'] = 'checked';
            }else{
                $info['is_check'] = '';
            }
            $shop_info_check[] = $info;
        }

        return $this->render('list', [
            'list' => $arrList,
            'count' => $intTotal,
            'objPage' => $pages,
            'strStartDate' => $strStartDate,
            'strEndDate' => $strEndDate,
            'so' => $strSo,
            'intention_list' => $intention_list,
            'shop_info_check' => $shop_info_check
        ]);
    }


    //定义组织结构信息数组  按照id作为键值
    private $cids = array();

    /**
     * 根据id信息查询所有子级id列表
     * @param $id_str        当前层级id列表  多个用英文逗号分隔
     * @return array         子级id数组
     */
    protected function getChildsIds($id_str){
        $organizational_info = OrganizationalStructure::find()->select('id,pid')->asArray()->all();
        $organizational_list = array();
        foreach ($organizational_info as $item){
            $organizational_list[$item['pid']][] = $item;
        }
        $this->cids = $organizational_list;

        $id_arr = explode(',',$id_str);

        return $this->getCids($id_arr);
    }

    /**使用递归查询子级id
     * @param $id_arr      当前层级id数组
     * @return array
     */
    private function getCids($id_arr){

        $cids = $this->cids;

        static $cid_list = array();
        $cid_arr = array();

        foreach ($id_arr as $key=>$value){

            if(!empty($cids[$value])){
                $cid_arr = array_column($cids[$value],'id');
                $cid_list = array_merge($cid_list,$cid_arr);
                $this->getCids($cid_arr);
            }
        }
        return $cid_list;
    }
}
?>