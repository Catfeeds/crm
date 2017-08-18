<?php
namespace console\logic;
/**
 * 车型库数据更新脚本相关功能
 * @author 王雕
 */
use Yii;
class CarBrandAndTypeUpdateLogic {
    
    private $strCheChengDataBaseName = '';
    
    public function __construct() {
        //车城线上和线下的数据库名称不一致
        $this->strCheChengDataBaseName = YII_ENV_DEV ? 'checheng' : 'pss';
    }

    public function updateFactory()
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try
        {
            //先清空表
            $strDeleteSql = 'DELETE FROM crm.crm_car_factory';
            $db->createCommand($strDeleteSql)->execute();
            //后从车城的库中更新表数据
            $strInsertSql = 'INSERT INTO 
                                crm.crm_car_factory 
                                    (
                                        factory_id, 
                                        factory_name
                                    ) 
                            SELECT
                                factory_id,
                                factory_name
                            FROM
                                ' . $this->strCheChengDataBaseName . '.car_factory_info';
            $intRtn = $db->createCommand($strInsertSql)->execute();
            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollBack();
        }
        return $intRtn;
    }
    
    public function updateBrand()
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try
        {
            //先清空表
            $strDeleteSql = 'DELETE FROM crm.crm_car_brand';
            $db->createCommand($strDeleteSql)->execute();
            //后从车城的库中更新表数据
            $strInsertSql = 'INSERT INTO crm.crm_car_brand (
                                brand_id,
                                brand_name,
                                pic_url,
                                first_num,
                                py,
                                pyem,
                                isused,
                                create_date,
                                source_pic_url,
                                isdisplay,
                                autohome_brand_id
                            ) SELECT
                                car_brand_id AS brand_id,
                                car_brand_name AS brand_name,
                                pic_url,
                                first_num,
                                py,
                                pyem,
                                is_used AS isused,
                                insert_time AS create_date,
                                source_pic_url,
                                is_display AS isdisplay,
                                autohome_brand_id
                            FROM
                        ' . $this->strCheChengDataBaseName . '.car_brand_info';
            $intRtn = $db->createCommand($strInsertSql)->execute();
            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollBack();
        }
        return $intRtn;
    }
    
    
    public function updateBrandType()
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try
        {
            //先清空表
            $strDeleteSql = 'DELETE FROM crm.crm_car_brand_type';
            $db->createCommand($strDeleteSql)->execute();
            //后从车城的库中更新表数据
            $strInsertSql = 'INSERT INTO crm.crm_car_brand_type (
                                car_brand_type_id,
                                car_brand_type_name,
                                pic_url,
                                brand_id,
                                isused,
                                zhidao_price,
                                min_price,
                                max_price,
                                car_model_name,
                                car_model,
                                url,
                                Is_display,
                                autohome_brand_id,
                                autohome_brand_type_id,
                                factory_id
                            ) SELECT
                                car_brand_type_id,
                                car_brand_type_name,
                                pic_url,
                                car_brand_id,
                                1,
                                zhi_dao_price,
                                min_price,
                                max_price,
                                car_model_name,
                                car_model,
                                url,
                                is_display,
                                autohome_brand_id,
                                autohome_brand_type_id,
                                factory_id
                            FROM
                        ' . $this->strCheChengDataBaseName . '.car_brand_type_info';
            $intRtn = $db->createCommand($strInsertSql)->execute();
            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollBack();
        }
        return $intRtn;
    }
    
}
