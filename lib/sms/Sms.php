<?php

/**
 * Created by phpstorm
 * @name Sms 短信发送封装
 * @author xiaojiang432524@163.com
 * @time 2018/5/12
 * @version 0.1
 */

class Sms
{
    private $config = array();

    /**
     * 短信类
     */
    public $adapter = array();

    public function __construct($config = '')
    {
        $this->config = $config;
        require_once APP_ROOT.'/lib/sms/SmsAdapter.php';
        require_once APP_ROOT.'/lib/Request.php';
    }

    /**
     * 获取短信平台
     * @param string $platform
     * @author 93307399@qq.com
     * @return bool|SmsAdapter|aliyun
     */
    public function getAdapter($platform = 'aliyun'){
        if(isset($this->adapter[$platform])){
            return $this->adapter[$platform];
        }else{
            $file = APP_ROOT.'/lib/sms/'.$platform.'.php';
            if(is_file($file)){
                require_once $file;
                $this->adapter[$platform] = new $platform($this->config);
                return $this->adapter[$platform];
            }else{
                return false;
            }
        }
    }

}