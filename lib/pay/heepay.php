<?php
/**
 * Created by PhpStorm.
 * Date: 2018/08/11 14:43
 * @author: 93307399@qq.com
 */
class heepay extends PayAdapter {

    public function __destruct()
    {
        $this->request = null;
        $this->config  = null;
    }

    public function payOrder($param = array())
    {
        global $_F;
        $api  = $this->config['url'] . 'Payment/Index.aspx';
        $ip   = str_replace('.','_',FRequest::getClientIP());
        $this->config['return_url'] = 'http://'.$_F['http_host']. '/payresult.html?order_id=' . $param['order_no'];
        $data = array(
            'version'         => 1,
            'is_phone'        => 1,
            'is_frame'        => 1,
            'pay_type'        => $this->config['pay_type'],
            'agent_id'        => $this->config['agent_id'],
            'agent_bill_id'   => $param['order_no'],
            'remark'          => $param['order_no'],
            'pay_amt'         => Service_Public::formatMoney($param['money']),
            'notify_url'      => $this->config['notify_url'],
            'return_url'      => $this->config['return_url'],
            'user_ip'         => $ip,
            'agent_bill_time' => date('YmdHis'),
            'goods_name'      => $param['subject'],
            'goods_num'       => 1,
            'meta_option'     => urlencode(base64_encode(iconv('utf8','gb2312','{"s":"WAP","n":"高铁新城","id":"'.FConfig::get('global.ssl_domain').'"}'))),
        );
        $data['sign'] = $this->paySign($data);
        $this->request->refer = false;
        $this->request->user_agent = false;
        $result = $this->request->sendHttpQuick($api,array(
            'method' => 'POST',
            'param'  => $data,
        ));
        FLogger::write($data,'heepay');
        FLogger::write($result,'heepay');
        $return = array(
            'code' => 500,
            'msg'  => 'fail'
        );
        if(strpos($result,'Error.aspx') === false){
            $return['code'] = 200;
            $return['data'] = json_encode($result);
            $return['msg'] = 'success';
        }else{
            $return['msg'] = urldecode(end(explode('?message=',$result)));
        }
        return $return;
    }

    public function paySign($param = array())
    {
        $signParam = array(
            'version'         => $param['version'],
            'agent_id'        => $param['agent_id'],
            'agent_bill_id'   => $param['agent_bill_id'],
            'agent_bill_time' => $param['agent_bill_time'],
            'pay_type'        => $param['pay_type'],
            'pay_amt'         => $param['pay_amt'],
            'notify_url'      => $param['notify_url'],
            'return_url'      => $param['return_url'],
            'user_ip'         => $param['user_ip'],
            'key'             => $this->config['key'],
        );
        $signString = http_build_query($signParam);
        $signString = urldecode($signString);
        return md5($signString);
    }

    /**
     * 异步通知校验
     * @author 93307399@qq.com
     * @return array
     */
    public function notify()
    {
        $result       = FRequest::getString('result');
        $out_trade_no = FRequest::getString('agent_bill_id');
        $trade_no     = FRequest::getString('jnet_bill_no');
        $remark       = FRequest::getString('remark');
        $sign         = FRequest::getString('sign');
        $fee          = FRequest::getString('pay_amt');
        FLogger::write($_GET,'heepay_notify');
        $local_sign = md5(http_build_query(array(
            'result'        => $result,
            'agent_id'      => $this->config['agent_id'],
            'jnet_bill_no'  => $trade_no,
            'agent_bill_id' => $out_trade_no,
            'pay_type'      => $this->config['pay_type'],
            'pay_amt'       => $fee,
            'remark'        => $remark,
            'key'           => $this->config['key'],
        )));
        $result = array(
            'status'   => false,
            'order_no' => $out_trade_no,
            'trade_no' => $trade_no,
            'money'    => Service_Public::formatMoney($fee),
            'msg'      => 'error'
        );
        if($sign == $local_sign){
            $result['status'] = true;
            $result['msg'] = 'ok';
        }
        return $result;
    }

    public function getReturnInfo(){
        $out_trade_no = FRequest::getString('agent_bill_id');
        $trade_no     = FRequest::getString('jnet_bill_no');
        $fee          = FRequest::getString('pay_amt');

        return array(
            'order_id' => $out_trade_no,
            'trade_no' => $trade_no,
            'money'    => Service_Public::formatMoney($fee)
        );
    }

    /**
     * 订单查询
     * @param array $data
     * @author 93307399@qq.com
     * @return array
     */
    public function orderInfo($data = array())
    {
        $api  = $this->config['url'] . 'Payment/Query.aspx';
        $param = array(
            'version'         => 2,
            'agent_id'        => $this->config['agent_id'],
            'agent_bill_id'   => $data['order_no'],
            'agent_bill_time' => date('YmdHis'),
            'remark'          => $data['order_no'],
            'return_mode'     => 1,
        );
        $param['sign'] = md5(http_build_query(array(
            'version'         => $param['version'],
            'agent_id'        => $this->config['agent_id'],
            'agent_bill_id'   => $param['agent_bill_id'],
            'agent_bill_time' => $param['agent_bill_time'],
            'return_mode'     => $param['return_mode'],
            'key'             => $this->config['key'],
        )));;
        $this->request->refer = false;
        $this->request->user_agent = false;
        $result = $this->request->sendHttpQuick($api,array(
            'method' => 'POST',
            'param'  => $param,
        ));
        $result = json_decode($result,true);

        $return = array(
            'status' => false,
            'msg'  => 'fail'
        );
        if($result['status'] != 1){
            $msgMap = array(
                1 => '该订单支付成功',
                2 => '该订单待支付(只支持查询当日订单)',
                3 => '签名失败',
                4 => '订单号传递为空',
                5 => '无此支付类型',
            );
            $return['msg'] = $msgMap[$result['status']];
        }else{
            $return['status'] = true;
            $return['data'] = $result;
        }
        return $return;
    }


}