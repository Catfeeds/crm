<?php
/**
 * 功    能：系统的公共函数部分
 * 作    者：王雕
 * 修改日期：2017-3-21
 */
namespace common\common;

//公共函数的类
class PublicMethod
{
    /**
     * 截取字符串（支持中文的）
    * 参    数：$url       string          要请求的url地址
    *           $options   array           curl配置参数
    * 返    回：网络请求获得的结果
    * 作    者：王雕
    * 功    能：curl获取内容
    * 修改日期：2017-03-22
     */
    public static function mbSubStr($string, $intLenth = 6, $intStart = 0, $strAdding = '…', $strEncode = 'utf-8')
    {
        if(mb_strlen($string) > $intLenth)
        {
            return mb_substr($string, $intStart, $intLenth, $strEncode) . $strAdding;
        }
        else
        {
            return $string;
        }
    }
    
    /**
    * curl获取内容
    * 参    数：$url       string          要请求的url地址
    *           $options   array           curl配置参数
    * 返    回：网络请求获得的结果
    * 作    者：王雕
    * 功    能：curl获取内容
    * 修改日期：2017-03-22
    */
    public static function curl_get_contents($url, $options = array())
    {
        $default = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 6.1; rv:17.0) Gecko/17.0 Firefox/17.0",
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 3,
        );
        foreach ($options as $key => $value)
        {
            $default[$key] = $value;
        }
        $ch = curl_init();
        curl_setopt_array($ch, $default);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    
    /**
    * http get请求
    * 参    数：$url       string          要请求的url地址
    *           $params    array           get参数
    *           $options   array           curl配置参数
    * 返    回：网络请求获得的结果
    * 作    者：王雕
    * 功    能：http get请求
    * 修改日期：2017-03-22
    */
   public static function http_get($url, $params = array(), $options = array())
   {
       $paramsFMT = array();
       foreach ($params as $key => $val)
       {
           $paramsFMT[] = $key . "=" . urlencode($val);
       }
       return self::curl_get_contents($url . ($paramsFMT ? ( "?" . join("&", $paramsFMT)) : ""), $options);
   }

   /**
    * http post请求
    * 参    数：$url       string          要请求的url地址
    *           $params    array           post参数
    *           $options   array           curl配置参数
    * 返    回：网络请求获得的结果
    * 作    者：王雕
    * 功    能：http post请求
    * 修改日期：2017-03-22
    */
   public static function http_post($url, $params = array(), $options = array())
   {
       $paramsFMT = array();
       foreach ($params as $key => $val)
       {
           $paramsFMT[] = $key . "=" . urlencode($val);
       }
       $options[CURLOPT_POST] = 1;
       $options[CURLOPT_POSTFIELDS] = join("&", $paramsFMT);
       return self::curl_get_contents($url, $options);
   }

    /**
     * 获取数据更新时间
     * 参    数：$type       string          数据类型
     * 返    回：当前请求数据类型的更新时间
     * 作    者：李宗兴
     * 功    能：获取数据更新时间
     * 修改日期：2017-04-19
     */
    public static function data_update_time($type = '',$source = '')
    {
        $min = floor(date('i')/5)*5;

        if($min == '0'){

            $min = '00';

        }elseif($min == '5'){
            $min = '05';
        }
        if($type == 1){
            $time = date('Y-m-d H:') . $min;
        }else{
            $time = date('m-d H:') . $min;
        }
        //每5分钟更新一次

        return $time;
    }
    
    public static function noticeJump($strUrl, $strMsg = '', $intSecends = 0)
    {
        //计时器
        $strTimesLess = '';
        if(is_numeric($intSecends) && $intSecends > 0)
        {
            $strHtml = <<<HTMLOUT
                <div>
                    <span>{$strMsg}</span>
                    <span id="secend_num" style="color:red;">{$intSecends}</span>秒
                </div>
                <script>
                    //计数器
                    var numsNow = $intSecends;
                    function reduceNum()
                    {
                        if(numsNow > 0)
                        {
                            numsNow = numsNow - 1;
                            document.getElementById('secend_num').innerHTML = numsNow;
                            setTimeout(function(){
                                    reduceNum();
                                }, 1000);
                        }
                        else
                        {
                            location.href='{$strUrl}';
                        }
                    }
                    setTimeout(function(){
                            reduceNum();
                        }, 1000);
                </script>
HTMLOUT;
            die($strHtml);//退出页面 提示请求出错
        }
        else
        {
            header('Location: ' . $strUrl);
            exit;
        }
        

    }
    
    
}