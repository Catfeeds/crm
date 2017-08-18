<?php

namespace backend\controllers;

use Codeception\Command\SelfUpdate;
use common\helpers\Helper;
use common\models\AppSelfUpdate;
use Yii;
use common\models\AgeGroup;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\Pagination;

/**
 * AgeGroupController implements the CRUD actions for AgeGroup model.
 */
class SelfUpdateController extends BaseController
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
     * Lists all AgeGroup models.
     * @return mixed
     */
    public function actionIndex()
    {
        //权限控制 - 总部
        $this->checkPermission('/self-update/index', 0);

        //接收数据

        $search_time = Yii::$app->request->post('search_time');
        $search_key = Yii::$app->request->post('search_key');

        if($search_time){
            $date = explode(' - ',$search_time);
            $start_day = $date[0];
            $end_day = $date[1];
        }else{
            $start_day = date('Y-m-d',strtotime("-1 month"));
            $end_day = date('Y-m-d');
        }

        $start_time = strtotime($start_day);
        $end_time = strtotime($end_day) + 86400;
        $search_time = "{$start_day} - {$end_day}";

        $query = AppSelfUpdate::find()->select(['*'])
            ->where(['between','create_time',$start_time,$end_time]);

        if(!empty($search_key)){
            $query = $query->andWhere(['OR',['like','content',$search_key],['like','tips',$search_key]]);
        }


//        die($query->createCommand()->getRawSql());
//        $countQuery = clone $query;
        $intTotal = $query->count();
//        $pages = new Pagination(['totalCount' => $intTotal]);//分页信息
//        $pages->setPageSize($intPageSize);
//        $pages->setPage($intPage - 1); //坑爹的  页码它是从0算起的

        $pagination = new Pagination([
            'defaultPageSize' => 10,
            'totalCount' => $intTotal,
        ]);

        $search_data['search_key'] = $search_key;
        $search_data['search_time'] = $search_time;

        $arrList = $query->orderBy('id DESC')->offset($pagination->offset)->limit($pagination->limit)
            ->asArray()->all();

        //查询每个APP的最新版本号  只有版本号最大的才可以编辑
        $app_arr_key = [1,2,3,4];
        $app_arr = [];
        foreach ($app_arr_key as $k=>$v){
            $max_code = AppSelfUpdate::find()->where(['=','app_id',$v])->max('versionCode');
            $app_arr[$v] = $max_code;
        }

        foreach ($arrList as $key=>$value){
            if($value['versionCode'] == $app_arr[$value['app_id']]){
                $arrList[$key]['can_modify'] = 1;
            }else{
                $arrList[$key]['can_modify'] = 0;
            }
        }

        return $this->render('index', [
            'list' => $arrList,
            'count' => $intTotal,
            'pagination' => $pagination,
            'search_data' => $search_data,
        ]);
    }

    public function actionTestfile(){
        set_time_limit(0);
        if($_FILES){
            var_dump($_FILES);die;
        }
        return $this->render('testfile');
    }

    public function actionUpdateOrCreate()
    {
        set_time_limit(0);
        $post = Yii::$app->request->post();
        $intId = intval($post['id']);
        $app_id = intval($post['app_id']);
        $version_name = strval($post['versionName']);
        //宗兴 - 反啦   1 3 -助手  2 4 -速报
        if($app_id == 1 || $app_id == 3){
            $app_name = '销售助手';
            $app_name_pinyin = 'sales';
        }elseif($app_id == 2 || $app_id == 4){
            $app_name = '管理速报';
            $app_name_pinyin = 'glsb';
        }

        if($app_id == 1 || $app_id == 2){
            $ios_or_android = '安卓';
            $this->strRoute = 'addAndroidApp';

            //如果编辑信息且没有上传文件时取出文件地址  如果新建必须有上传文件
            if($intId && $_FILES['file']['size'] == 0){
                $file_url = $post['file_url'];
            }else{
                if(!empty($_FILES['file'])){

                    if($app_id == 1){
                        $app_name_url = 'android_sales.apk';
                    }else{
                        $app_name_url = 'android_glsb.apk';
                    }

                    //检查文件信息
                    if($this->my_checkfile($_FILES['file'])){

                        $info = $this->my_checkfile($_FILES['file']);
                        if($info['code'] == 200){
                            $newversion = $info['filename'];
                        }else{
                            return false;
                        }
                    }else{
                        return false;
                    }

                    try {
                        //保存文件
                        if (is_uploaded_file($_FILES['file']['tmp_name'])) {
                            if (!file_exists("appfile")){mkdir("appfile",0777,true);}
                            //保存文件夹地址
                            $rootPath = \Yii::getAlias('@backend/web/appfile/');

                            //新建文件夹
                            if (!is_dir($rootPath)) {
                                @FileHelper::createDirectory($rootPath, 0777);
                            }

                            //拼接完整路径
                            $save_path = $rootPath . $app_name_url;

                            //清除原文件
                            if(is_file($save_path)){
                                unlink($save_path);
                            }

                            //保存文件
                            if (!move_uploaded_file($_FILES['file']['tmp_name'], $save_path)) {
                                return false;
                            } else {
                                copy($save_path,$rootPath.$app_name_pinyin.'-'.$version_name.'.apk');
                                //保存文件地址
                                $file_url = \Yii::$app->request->hostInfo.'/appfile/' . $app_name_url;
                            }
                        }
                    } catch (\Exception $e) {
                        Helper::logs('/error/'.date('Ymd').'-self-upload-error.log', [
                            'time' => date('Y-m-d H:i:s'),
                            'error' => $e->getMessage(),
                        ]);

                        return false;
                    }

                }else{
                    return false;
                }
            }
        }elseif($app_id == 3 || $app_id == 4){
            $this->strRoute = 'addiOSApp';
            $ios_or_android = 'iOS';
            $file_url = '';
        }

        //记录操作日志
        $this->arrLogParam = [
            'version_name' => strval($post['versionName'])
        ];

        $arrSave = [
            'versionName' => strval($post['versionName']),
            'versionCode' => strval($post['versionCode']),
            'content' => strval($post['content']),
            'tips' => strval($post['tips']),
            'is_forced_update' => intval($post['is_forced_update']),
            'app_id' => intval($post['app_id']),
            'create_time' => time(),
            'app_name' => $app_name,
            'file_url' => $file_url,
            'ios_or_android' => $ios_or_android,
        ];

        $appSelfUpdate = new AppSelfUpdate();
        if($intId)
        {
            //编辑
            $strTableName = $appSelfUpdate->tableName();
            $strWhere = " id = $intId ";
            Yii::$app->db->createCommand()->update($strTableName, $arrSave, $strWhere)->execute();
        }
        else
        {
            //新建
            $appSelfUpdate->setAttributes($arrSave);
            $appSelfUpdate->save();
        }
        return $this->redirect(['index']);
    }

    /**
     * APP更新历史
     * @return string
     */
    public function actionUpdateHistory(){
        //接收参数
        $app_name = Yii::$app->request->get('app_name');

        $currentPage = 1;
        $perPage = 2;
        $query = AppSelfUpdate::find()->select('id,create_time,versionName,content')
            ->offset(($currentPage-1)*$perPage)
            ->limit($perPage)->orderBy('create_time DESC');

        if($app_name == '管理速报'){

            $query = $query->where(['=','app_name','管理速报']);
        }else{
            $query = $query->where(['=','app_name','销售助手']);
        }
//        die($query->createCommand()->getRawSql());
        $ios_list = clone $query;
        $android_list = clone $query;

        $ios_list = $ios_list->andWhere(['=','ios_or_android','iOS'])->asArray()->all();

        $android_list = $android_list->andWhere(['=','ios_or_android','安卓'])->asArray()->all();

        return $this->render('updatehistory', [
            'app_name' => $app_name,
            'ios_list' => $ios_list,
            'android_list' => $android_list,
        ]);

    }

    /**
     * 动态获取APP更新历史
     */
    public function actionAjaxUpdateHistory(){

        $currentPage = Yii::$app->request->post('currentPage');
        //接收参数
        $app_name = Yii::$app->request->post('app_name');
        
        $perPage = 2;
        $query = AppSelfUpdate::find()->select('id,create_time,versionName,content')
            ->offset(($currentPage-1)*$perPage)
            ->limit($perPage)->orderBy('create_time DESC');

        if($app_name == '管理速报'){
            $query = $query->where(['=','app_name','管理速报']);
        }else{
            $query = $query->where(['=','app_name','销售助手']);
        }

        $ios_list = clone $query;
        $android_list = clone $query;

        $ios_list = $ios_list->andWhere(['=','ios_or_android','iOS'])
//        die($ios_list->createCommand()->getRawSql());
            ->asArray()->all();

        $android_list = $android_list->andWhere(['=','ios_or_android','安卓'])->asArray()->all();


        foreach ($android_list as &$item) {
            $item['create_time'] = date('Y-m-d',$item['create_time']);
        }
        foreach ($ios_list as &$item) {
            $item['create_time'] = date('Y-m-d',$item['create_time']);
        }

        $list['android_list'] = $android_list;
        $list['ios_list'] = $ios_list;
        if(empty($android_list) && empty($ios_list)){
            echo 'no';die;
        }

        echo json_encode($list);
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

        if($file["size"] > 20000000000){
            return ['code'=>400,'msg'=>'操作失败,文件过大！'];
        }

//        if (($file["type"] != "image/gif") && ($file["type"] != "image/jpeg") && ($file["type"] != "image/pjpeg") && ($file["type"] != "image/png") && ($file["type"] != "image/x-png")){
//            return ['code'=>400,'msg'=>'文件格式只能包含gif/jpg/jpeg/png等格式'];
//        }
        if(!empty(explode('.',$file['name'])[1])){
            $type = '.' . explode('.',$file['name'])[1];
        }
//        elseif($file["type"] == 'image/gif'){
//            $type = '.gif';
//        }elseif($file["type"] == 'image/jpeg' || $file["type"] == 'image/pjpeg'){
//            $type = '.jpeg';
//        }elseif($file["type"] == 'image/png' || $file["type"] == 'image/x-png'){
//            $type = '.png';
//        }

        $today = date("YmdHis");

        $upfile = $today . $type;

        return ['code'=>200,'filename'=>$upfile];
    }
}
