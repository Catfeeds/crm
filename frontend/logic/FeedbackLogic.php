<?php
namespace frontend\logic;

use common\models\Feedback;
use common\models\OrganizationalStructure;
use yii\helpers\FileHelper;

class FeedbackLogic
{
    /**
     * 意见反馈保存
     * @param $content    反馈内容
     * @param $user_ip    用户ip
     * @param $app_id     APPid
     * @param $file       上传附件
     * @return bool
     */
    public function feedback($content,$user_ip,$app_id,$file)
    {
        $user = \Yii::$app->getUser()->identity;

//        $user_ip = Yii::$app->request->userIP;

        $file_url = '';
        if(!empty($file)){
//            $file = $_FILES['imgs'];
            $count  = count($file['name']);

            //循环处理每个文件
            for($i=0;$i<$count;$i++){

                if($file["error"][$i] > 0){
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
                    continue;
//                    return ['code'=>400,'msg'=>'操作失败！'.$error];
                }

                if($file["size"][$i] > 2000000000000){
                    continue;
//                    return ['code'=>400,'msg'=>'操作失败,文件过大！'];
                }

                $name_arr = explode('.',$file['name'][$i]);

                if(!empty($name_arr[count($name_arr)-1])){
                    $type = '.' . $name_arr[count($name_arr)-1];
                }

                $today = date("YmdHis");

                //拼接文件名
                $new_file_name = $today. rand() . $type;

                //保存文件
                if (is_uploaded_file($_FILES['imgs']['tmp_name'][$i])) {

                    $rootPath = \Yii::getAlias('@frontend/web/upload/feedback/');

                    //新建文件夹
//                    if (!file_exists("upload/feedback")){mkdir("upload/feedback",0777,true);}//第二种好像无效
                    if (!is_dir($rootPath)) {
                        @FileHelper::createDirectory($rootPath, 0777);
                    }

                    //拼接完整路径
                    $save_path = $rootPath . $new_file_name;

                    //保存文件
                    if (!move_uploaded_file($_FILES['imgs']['tmp_name'][$i], $save_path)) {
                        continue;
                    } else {
                        //保存文件地址
                        $file_url .= \Yii::$app->request->hostInfo.'/upload/feedback/' . $new_file_name . ',';
                    }

//                    if (!file_exists("appfile")){mkdir("appfile",0777,true);}
//                    if (!move_uploaded_file($_FILES['imgs']['tmp_name'][$i], 'appfile/' . $new_file_name)) {
//                        continue;
////                        return false;
//                    } else {
//                        //删除原文件
//                        //保存文件地址
//                        $file_url .= \Yii::$app->request->hostInfo.'/appfile/' . $new_file_name .',';
//                    }

                }
            }
            $file_url = trim($file_url,',');
        }

        //获取用户信息  包括用户id 用户姓名  用户手机号
        $user_id = $user->id;
        $user_name = $user->name;
        $user_phone = $user->phone;

        //获取用户组织层级及org_id 查询组织架构名称  总部为总部  大区为大区名称  门店为  大区——门店
        $arrAllOrgTmp = OrganizationalStructure::find()->asArray()->all();
        $arrAllOrg = [];
        foreach($arrAllOrgTmp as $val)
        {
            $arrAllOrg[$val['id']] = $val;
        }
        $org_name = '';
        $thisId = $user->org_id;
        while(isset($arrAllOrg[$thisId]) && $thisId > 0)
        {
            $org_name = (empty($org_name) ? $arrAllOrg[$thisId]['name'] :  $arrAllOrg[$thisId]['name'] .'-' . $org_name);
            $thisId = $arrAllOrg[$thisId]['pid'];
        }
        if(empty($org_name)){
            $org_name = '--';
        }
        
        //保存数据
        $model = new Feedback();
        $model->user_id = $user_id;
        $model->user_name = $user_name;
        $model->user_phone = $user_phone;
        $model->user_ip = $user_ip;
        $model->org_name = $org_name;
        $model->content = $content;
        $model->imgs = $file_url;
        $model->app_id = $app_id;
        $model->create_time = time();
        if($model->save()){
            return true;
        }else{
            return false;
        }
    }
}
?>