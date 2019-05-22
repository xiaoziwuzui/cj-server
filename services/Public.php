<?php

/**
 * Created by PhpStorm.
 * User: whimp
 * Date: 2017/2/23
 * Time: 12:01
 */
class Service_Public
{
    public static function authCode($code_name = 'rand_code')
    {
        session_start();
        /**
         * 生成验证码图片
         */
        header("Content-type: image/PNG ");
        srand((double)microtime() * 1000000);
        $authnum = rand(1000, 9999);
        $_SESSION[$code_name] = $authnum;
        $im = imagecreate(68, 32);
        imagecolorallocate($im, 0, 0, 0);
        $white = imagecolorallocate($im, 255, 255, 255);
        /**
         * 将五位整数验证码绘入图片
         */
        imagefttext($im, 20 , 0, 5, 26, $white, APP_ROOT.'public/assets/fonts/Arial.ttf',$authnum);
//        imagestring($im, 12, 4, 3, $authnum, $white);
        /**
         * 加入干扰象素
         */
        for ($i = 0; $i < 200; $i++) {
            $randcolor = imagecolorallocate($im, rand(0, 255), rand(0, 255), rand(0, 255));
            imagesetpixel($im, rand() % 70, rand() % 30, $randcolor);
        }
        imagepng($im);
        imagedestroy($im);
    }

    /**
     * 获取时间周期
     * @param string $start 开始时间
     * @param int $type 生成类型
     * @return array
     */
    public static function getTimeList($start = '2017-03-13', $type = 1)
    {
        $nowtime = date('Y-m-d');
        $st = 1;
        $timesArr = array();
        while ($st) {
            $c = date("Y-m-d", strtotime("+6 days", strtotime($start)));
            $ret = strtotime($c) - strtotime($nowtime);
            if ($ret <= 518400) {
                if ($type == 1) {
                    $timesArr[] = "{$start}至{$c}";
                } else {
                    $timesArr[] = $start;
                }
                $start = date("Y-m-d", strtotime("+7 days", strtotime($start)));
            } else {
                $st = 0;
            }
        }
        rsort($timesArr);
        return $timesArr;
    }

    /**
     * 获取一个指定日期上周范围
     * @param string $date
     * @author 93307399@qq.com
     * @return array
     */
    public static function getPrevWeekArea($date = ''){
        $time  = strtotime($date);
        $start = mktime(0,0,0,date('m',$time),date('d',$time)-date('w',$time)+1-7,date('Y',$time));
        $end   = mktime(23,59,59,date('m',$time),date('d',$time)-date('w',$time)+7-7,date('Y',$time));
        return array(
            'start' => $start,
            'end'   => $end,
        );
    }

    /**
     * 判断是否能修改提现类型
     * 如果是周一，周二，周三，不能修改提现类型
     * @return int
     */
    public static function editGroupType()
    {
        $week = date('w', CURRENT_TIMESTAMP);
        if ($week == 1 || $week == 2 || $week == 3) {
            return 2;
        } else {
            return 1;
        }
    }

    /**
     * 把秒转化为简单的时间
     * @param int $secs 秒
     * @param int $level 返回级别
     * @param string $step
     * @param bool $show_secs
     * @author xiaojiang432524@163.com
     * @return string
     */
    public static function sec2time($secs,$level = 0,$step = ', ',$show_secs = true)
    {
        $r = '';
        if ($secs >= 86400) {
            $days = floor($secs / 86400);
            $secs = $secs % 86400;
            $r = $days . '天';
            if ($secs > 0) {
                $r .= $step;
            }
        }
        if($level == 1){
            if($r == ''){
                $r = '0天';
            }else{
                $r = str_replace(', ','',$r);
            }
            return $r;
        }
        if ($secs >= 3600) {
            $hours = floor($secs / 3600);
            $secs = $secs % 3600;
            $r .= $hours . '小时';
            if ($secs > 0) {
                $r .= $step;
            }
        }
        if ($secs >= 60) {
            $minutes = floor($secs / 60);
            $secs = $secs % 60;
            $r .= $minutes . '分钟';
            if ($secs > 0) {
                $r .= $step;
            }
        }
        if ($secs > 0 && $show_secs === true) {
            $r .= $secs . '秒';
        }
        return $r;
    }

    /**
     * 格式化并检验查询时间范围
     * @param string $startDate 开始时间
     * @param string $endDate 结束时间
     * @param string $format 时间格式
     * @param string $offset 偏移量
     * @author 93307399@qq.com
     * @return array
     */
    public static function formatWhereDate($startDate = '', $endDate = '',$format = 'Y-m-d',$offset = ''){
        $where = array();
        $startDate = strtotime($startDate);
        $endDate = strtotime($endDate);
        $startDate = $startDate === false ? date($format,($offset == '' ? time() : strtotime($offset))) : date($format,$startDate);
        $endDate = $endDate === false ? date($format) : date($format,$endDate);
        if(strtotime($endDate) < strtotime($startDate)){
            $endDate = $startDate;
        }
        $where['startDate'] = $startDate;
        $where['endDate'] = $endDate;
        return $where;
    }


    /**
     * 获取指定日期所在周的开始结束时间
     * @param string $date
     * @param int $weekStart 开始类型,1:从周一开始,2:从周日开始
     * @author 93307399@qq.com
     * @return array
     */
    public static function getAWeekTimeSlot($date = '', $weekStart = 1) {
        if (! $date){
            $date = date ( "Y-m-d" );
        }
        $w = date ( "w", strtotime ( $date ) ); //取得一周的第几天,星期天开始0-6
        $dn = $w ? $w - $weekStart : 6; //要减去的天数
        $st = date ( "Y-m-d", strtotime ( "$date  - " . $dn . "  days " ) );
        $en = date ( "Y-m-d", strtotime ( "$st  +6  days " ) );
        return array ($st, $en );
    }

    /**
     * 计算升降比率
     * @param int $int_1
     * @param int $int_2
     * @return int
     * @author 93307399@qq.com
     */
    public static function formatCompare($int_1 = 0,$int_2 = 0){
//        $result = ceil(($int_2 / abs($int_1 - $int_2))) * 100;
//        if($int_1 < $int_2){
//            $result = -$result;
//        }
        $result = ceil((($int_1 - $int_2) / $int_2) * 100);
        return intval($result);
    }

    /**
     * 格式化数字千分位显示
     * @param array|int $array
     * @author 93307399@qq.com
     * @return array
     */
    public static function formatInt($array = array()){
        if(is_array($array)){
            foreach ($array as $k=>$v){
                $array[$k] = number_format($v,2);
            }
        }else{
            $array = number_format($array,2);
        }
        return $array;
    }

    /**
     * 显示金额数量
     * @param int|float $int
     * @param int $type
     * @author 93307399@qq.com
     * @return float
     */
    public static function formatMoney($int,$type = 1){
        if($int == 0){
            return 0;
        }
        if($type === 1){
            return floatval($int / 100);
        }else{
            return $int * 100;
        }
    }

    /**
     * 获取加密后的密码
     * @param string $password 密码
     * @return string
     */
    public static function getEncryptPassword($password)
    {
        return md5($password);
    }

    /**
     * 生成图片上传表单
     * @param $name
     * @param string $title
     * @param string $value
     * @param bool $multiple
     * @param string $left
     * @param string $right
     * @param string $label
     * @param string $content
     * @param string $server
     * @param bool $autoRemove
     * @author 93307399@qq.com
     * @return string
     */
    public static function thumbUpload($name,$value = '',$multiple = false,$title = '',$left = '',$right = '',$label = '',$content = '',$server = '/ueditor/uploadimage',$autoRemove = true){
        $value = trim($value);
        if($title === ''){
            $title = '配图：';
        }
        if($left === ''){
            $left = '<div class="form-group col-sm-12">';
        }
        if($right === ''){
            $right = '<div><span class="help-block m-t-xs"><i class="fa fa-info-circle"></i> 可选</span></div></div>';
        }
        if($label === ''){
            $label = '<label>%s</label>';
        }
        if($content === ''){
            $content = '<div>%s</div>';
        }
        if($server === ''){
            $server = '/ueditor/uploadimage';
        }
        $config = array(
            'el'              => '#'.$name,
            'inputName'       => $name,
            'inputVal'        => $value === '' ? false : $value,
            'pick'            => '#picker_'.$name,
            'multiple'        => $multiple,
            'fileNumLimit'    => 10,
            'errorAutoRemove' => $autoRemove,
            'server'          => $server
        );
        $html = array();
        $html[] = '<div id="'.$name.'">';
        if($value !== ''){
            $html[] = '<div id="uploader_'.$name.'"><div class="uploader-list">';
            if($multiple === false){
                $html[] = '<div class="item"><div class="imgWrap"><table class="imgTable"><tbody><tr><td><img src="'.'/'.$value.'" /></td></tr></tbody></table></div><input type="hidden" value="'.$value.'" name="'.$name.'" /></div>';
            }else{
                $values = Service_Public::string2array($value);
                foreach ($values as $k=>$v){
                    $html[] = '<div class="item"><span class="_moveImg"><i class="wb-close"></i></span> <div class="imgWrap"><table class="imgTable"><tbody><tr><td><img src="'.'/'.$v.'" /></td></tr></tbody></table></div><input type="hidden" value="'.$v.'" name="'.$name.'[]" /></div>';
                }
            }
            $html[] = '</div><div id="picker_'.$name.'">选择图片</div></div>';
        }
        $html[] = '</div>';
//        if(!defined('_initImageScript')){
//            $html[] = '<link rel="stylesheet" href="'.FConfig::get('global.ssl_domain').FConfig::get('global.ui_assets').'plugins/ueditor/third-party/webuploader/webuploader.css">';
//            $html[] = '<script type="text/javascript" src="'.FConfig::get('global.ssl_domain').FConfig::get('global.ui_assets').'plugins/ueditor/third-party/webuploader/webuploader.min.js"></script>';
//        }
        $html[] = '<script type="text/javascript">var '.$name.'Upload = new imageUpload('.json_encode($config).');</script>';
        return $left . sprintf($label,$title) . sprintf($content,implode('',$html)) . $right;
    }

    /**
     * 替换掉emoji表情
     * @param $text
     * @param string $replaceTo
     * @return mixed|string
     */
    public static function filterEmoji($text, $replaceTo = '')
    {
        $text = trim($text);
        // Match Emoticons
        $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clean_text = preg_replace($regexEmoticons, $replaceTo, $text);
        // Match Miscellaneous Symbols and Pictographs
        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clean_text = preg_replace($regexSymbols, $replaceTo, $clean_text);
        // Match Transport And Map Symbols
        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clean_text = preg_replace($regexTransport, $replaceTo, $clean_text);
        // Match Miscellaneous Symbols
        $regexMisc = '/[\x{2600}-\x{26FF}]/u';
        $clean_text = preg_replace($regexMisc, $replaceTo, $clean_text);
        // Match Dingbats
        $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        $clean_text = preg_replace($regexDingbats, $replaceTo, $clean_text);
        return $clean_text;
    }

    /**
     * 过滤非法查询字符
     * @param $string
     * @author 93307399@qq.com
     * @return mixed|string
     */
    public static function fixSql($string){
        if($string == ''){
            return '';
        }
        $string = str_replace(array('-','select','insert','update','delete','%',"'"),'',$string);
        return $string;
    }

    /**
     * 统计图片数量
     * @param string $html
     * @author 93307399@qq.com
     * @return int
     */
    public static function countImgTag($html){
        $int = 0;
        preg_match_all('/<img[^>]*src=[\'"]?([^>\'"\s]*)[\'"]?[^>]*>/i', $html, $match);
        if($match){
            $int = count($match[1]);
        }
        return $int;
    }

    /**
     * 统计中文字数
     * @param string $string
     * @author 93307399@qq.com
     * @return int
     */
    public static function countZhWord($string = ''){
        //去除html标签
        $string = strip_tags($string);
        //过滤掉空格
        $string = str_replace(' ','',$string);
        $string = str_replace('　','',$string);
        //过滤标点符号
//        $string = str_replace(',','',$string);
//        $string = str_replace('.','',$string);
//        $string = str_replace('，','',$string);
//        $string = str_replace('。','',$string);
        $string = str_replace('&nbsp;','',$string);
        return mb_strlen($string);
    }

    /**
     * 统计中文字数
     * @param string $string
     * @author 93307399@qq.com
     * @return int
     */
    public static function fixTitle($string = ''){
        //去除html标签
        $string = strip_tags($string);
        //过滤掉空格
        $string = str_replace(' ','',$string);
        //过滤标点符号
        $string = str_replace(',','',$string);
        $string = str_replace('.','',$string);
        $string = str_replace('，','',$string);
        $string = str_replace('。','',$string);
        $string = str_replace('、','',$string);
        $string = str_replace(':','',$string);
        $string = str_replace('：','',$string);
        $string = str_replace('&nbsp;','',$string);
        return $string;
    }

    /**
     * 生成where条件
     * @param array $config
     * @param array|bool $data
     * @param array $output
     * @author 93307399@qq.com
     * @return array
     */
    public static function getWhere($config = array(),$data = false,&$output = array()){
        $where = array();
        $parser = array();
        foreach ($config as $key=>$value){
            $conf = explode('|',$value);
            foreach ($conf as $c){
                if(isset($parser[$key])) continue;
                $type      = '';
                $fix_value = '';
                $value     = false;
                $fix = explode(':',$c);
                if(count($fix) != 2) continue;
                $field_config = explode('/',$fix[1]);
                $field = $field_config[0];
                if(count($field_config) == 2){
                    $child = explode('=',$field_config[1]);
                    $type = $child[0];
                    if(count($child) == 2){
                        $fix_value = $child[1];
                    }
                }
                if($fix[0] == 'd'){
                    if($data !== false){
                        $value = intval($data[$key]);
                    }else{
                        $value = intval(FRequest::getInt($key));
                    }
                }
                if($fix[0] == 't'){
                    if($data !== false){
                        $value = strtotime($data[$key]);
                    }else{
                        $value = strtotime(FRequest::getString($key));
                        if($value !== false && ($type == 'lt' || $type == 'lte')){
                            $value = strtotime(date('Y-m-d').' 23:59');
                        }
                    }
                }
                if($fix[0] == 'tt'){
                    if($data !== false){
                        $value = strtotime($data[$key]);
                    }else{
                        $value = strtotime(FRequest::getString($key));
                        if($value !== false && ($type == 'lt' || $type == 'lte')){
                            $value = strtotime(date('Y-m-d H:i'));
                        }
                    }
                }
                if($fix[0] == 's'){
                    if($data !== false){
                        $value = $data[$key];
                    }else{
                        $value = FRequest::getString($key);
                    }
                }
                if($value !== false && $value != ''){
                    $output[$key] = $value;
                    $parser[$key] = 1;
                    switch ($type){
                        case 'gt':
                        case 'gte':
                        case 'lt':
                        case 'lte':
                            $where[$field][$type] = $value;
                            break;
                        case 'like':
                            $where[$field] = array('like' => $value);
                            break;
                        case 'max':
                            if($value > 0 && $value < intval($fix_value)){
                                $where[$field] = $value;
                            }
                            break;
                        case 'in':
                            $in_array = explode(',',$fix_value);
                            if(in_array($value,$in_array)){
                                $where[$field] = $value;
                            }
                            break;
                        default :
                            $where[$field] = $value;
                    }
                }
            }
        }
        return $where;
    }

    /**
     * 生成排序URI
     * @param $uri
     * @param $search
     * @param $key
     * @author 93307399@qq.com
     * @return string
     */
    public static function createOrderUri($uri,$search,$key){
        if($key != ''){
            if($search['order_field'] == $key){
                $search['order_type']  = $search['order_type'] == 'asc' ? 'desc' : 'asc';
            }else{
                $search['order_type'] = 'desc';
            }
            $search['order_field'] = $key;
        }
        return $uri .'?' . http_build_query($search);
    }

    /**
     * 格式化过期时间显示
     * @param $time
     * @author 93307399@qq.com
     * @return string
     */
    public static function formatExpire($time){
        $result = date('Y-m-d H',$time).':59:59';
        return $result;
    }

    /**
     * 生成编辑器
     * @param array $config
     * @author 93307399@qq.com
     * @return string
     */
    public static function createEditor($config = array()){
        $code    = array();
        $skin    = FConfig::get('global.ui_assets');
        $version = FConfig::get('global.editor_version');
        $ext     = true;
        if(!defined('__init_editor')){
            $code[] = '<script type="text/javascript" src="'.$skin.'plugins/neditor/neditor.release.js?v='.$version.'"></script>';
        }
        define('__init_editor',1);
        if(isset($config['toolbars'])){
            $config['toolbars'] = array($config['toolbars']);
        }
        if(!isset($config['serverUrl'])){
            $config['serverUrl'] = '/ueditor/action';
        }
        if(!isset($config['UEDITOR_HOME_URL'])){
            $config['UEDITOR_HOME_URL'] = $skin.'plugins/ueditor/';
        }
        if(!isset($config['themePath'])){
            $config['themePath'] = $skin.'plugins/neditor/themes/';
        }
        if(!isset($config['theme'])){
            $config['theme'] = 'notadd';
        }
        if(!isset($config['id'])){
            $config['id'] = 'content';
            $ext = false;
        }
        if(isset($config['minWord'])){
            $config['wordCount'] = true;
            if(!isset($config['wordCountMsg'])){
                $config['wordCountMsg'] = '当前已输入 {#count} 个字';
            }
        }else{
            $config['wordCount'] = false;
        }
        if(isset($config['maximumWords'])){
            if(isset($config['wordCountMsg'])){
                $config['wordCountMsg'] .= '，最多可以输入'.$config['maximumWords'].'个字，还可以输入 {#leave} 个字';
            }else{
                $config['wordCountMsg'] = '最多可以输入'.$config['maximumWords'].'个字，还可以输入 {#leave} 个字';
            }
        }
        $id = $config['id'];
        unset($config['id']);
        $config['version'] = $version;
        $jsVar = 'Editor' . ($ext === true ? '_' . $id : '');

        $code[] = '<script type="text/javascript">';
        $code[] = 'var '.$jsVar.' = unit.neditor('.json_encode($config).');';
        if(!isset($config['autoRender']) || $config['autoRender'] == true){
            $code[] = '$(document).ready(function (e) {'.$jsVar.'.render("'.$id.'");});';
        }
        $code[] = '</script>';
        return implode(chr(10),$code);
    }

    /**
     * 获取header头
     * @param string $url
     * @param string $refer
     * @param string $mode
     * @author 93307399@qq.com
     * @return array|string
     */
    public static function getHeader($url,$refer = '',$mode = 'header'){
        $ip = FRequest::getClientIP();
        $headerArr = array(
            'CLIENT-IP:' . $ip,
            'X-FORWARDED-FOR:' . $ip,
        );
        if($refer == ''){
            $urlInfo = Flib::parseHost($url);
            $refer = 'http://'.$urlInfo[1];
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPGET, true);
//        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0); //强制协议为1.0
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect: ')); //头部要送出'Expect: '
//        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 ); //强制使用IPV4协议解析域名
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        //不对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArr);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.84 Safari/537.36');
        curl_setopt($ch, CURLOPT_REFERER, $refer);
        curl_setopt($ch, CURLOPT_URL, $url);
        $values = '';
        if($mode == 'header'){
            curl_setopt($ch, CURLOPT_NOBODY,true);
            curl_setopt($ch, CURLOPT_HEADER, 1); //返回response头部信息
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            $result = curl_exec($ch);
            curl_close($ch);
            $result = explode("\r\n",$result);
            $values = array();
            foreach ($result as $k=>$v){
                if(strpos($v,': ')){
                    $vs = explode(': ',$v);
                    $values[$vs[0]] = $vs[1];
                }else{
                    $values[$k] = $v;
                }
            }
        }else if($mode == 'html'){
            $values = curl_exec($ch);
            curl_close($ch);
        }
        return $values;
    }

    public static function parseImgRefer($imgUrl){
        $imgUrl = str_replace("&amp;", "&", $imgUrl);

        //http开头验证
        if (strpos($imgUrl, "http") !== 0) {
            echo ("ERROR_HTTP_LINK");
            return false;
        }

        preg_match('/(^https*:\/\/[^:\/]+)/', $imgUrl, $matches);
        $host_with_protocol = count($matches) > 1 ? $matches[1] : '';


        preg_match('/^https*:\/\/(.+)/', $host_with_protocol, $matches);
        $host_without_protocol = count($matches) > 1 ? $matches[1] : '';

        // 此时提取出来的可能是 ip 也有可能是域名，先获取 ip
        $ip = gethostbyname($host_without_protocol);
        // 判断是否是私有 ip
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
            echo ("INVALID_IP");
            return false;
        }

        $urlInfo = Flib::parseHost($imgUrl);
        if($urlInfo[0] == 'timgsa.baidu.com'){
            $url = parse_url($imgUrl);
            parse_str($url['query'],$query);
            if(isset($query['src'])){
                $imgUrl = $query['src'];
                $urlInfo = Flib::parseHost($imgUrl);
            }
        }
        $refer = 'http://'.$urlInfo[1];

        $img_refer = FConfig::get('img_refer');
        if(isset($img_refer[$urlInfo[0]])){
            $refer = $img_refer[$urlInfo[0]];
        }
        //获取请求头并检测死链
        $heads = self::getHeader($imgUrl,$refer);
        if (!(stristr($heads[0], "200") && stristr($heads[0], "OK"))) {
            echo ("ERROR_DEAD_LINK");
            return false;
        }
        //格式验证(扩展名验证和Content-Type验证)
        $fileType = strtolower(strrchr($imgUrl, '.'));
        if (!isset($heads['Content-Type']) || !stristr($heads['Content-Type'], "image")) {
            echo ("ERROR_HTTP_CONTENTTYPE");
            return false;
        }

        echo 'success';
        return true;
    }

    /**
     * 根据响应头获取图片类型
     * @param $type
     * @author 93307399@qq.com
     * @return mixed|string
     */
    public static function getFileTypeByMine($type){
        $array = array(
            'image/bmp' => 'bmp'  ,
            'image/gif' => 'gif'  ,
            'image/x-icon' => 'ico'  ,
            'image/ief' => 'ief'  ,
            'image/jp2' => 'jp2'  ,
            'image/jpeg' => 'jpg'  ,
            'image/x-portable-bitmap' => 'pbm'  ,
            'image/x-portable-graymap' => 'pgm'  ,
            'image/pict' => 'pic'  ,
            'image/png' => 'png'  ,
            'image/x-portable-anymap' => 'pnm'  ,
            'image/x-macpaint' => 'pnt'  ,
        );
        if(isset($array[strtolower($type)])){
            return '.'.$array[strtolower($type)];
        }else{
            return '';
        }
    }

    public static function formatTime($time) {
        $fix = '前';
        if($time > time()){
            $fix = '后';
            $t = $time - time();
        }else{
            $t = time()-$time;
        }
        $f = array(
            '31536000' => '年',
            '2592000'  => '个月',
            '604800'   => '星期',
            '86400'    => '天',
            '3600'     => '小时',
            '60'       => '分钟',
            '1'        => '秒'
        );
        foreach ($f as $k=>$v)    {
            if (0 != $c = floor($t/(int)$k)) {
                return $c.$v.$fix;
            }
        }
        return '刚刚';
    }

    /**
     * 截取指定长度的中文字符
     * @param string $string 原字符串
     * @param int $length 长度
     * @param string $suffix 要添加的后缀
     * @param int $start 开始位置
     * @return string
     */
    public static function dsubstr($string, $length, $suffix = '', $start = 0) {
        $string = html_entity_decode($string);
        if($start) {
            $tmp = self::dsubstr($string, $start);
            $string = substr($string, strlen($tmp));
        }
        $len = strlen($string);
        if($len <= $length) {
            return $string;
        }
        $string = str_replace(array('&quot;', '&lt;', '&gt;'), array('"', '<', '>'), $string);
        $length = $length - strlen($suffix);
        $str = '';
        $n = $tn = $noc = 0;
        while($n < $len)	{
            $t = ord($string{$n});
            if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1; $n++; $noc++;
            } elseif(194 <= $t && $t <= 223) {
                $tn = 2; $n += 2; $noc += 2;
            } elseif(224 <= $t && $t <= 239) {
                $tn = 3; $n += 3; $noc += 2;
            } elseif(240 <= $t && $t <= 247) {
                $tn = 4; $n += 4; $noc += 2;
            } elseif(248 <= $t && $t <= 251) {
                $tn = 5; $n += 5; $noc += 2;
            } elseif($t == 252 || $t == 253) {
                $tn = 6; $n += 6; $noc += 2;
            } else {
                $n++;
            }
            if($noc >= $length) break;
        }
        if($noc > $length) {
            $n -= $tn;
        }
        $str = substr($string, 0, $n);
        $str = str_replace(array('"', '<', '>'), array('&quot;', '&lt;', '&gt;'), $str);
        return $str == $string ? $str : $str.$suffix;
    }

    /**
     * 生成查询URI
     * @param $uri
     * @param $search
     * @param $key
     * @param $value
     * @author 93307399@qq.com
     * @return string
     */
    public static function createSearchUri($uri,$search,$key,$value){
        if($key != ''){
            $search[$key] = $value;
        }
        return $uri .'?' . http_build_query($search);
    }

    /**
     * 返回经stripslashes处理过的字符串或数组
     * @param $string string|array 需要处理的字符串或数组
     * @return mixed
     */
    public static function new_stripslashes($string) {
        if(!is_array($string)) return stripslashes($string);
        foreach($string as $key => $val) $string[$key] = self::new_stripslashes($val);
        return $string;
    }

    /**
     * 将字符串转换为数组
     * @param	string	$data	字符串
     * @return	array	返回数组格式，如果，data为空，则返回空数组
     */
    public static function string2array($data) {
        $data = trim($data);
        $array = array();
        if($data == '') return array();
        if(strpos($data, 'array')===0){
            @eval("\$array = $data;");
        }else{
            if(strpos($data, '{\\')===0) $data = stripslashes($data);
            $array=json_decode($data,true);
        }
        return $array;
    }

    /**
     * 将数组转换为字符串
     * @param array $data 数组
     * @param int $is_formData
     * @return string
     */
    public static function array2string($data, $is_formData = 1) {
        if($data == '' || empty($data)) return '';
        if($is_formData) $data = self::new_stripslashes($data);
        if (version_compare(PHP_VERSION,'5.3.0','<')){
            return addslashes(json_encode($data));
        }else{
            return addslashes(json_encode($data,JSON_FORCE_OBJECT));
        }
    }

    /**
     * 获取缩略图列表或第一张图
     * @param $thumb
     * @param bool $first
     * @author 93307399@qq.com
     * @return array|mixed
     */
    public static function getThumb($thumb,$first = false){
        $img = array();
        if(substr($thumb,0,6) == 'upload'){
            $img[] = $thumb;
        }else{
            $img = self::string2array($thumb);
        }
        if($first !== false){
            return $img[0];
        }else{
            return $img;
        }
    }

    /**
     * 判断是否为微信浏览器
     * @author 93307399@qq.com
     * @return bool
     */
    public static function is_weixin() {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return true;
        }
        return false;
    }

    public static function is_ios()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 解析命令行输入的命令
     * @return array
     */
    public static function parser_argparam(){
        global $argv,$_F;
        $result = array();
        if($_F['run_in'] != 'shell'){
            return $result;
        }
        $argv_zero = $argv[0];
        unset($argv[0]);
        if(count($argv) > 0){
            $new_arg   = array_values($argv);
            $arg_total = count($new_arg);
            for($i=0;$i<count($new_arg);$i++){
                $v = $new_arg[$i];
                if(substr($v,0,1) == '-'){
                    $key = substr($v,1);
                    $result[$key] = '';
                    if(($i + 1 < $arg_total) && substr($new_arg[$i+1],0,1) != '-'){
                        $result[$key] = $new_arg[$i+1];
                        $i++;
                    }
                }else{
                    $result[$v] = $v;
                }
            }
            unset($new_arg,$arg_total,$v,$i,$key);
        }
        $argv[0] = $argv_zero;
        return $result;
    }

    /**
     * 根据路由配置生成URL
     * @param string $c 控制器名 有模块时 m/c
     * @param string $a 方法名
     * @param array $param 参数数组
     * @param bool $http 是否添加前缀
     * @return string
     */
    public static function url($c = 'main', $a = 'index', $param = array(), $http = false)
    {
        global $X, $controller, $action;
        $_routers = FConfig::get('router');
        if (is_array($c)) {
            $param = $c;
            $c = isset($param['c']) ? $param['c'] : $controller;
            unset($param['c']);
            $a = isset($param['a']) ? $param['a'] : $action;
            unset($param['a']);
        }
        if (!isset($param['hash']) && isset($_GET['hash'])) {
            $param['hash'] = trim($_GET['hash']);
        }
        $params = empty($param) ? '' : '&' . http_build_query($param);
        if (strpos($c, '/') !== false) {
            list($m, $c) = explode('/', $c);
            $route       = "$m/$c/$a";
            $url         = $_SERVER["SCRIPT_NAME"] . "?m=$m&c=$c&a=$a$params";
        } else {
            $m     = '';
            $route = "$c/$a";
            $url   = $_SERVER["SCRIPT_NAME"] . "?c=$c&a=$a$params";
        }
        if($m == '') $m = 'material';
        if (!empty($_routers[$m])) {
            static $urlArray = array();
            if (!isset($urlArray[$url])) {
                foreach ($_routers[$m] as $rule => $mapper) {
                    $mapper = '/' . str_ireplace(array('/', '<a>', '<c>', '<m>'), array('\/', '(?<a>\w+)', '(?<c>\w+)', '(?<m>\w+)'), $mapper) . '/i';
                    if (preg_match($mapper, $route, $matchs)) {
                        $urlArray[$url] = str_ireplace(array('<a>', '<c>', '<m>'), array($a, $c, $m), $rule);
                        if (!empty($param)) {
                            $_args = array();
                            foreach ($param as $argkey => $arg) {
                                $count = 0;
                                $urlArray[$url] = str_ireplace('<' . $argkey . '>', $arg, $urlArray[$url], $count);
                                if (!$count) $_args[$argkey] = $arg;
                            }
                            $urlArray[$url] = preg_replace('/<\w+>/', '', $urlArray[$url]) . (!empty($_args) ? '?' . http_build_query($_args) : '');
                        }

                        if (0 !== stripos($urlArray[$url], 'http://')) {
                            if ($http === false) {
                                $urlArray[$url] = $X['config']['dir_path'] . $urlArray[$url];
                            } else {
                                $urlArray[$url] = 'http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER["SCRIPT_NAME"]), '/\\') . '/' . $urlArray[$url];
                            }
                        }
                        $rule = str_ireplace(array('<m>', '<c>', '<a>'), '', $rule);
                        if (count($param) == preg_match_all('/<\w+>/is', $rule, $_match)) {
                            return $urlArray[$url];
                        }
                        break;
                    }
                }
                return isset($urlArray[$url]) ? $urlArray[$url] : $url;
            }
            return $urlArray[$url];
        }
        return $url;
    }

    /**
     * 记录恶意访问日志
     * @param int $type 类型,1:试探404,2:错误路径
     * @author 93307399@qq.com
     */
    public static function failLog($type = 1){
        global $_F;
        FLogger::write(implode("|",array(
            'TYPE:'.$type,
            'IP:'.FRequest::get_client_ip(0,true),
            'URL:'.$_F['uri'],
            'COOKIE:'.json_encode($_COOKIE),
        )),'fail_log');
    }

    /**
     * 处理编码问题
     * @param $string
     * @param string $encode
     * @author 93307399@qq.com
     * @return string
     */
    public static function formatEncode($string,$encode = 'UTF-8'){
        $str_encode = mb_detect_encoding($string, array('ASCII','UTF-8','GB2312','GBK','BIG5'));
        if ($str_encode != $encode){
            $string = iconv($str_encode,$encode,$string);
        }
        unset($str_encode);
        return $string;
    }

    /**
     * 获取域名基本信息
     * @param string $url
     * @author 93307399@qq.com
     * @return array|bool
     */
    public static function getBaseDomain($url=''){
        if(!$url){
            return false;
        }
        $state_domain = array(
            'al','dz','af','ar','ae','aw','om','az','eg','et','ie','ee','ad','ao','ai','ag','at','au','mo','bb','pg','bs','pk','py','ps','bh','pa','br','by','bm','bg','mp','bj','be','is','pr','ba','pl','bo','bz','bw','bt','bf','bi','bv','kp','gq','dk','de','tl','tp','tg','dm','do','ru','ec','er','fr','fo','pf','gf','tf','va','ph','fj','fi','cv','fk','gm','cg','cd','co','cr','gg','gd','gl','ge','cu','gp','gu','gy','kz','ht','kr','nl','an','hm','hn','ki','dj','kg','gn','gw','ca','gh','ga','kh','cz','zw','cm','qa','ky','km','ci','kw','cc','hr','ke','ck','lv','ls','la','lb','lt','lr','ly','li','re','lu','rw','ro','mg','im','mv','mt','mw','my','ml','mk','mh','mq','yt','mu','mr','us','um','as','vi','mn','ms','bd','pe','fm','mm','md','ma','mc','mz','mx','nr','np','ni','ne','ng','nu','no','nf','na','za','aq','gs','eu','pw','pn','pt','jp','se','ch','sv','ws','yu','sl','sn','cy','sc','sa','cx','st','sh','kn','lc','sm','pm','vc','lk','sk','si','sj','sz','sd','sr','sb','so','tj','tw','th','tz','to','tc','tt','tn','tv','tr','tm','tk','wf','vu','gt','ve','bn','ug','ua','uy','uz','es','eh','gr','hk','sg','nc','nz','hu','sy','jm','am','ac','ye','iq','ir','il','it','in','id','uk','vg','io','jo','vn','zm','je','td','gi','cl','cf','cn','yr','com','arpa','edu','gov','int','mil','net','org','biz','info','pro','name','museum','coop','aero','xxx','idv','me','mobi','asia','ax','bl','bq','cat','cw','gb','jobs','mf','rs','su','sx','tel','travel'
        );

        if(!preg_match("/^http/is", $url)){
            $url="http://".$url;
        }

        $result = array(
            'domain' => '',
            'host'   => '',
            'scheme' => '',
        );
        $url_parse      = parse_url(strtolower($url));
        $urlarr         = explode('.', $url_parse['host']);
        $count          = count($urlarr);
        $result['host']   = $url_parse['host'];
        $result['scheme'] = $url_parse['scheme'];
        if($count <= 2){
            $result['domain'] = $url_parse['host'];
        }elseif($count > 2){
            $last   = array_pop($urlarr);
            $last_1 = array_pop($urlarr);
            $last_2 = array_pop($urlarr);

            $result['domain'] = $last_1.'.'.$last;

            if(in_array($last, $state_domain)){
                $result['domain'] = $last_1.'.'.$last;
            }

            if(in_array($last_1, $state_domain)){
                $result['domain'] = $last_2.'.'.$last_1.'.'.$last;
            }
            unset($last,$last_1,$last_2);
        }
        unset($url_parse,$urlarr,$count,$state_domain);
        return $result;
    }

    /**
     * 格式化配置项
     * @param $config
     * @author 93307399@qq.com
     * @return mixed
     */
    public static function formatConfig($config){
        foreach ($config as $k=>$v){
            $item = array();
            parse_str($v,$item);
            switch ($item['type']){
                case 'checkbox':
                    $item['value'] = explode(',',$item['value']);
                case 'radio':
                case 'select':
                    $option = array();
                    foreach (explode(',',$item['options']) as $ok=>$ov){
                        $child_op = explode('|',$ov);
                        $option[$child_op[0]] = $child_op[1];
                    }
                    $item['options'] = $option;
                    break;
            }
            $config[$k] = $item;
        }
        return $config;
    }

    /**
     * 文章内容过滤方法
     * @param $data
     * @author 93307399@qq.com
     * @return mixed
     */
    public static function fixContent($data){
        //过滤非法标签
        $data = preg_replace("/<a[^>]*>(.*?)<\/a>/is", "$1", $data);
        $data = preg_replace("/<h1[^>]*>(.*?)<\/h1>/is", "<p>$1</p>", $data);
        $data = preg_replace("/<h2[^>]*>(.*?)<\/h2>/is", "<p>$1</p>", $data);
        $data = preg_replace("/<h3[^>]*>(.*?)<\/h3>/is", "<p>$1</p>", $data);
        $data = preg_replace("/<h4[^>]*>(.*?)<\/h4>/is", "<p>$1</p>", $data);
        $data = preg_replace("/<h5[^>]*>(.*?)<\/h5>/is", "<p>$1</p>", $data);
        $data = preg_replace("/<h6[^>]*>(.*?)<\/h6>/is", "<p>$1</p>", $data);
        $data = preg_replace('/height="(\d+)"/','',$data);
        /**
         * 处理图片之后连接文字
         */
        $data = str_replace('　', '',$data);
        $data = str_replace('&nbsp;', '',$data);
        $data = str_replace('<p><br></p><p><br></p>', '',$data);
        $data = preg_replace('/<img src=\"data\:(.*?)"\/>/i', '',$data);
        $data = preg_replace('/<img(.*?)>(.*?)<\/p>/i', '<img$1></p><p>$2</p>',$data);
        $data = preg_replace('/<img(.*?)><\/p>/i', '</p><p><img$1></p>',$data);
        $data = str_replace('<p><br/></p>', '',$data);
        $data = str_replace('<p>&nbsp;</p>', '',$data);
        $data = str_replace('<p></p>', '',$data);
        return $data;
    }
}