<?php
/**
 * Created by PhpStorm.
 * User: jiangtaiping
 * Date: 2017/2/23
 * Time: 11:39
 */
class Service_Member{

    public static $cache_fix = 'member_v1_';

    /**
     * 获取会员信息
     * @param int $uid 指定的账号ID
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

    public static function clearInfoById($uid=0){
        return self::getInfoById($uid,true);
    }

    /**
     * 获取当前用户的信息和状态
     * @throws Exception
     * @return array|bool
     */
    public static function getSessionData()
    {
        global $_F;
        $auth_str = FCookie::get(FConfig::get('global.member_cookie_name'));
        list($uid, $auth) = explode("\t", $auth_str);
        if (!$auth) {
            return false;
        }
        $check_auth = null;
        $memberData = self::getInfoById($uid);
        if ($memberData) {
            $check_auth = md5("{$memberData['username']}|{$memberData['password']}|{$memberData['media_id']}");
        }
        if ($check_auth == $auth) {
            $_F['member'] = self::getInfoById($uid);
            $_F['uid']    = $uid;
            $_F['gid']    = $memberData['group_id'];
            return $memberData;
        }

        FCookie::set(FConfig::get('global.member_cookie_name'), '', -1,$_F['http_host']);
        return false;
    }

    /**
     * 移除当前账号的认证cookie
     */
    public static function removeSession()
    {
        global $_F;
        FCookie::set(FConfig::get('global.member_cookie_name'), '', -1,$_F['http_host']);
    }

    /**
     * 检测是否有权限访问一个地址
     * @param string $url 要访问的方法，为空时自动获取当前的方法
     * @param int $gid
     * @return bool
     */
    public static function checkRole($url = '',$gid=0){
        global $_F;
        $controller = str_replace('controller_' . $_F['module'] . '_', '', strtolower($_F['controller']));
        if ($gid == 0) {
            $gid = $_F['member']['account_type'];
        }
        if($controller !== 'member' && $controller !== 'material'){
            return true;
        }
        $memberPermission = FConfig::get('member_permission');
        $permission = $memberPermission[$gid];
        /**
         * 格式化配置的权限.
         */
        $formatP = array();
        foreach ($permission as $k=>$v){
            $formatP[strtolower($k)] = $v;
        }
        if ($url == '') {
            $url = '/' . $controller . '/' . $_F['action'];
        }
        $url = strtolower($url);
        if (isset($formatP[$url])) {
            unset($formatP);
            return true;
        }
        unset($formatP);
        return false;
    }


    /**
     * 保存openid和对应的UID到redis
     * @param string $openid_key 微信openid
     * @param int $uid 用户UID
     * @param int $time 缓存时间
     * @return bool
     */
    public static function setOpenid($openid_key, $uid, $time = 2592000)
    {
        FCache::set($openid_key, $uid, $time);
        return true;
    }

    /**
     * 根据微信openid 取对应的uid
     * @throws Exception
     * @param string $openid_key
     * @return int
     */
    public static function getOpenid($openid_key)
    {
        $table = new FTable('member');
        $info = $table->fields('uid')->where(array('openid' => $openid_key))->find();
        if ($info) {
            return $info['uid'];
        } else {
            return false;
        }
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
     * 验证手机号码是否存在
     * @param $mobile
     * @return bool|null
     * @throws Exception
     * @author 93307399@qq.com
     */
    public static function verifyMobile($mobile){
        $cache_key = 'mbp3_'.$mobile;
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
     * 发送验证码
     * @param string $mobile
     * @param string $key
     * @author 93307399@qq.com
     * @return bool|int
     */
    public static function sendVerifySms($mobile,$key='register_code'){
        session_start();
        srand((double)microtime() * 1000000);
        $code = rand(1000, 9999);
        $_SESSION[$key] = $code;
        $data = array(
            'TemplateCode' => 'SMS_165414696',
            'SignName' => '高铁新城',
            'mobile'   => $mobile,
            'template' => json_encode(array('code'=>$code)),
            'id'       => 1,
        );
        $result = self::sendSms($mobile,$data);
        if($result == 'success'){
            return $code;
        }else{
            return false;
        }
    }

    /**
     * 向指定手机发送一条短信
     * @param string $mobile 手机号码
     * @param string|array $message 短信内容
     * @author 93307399@qq.com
     * @return string
     */
    public static function sendSms($mobile,$message){
        global $_F;
        require_once APP_ROOT .'lib/sms/Sms.php';
        $status = 'Faild';
        if(!$_F['dev_mode']){
            //调用阿里云短信接口
            $config  = FConfig::get('sms.aliyun');
            $s = new Sms($config);
            $a = $s->getAdapter('aliyun');
            $result = $a->sendSms($message);
            if($result){
                $status = 'success';
            }
        }else{
            $status = 'success';
        }
        $status = strtolower($status);
        $table = new FTable('sms');
        $table->insert(array(
            'mobile'      => $mobile,
            'msg'         => json_encode($message),
            'create_time' => time(),
            'code'        => $status
        ));
        return $status;
    }

    /**
     * 获取用户当前有效的卡券数
     * @param int $uid
     * @param bool $update
     * @return array|null
     * @throws Exception
     * @author 93307399@qq.com
     */
    public static function getReceiveCardNumber($uid = 0,$update = false){
        $cache_key = self::$cache_fix . '_card_n_' . $uid;
        $result = FCache::get($cache_key);
        if (!$result || $update === true) {
            $Table  = new FTable('card_log','l');
            $where  = array(
                'l.uid'=>$uid,'l.status'=>1,'c.status'=>1,'c.expire_time'=>array('gt'=>time())
            );
            $data = $Table->fields('count(l.id) as total')->leftJoin('card','c','l.card_id=c.id')->where($where)->find();
            $result = $data['total'];
            FCache::set($cache_key,$result,600);
            unset($table,$data);
        }
        unset($cache_key);
        return $result;
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
        $host_name   = 'http://'.FConfig::get('global.share_domain').'/';
        /**
         * 停车信息提醒
         * KnGH3-_QFyHb5QeGTRoxdENQYOCaqt05AFU9jIh1rl8
         * {{first.DATA}}
        停车场：{{keyword1.DATA}}
        入场时间：{{keyword2.DATA}}
        入场收费员：{{keyword3.DATA}}
        泊位编号：{{keyword4.DATA}}
        预收金额：{{keyword5.DATA}}
        {{remark.DATA}}
         */
        /**
         * 开票成功通知
         * WhLs9gC0xdtOghfQObB1EpqGujQdIbb5TrQpWvtX_lY
         * {{first.DATA}}
        发票代码：{{keyword1.DATA}}
        发票号码：{{keyword2.DATA}}
        开票日期：{{keyword3.DATA}}
        发票金额：{{keyword4.DATA}}
        {{remark.DATA}}
         */
        $msgType   = array(
            'push_fee'   => array(
                'template_id' => 'KnGH3-_QFyHb5QeGTRoxdENQYOCaqt05AFU9jIh1rl8',
                'data'        => array(
                    'first'    => array(
                        'tpl' => '尊敬的 plate 车主',
                        'color' => '#173177',
                    ),
                    'keyword1' => array(
                        'value' => '高铁新城东广场',
                        'color' => '#173177',
                    ),
                    'keyword2' => 'income_time',
                    'keyword3' => '无',
                    'keyword4' => '无',
                    'keyword5' => array(
                        'tpl' => 'money元',
                        'color' => '#173177',
                    ),
                    'remark'   => array(
                        'tpl' => 'remark',
                        'color' => '#173177',
                    ),
                ),
                //'url' => 'member/success',
            ),
            'ticket_success'   => array(
                'template_id' => 'WhLs9gC0xdtOghfQObB1EpqGujQdIbb5TrQpWvtX_lY',
                'data'        => array(
                    'first'    => array(
                        'tpl' => '尊敬的用户，您的电子发票已生成',
                        'color' => '#173177',
                    ),
                    'keyword1' => 'fpcode',
                    'keyword2' => 'fphm',
                    'keyword3' => 'ticket_date',
                    'keyword4' => array(
                        'tpl' => 'money元',
                        'color' => '#173177',
                    ),
                    'remark'   => array(
                        'tpl' => 'remark',
                        'color' => '#173177',
                    ),
                ),
                //'url' => 'member/success',
            ),
        );
        if(isset($msg['type']) && isset($msgType[$msg['type']])){
            require_once(APP_ROOT . 'lib/weixin/Wechat.class.php');
            $weixin      = new Wechat(FConfig::get('pay.gtxc'));
            $template_id = $msgType[$msg['type']]['template_id'];
            $data        = array();
            $msg_key     = array_keys($msg);
            $msg_value   = array_values($msg);
            foreach ($msgType[$msg['type']]['data'] as $key=>$value){
                $item_value = '';
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
                        'color' => '#000000'
                    );
                    if(isset($value['color'])){
                        $data[$key]['color'] = $value['color'];
                    }
                }
            }
            if($url == '#'){
                $url = '';
            }
            if($url == '' && isset($msgType[$msg['type']]['url'])){
                $url = $msgType[$msg['type']]['url'];
            }
            FLogger::write($data,'wx_notice');

            $arr = array(
                'touser'      => $userInfo['openid'],
                'template_id' => $template_id,
                'url'         => $url,
            );
            $arr['data'] = $data;

            $result  = $weixin->sendTemplateMessage($arr);
            return $result;
        }
        return false;
    }



}
