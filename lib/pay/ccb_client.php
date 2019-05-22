<?php
/**
 * Created by PhpStorm.
 * Date: 2018/08/11 14:43
 * @author: 93307399@qq.com
 */
class ccb_client extends PayAdapter {

    public function __destruct()
    {
        $this->request = null;
        $this->config  = null;
    }

    public function payOrder($param = array())
    {
        global $_F;
        $api  = $this->config['url'] . 'CCBIS/ccbMain';
        $this->config['return_url'] = 'http://'.$_F['http_host']. '/payresult.html?order_id=' . $param['order_no'];
        $this->config['PUB'] = substr($this->config['PUB'],strlen($this->config['PUB']) - 30,30);
        $data = array(
            'MERCHANTID' => $this->config['MERCHANTID'],
            'POSID'      => $this->config['POSID'],
            'BRANCHID'   => $this->config['BRANCHID'],
            'ORDERID'    => $param['order_no'],
            'PAYMENT'    => Service_Public::formatMoney($param['money']),
            'CURCODE'    => '01',
            'TXCODE'     => $this->config['TXCODE'],
            'REMARK1'    => '',
            'REMARK2'    => '',
            'TYPE'       => $this->config['CCB_TYPE'],
            'PUB'        => $this->config['PUB'],
            'GATEWAY'    => $this->config['GATEWAY'],
            'CLIENTIP'   => '212.198.22.33',
            'REGINFO'    => escape($param['nickname']),
            'PROINFO'    => escape($param['subject']),
            'REFERER'    => $this->config['return_url'],
            'TIMEOUT'    => date('YmdHis',time() + 1800),
//            'CCB_IBSVersion' => 'V6',
        );
        $data['MAC'] = $this->paySign($data);
//        $this->request->refer      = false;
//        $this->request->user_agent = false;
//
//        $result = $this->request->sendHttpQuick($api,array(
//            'method' => 'GET',
//            'param'  => $data,
//        ));

        $return = array(
            'code' => 200,
            'data' => $api . '?' . http_build_query($data),
            'msg'  => 'success'
        );

        return $return;
    }

    public function paySign($param = array())
    {
        $signKey = array(
            'MERCHANTID', 'POSID', 'BRANCHID', 'ORDERID', 'PAYMENT',
            'CURCODE', 'TXCODE', 'REMARK1', 'REMARK2', 'TYPE',
            'PUB',
            'GATEWAY', 'CLIENTIP', 'REGINFO', 'PROINFO',
            'REFERER', 'SMERID', 'SMERNAME', 'SMERTYPEID', 'SMERTYPE',
            'TRADECODE', 'TRADENAME', 'SMEPROTYPE', 'PRONAME', 'TIMEOUT',
            'TRADE_TYPE', 'SUB_APPID', 'SUB_OPENID',
        );
        $noEmpty = array(
            'TRADE_TYPE', 'SUB_APPID', 'SUB_OPENID',
        );
        $signMap = array();
        foreach ($signKey as $item){
            if(isset($param[$item])){
                if(in_array($item,$noEmpty) && $param[$item] == ''){
                    continue;
                }
                $signMap[] = $item .'='. $param[$item];
            }
        }
        $signString = implode('&',$signMap);
        return md5($signString);
    }

    /**
     * 异步通知校验
     * @author 93307399@qq.com
     * @return array
     */
    public function notify()
    {
        $flag         = FRequest::getString('SUCCESS');
        $out_trade_no = FRequest::getString('ORDERID');
        $trade_no     = '';
        $remark       = FRequest::getString('REMARK1');
        $sign         = FRequest::getString('SIGN');
        $fee          = FRequest::getString('PAYMENT');
        FLogger::write($_GET,'ccb_notify');

        $result = array(
            'status'   => false,
            'order_no' => $out_trade_no,
            'trade_no' => $trade_no,
            'money'    => Service_Public::formatMoney($fee,2),
            'msg'      => 'error-2'
        );
        /**
         * 当状态不成功时直接返回
         */
        if($flag !== 'Y'){
            return $result;
        }

        $source = $this->notifySign($_GET);

        if($this->verifySign($source,$sign)){
            $result['status'] = true;
            $result['msg'] = 'ok';
        }else{
            $result['msg'] = 'error-3';
        }

        return $result;
    }

    /**
     * 生成待签名字符串
     * @param $data
     * @return string
     * @author 93307399@qq.com
     */
    public function notifySign($data){
        $signKey = array(
            'POSID', 'BRANCHID', 'ORDERID', 'PAYMENT',
            'CURCODE', 'REMARK1', 'REMARK2', 'ACC_TYPE',
            'SUCCESS', 'TYPE', 'REFERER', 'CLIENTIP',
            'ACCDATE', 'USRMSG', 'INSTALLNUM', 'USRINFO',
        );
        $noEmpty = array(
            'ACCDATE', 'USRMSG', 'INSTALLNUM', 'USRINFO',
        );
        $signMap = array();
        foreach ($signKey as $item){
            if(isset($data[$item])){
                if(in_array($item,$noEmpty) && $data[$item] == ''){
                    continue;
                }
                $signMap[] = $item .'='. $data[$item];
            }
        }
        $signString = implode('&',$signMap);
        return $signString;
    }

    /**
     * 公钥验证签名
     * @param $source
     * @param $sign
     * @return bool
     * @author 93307399@qq.com
     */
    public function verifySign($source,$sign) {
        /**
         * 转换建行公钥格式到PEM
         */
        $this->config['PUB'] = base64_encode(pack('H*', $this->config['PUB']));
        $pem    = chunk_split($this->config['PUB'],64,"\n");//转换为pem格式的公钥
        $pem    = "-----BEGIN PUBLIC KEY-----\n".$pem."-----END PUBLIC KEY-----\n";
        $pu_key = openssl_pkey_get_public($pem);
        /**
         * 转换签名到十六进制
         */
        $sign = pack('H*', $sign);
        /**
         * 使用公钥进行验证签名(建行使用了MD5格式)
         */
        $flag = (bool)openssl_verify($source,$sign,$pu_key,OPENSSL_ALGO_MD5);
        return $flag;
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