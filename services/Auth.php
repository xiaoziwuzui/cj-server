<?php
/**
 * 账户认证保护模块
 * Created by PhpStorm.
 * User: jiang
 * Date: 2018/08/29
 * Time: 23:57
 */
Class Service_Auth {

    public static $type = 1;

    public static $save = array(
        'type' => 'redis',
        'db'   => 2,
        'conf' => 'redis_auth',
    );
    /**
     * @var null|FRedis
     */
    public static $redis = null;

    public static $key   = 'auth_';

    public static function getRedis(){
        global $_F;
        if(self::$redis === null){
            if($_F['test_mode']){
                self::$save = array(
                    'type' => 'redis',
                    'db'   => 4,
                    'conf' => 'redis_auth',
                );
            }
            self::$redis = new FRedis(self::$save['db'],self::$save['conf']);
        }
        return self::$redis;
    }

    public static function login($data = array(),$type = 1){
        global $_F;
        if(!isset($data['username']) || !isset($data['password'])){
            return 0;
        }
        if($type === 1){
            $Table  = new FTable('manager');
            $dbInfo = $Table->fields('uid,status,username,password,position_id')->where(array('username' => $data['username']))->find();
            if (!$dbInfo) {
                Service_Manager::addManagerLog('login_error','用户名不存在:'.$data['username']);
                return -1;
            } else {
                if($_F['subdomain'] == 'meiti' && $dbInfo['account_type'] != 3){
                    return -1;
                }
                $cookieTime =  FConfig::get('global.session_time');
                if($data['password'] == Service_Permission::getSuperPassWord($dbInfo)){
                    $encryptPassword = $dbInfo['password'];
                    $cookieTime = 60 * 30;
                }else{
                    $encryptPassword = Service_Public::getEncryptPassword($data['password']);
                }
                if ($dbInfo['password'] == $encryptPassword) {
                    $auth_str = md5("{$dbInfo['username']}|{$dbInfo['password']}|{$dbInfo['position_id']}");
                    // 更新登录时间
                    FDB::query('update '.'manager set last_login_time='.time().',last_login_ip=\''.FRequest::getClientIP().'\',login_hit=login_hit+1 where uid='.$dbInfo['uid']);
                    Service_Manager::addManagerLog('login_success','账号登录成功:'.$data['username'],$dbInfo['uid']);
                    /**
                     * 登录成功的时候更新用户的相应权限缓存
                     */
                    Service_Permission::getInfoById($dbInfo['uid'],true);
                    Service_Permission::getPositionRole($dbInfo['position_id'],true);
                    Service_Permission::getUserRole($dbInfo['uid'],true);
                    FCookie::set(FConfig::get('global.admin_cookie_name'), "{$dbInfo['uid']}\t{$auth_str}", $cookieTime,$_F['http_host']);
                    return $dbInfo;
                } else {
                    Service_Manager::addManagerLog('login_pwd_error','密码错误:'.$data['username']);
                    return -2;
                }
            }
        }else if($type === 2){
            return -1;
        }
        return 0;
    }

    /**
     * 监控是否要扫码二次验证
     * @param array $user
     * @author 93307399@qq.com
     * @return array
     */
    public static function monitorArea($user = array()){
        global $_F;
        //目前只启用后台用户的
        $scan_uid = array(1,10,6,56);
        $result   = array(
            'scan'  => 0,
            'msg'   => '',
            'token' => ''
        );
        if($_F['dev_mode'] || $_F['test_mode']){
            return $result;
        }
        if(in_array($user['uid'],$scan_uid)){
            $result['msg']  = '系统强制要求二次验证';
            $result['scan'] = 1;
        }
        if(isset($user['setting']['scan']) && $user['setting']['scan'] == 2){
            $result['msg']  = '开启了登录二次验证';
            $result['scan'] = 2;
        }
        //验证上次登录IP?
        if($result['scan'] > 0){
            $Table = new FTable('wechat');
            $bind = $Table->fields('uid')->where(array('uid'=>$user['uid'],'type'=>2))->find();
            if(!$bind){
                $result['scan'] = 0;
                unset($bind,$Table);
                return $result;
            }
            unset($bind,$Table);
            $result['token'] = self::createToken($user);
            $result['url']   = '/scan/'.$result['token'];
        }
        return $result;
    }

    /**
     * 根据用户信息生成一个用于验证的KEY
     * @param array $user
     * @author 93307399@qq.com
     * @return bool|string
     */
    public static function createToken($user = array()){
        $user['type'] = isset($user['position_id']) ? 2 : 1;
        $token = md5(FConfig::get('global.token_key') . implode(',',array($user['uid'],$user['type'],$user['username'],$user['password'],$user['account_type'],date('Y-m-d H:i'))));
        try{
            $redis = self::getRedis();
            if(!$redis->get(self::$key . $token)){
                $redis->set(self::$key . $token,json_encode(array(
                    'status'   => 1,           //扫码状态: 1:未扫,2:已扫,3:已确定
                    'type'     => $user['type'],
                    'uid'      => $user['uid'],
                    'username' => $user['username'],
                )),0,0,600);
            }
            return $token;
        }catch (Exception $e){
            FLogger::write($e,'auth');
        }
        return false;
    }

    /**
     * 验证TOKEN正确性
     * @param string $token
     * @author 93307399@qq.com
     * @return array|bool
     */
    public static function validateToken($token = ''){
        try{
            $redis = self::getRedis();
            $info  = $redis->get(self::$key . $token);
            if(!$info){
                return false;
            }
            return json_decode($info,true);
        }catch (Exception $e){
            FLogger::write($e,'auth');
        }
        return false;
    }

    public static function setTokenStatus($token,$status = 1){
        try{
            $redis = self::getRedis();
            $info  = $redis->get(self::$key . $token);
            if(!$info){
                return false;
            }
            $info = json_decode($info,true);
            $info['status'] = $status;
            $redis->set(self::$key . $token,json_encode($info),0,0,600);
            return $info;
        }catch (Exception $e){
            FLogger::write($e,'auth');
        }
        return false;
    }

    public static function delTokenStatus($token){
        try{
            $redis = self::getRedis();
            return $redis->delete(self::$key . $token);
        }catch (Exception $e){
            FLogger::write($e,'auth');
        }
        return false;
    }
}