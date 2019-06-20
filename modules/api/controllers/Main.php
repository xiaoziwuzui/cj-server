<?php

/**
 * Created by PhpStorm.
 * User: 93307399@qq.com
 * Date: 2019/5/22
 * Time: 14:22
 */
class Controller_Api_Main extends Controller_Api_Abstract
{

    public function beforeAction()
    {
        $flag = parent::beforeAction();
        if($flag){
            //初始化一些东西
        }
        return $flag;
    }

    /**
     * 获取首页数据
     * @author 93307399@qq.com
     */
    public function indexAction(){
        //获取当前在进行中的抽奖活动










        $this->success('success');
    }

    /**
     * 上传表单ID
     * @author 93307399@qq.com
     */
    public function UpFormIDAction(){
        $formId = $this->param['formId'];
        if(!$formId){
            $this->error('formid empty!');
        }
        FLogger::write($formId,'miniTemplate');
        /**
         * 发起一条消息推送
         */
        $w = new Service_Mini('cj');

        $config = Service_Member::formatTempateData('buy_success',array(
            'title'       => '超级游戏礼包到账啦!!',
            'create_time' => date('Y-m-d H:i:s'),
            'order_no'    => date('Ymd') . FMisc::str2crc32(md5(date('H:i:s'))),
            'price'       => '6 元',
            'number'      => '超值档*1',
            'remark'      => '充钱才是强大的根本要素',
        ));
        if($config){
//            $msg = $w->weApp->getTemplateMsg();
////            $a = $msg->getListFromLib(0,10);
//            //手动添加一个模板
//            $d = $msg->getTempFromLib('AT0002');
//            $msg->add('AT0002',array(
//                5,4,13,
//            ));
//            $list = $msg->getList(0,5);
//            $this->output($list);

            $result = $w->sendTemplateUnionMsg(array(
                'openid'      => $this->user['openid'],
                'formId'      => $formId,
                'template_id' => $config['template_id'],
                'message'     => $config['msg']
            ));
            FLogger::write($result,'miniTemplate');
        }
        $this->success('success');
    }

    /**
     * 获取首页数据
     * @author 93307399@qq.com
     */
    public function getIndexAction(){

    }

    /**
     * 用户登录获取令牌
     * @author 93307399@qq.com
     */
    public function loginAction(){
        $code = FRequest::getString('code');
        if(strlen($code) == 0){
            $this->error('code empty!');
        }
        $w = new Service_Mini('xhb');
        $result = $w->codeToSession($code);
        FLogger::write($result);
        if($result){
            /**
             * 处理用户注册账号操作
             */
            $table = new FTable('member','','default');
            $uid   = Service_Member::getOpenid($result['openid']);
            if($uid){
                $userInfo = Service_Member::getInfoById($uid);
                //更新会话密钥
                try{
                    $table->where(array('uid'=>$uid))->update(array('session_key' => $result['session_key']));
                    //前期可以直接查询更新缓存,以后要改成直接更新缓存
                    Service_Member::clearInfoById($uid);
                }catch (Exception $exception){
                    FLogger::write($exception,'exception');
                }
            }else{
                //注册一个用户账号
                $userInfo = array(
                    'openid'   => $result['openid'],
                    'nickname' => '',
                    'truename' => '',
                    'avatar'   => '',
                    'sex'      => 0,
                    'mobile'   => '',
                    'birthday' => 0,
                    'email'    => '',
                    'status'   => 1,
                    'session_key'   => $result['session_key'],
                    'register_time' => time(),
                    'last_time'     => time(),
                );
                $uid = false;
                try{
                    $table->save($userInfo);
                    $uid = $table->lastInsertId();
                    Service_Member::setOpenid($result['openid'],$uid);
                    $userInfo['uid'] = $uid;
                }catch (Exception $exception){
                    FLogger::write($exception,'exception');
                }
                if(!$uid){
                    $this->error('用户资料注册失败,请稍候再试!');
                }
            }
            /**
             * 统一组装返回的用户资料
             */
            $output = array(
                'uid'      => $userInfo['uid'],
                'openid'   => $userInfo['openid'],
                'nickname' => $userInfo['nickname'],
                'avatar'   => $userInfo['avatar'],
                'token'    => Service_Member::GenerateToken($userInfo),
            );
            $this->output($output);
        }else{
            $errInfo = $w->getErrInfo();
            $this->error($errInfo['errMsg']);
        }
    }

    /**
     * 令牌认证模式
     * 基类中已经做了前置检查,此处只需要返回用户资料和新的令牌即可
     * @author 93307399@qq.com
     */
    public function authAction(){
        global $_F;
        $userInfo = Service_Member::getInfoById($_F['uid']);
        $output = array(
            'uid'      => $userInfo['uid'],
            'openid'   => $userInfo['openid'],
            'nickname' => $userInfo['nickname'],
            'avatar'   => $userInfo['avatar'],
            'token'    => Service_Member::GenerateToken($userInfo),
        );
        $this->output($output);
    }

    /**
     * 更新用户资料
     * 这个接口对前端应该是无感的 始终返回成功
     * @author 93307399@qq.com
     */
    public function upUserInfoAction(){
        $encryptedData = $this->param['encryptedData'];
        $userInfo = $this->user;
        $iv       = $this->param['iv'];
        $w        = new Service_Mini('cj');
        $result   = '';
        $code     = $w->decryptData($userInfo['session_key'],$encryptedData,$iv,$result);
        if($code === 0){
           //更新数据库中的用户资料
            $result = json_decode($result,true);
            /**
             * 与缓存数据比较,不一样的时候才更新
             * 机制:md5(数据 + key)
             */
            $key = FConfig::get('global.encrypt_key');
            if(md5(implode('',array(
                $userInfo['nickname'],
                $userInfo['avatar'],
                $userInfo['sex'],
                $userInfo['city'],
                $userInfo['province'],
                $key
            ))) != md5(implode('',array(
                    $result['nickName'],
                    $result['avatarUrl'],
                    $result['gender'],
                    $result['city'],
                    $result['province'],
                    $key
                )))){
                $table  = new FTable('member','','default');
                try{
                    $table->where(array('uid'=>$userInfo['uid']))->update(array(
                        'nickname' => $result['nickName'],
                        'avatar'   => $result['avatarUrl'],
                        'sex'      => $result['gender'],
                        'city'     => $result['city'],
                        'province' => $result['province'],
                        'last_time' => time(),
                    ));
                    //前期可以直接查询更新缓存,以后要改成直接更新缓存
                    Service_Member::clearInfoById($userInfo['uid']);
                }catch (Exception $exception){
                    FLogger::write($exception,'exception');
                }
            }
        }
        $this->output(200);
    }

    public function meAction(){
        echo '<html lang="zh"> <head><meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><title>我的</title></head> <body><button class="auth-btn" bindtap="getUserInfo" open-type="getUserInfo"><text>授权登录</text></button></body> </html> ';
    }
}
