<?php
/**
 * 重置密码逻辑层
 * 作    者：lzx
 * 功    能：用户信息逻辑层
 * 修改日期：2017-3-14
 */
namespace frontend\modules\glsb\controllers;

use Yii;
//use frontend\modules\glsb\models\User;
use common\models\User;
use common\logic\CompanyUserCenter;

/**
 * ResetPwdController implements the CRUD actions for User model.
 */
class ResetPwdController extends BaseController
{
    /**
     * 修改密码
     * @return array
     */
    public function actionModifyPassword()
    {
        //接收公共参数
        $r = json_decode(Yii::$app->request->post('r'),true);
        $p = json_decode(Yii::$app->request->post('p'),true);

        if(empty($p['newpassword']) || empty($r['access_token']) || empty($p['oldpassword'])){
            $this->echoData(400,'参数不全');
        }
        $newpassword = $p['newpassword'];

        $oldpassword = $p['oldpassword'];
        $access_token = $r['access_token'];

        $companyUserCenter = new CompanyUserCenter;

        $rtn = $companyUserCenter->curlChangePassword($newpassword, $access_token,$oldpassword);

        if($rtn['code'] == 0){
            $this->echoData(200,'修改成功');
        }elseif ($rtn['code'] == 1){
//            $this->echoData(400,$rtn['msg']);
            $this->echoData(400,'修改失败');
        }
    }

    /**
     * 发送短信验证码
     */
    public function actionGetPhoneCode()
    {
        $p = json_decode(Yii::$app->request->post('p'),true);

        if(empty($p['phone'])){
            $this->echoData(400,'参数不全');
        }

        $phone = $p['phone'];

        $companyUserCenter = new CompanyUserCenter;

        $rtn = $companyUserCenter->curlSendPhoneCode($phone);

        if($rtn['code'] == 0){
            $this->echoData(200,'发送成功');
        }elseif ($rtn['code'] == 1){
//            $this->echoData(400,$rtn['msg']);
            $this->echoData(400,'发送失败');
        }
    }

    /**
     * 重置密码
     */
    public function actionResetPwd(){
        //接收私有参数
        $p = json_decode(Yii::$app->request->post('p'),true);

        if(empty($p['phone']) || empty($p['newpassword']) || empty($p['code'])){
            $this->echoData(400,'参数不全');
        }

        $phone = $p['phone'];
        $newpassword = $p['newpassword'];
        $code = $p['code'];

        $companyUserCenter = new CompanyUserCenter;

        $rtn = $companyUserCenter->curlChangePasswordByPhone($phone,$code,$newpassword);

        if($rtn['code'] == 0){
            $this->echoData(200,'重置成功');
        }elseif ($rtn['code'] == 1){
//            $this->echoData(400,$rtn['msg']);
            $this->echoData(400,'重置失败');
        }
    }
}
