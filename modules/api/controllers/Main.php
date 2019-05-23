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

    public function indexAction(){
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
            'title' => '您中奖啦!!',
            'create_time' => date('Y-m-d H:i:s'),
            'remark' => '充钱才是强大的根本要素',
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

            $result = $w->sendTemplateMsg(array(
                'openid' => $this->user['openid'],
                'formId' => $formId,
                'template_id' => $config['template_id'],
                'message' => $config['msg']
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
     * 获取用户信息
     * @author 93307399@qq.com
     */
    public function getUserInfoAction(){

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
        $w = new Service_Mini('cj');
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

}
