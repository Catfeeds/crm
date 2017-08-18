<div class="main-sidebar" id="sidebar">
    <div class="sidebar">
        <?php
            $session = Yii::$app->getSession();
            $arrUserUriList = $session['userinfo']['menu_url'];
            //拼菜单信息
            $arrItems = [];
            //首页
            in_array('/index/index', $arrUserUriList) && $arrItems[] = ['label' => '首页', 'icon' => 'fa fa-home', 'url' => ['/index/index']];
            
            //线索管理
            $arrTmp = [];
            //线索导入
            in_array('/update-xlsx-log/index', $arrUserUriList) && $arrTmp[] = ['label' => '导入线索', 'icon' => 'fa fa-circle-o', 'url' => ['/update-xlsx-log/index']];
            //新增线索
            in_array('/clue/index', $arrUserUriList) && $arrTmp[] = ['label' => '新增线索', 'icon' => 'fa fa-circle-o', 'url' => ['/clue/index']];
            //未上市车型线索
            in_array('/pending-clue/unlisted-list', $arrUserUriList) && $arrTmp[] = ['label' => '未上市车型线索', 'icon' => 'fa fa-circle-o', 'url' => ['/pending-clue/unlisted-list']];

            //未下发线索
            in_array('/pending-clue/unassign-list', $arrUserUriList) && $arrTmp[] = ['label' => '未下发线索', 'icon' => 'fa fa-circle-o', 'url' => ['/pending-clue/unassign-list']];

            // 公海线索
            in_array('/gonghai/index', $arrUserUriList) && $arrTmp[] = ['label' => '公海线索', 'icon' => 'fa fa-circle-o', 'url' => ['/gonghai/index']];

	    if($arrTmp)
            {
                if($arrTmp)
                {
                    $arrItems[] = [
                        'label' => '线索管理',
                        'icon' => 'fa fa-code-fork',
                        'active' => true,
                        'url' => '#',
                        'items' => $arrTmp,
                    ];
                }
            }

            //任务审核 - 门店
            $arrTmp = [];
            //线索分配
            in_array('/assign-clue/unassign-list', $arrUserUriList) && $arrTmp[] = ['label' => '线索分配', 'icon' => 'fa fa-circle-o', 'url' => ['/assign-clue/unassign-list'],];
            //休眠客户
            in_array('/active-clue/unconnect-list', $arrUserUriList) && $arrTmp[] = ['label' => '休眠客户', 'icon' => 'fa fa-circle-o', 'url' => ['/active-clue/unconnect-list'],];
            //无人跟进的客户
            in_array('/active-clue/nofollow', $arrUserUriList) && $arrTmp[] = ['label' => '无人跟进的客户', 'icon' => 'fa fa-circle-o', 'url' => ['/active-clue/nofollow'],];
            if($arrTmp)
            {
                $arrItems[] = [
                    'label' => '待处理',
                    'icon' => 'fa fa-exclamation-circle',
                    'active' => true,
                    'url' => '#',
                    'items' => $arrTmp,
                ];
            }

            //客户管理 - 门店
            $arrTmp = [];

            //线索客户
            in_array('/customer/get-clue-customer', $arrUserUriList) && $arrTmp[] = ['label' => '线索客户', 'icon' => 'fa fa-circle-o', 'url' => ['/customer/get-clue-customer'],];
            //意向客户
            in_array('/customer/get-intention-customer', $arrUserUriList) && $arrTmp[] = ['label' => '意向客户', 'icon' => 'fa fa-circle-o', 'url' => ['/customer/get-intention-customer'],];
            //订车客户
            in_array('/customer/get-order-customer', $arrUserUriList) && $arrTmp[] = ['label' => '订车客户', 'icon' => 'fa fa-circle-o', 'url' => ['/customer/get-order-customer'],];
            //交车客户
            in_array('/customer/get-success-customer', $arrUserUriList) && $arrTmp[] = ['label' => '交车客户', 'icon' => 'fa fa-circle-o', 'url' => ['/customer/get-success-customer'],];
            //保有客户
//            in_array('/customer/get-keep-customer', $arrUserUriList) && $arrTmp[] = ['label' => '保有客户', 'icon' => 'fa fa-circle-o', 'url' => ['/customer/get-keep-customer'],];
            //战败客户
            in_array('/customer/get-fail-customer', $arrUserUriList) && $arrTmp[] = ['label' => '战败客户', 'icon' => 'fa fa-circle-o', 'url' => ['/customer/get-fail-customer'],];
            if($arrTmp)
            {
                $arrItems[] = [
                    'label' => '客户管理',
                    'icon' => 'fa fa-users',
                    'active' => true,
                    'url' => '#',
                    'items' => $arrTmp,
                ];
            }

            // PC店铺发布
            $arrTmp = [];

            // 门店活动
            in_array('/share-activity/index', $arrUserUriList) && $arrTmp[] = ['label' => '门店活动管理', 'icon' => 'fa fa-circle-o', 'url' => ['/share-activity/index'],];
            in_array('/share-shop/index', $arrUserUriList) && $arrTmp[] = ['label' => '门店管理', 'icon' => 'fa fa-circle-o', 'url' => ['/share-shop/index'],];

            // 门店管理
            if ($arrTmp) {
                $arrItems[] = [
                    'label' => 'PC店铺发布',
                    'icon' => 'fa fa-shopping-bag',
                    'active' => true,
                    'url' => '#',
                    'items' => $arrTmp,
                ];
            }

            //报表&统计模块
            $arrTmp = [];
            //明细查询
            in_array('/detailed/index', $arrUserUriList) && $arrTmp[] = ['label' => '明细查询', 'icon' => 'fa fa-circle-o', 'url' => ["/detailed/index"],];

            // 客户查询
            in_array('/customer-search/index', $arrUserUriList) && $arrTmp[] = ['label' => '客户查询', 'icon' => 'fa fa-circle-o', 'url' => ['/customer-search/index']];

            //商谈记录
            in_array('/talk-log-report/index', $arrUserUriList) && $arrTmp[] = ['label' => '商谈记录', 'icon' => 'fa fa-circle-o', 'url' => ['/talk-log-report/index'],];
            //报表查询 - 总部才显示
            in_array('/report-form/index', $arrUserUriList) && $arrTmp[] = ['label' => '报表查询', 'icon' => 'fa fa-circle-o', 'url' => ['/report-form/index'],];
            //逾期线索
            in_array('/yu-qi/index', $arrUserUriList) && $arrTmp[] = ['label' => '逾期线索', 'icon' => 'fa fa-circle-o', 'url' => ['/yu-qi/index'],];
            //未完成电话任务
            in_array('/unfinished-tel-task/list', $arrUserUriList) && $arrTmp[] = ['label' => '未完成电话任务', 'icon' => 'fa fa-circle-o', 'url' => ['/unfinished-tel-task/list'],];
            if($arrTmp)
            {
                $arrItems[] = [
                    'label' => '报表&统计',
                    'icon' => 'fa fa-bar-chart',
                    'active' => true,
                    'url' => '#',
                    'items' => $arrTmp,
                ];
            }

            //图表&分析模块
            $arrTmp = [];
            //排行榜
            in_array('/ran-king/index', $arrUserUriList) && $arrTmp[] = ['label' => '排行榜', 'icon' => 'fa fa-circle-o', 'url' => ['/ran-king/index'],];
            //转化漏斗
            in_array('/conversion-funnel/index', $arrUserUriList) && $arrTmp[] = ['label' => '转化漏斗', 'icon' => 'fa fa-circle-o', 'url' => ['/conversion-funnel/index'],];
            //客户分析-线索
            in_array('/customer-analysis/clue', $arrUserUriList) && $arrTmp[] = ['label' => '客户分析-线索', 'icon' => 'fa fa-circle-o', 'url' => ['/customer-analysis/clue'],];
            //客户分析-意向客户
            in_array('/customer-analysis/intention-level', $arrUserUriList) && $arrTmp[] = ['label' => '客户分析-意向客户', 'icon' => 'fa fa-circle-o', 'url' => ['/customer-analysis/intention-level'],];
            //客户分析-战败客户
            in_array('/customer-analysis/fail-customer', $arrUserUriList) && $arrTmp[] = ['label' => '客户分析-战败客户', 'icon' => 'fa fa-circle-o', 'url' => ['/customer-analysis/fail-customer'],];
            //客户分析-订车客户
            in_array('/customer-analysis/delivery-car-customer', $arrUserUriList) && $arrTmp[] = ['label' => '客户分析-订车客户', 'icon' => 'fa fa-circle-o', 'url' => ['/customer-analysis/delivery-car-customer'],];
            //成交趋势
            in_array('/deal-trend/index', $arrUserUriList) && $arrTmp[] = ['label' => '成交趋势', 'icon' => 'fa fa-circle-o', 'url' => ['/deal-trend/index'],];
            if($arrTmp)
            {
                $arrItems[] = [
                    'label' => '图表&分析',
                    'icon' => 'fa fa-pie-chart',
                    'active' => true,
                    'url' => '#',
                    'items' => $arrTmp,
                ];
            }
            
            //运营
            $arrTmp = [];
            //激励管理
            in_array('/excitation/index', $arrUserUriList) && $arrTmp[] = ['label' => '激励管理', 'icon' => 'fa fa-circle-o', 'url' => ['/excitation/index'],];
            //公告管理
            in_array('/announcement-send/index', $arrUserUriList) && $arrTmp[] = ['label' => '公告管理', 'icon' => 'fa fa-circle-o', 'url' => ['/announcement-send/index'],];
            //APP版本管理
            in_array('/self-update/index', $arrUserUriList) && $arrTmp[] = ['label' => 'APP版本管理', 'icon' => 'fa fa-circle-o', 'url' => ['/self-update/index'],];
            //提现处理
//            in_array('/excitation/cash-apply', $arrUserUriList) && $arrTmp[] = ['label' => '提现处理', 'icon' => 'fa fa-circle-o', 'url' => ['/excitation/cash-apply'],];
            if($arrTmp)
            {
                $arrItems[] = [
                    'label' => '运营',
                    'icon' => 'fa fa-dashboard',
                    'active' => true,
                    'url' => '#',
                    'items' => $arrTmp,
                ];
            }

            //基础数据设置
            $arrTmp = [];
            //意向等级设置
            in_array('/intention/index', $arrUserUriList) && $arrTmp[] = ['label' => '意向等级设置', 'icon' => 'fa fa-circle-o', 'url' => ['/intention/index'],];
            //客户信息设置
            in_array('/profession/index', $arrUserUriList) && $arrTmp[] = ['label' => '客户信息设置', 'icon' => 'fa fa-circle-o', 'url' => ['/profession/index'],];
            //到店/上门商谈设置
            in_array('/tags/index', $arrUserUriList) && $arrTmp[] = ['label' => '到店/上门商谈设置', 'icon' => 'fa fa-circle-o', 'url' => ['/tags/index'],];
            //战败原因设置
            in_array('/fail-tags/index', $arrUserUriList) && $arrTmp[] = ['label' => '战败原因设置', 'icon' => 'fa fa-circle-o', 'url' => ['/fail-tags/index'],];
            //短信模板设置
            in_array('/phone-letter-tmp/index', $arrUserUriList) && $arrTmp[] = ['label' => '短信模板设置', 'icon' => 'fa fa-circle-o', 'url' => ['/phone-letter-tmp/index'],];
            if($arrTmp)
            {
                $arrItems[] = [
                    'label' => '基础数据设置',
                    'icon' => 'fa fa-line-chart',
                    'active' => true,
                    'url' => '#',
                    'items' => $arrTmp,
                ];
            }
            
            //操作日志
            $arrTmp = [];
            //使用日志
            in_array('/logs/show-logs', $arrUserUriList) && $arrTmp[] = ['label' => '使用日志', 'icon' => 'fa fa-circle-o', 'url' => ['/logs/show-logs'],];
            //意见反馈
            in_array('/feedback/list', $arrUserUriList) && $arrTmp[] = ['label' => '意见反馈', 'icon' => 'fa fa-circle-o', 'url' => ['/feedback/list'],];
            if($arrTmp)
            {
                $arrItems[] = [
                    'label' => '使用日志',
                    'icon' => 'fa fa-file-text',
                    'active' => true,
                    'url' => '#',
                    'items' => $arrTmp,
                ];
            }
        ?>
        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu'],
                'items' => $arrItems,
            ]
        ) ?>

    </div>

</div>
