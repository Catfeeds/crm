<?php
/**
 * 用户信息逻辑层
 * 作    者：lzx
 * 功    能：用户信息逻辑层
 * 修改日期：2017-3-14
 */
namespace frontend\modules\glsb\controllers;

use Yii;
//use frontend\modules\glsb\models\User;
use common\models\User;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use common\logic\CompanyUserCenter;
/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends AuthController
{
    /**
     * @inheritdoc

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }*/

    /**
     * 修改个人信息
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate()
    {
        //获取用户
        $user = Yii::$app->getUser()->identity;

        //接收私有参数
        $p = json_decode(Yii::$app->request->post('p'),true);
        //获取用户信息对象
        $userid = $user->id;

        $model = $this->findModel($userid);

        /**
         *头像客户端保存
        //修改头像
        if(!empty($_FILES['avatar'])){
            $oldavatar = $model->avatar;

            //检查文件信息
            if($this->my_checkfile($_FILES['avatar'])){

                $info = $this->my_checkfile($_FILES['avatar']);
                if($info['code'] == 200){
                    $newavatar = $info['filename'];
                }else{
                    $this->echoData(400,$info['msg']);
                }
            }else{
                $this->echoData(400,'操作失败');
            }

            //保存头像
            if (is_uploaded_file($_FILES['avatar']['tmp_name'])) {

                if (!file_exists("upload/user/{$userid}/avatar")){mkdir("upload/user/{$userid}/avatar",0777,true);}

                if (!move_uploaded_file($_FILES['avatar']['tmp_name'], 'upload/user/'.$userid.'/avatar/' . $newavatar)) {
                    $this->echoData(400,'操作失败');
                } else {
                    //删除原文件
                    if(strpos($oldavatar,\Yii::$app->request->hostInfo) !== false){
                        if(is_file(str_replace(\Yii::$app->request->hostInfo.'/','',$oldavatar))){
                            unlink(str_replace(\Yii::$app->request->hostInfo.'/','',$oldavatar));
                        }
                    }
                    //保存头像地址
//                    $model->avatar = 'http://'.$_SERVER['HTTP_HOST'].'/upload/user/'.$userid.'/avatar/' . $newavatar;
                    $model->avatar = \Yii::$app->request->hostInfo.'/upload/user/'.$userid.'/avatar/' . $newavatar;
                }
            }
        }
        */

        //指定可修改属性
        if(!empty($p['nickname'])){
            $model->nickname = $p['nickname'];
        }
//        if(!empty($p['name'])){
//            $model->name = $p['name'];
//        }
        if(!empty($p['birthday'])){
            $model->birthday = $p['birthday'];
        }
        if(!empty($p['profession'])){
            $model->profession = $p['profession'];
        }
        if(!empty($p['email'])){
            $model->email = $p['email'];
        }

        if(!empty($p['sex'])){
            if($p['sex'] == '男'){
                $model->sex = 1;
            }elseif($p['sex'] == '女'){
                $model->sex = 2;
            }
        }

        //保存修改
        if($model->save()){
            if($model->sex == 1){
                $sex = '男';
            }elseif($model->sex == 2){
                $sex = '女';
            }else{
                $sex = '保密';
            }
            $data =  [
                'userid' => (int)$model->id,
                'avatar' => (string)$model->avatar,
                'nickname' => (string)$model->nickname,
                'name' => (string)$model->name,
                'phone' => (string)$model->phone,
                'sex' => (string)$sex,
                'birthday' => (string)$model->birthday,
                'profession' => (string)$model->profession,
                'email' => (string)$model->email,
//                'access_token' => (string)$model->access_token,
//                'organizational_structure_level' => (int)$user->organizational_structure_level
            ];

            $this->echoData(200,'修改成功',$data);
        }else{
            $this->echoData(400,'修改失败');
        }
    }

    /**
     * 头像客户端保存
     * @param $file
     * @return array

    //验证上传头像
    private function my_checkfile($file){

        if($file["error"] > 0){
            $error = '';
            switch($file["error"])
            {
                case 1: $error = '文件大小超过服务器限制';
                    break;
                case 2: $error = '文件太大！';
                    break;
                case 3: $error =  '文件只加载了一部分！';
                    break;
                case 4: $error =  '文件加载失败！';
                    break;
            }
            return ['code'=>400,'msg'=>'操作失败！'.$error];
        }

        if($file["size"] > 20000){
            return ['code'=>400,'msg'=>'操作失败,文件过大！'];
        }

        if (($file["type"] != "image/gif") && ($file["type"] != "image/jpeg") && ($file["type"] != "image/pjpeg") && ($file["type"] != "image/png") && ($file["type"] != "image/x-png")){
            return ['code'=>400,'msg'=>'文件格式只能包含gif/jpg/jpeg/png等格式'];
        }
        if(!empty(explode('.',$file['name'])[1])){
            $type = '.' . explode('.',$file['name'])[1];
        }elseif($file["type"] == 'image/gif'){
            $type = '.gif';
        }elseif($file["type"] == 'image/jpeg' || $file["type"] == 'image/pjpeg'){
            $type = '.jpeg';
        }elseif($file["type"] == 'image/png' || $file["type"] == 'image/x-png'){
            $type = '.png';
        }

        $today = date("YmdHis");

        $upfile = $today . $type;

        return ['code'=>200,'filename'=>$upfile];
    }
    */

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            $this->echoData(400,'The requested page does not exist.');
//            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * 该店员工
     * 是否需要？
     * @param $shop_id
     * @return array
     */
    public function actionShopSalesman(){
        $shop_id = $this->getShopId();
//        $user = Yii::$app->getUser()->identity;
        //接收参数
//        $r = json_decode(Yii::$app->request->post('r'),true);
//        if(empty($r['shop_id'])){
//            $this->echoData(400,'参数不全');
//        }
//        $shop_id = (int)$r['shop_id'];

//        $this->checkshop($user,$shop_id);

//        $model = new User();
//        $list = $model->find()->select('id,nickname,name,phone')->where(['=','shop_id',$shop_id])->andWhere(['=', 'is_delete', 0])->andWhere(['in','organizational_structure_level',[3,4]])->asArray()->all();
        $objCompanyUserCenter = new CompanyUserCenter();
        $list = $objCompanyUserCenter->getShopSales($shop_id);
        //返回数据
        if($list){
            foreach ($list as $k_list=>$v_list){
                $list[$k_list]['id'] = (int)$v_list['id'];
                $list[$k_list]['nickname'] = (string)$v_list['nickname'];
                $list[$k_list]['name'] = (string)$v_list['name'];
                $list[$k_list]['phone'] = (string)$v_list['phone'];
            }
            $data['models'] = $list;
            $msg = '获取成功';
            $count = count($list);
        }else{
            $data['models'] = array();
            $msg = '数据为空';
            $count = 0;
        }

        $data['pages'] = [
            'totalCount' => $count,
            'pageCount' => 1,
            'currentPage' => 1,
            'perPage' => $count,
        ];
        $this->echoData(200,$msg,$data);
    }
}
