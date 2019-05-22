<?php
/**
 * Created by PhpStorm.
 * Date: 2018/07/20 10:30
 * @author: 93307399@qq.com
 * 支付平台基类
 */
abstract class SmsAdapter
{
    /**
     * HTTP请求对象
     * @var null|Request
     */
    public $request = null;

    public $config  = array();

    public function __construct($config = null)
    {
        if($config === null || $config === false){
            return false;
        }
        if(is_array($config)){
            $this->config = $config;
        }else{
            $allConfig = FConfig::get('sms');
            if(isset($allConfig[$config])){
                $this->config = $allConfig[$config];
            }else{
                return false;
            }
        }
        $this->request = new Request();
        return true;
    }

    /**
     * 发送普通短信
     * @param array $data
     * @author 93307399@qq.com
     * @return array
     */
    public abstract function sendSms($data = array());
}