<?php

namespace common\models;

use common\helpers\Helper;
use Yii;
use common\models\Yuqi;
use common\models\Customer;
use common\models\Clue;

/**
 * This is the model class for table "crm_update_xlsx_log".
 *
 * @property string $id
 * @property string $update_file
 * @property string $error_file
 * @property integer $success_num
 * @property integer $error_num
 * @property integer $update_time
 * @property integer $update_person_id
 * @property string $update_person_name
 * @property integer $update_type
 * @property integer $update_from
 */
class UpdateXlsxLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_update_xlsx_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['update_file'], 'required'],
            [['success_num', 'error_num', 'update_time', 'update_person_id', 'update_type', 'update_from'], 'integer'],
            [['update_file', 'error_file', 'update_person_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'update_file' => '文件名称',
            'error_file' => 'Error File',
            'success_num' => '成功导入数量',
            'error_num' => '失败数量',
            'update_time' => '导入时间',
            'update_person_id' => 'Update Person ID',
            'update_person_name' => '操作人',
            'update_type' => 'Update Type',
            'update_from' => 'Update From',
        ];
    }


    /**
     * @return array 门店
     */
    public function get_structure()
    {

        return (new \yii\db\Query())
            ->select(['id', 'name'])
            ->from('crm_organizational_structure')
            ->where(['level' => 3])
            ->all();

    }

    /*
     * 客户来源
     */
    public function get_input_type()
    {
        return (new \yii\db\Query())
            ->select(['id', 'name'])
            ->from('crm_dd_input_type')
            ->all();
    }

    /*
     * 细分来源
     */
    public function get_source()
    {
        return (new \yii\db\Query())
            ->select(['id', 'name'])
            ->from('crm_dd_source')
            ->all();
    }

    /*
     * 车型
     */
    public function get_car_type()
    {
        return (new \yii\db\Query())
            ->select(['id', 'name'])
            ->from('crm_dd_car_type')
            ->all();
    }

    /**
     * 查询已经存在的用户信息
     * @param  string $mobile  查询的手机号
     * @return array 数据结果集
     */
    public function get_user_clue($mobile)
    {

        $sql = "SELECT `a`.`id`, `a`.`customer_phone`, `a`.`customer_id`, `a`.`status`, `a`.`shop_id`, `a`.`is_fail`, `a`.`salesman_id` FROM `crm_clue` `a` JOIN
                (SELECT max(`id`) `id`, `customer_phone`, `shop_id` FROM 
                `crm_clue` WHERE `customer_phone` IN ($mobile) GROUP BY `customer_phone`) `b`
                ON `a`.`id` = `b`.`id`";
        return Yii::$app->db->createCommand($sql)->queryAll();
    }

    /**
     * 插入客户信息与线索信息
     * @param  array $res 数据集合
     * @return bool
     */
    public function insert_info($res)
    {
        // 处理导入数据
        $array = array_map(function($value) {
            return [
                'customer_name' => $value['A'],
                'customer_phone' => $value['B'],
                'area_id' => $value['area'],
                'shop_id' => $value['md'],
                'shop_name' => $value['C'],
                'intention_id' => $value['yx'],
                'intention_des' => $value['I'],
                'des' => $value['J'],
                'clue_source' => $value['xf'],
                'clue_input_type' => $value['kh'],
            ];
        }, $res);

        // 执行批量导入数据
        $session = Yii::$app->getSession();
        $arrReturn = Clue::batchInsert($array, 1, $session['userinfo']['name']);
        $isReturn = false;
        if ($arrReturn['status'] > 0) {
            $time = time();
            // 写入逾期线索
            if ($this->insertYuQi(date('Y-m-d H:i:s', $time), $time, $arrReturn['maxClueId'])) {
                $isReturn = true;
            }
        }

        return $isReturn;
    }

    public function dump($arr)
    {
        echo "<pre>";
        print_r($arr);
        exit;
    }

    /**
     * 新增预期处理数据
     * @param  string $date
     * @param  int $time
     * @param  int $id
     * @return bool
     */
    public function insertYuQi($date, $time, $id)
    {

        //获取当前最大的线索id
        $clue = Clue::find()->select('id,clue_input_type,shop_id')->where(['>', 'id', $id])->asArray()->all();
        //获取信息来源信息
        $data = new \common\logic\DataDictionary();
        $input_type = $data->getDictionaryData('input_type');

        //逾期表设定
        $arr = [];
        $i = 0;
        foreach ($clue as $v) {
            foreach ($input_type as $val) {
                //渠道来源id等于来源id 并且 is_yuqi==1 增加逾期表
                if ($v['clue_input_type'] == $val['id'] && $val['is_yuqi'] == 1) {
                    $arr[$i][] = $v['id'];
                    $arr[$i][] = $date;
                    $arr[$i][] = date("Y-m-d H:i:s", $time + ($val['yuqi_time'] * 3600));
                    $arr[$i][] = $v['shop_id'];
                    $i++;
                    break;
                }
            }
        }


        if (!empty($arr)) {
            if (Yii::$app->db->createCommand()->batchInsert('crm_yuqi', ['clue_id', 'start_time', 'end_time', 'shop_id'], $arr)->execute()) {
                return true;
            }
            return false;
        }
        return true;
    }
}
