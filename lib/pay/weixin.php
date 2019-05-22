<?php
/**
 * Created by PhpStorm.
 * Date: 2018/08/11 14:43
 * @author: 93307399@qq.com
 */
class weixin extends PayAdapter {

    public function __destruct()
    {
        $this->request = null;
        $this->config  = null;
    }

    public function __construct($config = null)
    {
        parent::__construct($config);
        require_once APP_ROOT.'/lib/wxpay/WxPay.Api.php';
        /**
         * 初始化配置参数
         */
        WxPayConfig::$APPID      = $this->config['app_id'];
        WxPayConfig::$MCHID      = $this->config['mch_id'];
        WxPayConfig::$KEY        = $this->config['key'];
        WxPayConfig::$APPSECRET  = $this->config['app_secret'];
        WxPayConfig::$NOTIFY_URL = $this->config['notify_url'];
    }

    public function payOrder($param = array())
    {
        $input = new WxPayUnifiedOrder();
        $input->SetBody($param['subject']);
        $input->SetAttach($param['order_no']);
        $input->SetOut_trade_no($param['order_no']);
        $input->SetTotal_fee($param['money']);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 30 * 60));
        $input->SetNotify_url($this->config['notify_url']);
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($param['openid']);
        $order = WxPayApi::unifiedOrder($input);
        $tools = new JsApiPay();
        $jsApiParameters = $tools->GetJsApiParameters($order);
        return array(
            'code' => 200,
            'data' => json_decode($jsApiParameters,true)
        );
    }

    /**
     * 异步通知校验
     * @author 93307399@qq.com
     * @return array
     */
    public function notify()
    {
        $notify = new PayNotifyCallBack();
        $result = $notify->Handle(false);
        if(is_array($result)){
            $result['no_output'] = true;
        }
        return $result;
    }

    public function getReturnInfo(){
        return array(
            'order_id' => '',
            'trade_no' => '',
            'money'    => 0
        );
    }
}