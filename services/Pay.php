<?php

/**
 * Created by phpstorm
 * @name Service_Pay 支付相关方法
 * @author xiaojiang432524@163.com
 * @time 2018/5/12
 * @version 0.1
 */

class Service_Pay
{
    private $config = array();

    /**
     * 支付类
     */
    public $adapter = array();

    public function __construct($config = '')
    {
        $this->config = $config;
        require_once APP_ROOT.'/lib/pay/PayAdapter.php';
        require_once APP_ROOT.'/lib/Request.php';
    }

    /**
     * 获取支付平台
     * @param string $platform
     * @author 93307399@qq.com
     * @return bool|PayAdapter|heepay|weixin|ccb|ccb_client
     */
    public function getAdapter($platform = 'weixin'){
        if(isset($this->adapter[$platform])){
            return $this->adapter[$platform];
        }else{
            $file = APP_ROOT.'/lib/pay/'.$platform.'.php';
            if(is_file($file)){
                require_once $file;
                $this->adapter[$platform] = new $platform($this->config);
                return $this->adapter[$platform];
            }else{
                return false;
            }
        }
    }

    /**
     * 处理支付通知
     * @param string $platform
     * @throws Exception
     * @author 93307399@qq.com
     */
    public function notify($platform = 'weixin'){
        $adapter = $this->getAdapter($platform);
        $result  = $adapter->notify();
        if(is_array($result) && $result['status'] === true){
            $update = Service_Order::payComplete($result['order_no'],$result['money'],2,$result['trade_no']);
            if(!$update){
                FLogger::write($result,'trade_fail');
            }
        }
        if(!isset($result['no_output'])){
            echo $result['msg'];
        }
    }

    /**
     * 获取同步跳转数据
     * @param string $platform
     * @author 93307399@qq.com
     * @return array
     */
    public function getReturnInfo($platform = 'weixin'){
        $adapter = $this->getAdapter($platform);
        $result  = $adapter->getReturnInfo();
        return $result;
    }
}