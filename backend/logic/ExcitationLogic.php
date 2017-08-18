<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/4/11
 * Time: 13:54
 */

namespace backend\logic;


use common\logic\AnnouncementLogic;
use common\logic\CompanyUserCenter;
use common\models\Excitation;
use common\models\ExcitationLog;
use common\models\ExcitationShop;
use common\models\OrganizationalStructure;
use common\server\Logic;
use yii\db\Exception;
use yii\helpers\ArrayHelper;


/**
 * 激励逻辑
 *
 * Class ExcitationLogic
 * @package backend\logic
 */
class ExcitationLogic extends Logic
{
    /**
     * 新增激励
     *
     * @param $data
     * @param $userInfo
     * @return bool
     * @throws Exception
     */
    public function add($data, $userInfo)
    {
        $db = \Yii::$app->db;
        $transaction = $db->beginTransaction();
        $insertData = [];
        $shopIds = [];
        if ($data['type'] == 'shop') {
            $shopIds = $data['shop_ids'];
        }
        if ($data['type'] == 'all') {
           $shopIds = ArrayHelper::getColumn(OrganizationalStructure::find()->where([
               'level' => 30,
               'is_delete' => 0
           ])->all(), 'id');
        }
        if ($data['type'] == 'area') {
            $shopIds = ArrayHelper::getColumn(OrganizationalStructure::find()->where([
                'level' => 30,
                'is_delete' => 0
            ])->andWhere([
                'in', 'pid', $data['shop_ids']
            ])->all(), 'id');
        }
        if ($data['type'] == 'company') {
            $area = ArrayHelper::getColumn(OrganizationalStructure::find()->where([
                'level' => 20,
                'is_delete' => 0
            ])->andWhere([
                'in', 'pid', $data['shop_ids']
            ])->all(), 'id');
            $shopIds = ArrayHelper::getColumn(OrganizationalStructure::find()->where([
                'level' => 30,
                'is_delete' => 0
            ])->andWhere([
                'in', 'pid', $area
            ])->all(), 'id');;
        }

        $announcement_send_id_arr = array();
        foreach ($shopIds as $v) {
            if (in_array($v, $this->getActiveShopId())) continue;

            //为防止给公司或大区添加激励时所有门店发布公告，只对添加激励的门店发布公告
            $announcement_send_id_arr[] = $v;

            $shop = OrganizationalStructure::findOne($v);
            $companyId = $this->getCompanyId($shop->pid);
            $insertData[] = [
                $shop->id, $shop->pid, $companyId
            ];
        }
        if (empty($insertData)) {
            return false;
        }
        try {
            $model = new Excitation();
            $model->create_person = $userInfo['name'];
            $model->create_person_id = $userInfo['id'];
            $model->name = $data['name'];
            $model->start_time = date('Y-m-d H:i:s');
            $model->money = $data['money'];
            $model->status = 0;
            $model->active_shop_ids = '';
            $model->clue_price = $data['clue_price'] ?: 0;
            $model->clue_to_intention_price = $data['clue_to_intention_price'] ?: 0;
            $model->new_intention_price = $data['new_intention_price'] ?: 0;
            $model->finish_phone_task_price = $data['finish_phone_task_price'] ?: 0;
            $model->to_shop_price = $data['to_shop_price'] ?: 0;
            $model->to_home_price = $data['to_home_price'] ?: 0;
            $model->dingche_price = $data['dingche_price'] ?: 0;
            $model->jiaoche_price = $data['jiaoche_price'] ?: 0;
            if (!$model->save()) {
                new Exception('新增失败', $model->errors);
            }
            foreach ($insertData as $k => $v) {
                array_push($v, $model->id);
                $insertData[$k] = $v;
            }
            $db->createCommand()->batchInsert('crm_excitation_shop',[
                'shop_id', 'area_id', 'company_id', 'e_id'
            ], $insertData)->execute();
            $transaction->commit();

            $this->createAnnouncement($data,$announcement_send_id_arr,$userInfo);

            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }


    /**
     * 正在进行中的有激励的shop_id
     *
     * @return array
     */
    public function getActiveShopId()
    {
        $eIds = ArrayHelper::getColumn(Excitation::find()->where([
            'status' => 0,
        ])->all(), 'id');
        $excitationShop = ExcitationShop::find()->where(['in','e_id', $eIds])->all();
        return ArrayHelper::getColumn($excitationShop, 'shop_id');
    }

    /**
     * 获取门店列表
     *
     * @param $shopId
     * @return mixed|string
     */
    public function getShopLevel($shopId)
    {

        $self = OrganizationalStructure::findOne($shopId);
        $area = OrganizationalStructure::findOne($self->pid);
        //$company = OrganizationalStructure::findOne($area->pid);
        $path = [
            'area' => $area->name,
            'shop' => $self->name
        ];
        return $path;
    }

    /**
     * 根据大区ID获取公司ID
     *
     * @param $areaId
     * @return string
     */
    public function getCompanyId($areaId)
    {
        $obj = OrganizationalStructure::findOne($areaId);
        return OrganizationalStructure::findOne($obj->pid)->id;
    }

    public function getTotalMoney($eId)
    {
        return ExcitationLog::find()->where([
            'e_id' => $eId
        ])->sum('e_money') ? : '0.00';
    }

    public function shopOptions()
    {
        $organizational = OrganizationalStructure::find()->where(['is_delete' => 0])->asArray()->all();
        $data = [];
        foreach ($organizational as $v) {
            $data[$v['level']][] = $v;
        }
        $all = isset($data['10']) ? $data['10'] : [];
        $company = isset($data['15']) ? $data['15'] : [];
        $area = isset($data['20']) ? $data['20'] : [];
        $shop = isset($data['30']) ? $data['30'] : [];

        //店铺
        $areaMap = ArrayHelper::map($area, 'id', 'name');
        $shopList = [];
        foreach ($shop as $k => $v) {
            if (in_array($v['id'], $this->getActiveShopId()))
                continue;
            $shopList[$areaMap[$v['pid']]][] = [
                'id' => $v['id'],
                'name' => $v['name']
            ];
        }

        //地区
        $companyArr = ArrayHelper::map($company, 'id', 'name');
        $areaList = [];
        foreach ($area as $k => $v) {
            if(empty($shopList[$v['name']])) continue;
            $areaList[$companyArr[$v['pid']]][] = [
                'id' => $v['id'],
                'name' => $v['name']
            ];
        }

        $companyList = [];
        //没有门店的公司过滤掉
        foreach ($company as $k => $v) {
            if (empty($areaList[$v['name']])) continue;
            $companyList[] = [
                'id' => $v['id'],
                'name' => $v['name']
            ];
        }
        return compact('all', 'companyList', 'areaList', 'shopList');
    }

    /**
     * 发布公告
     * @param $data
     * @param $userInfo
     */
    public function createAnnouncement($data,$id_arr,$userInfo){

        $title = $data['name'];
        $options = $data['type'];
//        $send_person_name = $userInfo['name'];
        $send_person_name = '总部';

        //拼接公告内容
        $str = '';
        if(!empty($data['clue_price'])){
            $str .= '新增线索  奖励  ¥ '.number_format($data['clue_price'],2)."\n";
        }
        if(!empty($data['clue_to_intention_price'])){
            $str .= '线索转化  奖励  ¥ '.number_format($data['clue_to_intention_price'],2)."\n";
        }
        if(!empty($data['new_intention_price'])){
            $str .= '新增意向客户  奖励  ¥ '.number_format($data['new_intention_price'],2)."\n";
        }
        if(!empty($data['finish_phone_task_price'])){
            $str .= '完成电话任务  奖励  ¥ '.number_format($data['finish_phone_task_price'],2)."\n";
        }
        if(!empty($data['to_shop_price'])){
            $str .= '到店交谈  奖励  ¥ '.number_format($data['to_shop_price'],2)."\n";
        }
        if(!empty($data['to_home_price'])){
            $str .= '上门交谈  奖励  ¥ '.number_format($data['to_home_price'],2)."\n";
        }
        if(!empty($data['dingche_price'])){
            $str .= '客户订车  奖励  ¥ '.number_format($data['dingche_price'],2)."\n";
        }
        if(!empty($data['jiaoche_price'])){
            $str .= '交车  奖励  ¥ '.number_format($data['jiaoche_price'],2)."\n";
        }

        $content = $str;

        $logic = new AnnouncementLogic();
        //发布公告
        $logic->executeAnnouncementSend($userInfo,$options,$id_arr,$title,$send_person_name,$content);
    }
}