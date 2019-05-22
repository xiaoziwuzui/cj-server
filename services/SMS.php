<?php

//require_once "send_sms.php";

class Service_SMS
{

    const SMS_TYPE_VALIDATION = 1;
    const SMS_TYPE_FIND_PASSWORD = 2;
    const SMS_TYPE_REG = 3;

    /**
     * @var string
     */
    private $hash = null;

    private $type = null;

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * @return string
     */
    public function getHash()
    {

        if ($this->hash) {
            return $this->hash;
        } else {
            $this->setHash($this->getClientHash());
        }

        return $this->hash;
    }

    //短信余额
    static function balance()
    {
        $url = "http://www.282930.cn/SMSReceiver.aspx?username=hnzdx&password=7441948&queryoddcount=1";
        $ret = FHttp::get($url);
        return $ret;
    }

    //发送短信
    public function sendSMS($phone_num, $sms_type = self::SMS_TYPE_VALIDATION)
    {
        //IP防刷
        $ip = FRequest::getClientIP();
        $cache_key_ip = 'phone_IP_' . $ip;
        $cache_data_ip = FCache::get($cache_key_ip);
        if (!$cache_data_ip) {
            $cache_data_ip = 1;
            FCache::set($cache_key_ip, $cache_data_ip, 24 * 3600);
        } else {
            if ($cache_data_ip < 3) {
                $cache_data_ip++;
                FCache::set($cache_key_ip, $cache_data_ip, 24 * 3600);
            } else {

//                if($phone_num==18674866423){
//                    echo($cache_data);
//                    exit;
//                }

                return false;
            }
        }

        $cache_key = 'phone_' . $phone_num;
        $cache_data = FCache::get($cache_key);
        if (!$cache_data) {
            $cache_data = 1;
            FCache::set($cache_key, $cache_data, 24 * 3600);
        } else {
            if ($cache_data < 3) {
                $cache_data++;
                FCache::set($cache_key, $cache_data, 24 * 3600);
            } else {

//                if($phone_num==18674866423){
//                    echo($cache_data);
//                    exit;
//                }

                return false;
            }
        }

//        return true;
        $hash = $this->getHash();

        $smsTable = new FTable('sms');
        $existsSMS = $this->getRegSMS($phone_num);
//        if($phone_num==15810384570){
//            print_r(strtotime($existsSMS['create_time']));
//            exit;
//        }
        if ($existsSMS && strtotime($existsSMS['create_time']) > (time() - 60)) {

            return false;
        }


        if ($existsSMS) {
            $sms_content = $existsSMS['content'];
        } else {
            $verify_code = rand(1000, 9999);

            $sms_config_key = "";
            switch ($sms_type) {

                case self::SMS_TYPE_VALIDATION:
                    $this->setType('1');
                    $sms_config_key = 'global.sms_validation';
                    break;
                case self::SMS_TYPE_FIND_PASSWORD:
                    $this->setType('2');
                    $sms_config_key = 'global.sms_modify';
                    break;
                case self::SMS_TYPE_REG:
                    $this->setType('3');
                    $sms_config_key = 'global.sms_reg';
                    break;
                default:
                    throw new Exception('param sms_type is incorrect');
                    break;
            }

            $sms_content = FConfig::get($sms_config_key);
            $sms_content = str_replace('{1}', $verify_code, $sms_content);

            $newData = array(
                'hash' => $hash,
                'verify_code' => $verify_code,
                'phone_num' => $phone_num,
                'content' => $sms_content,
                'type' => $this->getType()
            );

            $smsTable->insert($newData);
        }

        //老的
//        $url = "http://www.282930.cn/SMSReceiver.aspx?username=hnzdx&password=7441948&mobiles=$phone_num&xmlmode=xmlmode&content=" . urlencode($sms_content) . "&targetdate=";

        //新的
        $url = "http://sdk2.entinfo.cn:8061/webservice.asmx/mdgxsend?sn=SDK-BBX-010-22729&pwd=" . strtoupper(md5('SDK-BBX-010-22729^-7a9c-4')) .
            "&mobile={$phone_num}&content=" . urlencode($sms_content) . "&ext=1&stime=&rrid=&msgfmt=";

        $ret = FHttp::get($url);
//        FLogger::write("SMS: " . $phone_num . " " . $sms_content . "\t" . $ret);

        return true;
    }

    public function sendModifySMS($phone_num)
    {

        $this->sendSMS($phone_num, self::SMS_TYPE_FIND_PASSWORD);
    }


    public static function removeRegSMS($phone_num)
    {
//        $hash = $this->getHash();

        $smsTable = new FTable('sms');
        $smsTable->where(array('status' => 1, 'phone_num' => $phone_num))->remove();

        return true;
    }

    public function getRegSMS($phone_num)
    {

        $hash = $this->getHash();

        $smsTable = new FTable('sms');
        $existsSMS = $smsTable->where(array('status' => 1, 'hash' => $hash, 'phone_num' => $phone_num))->order(array('sms_id' => 'desc'))->find();

        return $existsSMS;
    }

    public function checkRegSMS($phone_num, $verify_code)
    {
//        $hash = $this->getHash();

        $smsTable = new FTable('sms');
        $existsSMS = $smsTable->where(array('status' => 1,
            'phone_num' => $phone_num, 'verify_code' => $verify_code))->find();

        if ($existsSMS) {
            return true;
        }

        return false;
    }

    public static function checkSMS($phone_num, $verify_code)
    {
        $smsTable = new FTable('sms');
        $existsSMS = $smsTable->where(array(
            'status' => 1,
            'phone_num' => $phone_num,
            'verify_code' => $verify_code
        ))->find();

        if ($existsSMS) {
            return true;
        }

        return false;
    }

    public function getClientHash()
    {
        return md5($_SERVER['HTTP_COOKIE']);
    }

    public static function send($phone_num, $sms_content)
    {
        $sms_content .= '【慕慕】';
        //老的
//        $url = "http://www.282930.cn/SMSReceiver.aspx?username=hnzdx&password=7441948&mobiles=$phone_num&xmlmode=xmlmode&content=" . urlencode($sms_content) . "&targetdate=";

        //新的
        $url = "http://sdk2.entinfo.cn:8061/webservice.asmx/mdgxsend?sn=SDK-BBX-010-22729&pwd=" . strtoupper(md5('SDK-BBX-010-22729^-7a9c-4')) .
            "&mobile={$phone_num}&content=" . urlencode($sms_content) . "&ext=&stime=&rrid=&msgfmt=";

//        echo($url);exit;

        $ret = FHttp::get($url);
//        FLogger::write("SMS: " . $phone_num . " " . $sms_content . "\t" . $ret, 'sms_error');
    }

    public static function sendAiai($phone_num, $sms_content)
    {
        $sms_content .= '【爱爱同城交友】';
        //老的
//        $url = "http://www.282930.cn/SMSReceiver.aspx?username=hnzdx&password=7441948&mobiles=$phone_num&xmlmode=xmlmode&content=" . urlencode($sms_content) . "&targetdate=";

        //新的
        $url = "http://sdk2.entinfo.cn:8061/webservice.asmx/mdgxsend?sn=SDK-BBX-010-22729&pwd=" . strtoupper(md5('SDK-BBX-010-22729^-7a9c-4')) .
            "&mobile={$phone_num}&content=" . urlencode($sms_content) . "&ext=2&stime=&rrid=&msgfmt=";

//        echo($url);exit;

        $ret = FHttp::get($url);
//        FLogger::write("SMS: " . $phone_num . " " . $sms_content . "\t" . $ret, 'sms_error');
    }

    /**
     * 发送短信（人事部专用）
     * @param $phone_num 手机号
     * @param $sms_content 要发送短信的内容
     */
    public static function sendResume($phone_num, $sms_content)
    {
//        $sms_content .= '【爱爱同城交友】';
        $url = "http://sdk2.entinfo.cn:8061/webservice.asmx/mdgxsend?sn=SDK-BBX-010-22729&pwd=" . strtoupper(md5('SDK-BBX-010-22729^-7a9c-4')) . "&mobile={$phone_num}&content=" . urlencode($sms_content) . "&ext=&stime=&rrid=&msgfmt=";
        $ret = FHttp::get($url);
    }
}
