<?php

namespace backend\controllers;

use common\logic\ActiveClueLogic;
use common\logic\AssignClueLogic;
use common\logic\ClueValidate;
use common\logic\DataDictionary;
use common\logic\Excel;
use common\logic\NoticeTemplet;
use common\logic\PhoneLetter;
use common\models\Clue;
use common\models\Customer;
use common\models\InputType;
use common\models\Source;
use common\models\Task;
use common\models\User;
use common\models\Yuqi;
use Yii;
use common\models\AgeGroup;
use backend\models\AgeGroupSearch;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\Pagination;
use common\models\OrganizationalStructure;

/**
 * AgeGroupController implements the CRUD actions for AgeGroup model.
 */
class AssignClueController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'update-or-create' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * 待分配客户页面
     * Lists all AgeGroup models.
     * @return mixed
     */
    public function actionUnassignList()
    {
        //权限控制 - 门店
        $this->checkPermission('/assign-clue/unassign-list', 3);

        //获取session
        $session = Yii::$app->getSession();
//        $user_info = $session['userinfo'];
        $get = Yii::$app->request->get();
        //获取当前用户id、shop_id
        if(isset($get['shop_id']) && !empty($get['shop_id']) && in_array($get['shop_id'], $session['userinfo']['permisson_org_ids']))
        {
            $shop_id = $get['shop_id'];
        }
        else
        {
            $get['shop_id'] = $shop_id = $this->getDefaultShopId();
        }
        
        //获取门店 - 该界面是门店数据，等级传入固定的30 - 门店
        $objSelectDataLogic = new \common\logic\JsSelectDataLogic();
        $arrSelectorgList = $objSelectDataLogic->getSelectOrgNew($session['userinfo']['permisson_org_ids'], 30, true);
        
        //查询未分配线索
        $model = new Clue();
        $query = $model->find()->select('id,create_time,customer_name,customer_phone,clue_input_type,intention_des,clue_source')
            ->where(['=','shop_id',$shop_id])
            ->andWhere(['=','is_assign',0]);

        //计算总数
        $intTotal = $query->count();
        $pagination = new Pagination([
            'defaultPageSize' => 20,
            'totalCount' => $intTotal,
        ]);

        $list = $query->orderBy('create_time DESC')
            ->offset($pagination->offset)->limit($pagination->limit)
            ->asArray()->all();

        //处理线索来源列表
        $source_list = Source::find()->select('id,name')->asArray()->all();
        $source_list_new = array();
        foreach ($source_list as $item){
            $source_list_new[$item['id']] = $item;
        }

        //处理渠道来源列表
        $input_type_list = InputType::find()->select('id,name')->asArray()->all();
        $input_type_list_new = array();
        foreach ($input_type_list as $item){
            $input_type_list_new[$item['id']] = $item;
        }

        //取出线索id列表
        $clue_id_arr = array_column($list,'id');
        //查询逾期情况
        $overdue_list = Yuqi::find()->select('clue_id,end_time,')->where(['in','clue_id',$clue_id_arr])->asArray()->all();
        //处理数组
        $overdue_list_new = ArrayHelper::index($overdue_list,'clue_id');

        //添加线索来源渠道来源信息
        $time = time();  //当前时间
        foreach ($list as $key=>$item){
            $list[$key]['clue_source_name'] = isset($source_list_new[$item['clue_source']]['name']) ? $source_list_new[$item['clue_source']]['name']: '--';
            $list[$key]['clue_input_type_name'] = isset($input_type_list_new[$item['clue_input_type']]['name']) ? $input_type_list_new[$item['clue_input_type']]['name'] : '--';
            if(empty($overdue_list_new[$item['id']])){
                $list[$key]['overdue'] = '--';
            }else{
                $time_overdue = strtotime($overdue_list_new[$item['id']]['end_time']);
                if($time > $time_overdue){  //当前时间大于最后时间
                    $list[$key]['overdue'] = '已逾期';
                    //时间差
                    $time_differ =  $time - $time_overdue;
                    $d = floor($time_differ/3600/24);
                    $h = floor(($time_differ%(3600*24))/3600);  //%取余
                    $m = floor(($time_differ%(3600*24))%3600/60);
                    $list[$key]['overdue'] = '已逾期'.$d.'天'.$h.'小时'.$m.'分';
                }else{
                    //时间差
                    $time_differ = $time_overdue - $time;
                    $d = floor($time_differ/3600/24);
                    $h = floor(($time_differ%(3600*24))/3600);  //%取余
                    $m = floor(($time_differ%(3600*24))%3600/60);
                    $list[$key]['overdue'] = $d.'天'.$h.'小时'.$m.'分';
                }
            }
        }

        //查询本店员工
        $objCompanyUser = new \common\logic\CompanyUserCenter();
        $user_list = $objCompanyUser->getShopSales($shop_id);

        $list = array_values($list);

        return $this->render('index', [
            'list' => $list,
            'user_list' => $user_list,
            'pagination' => $pagination,
            'selectOrgJson' => json_encode($arrSelectorgList),
            'get' => $get,
        ]);
    }


    /**
     * 线索分配到同一顾问
     * @param $clue_id_list
     * @param $salesman_id
     * @return bool
     */
    public function actionAssign()
    {
        //获取session
        $user_info = Yii::$app->session->get('userinfo');
        //获取当前用户id、shop_id
        $shop_id = $this->getDefaultShopId();//
        $who_assign_id = $user_info['id'];

        //接收参数
        $clue_id_arr = Yii::$app->request->post('id_arr');
        $salesman_id = Yii::$app->request->post('salesman_id');

        //循环分配每个线索
        $assignClueLogic = new AssignClueLogic();
        foreach ($clue_id_arr as $clue_id){

            /**
             * edited by liujx 2017-06-27 start:
             *
             * 如果该客户之前存在没有战败线索信息，并且线索状态为意向或者订车 不允许激活现在的线索
             */

            $clue = Clue::findOne($clue_id);
            if (!$clue) {
                return false;
            }

            $isExists = ClueValidate::validateExists([
                'and',
                ['=', 'customer_id', $clue['customer_id']],
                ['!=', 'id', $clue['id']],
                ['=', 'is_fail', 0],
                ['in', 'status', [1, 2]]
            ]);

            // 存在线索
            if ($isExists) {
                return false;
            }

            // end;

            $rtn = $assignClueLogic->assignClue($shop_id,$clue_id,$salesman_id,$who_assign_id);
            if(!$rtn){
                return false;
            }
        }

        //推送消息
        $count = count($clue_id_arr);

        //查询顾问姓名
        $salesman = User::findOne($salesman_id);

        //查询分配人姓名
        $who_assign_name = $user_info['name'];

        $phone_letter = new PhoneLetter();
        //发送短信和语音短信
        if($count == 1){
            //查询线索信息
            $clue_info = Clue::findOne($clue_id_arr[0]);

            //渠道id
            $input_type = $clue_info->clue_input_type;
            $input_type_info = InputType::findOne($input_type);
            $input_type_name = $input_type_info->name;

            //店长分配线索给销售时发送短信通知 - 单条
            $phone_letter->shopownerAssignClueToSales($salesman->phone, $salesman->name, $who_assign_name, 1, $input_type_name);
            //店长分配线索给顾问语音短信  单条
            $phone_letter->shopOwnerAssignClueToSalesman($salesman->phone, $salesman->name, $who_assign_name,1,$input_type_name);
        }else{
            //店长分配线索给销售时发送短信通知 - 多条
            $phone_letter->shopownerAssignClues($salesman->phone, $salesman->name, $who_assign_name, $count);

            //店长分配线索给顾问语音短信   多条
            $phone_letter->shopOwnerAssignCluesToSalesman($salesman->phone, $salesman->name, $who_assign_name,$count);
        }

        $noticeTemplet = new NoticeTemplet();

        //店长分配线索
        $noticeTemplet->assignClueSendNotice($who_assign_id, $salesman_id ,$count);
        return true;
    }

    /**
     * Finds the AgeGroup model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return AgeGroup the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AgeGroup::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
