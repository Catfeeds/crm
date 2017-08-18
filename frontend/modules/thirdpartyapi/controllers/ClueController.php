<?php
/**
 * Created by PhpStorm.
 * User: liujx
 * Date: 2017/6/22
 * Time: 15:09
 */

namespace frontend\modules\thirdpartyapi\controllers;

use common\models\Clue;
use yii;

/**
 * Class ClueController
 * @package frontend\modules\thirdpartyapi\controllers
 * @desc 通过客户信息获取线索中的门店信息、顾问信息
 */
class ClueController extends \common\controllers\ApiController
{
    /**
     * 定义顾问信息
     * @var array
     */
    private $arrConsultant = [
        'id' => 343,
        'name' => '彭青'
    ];

    /**
     * 定义门店信息
     * @var array
     */
    private $arrShop = [
        'id' => 129,
        'name' => 'VIP1店'
    ];

    /**
     * 定义错误码对应错误信息
     * @var array
     */
    public $errCode = [
        0 => 'success',
        1 => '传递参数存在问题'
    ];

    /**
     * 通过客户手机号获取线索信息
     * @return array
     */
    public function actionGetClue()
    {
        // 验证请求参数
        $this->mixRequest = Yii::$app->request->get();
        if (!empty($this->mixRequest['phone'])) {
            // 查询这个客户是否存在没有战败线索
            $clue = Clue::find()->where(['customer_phone' => $this->mixRequest['phone'], 'is_fail' => 0])->orderBy(['id' => SORT_DESC])->one();
            if ($clue) {
                // 存在线索信息,使用线索信息中的门店和顾问
                $this->json['data'] = [
                    'shop_id' => $clue->shop_id,
                    'shop_name' => $clue->shop_name,
                    'salesman_id' => $clue->salesman_id,
                    'salesman_name' => $clue->salesman_name,
                ];
            } else {
                // 不存在使用默认定义的门店和顾问信息
                $this->json['data'] = [
                    'shop_id' => $this->arrShop['id'],
                    'shop_name' => $this->arrShop['name'],
                    'salesman_id' => $this->arrConsultant['id'],
                    'salesman_name' => $this->arrConsultant['name'],
                ];
            }

            $this->json['code'] = 0;
        }

        return $this->returnJson();
    }

}