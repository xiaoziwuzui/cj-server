<?php

/**
 * Created by phpstorm
 * @name Service_Pay 支付相关方法
 * @author xiaojiang432524@163.com
 * @time 2018/5/12
 * @version 0.1
 */
use JiaweiXS\WeApp\WeApp;

class Service_Mini
{

    private $config = array();

    public $weApp = null;

    public function __construct($config = null)
    {
        if($config === null || $config === false){
            return false;
        }
        if(is_array($config)){
            $this->config = $config;
        }else{
            $allConfig = FConfig::get('pay');
            if(isset($allConfig[$config])){
                $this->config = $allConfig[$config];
            }else{
                return false;
            }
        }
        require_once APP_ROOT.'/lib/Request.php';
        $this->weApp = new WeApp($this->config['app_id'],$this->config['app_secret']);
        return true;
    }

    /**
     * @param $code
     * @return array
     * @author 93307399@qq.com
     */
    public function codeToSession($code){
        $result = $this->weApp->getSessionKey($code);
        return $result;
    }



}