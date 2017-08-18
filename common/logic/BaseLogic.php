<?php
namespace common\logic;

use common\models\User;
use common\models\OrganizationalStructure;

class BaseLogic
{
    /**
     * 根据组织架构id查询当前所属层级及子级列表
     * @param $info_owner_id
     */
    public function getOrganizationalInfoAndChild($info_owner_id){
        //根据info_owner_id查询所属层级 判断查询条件

//        $level  = OrganizationalStructure::find()->where(['=','id',$info_owner_id])->one()->level;
        $organizational_structure  = OrganizationalStructure::find()->select('id,name,level')->where(['=','id',$info_owner_id])->asArray()->one();
//        var_dump($organizational_structure);die;
        $level = $organizational_structure['level'];
        if($level == 0){
            $this_level_field = 'all';
            $next_level_field = 'company_id';
        }elseif($level == 1){
            $this_level_field = 'company_id';
            $next_level_field = 'area_id';
        }elseif ($level == 2){
            $this_level_field = 'area_id';
            $next_level_field = 'shop_id';
        }elseif($level == 3){
            $this_level_field = 'shop_id';
            $next_level_field = 'salesman_id';
        }else{
            return false;
        }

        //查询下一层级列表
        if($level == 3){
            $child_list = User::find()->select('id,name')->where(['=','shop_id',$info_owner_id])->andWhere(['=','is_delete',0])->asArray()->all();
        }else{
            $child_list = OrganizationalStructure::find()->select('id,name')->where(['=','pid',$info_owner_id])->andWhere(['=','is_delete',0])->asArray()->all();
        }

        $child_list_new = array();
        foreach ($child_list as $value){

            $child_list_new[$value['id']] = $value;
        }

        $data['this_level_field'] = $this_level_field;
        $data['next_level_field'] = $next_level_field;
        $data['child_list'] = $child_list_new;
        $data['organizational_structure'] = $organizational_structure;

        return $data;
    }



    //定义组织结构信息数组  按照id作为键值
    private $pids = array();

    /**
     * 根据id信息查询所有父级id列表
     * @param $id_str        当前层级id列表  多个用英文逗号分隔
     * @return array         父级id数组
     */
    public function getParentsIds($id_str){
        //查询组织结构信息并处理
        $organizational_info = OrganizationalStructure::find()
            ->select('id,pid')
            ->asArray()
            ->all();
        $organizational_list = array();
        foreach ($organizational_info as $item){
            $organizational_list[$item['id']] = $item;
        }
        $this->pids = $organizational_list;
        $id_arr = explode(',',$id_str);

        //调用递归函数查询
        return $this->getPids($id_arr);
    }

    /**使用递归查询父级id
     * @param $id_arr      当前层级id数组
     * @return array
     */
    private function getPids($id_arr){

        //接收组织结构信息
        $pids = $this->pids;

        //定义递归变量
        static $pid_list = array();

        //定义每次查询临时保存数据变量
        $pid_arr = array();

        //如果当前id在组织结构中存在  则保存pid
        foreach ($id_arr as $item){
            if(!empty($pids[$item])){
                $pid_arr[] = $pids[$item]['pid'];
            }
        }

        //递归退出条件  如果到顶级该数组为空
        if(!empty($pid_arr)){
            $pid_list = array_merge($pid_list,$pid_arr);
            $this->getPids($pid_arr);
        }

        //去除id等于
        foreach ($pid_list as $key=>$value){
            if($value == 0){
                unset($pid_list[$key]);
            }
        }

        return array_unique($pid_list);
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