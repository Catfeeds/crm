<?php
/**
 * 功    能：生成会员

 */
namespace frontend\modules\thirdpartyapi\controllers;

use common\fixtures\User;
use yii;
use yii\rest\Controller;
use frontend\modules\sales\logic\MemberLogic;
use common\models\Customer;

class MemberController extends Controller
{
    //全部会员注册
    public function actionRegisters() {
        set_time_limit(0);
        $sql = "select cu.phone from crm_customer cu join crm_clue c where c.customer_id=cu.id and c.status=1 and LENGTH(cu.phone)=11 order by cu.id desc";
        $customer = Yii::$app->db->createCommand($sql)->queryAll();
        $member = new MemberLogic();
        foreach ($customer as $v) {
            $url    = 'inside/user/reg';
            //没有检测到会员id 注册会员
            $data['phone'] = $v['phone'];
            $jsonData = $member->memberHttpPostParames($url,$data);
            if ($jsonData['err_code'] == 0){
                $sql = "update crm_customer set member_id={$jsonData['data']['uid']} where phone={$v['phone']}";
                Yii::$app->db->createCommand($sql)->execute();
            }

        }

    }

    /**
     * 检测会员信息
     */
    public function actionCheckMember() {

        $member = new MemberLogic();
        $url    = 'inside/user/reg';
        //没有检测到会员id 注册会员
        $data['phone'] = $_POST['phone'];
        $jsonData = $member->memberHttpPostParames($url,$data);
        $this->dump($jsonData);

    }



    public function dump($data) {
        echo "<pre>";
        print_r($data);
        exit;
    }
}
