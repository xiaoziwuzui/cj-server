<?php
/**
 * Created by PhpStorm.
 * Date: 2018/08/10 09:56
 * @author: 93307399@qq.com
 */

class Request
{
    /**
     * 请求浏览器头信息
     * @var string
     */
    public $user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36';
    /**
     * COOKIE保存路径
     * @var string
     */
    public $cookie_path = 'data/cookie.txt';
    /**
     * 伪造的来源地址
     * @var string
     */
    public $refer = 'https://www.baidu.com/';
    /**
     * 当前进程访问的IP
     * @var null
     */
    public $ip = null;

    public $c_fix = '_gather_';

    public $location = 0;

    public $MAX_LOCATION = 3;
    /**
     * @var bool|array
     */
    public $proxy = false;

    public $cookie  = array();

    public $debug = true;

    public $write_log = true;

    public $log_level = 2;

    /**
     * 设置cookie路径
     * @param string $path
     * @author 93307399@qq.com
     */
    public function setCookiePath($path){
        $this->cookie_path = $path;
    }

    public function log($value,$level = 0){
        if($this->debug !== true){
            return false;
        }
        if($this->log_level > $level){
            return true;
        }
        if(is_string($value)){
            $string = $value;
        }else{
            $string = var_export($value);
        }
        if($this->write_log === true){
            FLogger::write($string,'monitor');
        }else{
            echo date('H:i'),' ';
            echo $string,chr(10);
        }
        return true;
    }

    /**
     * 获取当前使用的IP地址
     * @return string
     */
    public function getCurrentIP()
    {
        if ($this->ip === null) {
            $this->ip = FCache::get($this->c_fix.'ip');
            if (!$this->ip) {
                $this->ip = self::createRandIP();
                FCache::set($this->c_fix.'ip',$this->ip);
            }
        }
        return $this->ip;
    }

    /**
     * 获取当前对象的COOKIE值
     * @param bool $update
     * @param string $cookie_path
     * @author 93307399@qq.com
     * @return array
     */
    public function getCookie($update = false,$cookie_path = ''){
        if(!$this->cookie || $update !== false){
            $default_cookie = false;
            if($cookie_path === ''){
                $cookie_path = $this->cookie_path;
                $default_cookie = true;
            }
            $cookie = $this->parse_cookie($cookie_path);
            if($default_cookie){
                $this->cookie = $cookie;
            }
        }else{
            $cookie = $this->cookie;
        }
        return $cookie;
    }

    /**
     * 解析保存的cookie信息
     * @param string $cookie_path
     * @author xiaojiang432524@163.com
     * @return array
     */
    public function parse_cookie($cookie_path)
    {
        $cookie_content = file_get_contents($cookie_path);
        $cookie = array();
        foreach (explode(chr(10), $cookie_content) as $k=>$v) {
            if ($k <= 3) {
                continue;
            }
            $v_array = explode("\t", $v);
            if (count($v_array) == 7) {
                if($v_array[6] == 'deleted'){
                    continue;
                }
                $v_array[0] = str_replace('#HttpOnly_','',$v_array[0]);
                $key = $v_array[0] .':'. $v_array[5];
                if(isset($cookie[$key])){
                    if($v_array[4] > $cookie[$key][4]){
                        $cookie[$key] = $v_array;
                    }
                }else{
                    $cookie[$key] = $v_array;
                }
            }
        }
        return $cookie;
    }

    /**
     * 创建一个随机的IP
     * @param int $mode 生成方式,1:生成国内IP,2:随机全网IP,3:当前机器外网IP
     * @return string
     */
    public function createRandIP($mode = 1)
    {
        $ip = null;
        switch ($mode){
            case 1:
                $ip_long = array(
                    array('607649792', '608174079'),     //36.56.0.0-36.63.255.255
                    array('975044608', '977272831'),     //58.30.0.0-58.63.255.255
                    array('999751680', '999784447'),     //59.151.0.0-59.151.127.255
                    array('1019346944', '1019478015'),   //60.194.0.0-60.195.255.255
                    array('1038614528', '1039007743'),   //61.232.0.0-61.237.255.255
                    array('1783627776', '1784676351'),   //106.80.0.0-106.95.255.255
                    array('1947009024', '1947074559'),   //116.13.0.0-116.13.255.255
                    array('1987051520', '1988034559'),   //118.112.0.0-118.126.255.255
                    array('2035023872', '2035154943'),   //121.76.0.0-121.77.255.255
                    array('2078801920', '2079064063'),   //123.232.0.0-123.235.255.255
                    array('-1950089216', '-1948778497'), //139.196.0.0-139.215.255.255
                    array('-1425539072', '-1425014785'), //171.8.0.0-171.15.255.255
                    array('-1236271104', '-1235419137'), //182.80.0.0-182.92.255.255
                    array('-770113536', '-768606209'),   //210.25.0.0-210.47.255.255
                    array('-569376768', '-564133889'),   //222.16.0.0-222.95.255.255
                );
                $rand = mt_rand(0, 14);
                $ip   = long2ip(mt_rand($ip_long[$rand][0], $ip_long[$rand][1]));
                break;
            case 2:
                $ip = long2ip(mt_rand(1, 244967295));
                break;
            default:
                $ip = gethostbyname($_ENV['COMPUTERNAME']);
        }
        return $ip;
    }

    /**
     * 发送HTTP请求
     * @param string $url 要访问的URL
     * @param array $param 查询参数
     * @param bool $location 主动跳转
     * @param string $set_cookie cookie保存文件路径
     * @param string $send_cookie 要使用的cookie文件路径
     * @param array $header 头信息
     * @param bool $gzip 是否启用gzip
     * @author 93307399@qq.com
     * @return bool|string
     */
    public function sendHttpGet($url, $param = array(),$location = false,$set_cookie = '',$send_cookie = '',$header = array(),$gzip = false)
    {
        $this->log('GET请求:'.$url,3);
        if(count($param) > 0){
            $this->log('参数:'.http_build_query($param),2);
        }
        if($set_cookie == ''){
            $set_cookie = $this->cookie_path;
        }
        if($send_cookie == ''){
            $send_cookie = $this->cookie_path;
        }
        $url = $url . (count($param) > 0 ? '?' . http_build_query($param) : '');
        $ch = curl_init();
        if($this->proxy != false && isset($this->proxy[0])){
            curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy[0]);
            curl_setopt($ch, CURLOPT_PROXYPORT,$this->proxy[1]);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($ch, CURLOPT_REFERER, $this->refer);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $set_cookie);
        if (is_file($send_cookie)) {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $send_cookie);
        }
        if(!empty($header)){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        if($gzip !== false){
            curl_setopt($ch, CURLOPT_ENCODING,'gzip');
        }
        $content      = curl_exec($ch);
        $http_code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $redirect_url = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
        curl_close($ch);
        /**
         * 解析返回状态
         */
        if ($http_code == 302) {
            $this->log('302跳转路径:'.$redirect_url,1);
            if ($location == true) {
                if ($this->location > $this->MAX_LOCATION) {
                    return 'location max';
                }
                $this->location ++;
                return self::sendHttpQuick($redirect_url, array('param'=>array(),'location'=>$location,'set_cookie'=>$set_cookie,'send_cookie'=>$send_cookie,'header'=>$header,'gzip' => $gzip));
            } else {
                return $redirect_url;
            }
        }
        $this->location = 0;
        if ($http_code == 200) {
            return $content;
        } else {
            $this->log('GET请求失败,CODE:'.$http_code . ',content:'.$content,3);
            return false;
        }
    }

    public function sendHttpPOST($url, $param = array(),$header = array(),$location = false,$cookie = false,$gzip = false)
    {
        $this->log('POST请求:'.$url,3);
        if(is_array($param)){
            $param = http_build_query($param);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if($param != ''){
            if(strlen($param) < 200){
                $this->log('POST参数:'.$param,2);
            }
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$param);
        }
        if(!empty($header)){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        if($this->proxy != false && isset($this->proxy[0])){
            curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy[0]);
            curl_setopt($ch, CURLOPT_PROXYPORT,$this->proxy[1]);
        }
        if($cookie != false){
            curl_setopt($ch,CURLOPT_COOKIE,$cookie);
        }
        if($gzip !== false){
            curl_setopt($ch, CURLOPT_ENCODING,'gzip');
        }
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($ch, CURLOPT_REFERER, $this->refer);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_path);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        if (is_file($this->cookie_path)) {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_path);
        }
        $content   = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $redirect_url = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
        curl_close($ch);
        if ($http_code == 302) {
            $this->log('302跳转路径:'.$redirect_url,1);
            if ($location == true) {
                if ($this->location > $this->MAX_LOCATION) {
                    return 'location max';
                }
                $this->location ++;
                return self::sendHttpGet($redirect_url, array(), array(), $location);
            } else {
                return $redirect_url;
            }
        }
        $this->location = 0;
        if ($http_code == 200) {
            return $content;
        } else {
            $this->log('POST请求失败,CODE:'.$http_code.',内容:'.$content,3);
            return false;
        }
    }

    public function sendHttpQuick($url,$param = array()){
        $option = array(
            'param'       => array(),
            'header'      => array(),
            'location'    => false,
            'cookie'      => false,
            'set_cookie'  => '',
            'send_cookie' => '',
            'gzip'        => false,
            'method'      => 'GET',
        );
        if(!$url){
            return false;
        }
        $param = array_merge($option,$param);
        if($param['method'] === 'GET'){
            return $this->sendHttpGet($url,$param['param'],$param['location'],$param['set_cookie'],$param['send_cookie'],$param['header'],$param['gzip']);
        }else{
            return $this->sendHttpPOST($url,$param['param'],$param['header'],$param['location'],$param['cookie'],$param['gzip']);
        }
    }
}