<?php
/**
 * Created by PhpStorm.
 * Date: 2018/08/11 14:43
 * @author: 93307399@qq.com
 */
class aliyun extends SmsAdapter {

    public function __destruct()
    {
        $this->request = null;
        $this->config  = null;
    }

    public function sendSms($data = array())
    {
        //组装发送参数
        $param = array(
            'PhoneNumbers'     => $data['mobile'],
            'SignName'         => $data['SignName'],
            'TemplateCode'     => $data['TemplateCode'],
            'AccessKeyId'      => $this->config['AccessId'],
            'Action'           => 'SendSms',
            'TemplateParam'    => $data['template'],
            'OutId'            => $data['id'],
            'Format'           => 'json',
            'RegionId'         => 'cn-hangzhou',
            'SignatureMethod'  => 'HMAC-SHA1',
            'SignatureVersion' => '1.0',
            'Version'          => '2017-05-25',
        );
        //进行数据签名
        $param = $this->sign($param);

        FLogger::$disableRefer = true;
        FLogger::write($param,'sms_request');
        $result = $this->request->sendHttpQuick($this->config['url'] . '',array(
            'method' => 'GET',
            'param'  => $param,
        ));
        FLogger::write($result,'sms_request');
        FLogger::$disableRefer = false;
        $result = json_decode($result,true);
        if($result['Code'] == 'OK'){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 生成签名数据
     * @param $param
     * @return mixed
     * @author 93307399@qq.com
     */
    public function sign($param){
        $param['SignatureNonce'] = md5(FMisc::str2crc32(md5(time())) . FConfig::get('global.encrypt_key'));
        $param['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z',time());

        $signKey = array(
            'SignatureMethod',
            'SignatureNonce',
            'AccessKeyId',
            'SignatureVersion',
            'Timestamp',
            'Format',
            'Action',
            'Version',
            'RegionId',
            'PhoneNumbers',
            'SignName',
            'TemplateParam',
            'TemplateCode',
            'OutId',
        );
        $signParam = array();
        foreach ($param as $key=>$value){
            if(in_array($key,$signKey)){
                $signParam[$key] = $value;
            }
        }
        ksort($signParam);

        $string = '';
        foreach ($signParam as $key => $value) {
            $string .= '&' . $this->percentEncode($key) . '=' . $this->percentEncode($value);
        }

        $string = 'GET&%2F&' . $this->percentEncode(substr($string, 1));

        $sign = base64_encode(hash_hmac('sha1', $string, $this->config['AccessSecret'] . '&', true));
        $param['Signature'] = $sign;
        return $param;
    }

    /**
     * @param string $string
     * @return null|string|string[]
     */
    public function percentEncode($string)
    {
        $result = urlencode($string);
        $result = str_replace(['+', '*'], ['%20', '%2A'], $result);
        $result = preg_replace('/%7E/', '~', $result);
        return $result;
    }


}