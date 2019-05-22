<?php

/**
 * Created by JetBrains PhpStorm.
 * User: 93307399@qq.com
 * Date: 12-7-18
 * Time: 上午11: 32
 * 素材系统统一上传处理类
 */
class Service_Upload
{
    private $db;         //数据库配置
    private $fileField;  //文件域名
    private $file;       //文件上传对象
    private $config;     //配置信息
    private $oriName;    //原始文件名
    private $fileName;   //新文件名
    private $fullName;   //完整文件名,即从当前配置目录开始的URL
    private $filePath;   //完整文件名,即从当前配置目录开始的URL
    private $fileHash;   //文件获取TOKEN
    private $fileSize;   //文件大小
    private $fileType;   //文件类型
    private $stateInfo;  //上传状态信息,
    private $param;      //前置参数内容,
    private $chunked;    //是否检测重复文件,
    private $stateMap = array( //上传状态映射表，国际化用户需考虑此处数据的国际化
        "SUCCESS", //上传成功标记，在UEditor中内不可改变，否则flash判断会出错
        "文件大小超出 upload_max_filesize 限制",
        "文件大小超出 MAX_FILE_SIZE 限制",
        "文件未被完整上传",
        "没有文件被上传",
        "上传文件为空",
        "ERROR_TMP_FILE"           => "临时文件错误",
        "ERROR_TMP_FILE_NOT_FOUND" => "找不到临时文件",
        "ERROR_SIZE_EXCEED"        => "文件大小超出网站限制",
        "ERROR_TYPE_NOT_ALLOWED"   => "文件类型不允许",
        "ERROR_CREATE_DIR"         => "目录创建失败",
        "ERROR_DIR_NOT_WRITEABLE"  => "目录没有写权限",
        "ERROR_FILE_MOVE"          => "文件保存时出错",
        "ERROR_FILE_NOT_FOUND"     => "找不到上传文件",
        "ERROR_WRITE_CONTENT"      => "写入文件内容错误",
        "ERROR_UNKNOWN"            => "未知错误",
        "ERROR_DEAD_LINK"          => "链接不可用",
        "ERROR_HTTP_LINK"          => "链接不是http链接",
        "ERROR_HTTP_CONTENTTYPE"   => "链接contentType不正确",
        "ERROR_IMG_WIDTH_MIN"      => "图片尺寸太小,无法使用",
        "INVALID_URL"              => "非法 URL",
        "INVALID_IP"               => "非法 IP",
        "FILE_EXIST"               => "存在相同文件",
    );

    /**
     * 构造函数
     * @param string $fileField 表单名称
     * @param array $config 配置项
     * @param string $type 是否解析base64编码，可省略。若开启，则$fileField代表的是base64编码的字符串表单名
     * @param string $db 数据库配置
     * @param bool $chunked 是否检查重复文件
     */
    public function __construct($fileField, $config, $type = "upload",$db = '',$chunked = false)
    {
        $this->db        = $db;
        $this->fileField = $fileField;
        $this->config    = $config;
        $this->type      = $type;
        $this->chunked   = $chunked;
        $this->param     = array(
            'uid'    => isset($_GET['uid']) ? intval($_GET['uid']) : 0,
            'type'   => isset($_GET['type']) ? intval($_GET['type']) : 1,
            'source' => isset($_GET['source']) ? intval($_GET['source']) : 1,
        );
        if($type !== 'waterMark'){
            if ($type == "remote") {
                $this->saveRemote();
            } else if ($type == "base64") {
                $this->upBase64();
            } else {
                $this->upFile();
            }
        }
    }

    /**
     * 上传文件的主处理方法
     * @return mixed
     */
    private function upFile()
    {
        global $_F;
        $file = $this->file = $_FILES[$this->fileField];
        if (!$file) {
            $this->stateInfo = $this->getStateInfo("ERROR_FILE_NOT_FOUND");
            return false;
        }
        if ($this->file['error']) {
            $this->stateInfo = $this->getStateInfo($file['error']);
            return false;
        } else if (!file_exists($file['tmp_name'])) {
            $this->stateInfo = $this->getStateInfo("ERROR_TMP_FILE_NOT_FOUND");
            return false;
        } else if (!is_uploaded_file($file['tmp_name'])) {
            $this->stateInfo = $this->getStateInfo("ERROR_TMPFILE");
            return false;
        }

        $this->oriName = $file['name'];
        $this->fileSize = $file['size'];
        $this->fileType = $this->getFileExt();
        $this->fullName = $this->getFullName();
        $this->filePath = $this->getFilePath();
        $this->fileName = $this->getFileName();

        $dirname = dirname($this->filePath);
        //检查文件大小是否超出限制
        if (!$this->checkSize()) {
            $this->stateInfo = $this->getStateInfo("ERROR_SIZE_EXCEED");
            return false;
        }

        //检查是否不允许的文件格式
        if (!$this->checkType()) {
            $this->stateInfo = $this->getStateInfo("ERROR_TYPE_NOT_ALLOWED");
            return false;
        }
        //处理图片宽度限制.
        if($this->chunked === true) {
            $checkSize = true;
        }else{
            $checkSize = false;
        }
        if($checkSize && in_array($this->getFileExt(),array(".png", ".jpg", ".jpeg", ".gif", ".bmp"))){
            $imgInfo = getimagesize($file['tmp_name']);
            if(intval($imgInfo[0]) <= intval(FConfig::get('global.min_img_width'))){
                $this->stateInfo = $this->getStateInfo("ERROR_IMG_WIDTH_MIN");
                return false;
            }
        }
        /**
         * ==================
         * 文件上传去重处理开始
         * ==================
         */
        $Table = new FTable('files','',$this->db);
        $this->fileHash  = md5(FConfig::get('global.file_key') . md5_file($_FILES[$this->fileField]['tmp_name']));
        $info  = $Table->fields('path,name,hash')->where(array('hash' => $this->fileHash))->find();
        if ($info) {
            if (is_file(APP_ROOT .'public/'. $info['path'])) {
                if($this->chunked === true){
                    $this->stateInfo = $this->getStateInfo('FILE_EXIST');
                    return false;
                }
                $this->stateInfo = $this->stateMap[0];
                $this->fullName  = $info['path'];
                $this->filePath  = APP_ROOT .'public/' . $info['path'];
                $this->fileName  = $info['name'];
                @unlink($_FILES[$this->fileField]['tmp_name']);
                return true;
            } else {
                $Table->where(array('hash' => $this->fileHash))->update(array(
                    'uid'         => $this->param['uid'],
                    'type'        => $this->param['type'],
                    'create_time' => time(),
                    'path'        => $this->fullName,
                    'size'        => $this->fileSize,
                    'name'        => $this->fileName,
                    'ext'         => $this->fileType,
                    'ip'          => FRequest::getClientIP()
                ));
                $dirname = dirname($this->filePath);
            }
        } else {
            $Table->insert(array(
                'uid'         => $this->param['uid'],
                'type'        => $this->param['type'],
                'hash'        => $this->fileHash,
                'create_time' => time(),
                'path'        => $this->fullName,
                'size'        => $this->fileSize,
                'name'        => $this->fileName,
                'ext'         => $this->fileType,
                'ip'          => FRequest::getClientIP()
            ));
        }
        unset($info,$Table);
        /**
         * ==================
         * 文件上传去重处理结束
         * ==================
         */
        //创建目录失败
        if (!is_dir($dirname) && !mkdir($dirname)) {
            $this->stateInfo = $this->getStateInfo("ERROR_CREATE_DIR");
            return false;
        } else if (!is_writeable($dirname)) {
            $this->stateInfo = $this->getStateInfo("ERROR_DIR_NOT_WRITEABLE");
            return false;
        }
        //移动文件
        if (!(move_uploaded_file($file["tmp_name"], $this->filePath) && file_exists($this->filePath))) {
            $this->stateInfo = $this->getStateInfo("ERROR_FILE_MOVE");
        } else {
            $this->stateInfo = $this->stateMap[0];
        }
        return true;
    }

    /**
     * 处理base64编码的图片上传
     * @return mixed
     */
    private function upBase64()
    {
        $base64Data = $_POST[$this->fileField];
        $img = base64_decode($base64Data);

        $this->oriName = $this->config['oriName'];
        $this->fileSize = strlen($img);
        $this->fileType = $this->getFileExt();
        $this->fullName = $this->getFullName();
        $this->filePath = $this->getFilePath();
        $this->fileName = $this->getFileName();
        $dirname = dirname($this->filePath);

        //检查文件大小是否超出限制
        if (!$this->checkSize()) {
            $this->stateInfo = $this->getStateInfo("ERROR_SIZE_EXCEED");
            return false;
        }
        /**
         * ==================
         * 文件上传去重处理开始
         * ==================
         */
        $Table = new FTable('files','',$this->db);
        $this->fileHash  = md5(FConfig::get('global.file_key') . md5($base64Data));
        $info  = $Table->fields('path,name,hash')->where(array('hash'=>$this->fileHash))->find();
        if ($info) {
            if (is_file(APP_ROOT  .'public/'. $info['path'])) {
                if($this->chunked === true){
                    $this->stateInfo = $this->getStateInfo('FILE_EXIST');
                    return false;
                }                $this->stateInfo = $this->stateMap[0];
                $this->fullName  = $info['path'];
                $this->filePath  = APP_ROOT  .'public/'. $info['path'];
                $this->fileName  = $info['name'];
                @unlink($_FILES[$this->fileField]['tmp_name']);
                return true;
            } else {
                $Table->where(array('hash'=>$this->fileHash))->update(array(
                    'uid'         => $this->param['uid'],
                    'type'        => $this->param['type'],
                    'create_time' => time(),
                    'path'        => $this->fullName,
                    'size'        => $this->fileSize,
                    'name'        => $this->fileName,
                    'ext'         => $this->fileType,
                    'ip'          => FRequest::getClientIP()
                ));
                $dirname = dirname($this->filePath);
            }
        } else {
            $Table->insert(array(
                'uid'         => $this->param['uid'],
                'type'        => $this->param['type'],
                'hash'        => $this->fileHash,
                'create_time' => time(),
                'path'        => $this->fullName,
                'size'        => $this->fileSize,
                'name'        => $this->fileName,
                'ext'         => $this->fileType,
                'ip'          => FRequest::getClientIP()
            ));
        }
        unset($info,$Table,$hash);
        /**
         * ==================
         * 文件上传去重处理结束
         * ==================
         */
        //创建目录失败
        if (!is_dir($dirname) && !mkdir($dirname)) {
            $this->stateInfo = $this->getStateInfo("ERROR_CREATE_DIR");
            return false;
        } else if (!is_writeable($dirname)) {
            $this->stateInfo = $this->getStateInfo("ERROR_DIR_NOT_WRITEABLE");
            return false;
        }

        //移动文件
        if (!(file_put_contents($this->filePath, $img) && file_exists($this->filePath))) { //移动失败
            $this->stateInfo = $this->getStateInfo("ERROR_WRITE_CONTENT");
        } else { //移动成功
            $this->stateInfo = $this->stateMap[0];
        }
        return true;
    }

    /**
     * 拉取远程图片
     * @return mixed
     */
    private function saveRemote()
    {
        global $_F;
        $imgUrl = htmlspecialchars($this->fileField);
        $imgUrl = str_replace("&amp;", "&", $imgUrl);

        //http开头验证
        if (strpos($imgUrl, "http") !== 0) {
            $this->stateInfo = $this->getStateInfo("ERROR_HTTP_LINK");
            return false;
        }

        preg_match('/(^https*:\/\/[^:\/]+)/', $imgUrl, $matches);
        $host_with_protocol = count($matches) > 1 ? $matches[1] : '';

        // 判断是否是合法 url
        if (!filter_var($host_with_protocol, FILTER_VALIDATE_URL)) {
            $this->stateInfo = $this->getStateInfo("INVALID_URL");
            return false;
        }

        preg_match('/^https*:\/\/(.+)/', $host_with_protocol, $matches);
        $host_without_protocol = count($matches) > 1 ? $matches[1] : '';

        // 此时提取出来的可能是 ip 也有可能是域名，先获取 ip
        $ip = gethostbyname($host_without_protocol);
        // 判断是否是私有 ip
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
            $this->stateInfo = $this->getStateInfo("INVALID_IP");
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
        $heads = Service_Public::getHeader($imgUrl,$refer);
        if (!(stristr($heads[0], "200") && stristr($heads[0], "OK"))) {
            $this->stateInfo = $this->getStateInfo("ERROR_DEAD_LINK");
            return false;
        }
        //格式验证(扩展名验证和Content-Type验证)
        $fileType = Service_Public::getFileTypeByMine($heads['Content-Type']);
        if($fileType == ''){
            $fileType = strtolower(strrchr($imgUrl, '.'));
        }
        if (!in_array($fileType, $this->config['allowFiles']) || !isset($heads['Content-Type']) || !stristr($heads['Content-Type'], "image")) {
            $this->stateInfo = $this->getStateInfo("ERROR_HTTP_CONTENTTYPE");
            return false;
        }
        //打开输出缓冲区并获取远程图片
        ob_start();
        $context = stream_context_create(
            array(
                'http' => array(
                    'follow_location' => false,
                    'header'          => "Referer:".$refer,
                )
            )
        );
        readfile($imgUrl, false, $context);
        $img = ob_get_contents();
        ob_end_clean();
        preg_match("/[\/]([^\/]*)[\.]?[^\.\/]*$/", $imgUrl, $m);
        if($refer == 'http://news.sogou.com/'){
            $this->oriName = md5($imgUrl) . $fileType;
        }else{
            $this->oriName = $m ? $m[1] : "";
        }
        $this->fileSize = strlen($img);
        $this->fileType = $this->getFileExt();
        $this->fullName = $this->getFullName();
        $this->filePath = $this->getFilePath();
        $this->fileName = $this->getFileName();
        if(strlen($this->fileType) > 10){
            $this->stateInfo = $this->getStateInfo("ERROR_DEAD_LINK");
            return false;
        }
        Services_Api::outputJSON(200,'','',$this->filePath);
        $dirname = dirname($this->filePath);
        /**
         * ==================
         * 文件上传去重处理开始
         * ==================
         */
        $Table = new FTable('files','',$this->db);
        $this->fileHash  = md5(FConfig::get('global.file_key') . md5($img));
        $info  = $Table->fields('path,name,hash')->where(array('hash'=>$this->fileHash))->find();
        if ($info) {
            if (is_file(APP_ROOT  .'public/'. $info['path'])) {
                if($this->chunked === true){
                    $this->stateInfo = $this->getStateInfo('FILE_EXIST');
                    return false;
                }                $this->stateInfo = $this->stateMap[0];
                $this->fullName  = $info['path'];
                $this->filePath  = APP_ROOT  .'public/'. $info['path'];
                $this->fileName  = $info['name'];
                @unlink($_FILES[$this->fileField]['tmp_name']);
                return true;
            } else {
                $Table->where(array('hash'=>$this->fileHash))->update(array(
                    'uid'         => isset($_F['uid']) ? intval($_F['uid']) : 0,
                    'type'        => isset($_F['in_manage']) ? intval($_F['in_manage']) : 2,
                    'create_time' => time(),
                    'path'        => $this->fullName,
                    'size'        => $this->fileSize,
                    'name'        => $this->fileName,
                    'ext'         => $this->fileType,
                    'ip'          => FRequest::getClientIP()
                ));
                $dirname = dirname($this->filePath);
            }
        } else {
            $Table->insert(array(
                'uid'         => isset($_F['uid']) ? intval($_F['uid']) : 0,
                'type'        => isset($_F['in_manage']) ? intval($_F['in_manage']) : 2,
                'hash'        => $this->fileHash,
                'create_time' => time(),
                'path'        => $this->fullName,
                'size'        => $this->fileSize,
                'name'        => $this->fileName,
                'ext'         => $this->fileType,
                'ip'          => FRequest::getClientIP()
            ));
        }
        unset($info,$Table,$hash);
        /**
         * ==================
         * 文件上传去重处理结束
         * ==================
         */
        //检查文件大小是否超出限制
        if (!$this->checkSize()) {
            $this->stateInfo = $this->getStateInfo("ERROR_SIZE_EXCEED");
            return false;
        }

        //创建目录失败
        if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
            $this->stateInfo = $this->getStateInfo("ERROR_CREATE_DIR");
            return false;
        } else if (!is_writeable($dirname)) {
            $this->stateInfo = $this->getStateInfo("ERROR_DIR_NOT_WRITEABLE");
            return false;
        }

        //移动文件
        if (!(file_put_contents($this->filePath, $img) && file_exists($this->filePath))) { //移动失败
            $this->stateInfo = $this->getStateInfo("ERROR_WRITE_CONTENT");
        } else { //移动成功
            $this->stateInfo = $this->stateMap[0];
        }
        return true;
    }

    /**
     * 上传错误检查
     * @param $errCode
     * @return string
     */
    private function getStateInfo($errCode)
    {
        return !$this->stateMap[$errCode] ? $this->stateMap["ERROR_UNKNOWN"] : $this->stateMap[$errCode];
    }

    /**
     * 获取文件扩展名
     * @return string
     */
    private function getFileExt()
    {
        return strtolower(strrchr($this->oriName, '.'));
    }

    /**
     * 重命名文件
     * @return string
     */
    private function getFullName()
    {
        //替换日期事件
        $t = time();
        $d = explode('-', date("Y-y-m-d-H-i-s"));
        $format = $this->config["pathFormat"];
        $format = str_replace("{yyyy}", $d[0], $format);
        $format = str_replace("{yy}", $d[1], $format);
        $format = str_replace("{mm}", $d[2], $format);
        $format = str_replace("{dd}", $d[3], $format);
        $format = str_replace("{hh}", $d[4], $format);
        $format = str_replace("{ii}", $d[5], $format);
        $format = str_replace("{ss}", $d[6], $format);
        $format = str_replace("{time}", $t, $format);
        //过滤文件名的非法字符,并替换文件名
        $oriName = substr($this->oriName, 0, strrpos($this->oriName, '.'));
        $oriName = preg_replace("/[\|\?\"\<\>\/\*\\\\]+/", '', $oriName);
        $format  = str_replace("{filename}", $oriName, $format);
        return str_replace("{filename}", $oriName, $format) . substr(md5($format), rand(0, 20), 10) . $this->getFileExt();
    }

    /**
     * 获取文件名
     * @return string
     */
    private function getFileName()
    {
        return substr($this->filePath, strrpos($this->filePath, '/') + 1);
    }

    /**
     * 获取文件完整路径
     * @return string
     */
    private function getFilePath()
    {
        $fullname = $this->fullName;
        $rootPath = $_SERVER['DOCUMENT_ROOT'];

        if (substr($fullname, 0, 1) != '/') {
            $fullname = '/' . $fullname;
        }
        return $rootPath . $fullname;
    }

    /**
     * 文件类型检测
     * @return bool
     */
    private function checkType()
    {
        return in_array($this->getFileExt(), $this->config["allowFiles"]);
    }

    /**
     * 文件大小检测
     * @return bool
     */
    private function checkSize()
    {
        return $this->fileSize <= ($this->config["maxSize"]);
    }

    /**
     * 获取当前上传成功文件的各项信息
     * @return array
     */
    public function getFileInfo()
    {
        $result = array(
            'state' => $this->stateInfo
        );
        if($this->stateInfo === 'SUCCESS'){
            $result = array(
                'state'    => $this->stateInfo,
                "hash"     => $this->fileHash,
                "url"      => Services_Api::getFilesUrl($this->fileHash,1),
                "title"    => trim($this->fileName),
                "original" => trim($this->oriName),
                "type"     => trim($this->fileType),
                "size"     => intval($this->fileSize)
            );
        }
        return $result;
    }

    /**
     * 创建缩略图
     * @param string $file 来源图像路径
     * @param string $thumb_file 缩略图路径
     * @param int $width 缩略图路径
     * @param int $height 缩略图路径
     * @return bool
     */
    public function create_thumb($file, $thumb_file,$width = 0,$height = 0) {
        if (!file_exists($file)) return false;
        if($width <= 0 || $height <= 0){
            return false;
        }
        $info = getimagesize($file);
        if ($info[0] <= $width && $info[1] <= $height) {
            if (!copy($file, $thumb_file)) {
                return false;
            }
            return true;
        }
        $ext = 'jpg';
        /**
         * 使用真实文件类型,避免出错
         */
        switch ($info['mime']){
            case 'image/png':
                $ext = 'png';
                break;
            case 'image/jpg':
            case 'image/jpeg':
                $ext = 'jpg';
                break;
            case 'image/gif':
                $ext = 'gif';
                break;
        }
        $img = $thumb_img = null;
        $img_width  = $info[0];
        $img_height = $info[1];
        $size_width = $size_height = 0;
        switch ($ext) {
            case 'jpg' :
                $img = imagecreatefromjpeg($file);
                break;
            case 'png' :
                $img = imagecreatefrompng($file);
                break;
            case 'gif' :
                $img = imagecreatefromgif($file);
                break;
        }
        if ($img === null) {
            return false;
        }
        if($img_width >= $img_height && $img_height >= $height){
            /**
             * 宽图缩放处理
             */
            $size_width  = ($height / $img_height) * $img_width;
            $size_height = $height;
        }else if($img_width < $img_height && $img_width >= $width){
            /**
             * 长图缩放处理
             */
            $size_height = ($width / $img_width) * $img_height;
            $size_width = $width;
        }
        if($size_width > 0 && $size_height > 0){
            //创建一个真彩色的缩略图像
            $thumb_img = @imagecreatetruecolor($size_width, $size_height);
            if (function_exists('imagecopyresampled')) {
                @imagecopyresampled($thumb_img, $img, 0, 0, 0, 0, $size_width, $size_height, $info[0], $info[1]);
            } else {
                @imagecopyresized($thumb_img, $img, 0, 0, 0, 0, $size_width, $size_height, $info[0], $info[1]);
            }
        }else{
            $thumb_img   = $img;
            $size_width  = $img_width;
            $size_height = $img_height;
        }
        if($thumb_img){
            $save_img = imagecreatetruecolor($width, $height);
            $src_x = 0;
            $src_y = 0;
            if($size_width > $width){
                $src_x = ceil(($size_width - $width) / 2);
            }
            if($size_height > $height){
                $src_y = ceil(($size_height - $height) / 2);
            }
            if (function_exists('imagecopyresampled')) {
                @imagecopyresampled($save_img, $thumb_img, 0, 0, $src_x, $src_y, $width, $height, $width,$height);
            } else {
                @imagecopyresized($save_img, $thumb_img, 0, 0, $src_x, $src_y, $width, $height, $width,$height);
            }
            switch ($ext) {
                case 'jpg' :
                    imagejpeg($save_img, $thumb_file,70);
                    break;
                case 'png' :
                    imagepng($save_img, $thumb_file);
                    break;
                case 'gif' :
                    imagegif($save_img, $thumb_file);
                    break;
            }
            @imagedestroy($save_img);
        }
        @imagedestroy($img);
        @imagedestroy($thumb_img);

        return true;
    }

    /**
     * 裁剪图片
     * @param string $file 来源图像路径
     * @param string $thumb_file 缩略图路径
     * @param int $width 缩略图宽度
     * @param int $height 缩略图高度
     * @param int $x X轴坐标
     * @param int $y Y轴坐标
     * @return bool
     */
    public function imageCut($file, $thumb_file,$width = 0,$height = 0,$x = 0,$y = 0) {
        if (!file_exists($file)) return false;
        if($width <= 0 || $height <= 0){
            return false;
        }
        $info = getimagesize($file);
        $ext = 'jpg';
        /**
         * 使用真实文件类型,避免出错
         */
        switch ($info['mime']){
            case 'image/png':
                $ext = 'png';
                break;
            case 'image/jpg':
            case 'image/jpeg':
                $ext = 'jpg';
                break;
            case 'image/gif':
                $ext = 'gif';
                break;
        }
        $img = $thumb_img = null;
        switch ($ext) {
            case 'jpg' :
                $img = imagecreatefromjpeg($file);
                break;
            case 'png' :
                $img = imagecreatefrompng($file);
                break;
            case 'gif' :
                $img = imagecreatefromgif($file);
                break;
        }
        if ($img === null) {
            return false;
        }
        //创建一个真彩色的缩略图像
        $thumb_img = @imagecreatetruecolor($width, $height);
        if (function_exists('imagecopyresampled')) {
            @imagecopyresampled($thumb_img, $img, 0,0, $x, $y, $width, $height, $width, $height);
        } else {
            @imagecopyresized($thumb_img, $img,0,0, $x, $y, $width, $height, $width, $height);
        }
        $flag = false;
        if($thumb_img){
            switch ($ext) {
                case 'jpg' :
                    $flag = imagejpeg($thumb_img, $thumb_file,80);
                    break;
                case 'png' :
                    $flag = imagepng($thumb_img, $thumb_file);
                    break;
                case 'gif' :
                    $flag = imagegif($thumb_img, $thumb_file);
                    break;
            }
            @imagedestroy($thumb_img);
        }
        @imagedestroy($img);
        @imagedestroy($thumb_img);
        return $flag;
    }

    /**
     * 为图片添加水印
     * @param string $groundImage  原始图片
     * @param int $waterPos        水印位置
     * @param string $waterImage   水印图片
     * @param string $waterText    水印文字
     * @param string $textFont     文字水印字体
     * @param string $textColor    文字水印颜色
     * @author 93307399@qq.com
     * @return int
     */
    public function imageWaterMark($groundImage,$waterPos = 0,$waterImage = '',$waterText ='',$textFont = '',$textColor = '#000000')
    {
        $isWaterImage = FALSE;
        $water_w      = 0;
        $water_h      = 0;
        $water_im     = null;
        //读取背景图片
        if (!empty($groundImage) && file_exists($groundImage)) {
            $ground_info = getimagesize($groundImage);
            $ground_w    = $ground_info[0];
            $ground_h    = $ground_info[1];
            switch ($ground_info[2]) {
                case 1:
                    $ground_im = imagecreatefromgif($groundImage);
                    break;
                case 2:
                    $ground_im = imagecreatefromjpeg($groundImage);
                    break;
                case 3:
                    $ground_im = imagecreatefrompng($groundImage);
                    break;
                default:
                    return 2;
            }
        } else {
            return 1;
        }
        //读取水印文件
        if (!empty($waterImage) && file_exists($waterImage)) {
            $isWaterImage = TRUE;
            $water_info   = getimagesize($waterImage);
            $water_w      = $water_info[0];
            $water_h      = $water_info[1];
            switch ($water_info[2]) {
                case 1:
                    $water_im = imagecreatefromgif($waterImage);
                    break;
                case 2:
                    $water_im = imagecreatefromjpeg($waterImage);
                    break;
                case 3:
                    $water_im = imagecreatefrompng($waterImage);
                    break;
                default:
                    return 3;
            }
        }

        //水印位置
        if ($isWaterImage) {
            $w = $water_w;
            $h = $water_h;
        } else {
            $t = imagettfbbox(ceil($textFont * 5), 0, $textFont, $waterText);
            $w = $t[2] - $t[6];
            $h = $t[3] - $t[7];
            unset($t);
        }
        if (($ground_w < $w) || ($ground_h < $h)) {
            return 4;
        }
        switch ($waterPos) {
            case 1:
                //顶端居左
                $posX = 0;
                $posY = 0;
                break;
            case 2:
                //顶端居中
                $posX = ($ground_w - $w) / 2;
                $posY = 0;
                break;
            case 3:
                //顶端居右
                $posX = $ground_w - $w;
                $posY = 0;
                break;
            case 4:
                //中部居左
                $posX = 0;
                $posY = ($ground_h - $h) / 2;
                break;
            case 5:
                //中部居中
                $posX = ($ground_w - $w) / 2;
                $posY = ($ground_h - $h) / 2;
                break;
            case 6:
                //中部居右
                $posX = $ground_w - $w;
                $posY = ($ground_h - $h) / 2;
                break;
            case 7:
                //底端居左
                $posX = 0;
                $posY = $ground_h - $h;
                break;
            case 8:
                //底端居中
                $posX = ($ground_w - $w) / 2;
                $posY = $ground_h - $h;
                break;
            case 9:
                //底端居右
                $posX = $ground_w - $w;
                $posY = $ground_h - $h;
                break;
            default:
                //随机
                $posX = rand(0, ($ground_w - $w));
                $posY = rand(0, ($ground_h - $h));
                break;
        }

        //设定图像的混色模式
        imagealphablending($ground_im, true);

        if ($isWaterImage) {
            imagecopy($ground_im, $water_im, $posX, $posY, 0, 0, $water_w, $water_h);
        } else {
            if (!empty($textColor) && (strlen($textColor) == 7)) {
                $R = hexdec(substr($textColor, 1, 2));
                $G = hexdec(substr($textColor, 3, 2));
                $B = hexdec(substr($textColor, 5));
            } else {
                return 5;
            }
            imagestring($ground_im, $textFont, $posX, $posY, $waterText, imagecolorallocate($ground_im, $R, $G, $B));
        }

        //生成水印后的图片
        @unlink($groundImage);
        switch ($ground_info[2]) {
            case 1:
                imagegif($ground_im, $groundImage);
                break;
            case 2:
                imagejpeg($ground_im, $groundImage);
                break;
            case 3:
                imagepng($ground_im, $groundImage);
                break;
            default:
                return 6;
        }
        if (isset($water_info)) unset($water_info);
        if (isset($water_im)) imagedestroy($water_im);
        unset($ground_info);
        imagedestroy($ground_im);
        return 0;
    }
}