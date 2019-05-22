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