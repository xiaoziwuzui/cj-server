<?php
/**
 * Created by PhpStorm.
 * User: jiangtaiping
 * Date: 2017/2/23
 * Time: 11:39
 */
class Service_Member{

    public static $token_fix = 't_';

    public static $cache_fix = 'member_v1_';

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

    /**
     * 获取用户缓存信息
     * @param int $uid 指定的UID
     * @param bool $update
     * @return array
     */
    public static function getInfoById($uid=0,$update=false)
    {
        if ($uid == 0) {
            return array();
        }
        $cacheKey = self::$cache_fix . "user_info_v4_" . $uid;
        $result = FCache::get($cacheKey);
        if (!$result || $update === true) {
            $managerTable = new FTable('member', 'm');
            try{
                $result = $managerTable->fields('m.*')->where(array('m.uid' => $uid))->find();
            }catch (Exception $exception){
                FLogger::write($exception,'exception_userInfo');
            }
            FCache::set($cacheKey, $result, 3600);
        }
        return $result;
    }

    /**
     * 更新用户缓存
     * @param int $uid
     * @return array
     * @author 93307399@qq.com
     */
    public static function clearInfoById($uid=0){
        return self::getInfoById($uid,true);
    }

    /**
     * 保存openid和对应的UID到redis
     * @param string $openid 微信openid
     * @param int $uid 用户UID
     * @param int $time 缓存时间
     * @return bool
     */
    public static function setOpenid($openid, $uid, $time = 3600)
    {
        $key = 'openid_' . substr(md5($openid . FConfig::get('global.encrypt_key')),0,16);
        FCache::set($key, $uid, $time);
        return true;
    }

    /**
     * 根据微信openid 取对应的uid
     * @param string $openid 微信openid
     * @param bool $update 是否主动更新
     * @return int
     */
    public static function getOpenid($openid,$update = false)
    {
        $key  = 'openid_' . substr(md5($openid . FConfig::get('global.encrypt_key')),0,16);
        $info = FCache::get($key);
        if(!$info || $update !== false){
            $table = new FTable('member');
            $info  = false;
            try{
                $info = $table->fields('uid')->where(array('openid' => $openid))->find();
            }catch (Exception $exception){
                FLogger::write($exception,'exception');
            }
            if($info){
                $info = $info['uid'];
                FCache::set($key, $info, 3600);
            }
        }
        return $info;
    }

    /**
     * 是否关注判断
     * @param string $openid 微信的OPENID
     * @param int $update 更新状态, 0:不处理,1:强制从数据库刷新缓存,2:通过参数设置缓存,3:删除缓存
     * @param int $status 要设置到缓存的值
     * @return bool 是否关注公众号
     */
    public static function getSubscribe($openid,$update=0,$status=9){
        $cache_key = self::$cache_fix.'subscribe_v1_'.$openid;
        $cache_time = 30 * 86400;
        if($update == 3){
            FCache::delete($cache_key);
            return false;
        }
        if($update == 2){
            FCache::set($cache_key,$status,$cache_time);
        }
        $status = FCache::get($cache_key);
        if(!$status || $update == 1){
            $table = new FTable('member');
            $check = false;
            try{
                $check = $table->where(array('openid' => $openid))->find();
            }catch (Exception $exception){}
            if($check){
                $status = 1;
            }else{
                $status = 9;
            }
            FCache::set($cache_key,$status,$cache_time);
        }
        if($status == 1){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 更新用户最后活动时间
     * @param string $openid 微信openid
     * @param int $sid 来源渠道
     */
    public static function InsertUserList($openid, $sid = 0)
    {
        global $_F;
        $cache_key = 'InsertUserList_v3_' . $openid;
        $cache_data = FCache::get($cache_key);
        $where = array('openid' => $openid);
        $setflag = 1;
        if ($cache_data) {
            $setflag = 0;
            if(self::getSubscribe($openid)){
                /**
                 * 存在缓存,并已经关注过公众号,更新最后活动时间用于发送通知
                 */
                $table = new FTable('member');
                $time = time() + 24 * 3600;
                try{
                    $table->where($where)->update1(array('last_time' => $time));
                }catch (Exception $exception){}
                /**
                 * 设置关注状态缓存时间
                 */
                self::getSubscribe($openid,2,1);
            }else{
                /**
                 * 存在缓存,但没有关注公众号,更新或保存来源二维码信息
                 */
                $setflag = 1;
            }
        }

        if($setflag == 1){
            $table = new FTable('member');
            /**
             * 如果有缓存数据，优先取缓存中的。
             */
            if(is_array($cache_data)){
                $data = $cache_data;
            }else{
                try{
                    $data = $table->where($where)->find();
                }catch (Exception $exception){}
            }
            $time = time() + 24 * 3600;
                $sid  = 0;

            if ($data) {
                if($data['status'] == 2){
                    //重新关注进来的人,上级不允许变动
                    $new_data = array(
                        'last_time'  => $time,
                        'status'     => 1,
                    );
                    try{
                        $table->where($where)->update1($new_data);
                    }catch (Exception $exception){}
                }else{
                    $new_data = array('last_time' => $time);
                    try{
                        $table->where($where)->update1($new_data);
                    }catch (Exception $exception){}
                }
                $data = array_merge($data,$new_data);
            } else {
                /**
                 * 获取用户个人资料保存头像,昵称,性别等
                 */
                require_once(APP_ROOT . 'lib/weixin/Wechat.class.php');
                $weixin = new Wechat(FConfig::get('pay.gtxc'));
                if($_F['dev_mode']){
                    $result = json_decode('{"subscribe":1,"openid":"oXJdmv2pcJ5IoOYeSUXFFQJrVU7s","nickname":"\u98ce\u534e","sex":1,"language":"zh_CN","city":"\u957f\u6c99","province":"\u6e56\u5357","country":"\u4e2d\u56fd","headimgurl":"http:\/\/thirdwx.qlogo.cn\/mmopen\/YRs8fR5u5tx4XDAB7Syv4tFJp0SPviaDofuzpJkX7IektZWeWaaFB0Nj2eticxPg5NfBchPbo2JwpUdqIMPZFd8kXPOW7exJfib\/132","subscribe_time":1463672487,"unionid":"ohoQ6xAkuIPBlHu835Z_WKFYUpOI","remark":"","groupid":0,"tagid_list":[],"subscribe_scene":"ADD_SCENE_OTHERS","qr_scene":0,"qr_scene_str":""}',true);
                }else{
                    $result = $weixin->getUserInfo($openid);
                }
                if($result){
                    $where['nickname'] = $result['nickname'];
                    $where['province'] = $result['province'];
                    $where['city']     = $result['city'];
                    $where['avatar']   = $result['headimgurl'];
                    $where['sex']      = $result['sex'];
                }
                $where['register_time']  = time();
                $where['last_time']      = $time;
                $where['status']         = 1;
                try{
                    $table->save($where);
                }catch (Exception $exception){
                    FLogger::write($exception,'save_wechatUser');
                }
                $data = $where;
            }
            FCache::set($cache_key, $data, 24 * 3600);
            /**
             * 设置关注状态缓存时间
             */
            self::getSubscribe($openid,2,1);
        }
    }

    /**
     * 删除用户关注绑定信息
     * @param string $openid
     * @param bool|int $update
     * @return bool
     */
    public static function DeleteUserList($openid,$update = false)
    {
        $cache_key = 'InsertUserList_v3_' . $openid;
        if($update === 3){
            FCache::delete($cache_key);
            return true;
        }
        $flag = false;
        try{
            $flag = FDB::update('member', array('status' => 2), array('openid' => $openid));
        }catch (Exception $exception){}
        if ($flag) {
            FCache::delete($cache_key);
            self::getSubscribe($openid,3);
        }
        return true;
    }

    /**
     * 格式化地理信息
     * @param array $item
     * @param string $tag
     * @author 93307399@qq.com
     * @return string
     */
    public static function formatAreaInfo($item = array(),$tag = ' '){
        $areaInfo = array();
        if(mb_strlen($item['province']) > 1){
            $areaInfo[] = $item['province'];
        }
        if(mb_strlen($item['city']) > 1){
            $areaInfo[] = $item['city'];
        }
        return implode($tag,$areaInfo);
    }

    /**
     * 验证手机号码是否使用
     * @param $mobile
     * @return bool|null
     * @throws Exception
     * @author 93307399@qq.com
     */
    public static function verifyMobile($mobile){
        $cache_key = 'mb_'.$mobile;
        $cache_info = FCache::get($cache_key);
        if($cache_info){
            return $cache_info;
        }
        $userTable = new FTable('member');
        $info = $userTable->fields('uid')->where(array('mobile'=>$mobile))->find();
        if($info){
            FCache::set($cache_key,$info['uid'],30*60);
            return $info['uid'];
        }
        return true;
    }

    /**
     * 向用户发送模板消息
     * @param int $uid
     * @param array $msg
     * @param string $url
     * @author 93307399@qq.com
     * @return bool|array
     */
    public static function sendNotice($uid,$msg,$url = ''){
        if(FConfig::get('global.wechat_push') != 1){
            return false;
        }
        $userInfo = self::getInfoById($uid);
        if(!$userInfo){
            return false;
        }
        if($userInfo['status'] == 2){
            return false;
        }
        if($userInfo['openid'] == ''){
            return false;
        }
        FLogger::write($msg,'wx_notice');

        $config = self::formatTempateData($msg['type'],$msg);
        if($config){
            require_once(APP_ROOT . 'lib/weixin/Wechat.class.php');
            $weixin      = new Wechat(FConfig::get('pay.cj'));
            if($url == '#'){
                $url = '';
            }
            if($url == '' && isset($config['url'])){
                $url = $config['url'];
            }
            FLogger::write($config,'wx_notice');

            $arr = array(
                'touser'      => $userInfo['openid'],
                'template_id' => $config['template_id'],
                'url'         => $url,
            );
            $arr['data'] = $config['msg'];

            $result  = $weixin->sendTemplateMessage($arr);
            return $result;
        }
        return false;
    }

    /**
     * 验证一个令牌
     * @param $token
     * @param $ip
     * @author 93307399@qq.com
     * @return int
     */
    public static function AuthToken($token,$ip = null){
        if(strlen($token) !== 32){
            //格式错误
            return -1;
        }
        if($ip === null){
            $ip = FRequest::getClientIP();
        }
        $tokenInfo = self::getTokenByCache($token);
        if(!$tokenInfo){
            $table     = new FTable('user_token');
            $tokenInfo = false;
            try{
                $tokenInfo = $table->fields('uid,expire_time,ip,token')->where(array('token'=>$token))->find();
            }catch (Exception $exception){}
            unset($table);
            if(!$tokenInfo){
                //无效令牌
                return -2;
            }
        }
        if($tokenInfo['expire_time'] < time()){
            //过期令牌
            return -3;
        }
        $cacheTime = 3600;
        $cacheInfo = array(
            'uid'         => $tokenInfo['uid'],
            'expire_time' => $tokenInfo['expire_time'],
            'ip'          => $tokenInfo['ip'],
        );
        if(self::AuthNetAddress($ip,$tokenInfo['ip']) && $cacheInfo['expire_time'] > time()){
            $cacheInfo['expire_time'] += (86400 * 3);
            $cacheTime = 86400;
        }
        self::setTokenByCache($token,$cacheInfo,$cacheTime);
        return $tokenInfo['uid'];
    }

    /**
     * 验证两个IP是否在同一城市
     * 暂时通过子网的形式来判断.也许会有问题
     * @param $ip
     * @param $new_ip
     * @author 93307399@qq.com
     * @return bool
     */
    public static function AuthNetAddress($ip,$new_ip){
        if($ip === $new_ip){
            return true;
        }
        $ipInfo    = explode('.',$ip);
        $newIpInfo = explode('.',$new_ip);
        if($ipInfo[0] . '.' . $ipInfo[1] === $newIpInfo[0] . '.' . $newIpInfo[1]){
            return true;
        }
        return false;
    }

    /**
     * 为用户生成令牌
     * @param array $userInfo
     * @param string $ip
     * @author 93307399@qq.com
     * @return string
     */
    public static function GenerateToken($userInfo,$ip = null){
        $token = '';
        if(!$userInfo){
            return $token;
        }
        if($ip === null){
            $ip = FRequest::getClientIP();
        }
        $token = md5(implode('|',array(
            $userInfo['uid'],
            $userInfo['status'],
            $userInfo['openid'],
            FConfig::get('global.token_key'),
        )));
        $table = new FTable('user_token');
        $find  = false;
        try{
            $find = $table->fields('uid')->where(array('uid'=>$userInfo['uid']))->find();
        }catch (Exception $exception){
            FLogger::write($exception,'exception');
        }
        if(!$find){
            try{
                $table->save(array(
                    'uid'         => $userInfo['uid'],
                    'token'       => $token,
                    'create_time' => time(),
                    'expire_time' => time() + (86400 * 3),
                    'ip'          => $ip,
                ));
            }catch (Exception $exception){
                FLogger::write($exception,'exception');
            }
        }else{
            try{
                $table->where(array('uid'=>$userInfo['uid']))->update(array(
                    'token'       => $token,
                    'create_time' => time(),
                    'expire_time' => time() + (86400 * 3),
                    'ip'          => $ip,
                ));
            }catch (Exception $exception){
                FLogger::write($exception,'exception');
            }
        }
        self::setTokenByCache($token,array(
            'uid'         => $userInfo['uid'],
            'create_time' => time(),
            'expire_time' => time() + (86400 * 3),
            'ip'          => $ip,
        ),3600);
        unset($table,$find,$userInfo);
        return $token;
    }

    /**
     * 销毁用户认证令牌
     * @param string $token
     * @author 93307399@qq.com
     * @return bool
     */
    public static function DestroyToken($token){
        if(strlen($token) !== 32){
            return false;
        }
        $uid = self::getTokenByCache($token);
        if(!$uid){
            $table = new FTable('manager_token');
            $find = false;
            try{
                $find = $table->fields('uid')->where(array('token'=>$token))->find();
            }catch (Exception $exception){
                FLogger::write($exception,'exception');
            }
            if(!$find){
                unset($find,$table);
                return false;
            }
            $userInfo = self::getInfoById($find['uid']);
        }else{
            $userInfo = self::getInfoById($uid);
            self::delTokenByCache($token);
        }
        if(!$userInfo){
            return false;
        }
        //随机生成一项加密值,使令牌失效
        $userInfo['openid'] = md5(time());
        self::GenerateToken($userInfo,'127.0.0.1');
        return true;
    }

    public static function getRedis(){
        global $_F;
        if(self::$redis === null){
            if($_F['dev_mode']){
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
     * 从缓存|Redis获取令牌信息
     * @param $token
     * @return array|bool|string
     * @author 93307399@qq.com
     */
    public static function getTokenByCache($token){
        global $_F;
        try{
            if($_F['dev_mode']){
                $result = FCache::get(self::$token_fix . $token);
            }else{
                $redis = self::getRedis();
                $result = $redis->get(self::$token_fix . $token);
            }
            return $result;
        }catch (Exception $e){
            FLogger::write($e,'exception');
        }
        return false;
    }

    /**
     * 设置令牌信息到缓存|Redis
     * @param string $token
     * @param array $data
     * @param int $expire
     * @return bool
     * @author 93307399@qq.com
     */
    public static function setTokenByCache($token,$data,$expire){
        global $_F;
        try{
            if($_F['dev_mode']){
                FCache::set(self::$token_fix . $token,$data,$expire);
                $result = true;
            }else{
                $redis  = self::getRedis();
                $result = $redis->set(self::$token_fix . $token,$data,0,0,$expire);
            }
            return $result;
        }catch (Exception $e){
            FLogger::write($e,'exception');
        }
        return false;
    }

    /**
     * 从缓存|Redis删除令牌
     * @param $token
     * @return bool
     * @author 93307399@qq.com
     */
    public static function delTokenByCache($token){
        global $_F;
        try{
            if($_F['dev_mode']){
                FCache::delete(self::$token_fix . $token);
                return true;
            }else{
                $redis = self::getRedis();
                return $redis->delete(self::$token_fix . $token);
            }
        }catch (Exception $e){
            FLogger::write($e,'exception');
        }
        return false;
    }

    /**
     * 格式化模板消息主体
     * @param $index
     * @param $msg
     * @return array|bool
     * @author 93307399@qq.com
     */
    public static function formatTempateData($index,$msg){
        $cfg = FConfig::get('msg_template.' . $index);
        if(!$cfg){
            return false;
        }
        $data        = array();
        $msg_key     = array_keys($msg);
        $msg_value   = array_values($msg);
        foreach ($cfg['data'] as $key=>$value){
            if(is_array($value)){
                if(isset($value['tpl'])){
                    $item_value = str_replace($msg_key,$msg_value,$value['tpl']);
                }else{
                    if(isset($msg[$value['value']])){
                        $item_value = $msg[$value['value']];
                    }else{
                        $item_value = $value['value'];
                    }
                }
            }else{
                if(isset($msg[$value])){
                    $item_value = $msg[$value];
                }else{
                    $item_value = $value;
                }
            }
            if($item_value != ''){
                $data[$key] = array(
                    'value' => $item_value,
//                    'color' => '#000000'
                );
//                if(isset($value['color'])){
//                    $data[$key]['color'] = $value['color'];
//                }
            }
        }
        $result = array(
            'template_id' => $cfg['template_id'],
            'msg' => $data
        );
        if(isset($cfg['url'])){
            $result['url'] = $cfg['url'];
        }
        return $result;
    }
}
