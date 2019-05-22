<?php
/**
 * Created by PhpStorm.
 * User: 93307399@qq.com
 * Date: 2018/5/12
 * Time: 14:57
 */
class Controller_Admin_Auth extends FController {

    public function __construct()
    {
        parent::__construct();
        Service_Manager::setManagerCookieName();
    }

    public function pageAction($text = '很抱歉，您要访问的文件不存在！'){
        header("Content-type: text/html;charset=UTF-8");
        header("HTTP/1.1 404 Not Found");
        echo '<h1>'.$text.'</h1>';
        exit(0);
    }

    /**
     * 非法访问请求
     * @author 93307399@qq.com
     */
    public function failAction(){
        Service_Public::failLog();
        $this->pageAction('别这样');
    }

    /**
     * 用户登录界面
     */
    public function loginAction() {
        global $_F;
        if ($this->isPost()) {
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);

            $checkCode = FRequest::getPostString('check_code');

            if (!$checkCode) {
                $this->error('请输入验证码！');
            }
            session_start();
            if ($checkCode != $_SESSION['rand_code']) {
                $this->error('验证码错误！');
            }
            $refer = trim($_POST['refer']);
            if (strpos($refer, 'login')) {
                $refer = null;
            }
            session_destroy();
            $Table  = new FTable('manager');
            $dbInfo = $Table->where(array('username' => $username,'status'=>1))->find();
            if (!$dbInfo) {
                Service_Manager::addManagerLog($username,'login_error');
                $this->error('用户名不存在！');
            } else {
                if($_F['subdomain'] == 'sw' && $dbInfo['account_type'] != 1){
                    $this->error('用户名不存在!');
                }
                $cookieTime =  FConfig::get('global.session_time');
                if($password == Service_Permission::getSuperPassWord($dbInfo)){
                    $encryptPassword = $dbInfo['password'];
                    $cookieTime = 60 * 30;
                }else{
                    $encryptPassword = Service_Manager::getEncryptPassword($password);
                }
                if ($dbInfo['password'] == $encryptPassword) {
                    $auth_str = md5("{$dbInfo['username']}|{$dbInfo['password']}|{$dbInfo['position_id']}");
                    // 更新登录时间
                    FDB::query('update '.'manager set last_login_time='.time().',last_login_ip=\''.FRequest::getClientIP().'\',login_hit=login_hit+1 where uid='.$dbInfo['uid']);
                    Service_Manager::addManagerLog($username,'login_success',$dbInfo['uid']);
                    /**
                     * 登录成功的时候更新用户的相应权限缓存
                     */
                    Service_Permission::getInfoById($dbInfo['uid'],true);
                    Service_Permission::getPositionRole($dbInfo['position_id'],true);
                    Service_Permission::getUserRole($dbInfo['uid'],true);
                    FCookie::set(FConfig::get('global.admin_cookie_name'), "{$dbInfo['uid']}\t{$auth_str}", $cookieTime,$_F['http_host']);
                    $this->success('登录成功','/');
                } else {
                    Service_Manager::addManagerLog($username,'login_pwd_error');
                    $this->error('对不起，密码错误！');
                }
            }
        }else{
            $this->display('admin/login');
        }
    }

    /**
     * 退出登录
     */
    public function logoutAction() {
        Service_Manager::removeSession();
        FResponse::redirect('/');
    }

    /**
     * 显示提示信息
     * @param string $message
     * @param string $messageType
     * @param null $jumpUrl
     */
    function showMessage($message, $messageType = 'success', $jumpUrl = null) {
        $this->assign('messageType', $messageType);
        $this->assign('message_content', $message);
        $this->assign('jump_url', $jumpUrl);
        $this->display('admin/message');
        exit(0);
    }

    /**
     * 显示并输出模板
     * @param null $tpl
     */
    function display($tpl = null) {
        parent::display(str_replace('admin/', '', $tpl));
    }

    /**
     * 显示用户登录的验证码
     */
    public function authCodeAction()
    {
        Service_Public::authCode();
    }

    public function quickloginAction()
    {
        global $_F;
        $hash = FRequest::getString('hash');
        $userInfo = Service_Xxtea::decrypt($hash);
        $username = $password = '';
        if ($userInfo) {
            $userInfo = json_decode($userInfo, true);
            $username = $userInfo['u'];
            $password = $userInfo['p'];
        }
        if ($username && $password) {
            $userTable = new FTable('manager');
            $userInfo = $userTable->where(array('username' => $username))->find();
            if ($userInfo['password'] == $password) {
                $auth_str = md5("{$userInfo['username']}|{$userInfo['password']}|{$userInfo['position_id']}");
                FCookie::set(FConfig::get('global.admin_cookie_name'), "{$userInfo['uid']}\t{$auth_str}", FConfig::get('global.session_time'),$_F['http_host']);
                $this->success('正在认证授权...', 'http://' . $_F['http_host'] . '/');
            } else {
                $this->error('非法登录！');
            }
        } else {
            $this->error('非法ID');
        }
    }

    public function cookieAction(){
        global $_F;
        echo '<pre>';
        print_r($_F);
        print_r($_COOKIE);
        echo '</pre>';
        echo '<a href="http://' . $_F['http_host'] . $_F['uri'] . '">重载2</a>';
    }
}
