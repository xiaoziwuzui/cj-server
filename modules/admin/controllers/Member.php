<?php

/**
 * Created by PhpStorm.
 * User: 93307399@qq.com
 * Date: 2017/2/23
 * Time: 14:57
 */
class Controller_Admin_Member extends Controller_Admin_Abstract
{
    private $member_sex = array();

    public function beforeAction()
    {
        $flag = parent::beforeAction();
        if($flag){
            $this->status_type = FConfig::get('type.member_status_type');
            $this->member_sex = FConfig::get('type.member_sex');
            $this->assign('status_type', $this->status_type);
            $this->assign('member_sex', $this->member_sex);
        }
        return $flag;
    }

    /**
     * 用户列表
     */
    public function defaultAction()
    {
        $page     = FRequest::getInt('page');
        $keyword  = trim(FRequest::getString('keyword'));
        $truename = trim(FRequest::getString('truename'));
        $openid   = trim(FRequest::getString('openid'));
        $sex      = isset($_GET['sex']) ? $_GET['sex'] : '';
        $status   = FRequest::getInt('status');
        $where = array(
            'm.status' => array('lt' => 9),
        );

        if ($keyword) {
            if (preg_match('#^\d+$#', $keyword)) {
                $where['m.uid'] = $keyword;
            } else {
                $where['m.nickname'] = array('like' => $keyword);
            }
        }
        if ($truename) {
            $where['m.truename'] = $truename;
        }
        if ($openid) {
            $where['m.openid'] = $openid;
        }
        if ($sex != '') {
            $sex = intval($sex);
            $where['m.sex'] = $sex;
        }
        if ($status && $status < 9) {
            $where['m.status'] = $status;
        }
        $table = new FTable('member', 'm');
        $list = $table->fields('m.*')->page($page)->where($where)->order(array('m.uid' => 'desc'))->limit(30)->select();
        $pagerInfo = $table->getPagerInfo();
        foreach ($list as $k=>$v){
            $v['last_time'] = $v['last_time'] ? $v['last_time'] - 86400 : 0;
            $v['area_info'] = Service_Member::formatAreaInfo($v);
            $list[$k] = $v;
        }
        $this->assign('page_info', $pagerInfo);
        $this->assign('list', $list);
        $this->assign('keyword', $keyword);
        $this->assign('sex', $sex);
        $this->assign('truename', $truename);
        $this->display('admin/member/list');
    }

    /**
     * 更新用户信息
     */
    public function modifyAction()
    {
        $id = FRequest::getInt('id');
        $memberTable = new FTable('member');
        if (!$id) {
            $this->error('请选择要操作的用户!');
        }
        $userInfo = $memberTable->where(array('uid' => $id))->find();
        if (!$userInfo) {
            $this->error('没有找到要操作的用户!');
        }
        $is_super = Service_Permission::checkRole('is_super_manager');
        if ($this->isPost()) {
            $truename = FRequest::getPostString('truename');
            $mobile   = FRequest::getPostString('mobile');
            $birthday = strtotime(FRequest::getPostString('birthday'));
            $type     = intval(FRequest::getPostInt('type'));

            if(!isset($this->account_type[$type])){
                $type = 1;
            }

            $truename = Service_Public::filterEmoji($truename);

            $data = array(
                'truename' => $truename,
                'mobile'   => $mobile,
            );
            if($birthday !== false){
                $data['birthday'] = $birthday;
            }
            $no_empty_key = array(
                'email','province','city','area','address'
            );
            foreach ($no_empty_key as $key){
                $value = isset($_POST[$key]) ? trim($_POST[$key]) : '';
                if($value != ''){
                    $data[$key] = $value;
                }
            }

            $result = $memberTable->where(array('uid' => $id))->update1($data);
            Service_Member::getInfoById($id,true);
            Service_Manager::addManagerLog($id);
            if(!$result){
                $this->error('没有更新任何资料!');
            }else{
                $this->success('修改用户账号成功！', 'r');
            }
        }
        $userInfo['birthday']  = $userInfo['birthday'] > 0 ? date('Y-m-d',$userInfo['birthday']) : '';
        $userInfo['area_info'] = Service_Member::formatAreaInfo($userInfo);
        ;
        $this->assign('is_super', $is_super);
        $this->assign('info', $userInfo);
        $this->display('member/modify');
    }

    /**
     * 删除用户
     * 93307399@qq.com
     */
    public function deleteAction()
    {
        $id = FRequest::getInt('id');
        if (!$id) {
            $this->error('请选择要操作的用户');
        }
        if ($id) {
            $Table = new FTable('member');
            $info  = $Table->where(array('uid' => $id))->find();
            $order = new FTable('pay_order');
            $o = $order->where(array('uid'=>$id,'status'=>array('lt'=>9)))->find();
            if($o){
                $this->error('用户已经存在充值订单,请先删除所有的充值订单后再试');
            }
            FCache::delete('InsertUserList_v3_'.$info['openid']);
            Service_Member::getSubscribe($info['openid'],3);
            $Table->where(array('uid' => $id))->remove(true);
            Service_Manager::addManagerLog('member_delete', $id);
            $this->success('删除用户成功！', 'r');
        } else {
            $this->error('error');
        }
    }

    /**
     * 获取微信用户资料
     * @author 93307399@qq.com
     */
    public function referUserAction(){
        $id = FRequest::getInt('id');
        $userInfo = Service_Member::getInfoById($id);
        /**
         * 获取用户个人资料保存头像,昵称,性别等
         */
        require_once(APP_ROOT . 'lib/weixin/Wechat.class.php');
        $weixin = new Wechat(FConfig::get('pay.gtxc'));
        $result = $weixin->getUserInfo($userInfo['openid']);
        $where = array();
        if($result){
            if(isset($_GET['d'])){
                dump($result);
            }
            $where['nickname'] = $result['nickname'];
            $where['province'] = $result['province'];
            $where['city']     = $result['city'];
            $where['avatar']   = $result['headimgurl'];
            $where['sex']      = $result['sex'];
            $Table = new FTable('member');
            $result = $Table->where(array('uid' => $id))->update($where);
            if($result){
                Service_Member::clearInfoById($id);
                $this->success('更新资料成功','r');
            }else{
                $this->error('没有变动');
            }
        }else{
            $this->error('获取失败');
        }
    }
}