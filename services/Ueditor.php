<?php

/**
 * Created by JetBrains PhpStorm.
 * User: 93307399@qq.com
 * Date: 12-7-18
 * Time: 上午11: 32
 * UEditor编辑器通用上传类
 */
class Service_Ueditor
{
    private $fileField; //文件域名
    private $file; //文件上传对象
    private $base64; //文件上传对象
    private $config; //配置信息
    private $oriName; //原始文件名
    private $fileName; //新文件名
    private $fullName; //完整文件名,即从当前配置目录开始的URL
    private $filePath; //完整文件名,即从当前配置目录开始的URL
    private $fileSize; //文件大小
    private $fileType; //文件类型
    private $stateInfo; //上传状态信息,
    private $stateMap = array( //上传状态映射表，国际化用户需考虑此处数据的国际化
        "SUCCESS", //上传成功标记，在UEditor中内不可改变，否则flash判断会出错
        "文件大小超出 upload_max_filesize 限制",
        "文件大小超出 MAX_FILE_SIZE 限制",
        "文件未被完整上传",
        "没有文件被上传",
        "上传文件为空",
        "ERROR_TMP_FILE" => "临时文件错误",
        "ERROR_TMP_FILE_NOT_FOUND" => "找不到临时文件",
        "ERROR_SIZE_EXCEED" => "文件大小超出网站限制",
        "ERROR_TYPE_NOT_ALLOWED" => "文件类型不允许",
        "ERROR_CREATE_DIR" => "目录创建失败",
        "ERROR_DIR_NOT_WRITEABLE" => "目录没有写权限",
        "ERROR_FILE_MOVE" => "文件保存时出错",
        "ERROR_FILE_NOT_FOUND" => "找不到上传文件",
        "ERROR_WRITE_CONTENT" => "写入文件内容错误",
        "ERROR_UNKNOWN" => "未知错误",
        "ERROR_DEAD_LINK" => "链接不可用",
        "ERROR_HTTP_LINK" => "链接不是http链接",
        "ERROR_HTTP_CONTENTTYPE" => "链接contentType不正确",
        "ERROR_IMG_WIDTH_MIN" => "图片尺寸太小,无法使用",
        "INVALID_URL" => "非法 URL",
        "INVALID_IP" => "非法 IP"
    );

    /**
     * 构造函数
     * @param string $fileField 表单名称
     * @param array $config 配置项
     * @param string $type 是否解析base64编码，可省略。若开启，则$fileField代表的是base64编码的字符串表单名
     */
    public function __construct($fileField, $config, $type = "upload")
    {
        $this->fileField = $fileField;
        $this->config = $config;
        $this->type = $type;
        if ($type == "remote") {
            $this->saveRemote();
        } else if ($type == "base64") {
            $this->upBase64();
        } else {
            $this->upFile();
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
        $refer = $_F['refer'];
        $checkSize = true;
        if(strpos($refer,'/goods/modify') !== false){
            $checkSize = false;
        }
        if(strpos($refer,'/material/publish') !== false){
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
        $Table = new FTable('upload');
        $hash = md5_file($_FILES[$this->fileField]['tmp_name']);
        $info = $Table->fields('path,name')->where(array('hash'=>$hash))->find();
        if ($info) {
            if (is_file(UPLOAD_ROOT . $info['path'])) {
                $this->stateInfo = $this->stateMap[0];
                $this->fullName  = $info['path'];
                $this->filePath  = UPLOAD_ROOT . $info['path'];
                $this->fileName  = $info['name'];
                @unlink($_FILES[$this->fileField]['tmp_name']);
                return true;
            } else {
                $Table->where(array('hash'=>$hash))->update(array(
                    'uid'         => isset($_F['uid']) ? intval($_F['uid']) : 0,
                    'user_type'   => isset($_F['in_manage']) ? intval($_F['in_manage']) : 2,
                    'create_time' => time(),
                    'path'        => $this->fullName,
                    'size'        => $this->fileSize,
                    'name'        => $this->fileName,
                    'ext'         => $this->fileType
                ));
                $this->filePath = $info['path'];
                $this->fullName = $info['path'];
                $this->fileName = $info['name'];
                $dirname = dirname($this->filePath);
            }
        } else {
            $Table->insert(array(
                'uid'         => isset($_F['uid']) ? intval($_F['uid']) : 0,
                'user_type'   => isset($_F['in_manage']) ? intval($_F['in_manage']) : 2,
                'hash'        => $hash,
                'create_time' => time(),
                'path'        => $this->fullName,
                'size'        => $this->fileSize,
                'name'        => $this->fileName,
                'ext'         => $this->fileType
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
        global $_F;
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
        $Table = new FTable('upload');
        $hash = md5($base64Data);
        $info = $Table->fields('path,name')->where(array('hash'=>$hash))->find();
        if ($info) {
            if (is_file(UPLOAD_ROOT . $info['path'])) {
                $this->stateInfo = $this->stateMap[0];
                $this->fullName  = $info['path'];
                $this->filePath  = UPLOAD_ROOT . $info['path'];
                $this->fileName  = $info['name'];
                @unlink($_FILES[$this->fileField]['tmp_name']);
                return true;
            } else {
                $Table->where(array('hash'=>$hash))->update(array(
                    'uid'         => isset($_F['uid']) ? intval($_F['uid']) : 0,
                    'user_type'   => isset($_F['in_manage']) ? intval($_F['in_manage']) : 2,
                    'create_time' => time(),
                    'path'        => $this->fullName,
                    'size'        => $this->fileSize,
                    'name'        => $this->fileName,
                    'ext'         => $this->fileType
                ));
                $this->filePath = $info['path'];
                $this->fullName = $info['path'];
                $this->fileName = $info['name'];
                $dirname = dirname($this->filePath);
            }
        } else {
            $Table->insert(array(
                'uid'         => isset($_F['uid']) ? intval($_F['uid']) : 0,
                'user_type'   => isset($_F['in_manage']) ? intval($_F['in_manage']) : 2,
                'hash'        => $hash,
                'create_time' => time(),
                'path'        => $this->fullName,
                'size'        => $this->fileSize,
                'name'        => $this->fileName,
                'ext'         => $this->fileType
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
        $dirname = dirname($this->filePath);
        /**
         * ==================
         * 文件上传去重处理开始
         * ==================
         */
        $Table = new FTable('upload');
        $hash = md5($img);
        $info = $Table->fields('path,name')->where(array('hash'=>$hash))->find();
        if ($info) {
            if (is_file(APP_ROOT . 'public/' . $info['path'])) {
                $this->stateInfo = $this->stateMap[0];
                $this->fullName  = $info['path'];
                $this->filePath  = APP_ROOT . 'public/' . $info['path'];
                $this->fileName  = $info['name'];
                @unlink($_FILES[$this->fileField]['tmp_name']);
                return true;
            } else {
                $Table->where(array('hash'=>$hash))->update(array(
                    'uid'         => isset($_F['uid']) ? intval($_F['uid']) : 0,
                    'user_type'   => isset($_F['in_manage']) ? intval($_F['in_manage']) : 2,
                    'create_time' => time(),
                    'path'        => $this->fullName,
                    'size'        => $this->fileSize,
                    'name'        => $this->fileName,
                    'ext'         => $this->fileType
                ));
                $this->filePath = $info['path'];
                $this->fullName = $info['path'];
                $this->fileName = $info['name'];
                $dirname = dirname($this->filePath);
            }
        } else {
            $Table->insert(array(
                'uid'         => isset($_F['uid']) ? intval($_F['uid']) : 0,
                'user_type'   => isset($_F['in_manage']) ? intval($_F['in_manage']) : 2,
                'hash'        => $hash,
                'create_time' => time(),
                'path'        => $this->fullName,
                'size'        => $this->fileSize,
                'name'        => $this->fileName,
                'ext'         => $this->fileType
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
        $ext = strtolower(strrchr($this->oriName, '.'));
        if(!$ext){
            $ext = 'jpg';
        }
        return $ext;
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
        $format = str_replace("{filename}", $oriName, $format);
        $format = substr(md5($format), rand(0, 20), 10);
        $path = 'upload/' . date('Ymd') . '/';
        $ext = $this->getFileExt();
        return $path . $format . $ext;
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
        $rootPath = APP_ROOT . 'public/';
        if (substr($fullname, 0, 1) == '/') {
            $fullname = substr($fullname, 1);
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
        return array(
            "state" => $this->stateInfo,
            "url" => $this->fullName,
            "title" => $this->fileName,
            "original" => $this->oriName,
            "type" => $this->fileType,
            "size" => $this->fileSize
        );
    }

}