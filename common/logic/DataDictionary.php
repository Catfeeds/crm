<?php
/**
 * 数据字典模块的逻辑层
 * 作    者：王雕
 * 功    能：数据字典模块的逻辑层
 * 修改日期：2017-3-12
 */
namespace common\logic;
use yii;
class DataDictionary
{
    
    /**
     * 数据字典的查询配置
     */
    private static $dictionaryConfig = [
        'area' => [
            'where' => [],
            'select' => [
                'id' => 'int',
                'name' => 'string',
                'pid' => 'int',
                'level' => 'int',
            ],
        ],
        'buy_type' => [ //购买方式
            'where' => ['status' => 1],//查询条件
            'select' => [//查询字典
                'id' => 'int',//查询的各字典的类型
                'name' => 'string'
            ],
        ], 
        'intention' => [//购车意向等级 特殊的不能选择
            'where' => ['status' => 1, 'is_special' => 0],
            'select' => [
                'id' => 'int',
                'name' => 'string',
                'frequency_day' => 'int',
                'total_times' => 'int',
                'has_today_task' => 'int',
            ],
            'orderby'=>' sort desc'
        ],
        'input_type' => [//数据导入方式 - 客户来源
            'where' => ['status' => 1],
            'select' => [
                'id' => 'int',
                'name' => 'string',
                'is_yuqi'=> 'int',
                'yuqi_time'=>'float',
            ],
        ],
        'age_group' => [//年龄段
            'where' => ['status' => 1],
            'select' => [
                'id' => 'int',
                'name' => 'string'
            ],
        ],
        'profession' => [//职业
            'where' => ['status' => 1],
            'select' => [
                'id' => 'int',
                'name' => 'string'
            ],
        ],
        'source' => [//客户来源 - 细分客户来源
            'where' => ['status' => 1],
            'select' => [
                'id' => 'int',
                'name' => 'string',

            ],
        ],
        'planned_purchase_time' => [//拟购时间
            'where' => ['status' => 1],
            'select' => [
                'id' => 'int',
                'name' => 'string'
            ],
        ],
        'phone_letter_tmp' => [//短信模板 - 只获取app的模板
            'where' => ['status' => 1, 'type' => 2],
            'select' => [
                'id' => 'int',
                'title' => 'string',
                'content' => 'string',
            ],
        ],
        'car_type' => [//车型库
            'where' => [],
            'select' => [
                'id' => 'int',
                'name' => 'string',
                'pid' => 'int',
                'level' => 'int',
                'logo' => 'string'
            ],
        ],
        'tags' => [//标签系统和数据字典类似
            'where' => ['status' => 1],
            'select' => [
                'id' => 'int',
                'name' => 'string',
                'type' => 'string',
            ],
        ],
        'fail_tags' => [//战败标签系统
            'where' => ['status' => 1],
            'select' => [
                'id' => 'int',
                'name' => 'string',
                'type' => 'string',
                'des' => 'string',
                'group' => 'string'
            ],
        ]
    ];
    
    /**
     * 缓存数据字典的静态变量
     */
    private static $dictionaryData = [];

    /**
     * 功    能：将数据字典信息缓存到成员变量中
     * 参    数：$dictionary    string      数据字典名称
     * 返    回：无
     * 作    者：王雕
     * 修改日期：2017-3-12
     */
    private function initDictionaryData($dictionary)
    {
        if(empty(self::$dictionaryData[$dictionary]))
        {
            $key = "CRM:dictionary:$dictionary";
            $redis = Yii::$app->redis;
            $jsonData = $redis->get($key);
            if(0)//($jsonData)
            {
                self::$dictionaryData[$dictionary] = json_decode($jsonData, true);
            }
            else
            {
                //为了客户端的格式统一   字典之间的单词用 _ 分隔   但是数据模型中是驼峰形式的
                $modelName = str_replace(' ', '', ucwords(str_replace('_', ' ', $dictionary)));
                $class = '\common\models\\' . $modelName;
                /* @var $dd_model \yii\db\ActiveRecord */
                $dd_model = new $class();
                $thisConfig = self::$dictionaryConfig[$dictionary];
                $selectInfo = self::$dictionaryConfig[$dictionary]['select'];
                $strSelect = implode(',', array_keys($selectInfo));
                $ddTmp = $dd_model::find()->select($strSelect)->where($thisConfig['where']);//->asArray()->all();
                if (isset($thisConfig['orderby'])) {
                    $ddTmp = $ddTmp->orderBy($thisConfig['orderby']);
                }

                $ddTmp = $ddTmp->asArray()->all();

                foreach($ddTmp as $val)
                {
                    $tmp = [];
                    foreach($selectInfo as $name => $type)
                    {
                        $tmp[$name] = ($type == 'int' ? intval($val[$name]) :strval($val[$name]));
                    }
                    self::$dictionaryData[$dictionary][] = $tmp;
                }
                if(!empty($ddTmp))
                {
                    $redis->set($key, json_encode(self::$dictionaryData[$dictionary]));
                }
            }
        }
    }
    
    /**
     * 功    能：根据数据字典名称获取数据字典内容
     * 参    数：$dictionary    string      数据字典名称
     * 返    回：$rtn           string      数据字典内容
     * 作    者：王雕
     * 修改日期：2017-3-12
     */
    public function getDictionaryData($dictionary)
    {
        $rtn = [];
        //配置数据字典类型
        $dictionaryNames = array_keys(self::$dictionaryConfig);
        if(in_array($dictionary, $dictionaryNames))
        {
            $this->initDictionaryData($dictionary);
            if(isset(self::$dictionaryData[$dictionary]))
            {
                $rtn = self::$dictionaryData[$dictionary];
            }
            //如果是标签的话  需要组合成按照type值聚合的数据
            if($dictionary == 'tags')
            {
                $rtn = $this->_formatTagDict($rtn);
            }
            else if($dictionary == 'fail_tags')//战败信息数据字典 - 结构特殊
            {
                $rtn = $this->_formatFailTagsDict($rtn);
            }
            else if($dictionary == 'intention')//意向等级
            {
                $rtn = $this->_formatIntentionDict($rtn);
            }
        }
        return $rtn;
    }
    
    //意向等级数据字典数据格式化给安卓端
    private function _formatIntentionDict($list)
    {
        $rtn = [];
        foreach($list as $k => &$val)
        {
            $val['content'] = '每' . $val['frequency_day'] . '天推送一次，共' . $val['total_times'] . '次' . ($val['has_today_task'] == 1 ? '，当天推送' : '' );
            unset($val['frequency_day'], $val['total_times'], $val['has_today_task']);
            $rtn[$k] = $val;
        }
        return $rtn;
    }
    
    //标签数据字典格式调整，去掉type属性
    private function _formatTagDict($list)
    {
        $rtn = [];
        foreach($list as $val)
        {
            $type = $val['type'];
            unset($val['type']);
            $rtn[$type][] = $val;
        }
        return $rtn;
    }
    
    //失败标签数据格式化
    private function _formatFailTagsDict($list)
    {
        $rtn = [];
        foreach($list as $val)
        {
            switch($val['type'])
            {
                case 'order_fail' ://订车战败
                    $rtn['order_fail'][$val['group']][] = [
                        'id' => $val['id'],
                        'des' => $val['des'],
                        'name' => $val['name'],
                    ];
                    break;
                default : //线索、意向客户战败
                    $rtn[$val['type']][] = ['id' => $val['id'], 'name' => $val['name']];
                    break;
            }
        }
        return $rtn;
    }
    
    /**
     * 功    能：地区code转换为名称（带有省市县层级划分）
     * 参    数：无
     * 返    回：无
     * 作    者：王雕
     * 修改日期：2017-3-12
     */
    public function areaCodeToName($areaId)
    {
        $rtn = '';
        if(is_numeric($areaId) && $areaId)
        {
            $this->getDictionaryData('area');
            foreach(self::$dictionaryData['area'] as $val)
            {
                $data[$val['id']] = $val;
            }
            //area_id有效
            if(isset($data[$areaId]))
            {
                $thisData = $data[$areaId];
                $rtn = $thisData['name'];
                $intDeepNow = 1;
                while(($thisData['pid'] != 0) && $intDeepNow++ < 3)//做多显示三层(省市县)，避免pid数据异常造成死循环
                {
                    $thisData = $data[$thisData['pid']];
                    $rtn = $thisData['name'] . '-' . $rtn;
                }
            }
        }
        return $rtn;
    }

    /**
     * 根据标签id获取标签名称
     * @param $mixId
     * @return array|null|string
     */
    public function getTagNamebyIds($mixId)
    {
        $rtn = null;
        if(is_numeric($mixId))
        {
            $rtn =  $this->getDataNameByTypeAndId('tags', $mixId);
        }
        else if(is_array($mixId))
        {
            $rtn = [];
            foreach($mixId as $id)
            {
                $rtn[] = $this->getDataNameByTypeAndId('tags', $id);
            }
        }
        return $rtn;
    }
    
    // 信息来源
    public function getSourceName($intId)
    {
        return $this->getDataNameByTypeAndId('source', $intId);
    }
    
    public function getCarName($intId)
    {
        return '宝马710li';
    }
    
    //购买方式
    public function getBuyTypeName($intId)
    {
        return $this->getDataNameByTypeAndId('buy_type', $intId);
    }
    
    //职业
    public function getProfessionName($intId)
    {
        return $this->getDataNameByTypeAndId('profession', $intId);
    }
    
    //拟购时间
    public function getPlannedPurchaseTime($intId)
    {
        return $this->getDataNameByTypeAndId('planned_purchase_time', $intId);
    }
    
    //获取年龄段描述
    public function getAgeGroupName($intId)
    {
        return $this->getDataNameByTypeAndId('age_group', $intId);
    }
    
    private function getDataNameByTypeAndId($strType, $intId)
    {
        $rtn = '';
        $arrType = ['source', 'tags', 'profession', 'buy_type', 'planned_purchase_time', 'age_group'];
        if(in_array($strType, $arrType) && is_numeric($intId) && $intId)
        {
            $this->getDictionaryData($strType);
            foreach(self::$dictionaryData[$strType] as $val)
            {
                if($val['id'] == $intId)
                {
                    $rtn = $val['name'];
                }
            }
        }
        return $rtn;
    }
    
    /**
     * 获取数据字典的版本信息
     */
    public function getDictionaryVersion()
    {
        $rtn = [];
        $key = 'CRM:dictionary_version';
        $redis = Yii::$app->redis;
        $jsonData = $redis->get($key);
        if(0)//($json_data)
        {
            $rtn = json_decode($jsonData, true);
        }
        else
        {
            //数据库中查询获取，缓存redis
            $versionDdModel = new \common\models\VersionOfDd();
            $versionTmp = $versionDdModel::find()->select('dd__table_name, dd_version')->asArray()->all();
            foreach($versionTmp as $val)
            {
                $k = str_replace(['crm_dd_', 'crm_'], '', $val['dd__table_name']);
                $rtn[$k] = intval($val['dd_version']);
            }
            $redis->set($key, json_encode($rtn));
        }
        return $rtn;
    }
    
    /**
     * 功    能：删除redis中缓存的数据字典的版本对应信息
     * 参    数：无
     * 返    回：无
     * 作    者：王雕
     * 修改日期：2017-3-12
     */
    public function deleteCacheDictionaryVersion()
    {
        $key = 'CRM:dictionary_version';
        $redis = Yii::$app->redis;
        $redis->executeCommand('DEL', [$key]);
    }
    
    /**
     * 功    能：删除redis中缓存的某个数据字典的内容
     * 参    数：无
     * 返    回：无
     * 作    者：王雕
     * 修改日期：2017-3-12
     */
    public function deleteCacheDictionary($dictionary)
    {
        if(in_array($dictionary, array_keys(self::$dictionaryConfig)))
        {
            $key = "CRM:dictionary:$dictionary";
            $redis = Yii::$app->redis;
            $redis->executeCommand('DEL', [$key]);
            $this->deleteCacheDictionaryVersion();//数据字典有变动时 版本信息的缓存清除掉
        }
    }
    
    //
    public function getTalkTypeName($intId)
    {
        switch($intId)
        {
            case 1 : $name = '修改客户信息';break;
            case 2 : $name = '来电';break;
            case 3 : $name = '去电';break;
            case 4 : $name = '短信';break;
            case 5 : $name = '到店-商谈';break;
            case 6 : $name = '到店-订车';break;
            case 7 : $name = '到店-交车';break;
            case 8 : $name = '上门-商谈';break;
            case 9 : $name = '上门-订车';break;
            case 10 : $name = '上门-交车';break;
            case 11 : $name = '取消电话任务审批';break;
            case 12 : $name = '取消电话任务审批结果';break;
            case 13 : $name = '意向客户战败';break;
            case 14 : $name = '意向客户战败审批';break;
            case 15 : $name = '意向客户战败审批结果';break;
            case 16 : $name = '订车客户战败';break;
            case 17 : $name = '订车客户战败审批';break;
            case 18 : $name = '订车客户战败审批结果';break;
            case 19 : $name = '试驾';break;
            case 20 : $name = '战败客户激活';break;
            case 21 : $name = '休眠客户激活';break;
            case 22 : $name = '订车客户换车';break;
            case 23 : $name = '添加备注';break;
            case 24 : $name = '顾问重新分配';break;
            case 25 : $name = 'ERP终止合同-客户转为意向';break;
            case 26 : $name = 'ERP确认交车';break;
            case 27 : $name = '客户在电商订车';break;
            default : $name = '未知种类';break;
        }
        return $name;
    }
    
}
