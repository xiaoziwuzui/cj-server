<?php
/**
 * Created by PhpStorm.
 * Date: 2018/07/20 10:30
 * @author: 93307399@qq.com
 * 支付平台基类
 */
abstract class PayAdapter
{
    /**
     * HTTP请求对象
     * @var null|Request
     */
    public $request = null;

    public $config  = array();

    public function __construct($config = null)
    {
        global $_F;
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
        $this->config['notify_url'] = 'http://'.$_F['http_host'].$this->config['notify_url'];
        $this->config['return_url'] = 'http://'.$_F['http_host'].$this->config['return_url'];

        $this->request = new Request();
        return true;
    }

    /**
     * 支付下单
     * @param array $param
     * @author 93307399@qq.com
     * @return array
     */
    public abstract function payOrder($param = array());

    /**
     * 订单回调验证
     * @author 93307399@qq.com
     * @return array
     */
    public abstract function notify();


    /**
     * 订单同步跳转信息获取
     * @author 93307399@qq.com
     * @return array
     */
    public abstract function getReturnInfo();

    /**
     * 订单退款
     * @param array $param
     * @author 93307399@qq.com
     * @return array
     */
    public function refund($param = array()){
        return array();
    }

    /**
     * 订单状态查询
     * @param array $data
     * @author 93307399@qq.com
     * @return array
     */
    public function orderInfo($data = array()){
        return array();
    }

    /**
     * 生成下单签名
     * @param array $param
     * @author 93307399@qq.com
     * @return string
     */
    public function paySign($param = array()){
        return '';
    }

    /**
     * 生成通知签名
     * @param array $param
     * @author 93307399@qq.com
     * @return string
     */
    public function vailSign($param = array()){
        return '';
    }
}