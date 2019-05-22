<?php

/**
 * Created by PhpStorm.
 * User: 93307399@qq.com
 * Date: 2019/5/22
 * Time: 14:57
 */
class Controller_Api_Abstract extends FController
{
    /**
     * 数据库配置
     * @var string
     */
    public $db = '';
    /**
     * 状态类型定义
     * @var array $status_type
     */
    public $status_type = array();
    /**
     * 控制器内容表
     * @var string
     */
    public $table = '';
    /**
     * 允许继承使用的方法
     * @var array
     */
    public $extendAction = array();

    public $user = array();

    public $param = array();

    public function beforeAction()
    {
        global $_F;
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
        header("Content-Type: application/json");
        $this->param = json_decode(FRequest::getRawPostData(),true);
        if($_F['action'] != 'login'){
            $this->authToken();
        }
        return true;
    }

    /**
     * 认证用户令牌
     * @author 93307399@qq.com
     */
    private function authToken(){
        global $_F;
        $ip    = FRequest::get_client_ip(0,true);
        $token = '';
        if($token == ''){
            $token = trim($this->param['token']);
        }
        if($token == '' && isset($_POST['token'])){
            $token = $_POST['token'];
        }
        if($token == '' && isset($_GET['token'])){
            $token = $_GET['token'];
        }
        $result = Service_Manager::AuthToken($token,$ip);
        if($result < 0){
            $msg = array(
                -1 => '请先登录',
                -2 => '用户认证失败',
                -3 => '用户状态过期',
            );
            $this->output(301,$msg[$result]);
        }
        $this->user = Service_Member::getInfoById($result);
        $_F['uid']  = $this->user['uid'];
    }

    public function showMessage($message, $messageType = 'success', $jumpUrl = null) {
        $this->error($message);
    }
}