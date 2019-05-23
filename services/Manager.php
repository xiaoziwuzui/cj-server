<?php
/**
 * Created by PhpStorm.
 * User: jiangtaiping
 * Date: 2017/2/23
 * Time: 11:39
 */
class Service_Manager{

    public static $token_fix = 't_';

    /**
     * 获取后台账号信息
     * @param int $uid 指定的账号ID
     * @return array
     */
    public static function getInfoById($uid=0)
    {
        return Service_Permission::getInfoById($uid);
    }

    public static function clearInfoById($uid=0){
        return Service_Permission::getInfoById($uid,true);
    }

    /**
     * 获取用户的设置项
     * @param int $uid
     * @param bool $update
     * @author 93307399@qq.com
     * @return array
     */
    public static function getSetting($uid = 0,$update = false){
        if ($uid == 0) {
            return array();
        }
        $cacheKey = Service_Permission::$cache_fix . "user_s_" . $uid;
        $result   = FCache::get($cacheKey);
        if (!$result || $update === true) {
            $Table  = new FTable('manager_setting');
            $result = $Table->where(array('uid' => $uid))->find();
            if(!$result){
                $result = array(
                    'test' => 1
                );
            }
            FCache::set($cacheKey, $result, 86400);
        }
        unset($result['uid'],$result['id']);
        return $result;
    }

    /**
     * 获取当前用户的信息和状态
     * @return array|bool
     */
    public static function getSessionData()
    {
        global $_F;
        $auth_str = FCookie::get(FConfig::get('global.admin_cookie_name'));
        list($uid, $auth) = explode("\t", $auth_str);

        if (!$auth) {
            return false;
        }

        $check_auth = null;
        $userInfo   = self::getInfoById($uid);
        if ($userInfo) {
            $check_auth = md5("{$userInfo['username']}|{$userInfo['password']}|{$userInfo['position_id']}");
        }
        /**
         * 加强登录保护
         * 1.是3天前登录的
         * 2.没有最后登录时间的
         * 3.登录认证无效的
         */
        //强制3天未登录时,直接T掉
        if($check_auth != $auth || $userInfo['status'] != 1 || !$userInfo['last_login_time']){
            unset($userInfo,$auth,$auth_str);
            FCookie::set(FConfig::get('global.admin_cookie_name'), '', -1,$_F['http_host']);
            return false;
        }
//        if(ceil((time() - $userInfo['last_login_time']) / 86400) > 3) {
//            unset($userInfo);
//            FCookie::set(FConfig::get('global.admin_cookie_name'), '', -1,$_F['http_host']);
//            return false;
//        }
        if ($check_auth == $auth) {
            $_F['member']      = $userInfo;
            $_F['uid']         = $uid;
            $_F['position_id'] = $userInfo['position_id'];
            $cookieTime        =  FConfig::get('global.session_time');
            //延长登录时间
            FCookie::set(FConfig::get('global.admin_cookie_name'), "{$userInfo['uid']}\t{$check_auth}", $cookieTime,$_F['http_host']);
            return $userInfo;
        }
        return false;
    }

    /**
     * 移除当前账号的认证cookie
     */
    public static function removeSession()
    {
        global $_F;
        FCookie::set(FConfig::get('global.admin_cookie_name'), '', -1,$_F['http_host']);
    }

    /**
     * 批量处理权限值
     * @param array $permission
     * @return array
     */
    public static function batchCheckRole($permission){
        return Service_Permission::batchCheckRole($permission);
    }

    /**
     * 检测是否有权限访问一个地址
     * @param string $url 要访问的方法，为空时自动获取当前的方法
     * @return bool
     */
    public static function checkRole($url = '',$gid=0,$uid=0){
        return Service_Permission::checkRole($url,$gid,$uid);
    }

    /**
     * 获取用户组的权限配置
     * @param int $gid 用户组ID
     * @return array
     */
    public static function getGroupRole($gid){
        $Role = Service_Permission::getPositionRole($gid);
        return array('permission' => $Role['position_permission']);
    }

    /**
     * 更新用户组权限缓存
     * @param int $gid 组id
     */
    public static function clearGroupRole($gid){
        Service_Permission::getPositionRole($gid,true);
//        $cache_key = 'admin_group_info_v2_cache_'.$gid;
//        FCache::delete($cache_key);
//        self::getGroupRole($gid);
    }

    /**
     * 获取加密后的密码
     * @param string $password 密码
     * @return string
     */
    public static function getEncryptPassword($password)
    {
        return Service_Public::getEncryptPassword($password);
    }

    /**
     * 写入一条后台操作日志
     * @param string $action 日志事件
     * @param string $comment 事件详细描述
     * @param int $uid 管理员ID
     * @return bool
     */
    public static function addManagerLog($comment='',$action='',$uid=0){
        global $_F;
        if($action == ''){
            $action = FApp::getController().'_'.strtolower($_F['action']);
        }
        if($action == "" && $comment == "") return false;
        if($uid == 0){
            $uid = isset($_F['uid']) ? $_F['uid'] : 0;
        }
        $logTable = new FTable('manager_log');
        $logTable->insert(array(
            'action'      => $action,
            'comment'     => $comment,
            'mid'         => $uid,
            'create_time' => CURRENT_DATE_TIME,
            'op_data'     => 'GET:'.json_encode($_GET) . (count($_POST) > 0 ? '，POST:'.json_encode($_POST) : ''),
            'ip'          => FRequest::getClientIP()
        ));
        return true;
    }

    /**
     * 获取下属的拓展员ID
     * @param int $uid
     * @return array
     */
    public static function getChilderUserId($uid=0){
        return Service_Permission::getChilderUserId($uid);
    }

    /**
     * 随机取一个QQ号码
     */
    public static function getRandUserQQ(){
        $cache_key = 'manager_qq_list';
        $qqlist = FCache::get($cache_key);
        if(!$qqlist){
            $userTable = new FTable('manager');
            $userList = $userTable->fields('qq')->where(array('account_type'=>2,'status'=>1,'LENGTH(qq)'=>array('gt'=>0)))->select();
            $qqlist = array();
            foreach ($userList as $v){
                $qqlist[] = $v['qq'];
            }
            if(count($qqlist) == 0){
                $qqlist = explode(',',FConfig::get('global.qq_list'));
            }
            FCache::set($cache_key,$qqlist,86400);
        }
        return $qqlist[rand(0,count($qqlist)-1)];
    }

    /**
     * 随机取一个拓展员ID
     */
    public static function getRandOperate(){
        $cache_key = 'manager_operate_list';
        $uidlist = FCache::get($cache_key);
        if(!$uidlist){
            $userTable = new FTable('manager');
            $userList = $userTable->fields('uid')->where(array('account_type'=>2,'status'=>1))->select();
            $uidlist = array();
            foreach ($userList as $v){
                $uidlist[] = $v['uid'];
            }
            FCache::set($cache_key,$uidlist,86400);
        }
        return $uidlist[rand(0,count($uidlist)-1)];
    }

    /**
     * 获取用户的权限配置
     * @param int $uid 用户ID
     * @return array
     */
    public static function getUserRole($uid){
        return Service_Permission::getUserRole($uid);
    }

    /**
     * 更新用户权限缓存
     * @param int $uid id
     */
    public static function clearUserRole($uid){
        Service_Permission::getUserRole($uid,true);
    }

    /**
     * 为当前域名生成一个特定的cookie字段名
     * @param string $name
     * @author xiaojiang432524@163.com
     */
    public static function setManagerCookieName($name = 'admin_cookie_name'){
        global $_F;
        $cookie_name = substr(md5($_F['http_host'] .'_'. FConfig::get('global.'.$name)),0,8);
        FConfig::set('global.'.$name,$cookie_name);
    }

    /**
     * 生成一个用于开启调试信息的访问KEY
     * @param string $key 定位KEY
     * @param int $type 类型,1:和GET参数中的ek比较,2:返回生成的认证KEY
     * @author 93307399@qq.com
     * @return bool|string
     */
    public static function vailDebugToken($key = 'debug',$type = 1){
        global $_F;
        if($type == 1){
            $ek = (isset($_GET['ek']) ? strtoupper(trim($_GET['ek'])) : '');
            if(strlen($ek) !== 8){
                return false;
            }
            $debugString = substr(strtoupper(md5($key .date('Y-m-d'). FConfig::get('global.encrypt_key'))),0,8);
            if($debugString === $ek){
                $_F['debug'] = true;
                return true;
            }else{
                return false;
            }
        }else{
            return substr(strtoupper(md5($key .date('Y-m-d'). FConfig::get('global.encrypt_key'))),0,8);
        }
    }

    /**
     * 获取待选的后台账号列表
     * @param int $type 账号类型
     * @param bool $update
     * @author 93307399@qq.com
     * @return array|bool
     */
    public static function getManagerSelect($type = 1,$update = false){
        global $_F;
        $cache_key = 'm_'.$_F['uid'].'u_sel_list';
        $result    = FCache::get($cache_key);
        if (!$result || $update === true) {
            $table = new FTable('manager');
            $where = array(
                'account_type' => $type,
                'status' => 1
            );
            $permissionWhere = Service_Permission::getByPositionWhereV2();
            if($permissionWhere['where'] !== false){
                $where['uid'] = $permissionWhere['where'];
            }
            $result = $table->fields('uid,username,truename,real_name')->where($where)->order(array('uid' => 'asc'))->select();
            FCache::set($cache_key,$result,60);
            unset($table,$cache_key,$where);
        }
        return $result;
    }

}
