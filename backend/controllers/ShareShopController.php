<?php

namespace backend\controllers;

use common\models\ShareShop;
use yii;
use common\models\OrganizationalStructure;
use common\logic\JsSelectDataLogic;
use yii\helpers\Json;

/**
 * Class ShareShopController PC店铺管理-门店管理
 * @package backend\controllers
 */
class ShareShopController extends BaseController
{
    /**
     * 引入json 返回处理
     */
    use \common\traits\Json;

    /**
     * 首页显示数据
     * @return string
     */
    public function actionIndex()
    {
        $shop_id = Yii::$app->request->get('shop_id');
        $shop_name = '请选择';
        if ($shop_id) {
            $arrShop = explode(',', $shop_id);
            $shop_id = array_pop($arrShop);
            if ($shop_id == -1) $shop_id = array_pop($arrShop);
            // 查询信息
            $arrShop = OrganizationalStructure::getChildIds($shop_id);
            $shop = OrganizationalStructure::findOne($shop_id);
            if ($shop) {
                $shop_name = $shop->name;
            }
        } else {
            $arrShop = [];
        }

        // 验证权限
        $this->checkPermission('/customer-search/index');

        $session = Yii::$app->getSession();

        // 获取到组织架构信息
        $arrOrgIds = $session['userinfo']['permisson_org_ids'];
        $objSelectDataLogic = new JsSelectDataLogic();
        $arrSelectOrgList = $objSelectDataLogic->getSelectOrgNew($arrOrgIds, $session['userinfo']['role_level'], true);

        // 求两个数组的交集
        if ($arrShop) {
            $arrOrgIds = array_intersect($arrOrgIds, $arrShop);
        }


        // 查询到组织架构信息
        $all = OrganizationalStructure::find()
            ->select('id,name')
            ->where(['id' => $arrOrgIds, 'level' => OrganizationalStructure::LEVEL_STORE])
            ->asArray()
            ->all();
        if ($all) {
            // 查询分享信息
            $shares = ShareShop::find()
                ->where(['shop_id' => $arrOrgIds])
                ->indexBy('shop_id')
                ->asArray()
                ->all();
            foreach ($all as &$value) {
                $value['status'] = 0;
                $value['updated_at'] = '';
                if (isset($shares[$value['id']])) {
                    $value['status'] = $shares[$value['id']]['status'];
                    $value['updated_at'] = date('Y-m-d H:i:s', $shares[$value['id']]['updated_at']);
                }
            }

            unset($value);
        }

        return $this->render('index', [
            'orgList' => Json::encode($arrSelectOrgList),
            'lists' => $all,
            'shop_name' => $shop_name,
            'shop_id' => $shop_id,
        ]);
    }

    public function actionUpdate()
    {
        return $this->renderPartial('update');
    }
}
