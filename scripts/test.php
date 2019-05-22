<?php
/**

 * User: xiaojiang432524@163.com
 * Date: 2017/7/3-15:35
 * 用于自动生成权限配置（未完成）
 */
define('APP_ROOT', dirname(dirname(__FILE__)) . '/');
define('FLIB_RUN_MODE', 'manual');
define('PUBLIC_ROOT', APP_ROOT . 'public/');
define('UPLOAD_ROOT', APP_ROOT . 'public/uploads/');

require_once APP_ROOT . "flib/Flib.php";
require_once APP_ROOT . "flib/functions/function_core.php";

//var_dump(Service_Simhash::similarity('星爷也有被整的那一天，达叔终于有了出头之日','十三期：影片影视找搞笑点，写人物和片段搞笑、内涵'));
set_time_limit(0);
global $_F;
if($_SERVER['USER'] == 'jiangtaiping'){
    $_F['dev_mode'] = true;
}
$cli_param = Service_Public::parser_argparam();
$_F['debug'] = true;
if(!isset($cli_param['date'])){
    $day = date('Y-m-d');
}else{
    $day = $cli_param['date'];
}

//$msg = array(
//    0 => '领取成功',
//    1 => '没有可以领取的券了',
//    2 => '领取过了',
//    3 => '领取失败',
//);
//$id = 3;
//for($i=1;$i<=61;$i++){
//    $c = Service_Card::receiveCard($id,$i);
//    echo 'card:',$id,',uid:',$i,',',$msg[$c];
//    echo chr(10);
//}

//$order_id = '201904101534504297';
//$table     = new FTable('pay_order','o');
//$orderInfo = $table->fields('o.id,o.order_no,o.money,o.data,o.status,p.plate')->leftJoin('plate','p','o.plate_id=p.id')->where(array('o.order_no'=>$order_id))->find();
//
//$payInfo  = FConfig::get('pay.ccb');
//if(!$payInfo || !is_array($payInfo)){
//    $this->error('支付请求失败,请联系客服-001!');
//}
//$pay     = new Service_Pay($payInfo);
//$adapter = $pay->getAdapter($payInfo['platform']);
//
//$orderInfo['nickname'] = '吃西瓜';
//$orderInfo['openid']   = $_F['member']['openid'];
//$orderInfo['subject'] = '停车费用';
//$payData = $adapter->payOrder($orderInfo);
//
//print_r($payData);

//验证签名

//$json = '{"POSID":"034808599","BRANCHID":"430000000","ORDERID":"201904241640115649","PAYMENT":"0.10","CURCODE":"01","REMARK1":"","REMARK2":"","ACC_TYPE":"WX","SUCCESS":"Y","ACCDATE":"20190424","SIGN":"340ceef985ca92d635d77d288a1f6f19d5e35d7957902e139424c29a0b7e340395df4af553deb507710128d760abb536211bbbb6f9cad8c68addc0229bf8e2a691a3228985b2a97e9c96bc1973f2228e8f4702b38a0ee04025b81838e7570b0c56e5b38899d1c2c788bc6dee026ce24b522a1047e720894270f166351bb00497","pay":"ccb"}';
//$_GET = json_decode($json,true);
//$payInfo  = FConfig::get('pay.ccb');
//if(!$payInfo || !is_array($payInfo)){
//    $this->error('支付请求失败,请联系客服-001!');
//}
//$pay     = new Service_Pay($payInfo);
//$adapter = $pay->getAdapter($payInfo['platform']);
//
//$result = $adapter->notify();
//print_r($result);

//$json = '{"tradeStatus":"SUCCESS","totalFee":0.01,"createDatetime":"2019-05-11 11:05:55","paymethod":"wechat","out_trade_no":"20190511030542305627","third_trade_no":"","product":"JSAPI"}';
//
//$param = json_decode($json,true);
//
//$data = Service_Plate::postSign($param);
//
//print_r($data);

$d = '{"identity": "2329CC5F90EDAA82D8BF8841E87DEBEAC6B53E6C407EA305", "order": {"account": "", "address": "", "clerk":"阮小二","buyername": "湖南航天信息有限公司", "checker": "哈罗", "email": "3", "invoicedate": "2019-04-22 10: 12: 16", "kptype": "1", "orderno": "FP201904221012169526", "payee": "张三", "phone": "13027437629", "saleaccount": "建设银行 88888888747", "saleaddress": "湖南长沙市五一路800号中隆国际", "salephone": "0731-85678421","saletaxnum": "339901999999516", "taxnum": "", "telephone": "", "tsfs": "", "detail": [{\'goodsname\': \'早餐面包\', \'hsbz\': \'1\', \'taxrate\': \'0.13\', \'spbm\': \'1030201020000000000\', \'fphxz\': \'1\', \'yhzcbs\': \'0\',\'zzstsgl\': \'\',\'lslbs\': \'\', \'taxamt\': \'5\'},{\'goodsname\': \'停车费\', \'hsbz\': \'1\', \'taxrate\': \'0.05\', \'spbm\': \'3040502020200000000\', \'fphxz\': \'1\', \'yhzcbs\': \'1\', \'zzstsgl\': \'简易征收\', \'lslbs\': \'\', \'taxamt\': \'5\'},{\'goodsname\': \'计生用品\', \'hsbz\': \'1\', \'taxrate\': \'免税\', \'spbm\': \'1070302150000000000\', \'fphxz\': \'1\', \'yhzcbs\': \'1\',\'zzstsgl\': \'免税\', \'lslbs\': \'免税\', \'taxamt\': \'5\'}]}}';

$t = '{"identity":"2329CC5F90EDAA82D8BF8841E87DEBEAC6B53E6C407EA305","order":{"buyername":"test","phone":"15874042184","orderno":0,"invoicedate":"2019-05-16 20:58:04","clerk":"\u7cfb\u7edf","salephone":"0731-","saleaddress":"\u6e56\u5357\u7701\u957f\u6c99\u5e02\u96e8\u82b1\u533a\u9ad8\u94c1\u7ad9","saletaxnum":"339901999999516","kptype":1,"tsfs":-1,"detail":[{"goodsname":"\u505c\u8f66\u8d39","hsbz":0,"taxrate":"0.16","spbm":"3040502020200000000","fphxz":1,"taxfreeamt":0,"tax":0.11,"taxamt":0.7}]}}';

$data = '{"identity":"93363DCC6064869708F1F3C72A0CE72A713A9D425CD50CDE","fpqqlsh":["20170526160449979446"]}';
//$result = Service_Ticket::encrypt($data);
//echo $result;

//Service_Ticket::syncTicketStatus(1);

//require_once APP_ROOT .'lib/sms/Sms.php';
//$config  = FConfig::get('sms.aliyun');
//
//$s = new Sms($config);
//$a = $s->getAdapter('aliyun');
//$a->sendSms(array(
//    'TemplateCode' => 'SMS_165414696',
//    'SignName' => '高铁新城',
//    'mobile' => '15874042184',
//    'template' => json_encode(array('code'=>'1234')),
//    'id' => 1,
//));

//Service_Member::sendVerifySms(15874042184);

echo chr(10);