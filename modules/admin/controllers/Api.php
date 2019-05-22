<?php
/**
 * Created by PhpStorm.
 * User: 93307399@qq.com
 * Date: 2018/5/12
 * Time: 14:57
 */
class Controller_Admin_Api extends FController {

    /**
     * 数据库配置
     * @var string
     */
    public $db = '';

    public $user = array();

    public $param = array();

    /**
     * 前置参数校验
     * @author 93307399@qq.com
     * @return bool
     */
    public function beforeAction(){
        global $_F;
        if($_SERVER['USER'] == 'jiangtaiping'){
            $_F['dev_mode'] = true;
        }
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
        header("Content-Type: application/json");
//        $this->authToken();
        return true;
    }

    function showMessage($message, $messageType = 'success', $jumpUrl = null)
    {
        $this->output($messageType === 'success' ? 200 : 500,$message);
    }

    function display($tpl = null)
    {
        parent::display(str_replace('admin/', '', $tpl));
    }

    /**
     * 认证用户令牌
     * @author 93307399@qq.com
     */
    private function authToken(){
        $ip    = FRequest::get_client_ip(0,true);
        $time  = FRequest::getString('time');
        $sign  = FRequest::getString('sign');
        $white_list = FConfig::get('white_list');
        if($sign == '' && isset($_POST['token'])){
            $sign = $_POST['token'];
        }
        if(!isset($white_list[$ip]) && $white_list[$ip] != 1){
            $this->output(501,'用户认证失败');
        }
        if($sign !== md5(md5($time . 'api_key000000'))){
            $this->output(500,'用户认证失败');
        }
    }
}