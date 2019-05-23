<?php

/**
 * Created by phpstorm
 * @name Service_Pay 支付相关方法
 * @author xiaojiang432524@163.com
 * @time 2018/5/12
 * @version 0.1
 */

class Service_Mini
{
    private $config = array();

    public $weApp = null;

    public $errCode = null;
    public $errMsg = null;

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
        require_once APP_ROOT.'/lib/WeApp/WeApp.php';
        $this->weApp = new lib\WeApp\WeApp($this->config['app_id'],$this->config['app_secret']);
        return true;
    }

    /**
     * 获取产生的错误信息
     * @return array
     * @author 93307399@qq.com
     */
    public function getErrInfo(){
        return array(
            'errCode' => $this->errCode,
            'errMsg'  => $this->errMsg,
        );
    }

    /**
     * 清除产生的错误信息
     * @author 93307399@qq.com
     */
    public function clearErrInfo(){
        $this->errCode = null;
        $this->errMsg = null;
    }

    /**
     * 通过登录code换取用户openid
     * @param $code
     * @return array|bool
     * @author 93307399@qq.com
     */
    public function codeToSession($code){
        $this->clearErrInfo();
        $json = $this->weApp->getSessionKey($code);
        if (!$json || isset($json['errcode'])) {
            $this->errCode = $json['errcode'];
            $this->errMsg = $json['errmsg'];
            return false;
        }
        return $json;
    }

    public function sendTemplateMsg($data){
        $msg = $this->weApp->getTemplateMsg();
        $result = $msg->send($data['openid'],$data['template_id'],$data['formId'],$data['message']);
        return $result;
    }



}