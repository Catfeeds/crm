<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_role".
 *
 * @property string $id
 * @property integer $organizational_id
 * @property string $name
 * @property string $permissions
 * @property string $remarks
 * @property integer $status
 * @property integer $num
 * @property integer $role_level
 */
class Role extends \yii\db\ActiveRecord
{
    public static $arrRoleLevelConfig = [
        'shu_ju_fen_xi_shi' => 10, //数据分析师 - 总部层级
        'ce_shi_ren_yuan' => 10,//测试人员 - 总部层级
        'zong_bu_ren_yuan' => 10,//总部人员 - 总部层级
        'gong_si_ren_yuan' => 15,//公司人员 - （大区层级）
        'qu_zhang' => 20,//区长 - （大区层级）
        'shopowner' => 30,//店长 - 4S店层级（门店层级）
        'salesman' => 30,//店员 - 4S店层级
        'duo_dian_shopowner' => 20,//多店店长 - 区层级
        'zong_bu_boss' => 10,//总部老板
        'company_jing_li' => 15, //区域经理 - 公司
        'zong_bu_system' => 10,//系统配置管理员 总部
        'zong_bu_yun_yin' => 10, //运营 - 总部
        'kefu' => 10, //客服 - 总部
        'ke_fu_zhu_guan_zong_bu' => 10,//客服主管 - 总部
        'zong_bu_yun_yin_dao_ru' => 10,//数据运营&导入-总部
        'zong_bu_super_admin' => 10,//超级管理员
        'zong_bu_data_input' => 10,//数据录入员
    ];
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_role';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['organizational_id', 'name', 'permissions'], 'required'],
            [['organizational_id', 'status', 'num', 'role_level'], 'integer'],
            [['permissions', 'remarks'], 'string'],
            [['name'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'organizational_id' => 'Organizational ID',
            'name' => 'Name',
            'permissions' => 'Permissions',
            'remarks' => 'Remarks',
            'status' => 'Status',
            'num' => 'Num',
            'role_level' => 'Role Level',
        ];
    }
}
