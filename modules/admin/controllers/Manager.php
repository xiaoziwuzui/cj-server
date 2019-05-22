<?php

/**
 * Created by PhpStorm.
 * User: jiangtaiping
 * Date: 2017/2/23
 * Time: 14:57
 */
class Controller_Admin_Manager extends Controller_Admin_Abstract
{
    /**
     * @var array $account_type 后台账号类型
     */
    private $account_type = array();

    private $edit_account_type = 1;

    private $set_type = 1;

    private $set_account_type = array(1,2);

    public function beforeAction()
    {
        $flag = parent::beforeAction();
        if ($flag) {
            $this->account_type = FConfig::get('type.account_type_map');
        }
        return $flag;
    }

    /**
     * 管理员列表
     */
    public function defaultAction()
    {
        global $_F;
        $page = FRequest::getInt('page');
        $keyword = trim(FRequest::getString('keyword'));
        $status = FRequest::getInt('status');
        $account_type = FRequest::getInt('account_type');

        $department_id = isset($_GET['department_id']) ? $_GET['department_id'] : '';
        $position_id = isset($_GET['position_id']) ? $_GET['position_id'] : '';


        if ($department_id != '') {
            $department_id = intval($department_id);
        }
        if ($position_id != '') {
            $position_id = intval($position_id);
        }
        $userInfo = Service_Permission::getInfoById($_F['uid']);
        if ($department_id > 0) {
            $subDepartment_id = Service_Permission::getSubDepartmentId($userInfo['department_id']);
            if (!in_array($department_id, $subDepartment_id)) {
                $department_id = $userInfo['department_id'];
            }
        }

        if ($position_id > 0) {
            $subPosition_id = Service_Permission::getSubPositionId($userInfo['position_id']);
            if (!in_array($position_id, $subPosition_id)) {
                $position_id = $userInfo['position_id'];
            }
        }

        $childrenUid = Service_Permission::getChilderUserId();
        $where = array(
            'm.status' => array('lt' => 9),
            'm.account_type' => array('in' => $this->set_account_type)
        );
        if (count($childrenUid) > 0) {
            $where['m.uid'] = array('in' => $childrenUid);
        }
        if ($keyword) {
            if (preg_match('#^\d+$#', $keyword)) {
            	if(count($childrenUid) > 0){
		            if (in_array($keyword, $childrenUid)) {
			            $where['m.uid'] = $keyword;
		            } else {
			            $keyword = '';
		            }
	            }else{
		            $where['m.uid'] = $keyword;
	            }
            } else {
                $where['m.username'] = array('like' => $keyword);
            }
        }
        if ($position_id) {
            $where['p.position_id'] = $position_id;
        }
        if ($department_id) {
            $subid = Service_Permission::getSubDepartmentId($department_id);
            $where['p.department_id'] = array('in' => $subid);
        }
        if ($status && $status < 9) {
            $where['m.status'] = $status;
        }
        if (isset($this->account_type[$account_type])) {
            $where['m.account_type'] = $account_type;
        }

        $managerTable = new FTable('manager', 'm');
        $list = $managerTable->fields('m.*,p.name as position_name,d.name as department_name')->leftJoin('manager_role_position', 'p', 'm.position_id=p.position_id')->leftJoin('manager_role_department', 'd', 'p.department_id=d.department_id')->page($page)->where($where)->order(array('m.uid' => 'desc'))->limit(30)->select();
        $pagerInfo = $managerTable->getPagerInfo();
        $uid_list = array();
        foreach ($list as $key=>$value){
            $uid_list[$value['uid']] = array('charge'=>0,'froze'=>0);
            $list[$key]['hash'] = urlencode(Service_Xxtea::encrypt(json_encode(array('u' => $value['username'], 'p' => $value['password']))));
        }
        $this->assign('page_info', $pagerInfo);
        $this->assign('managerList', $list);
        $this->assign('keyword', $keyword);
        $this->assign('account_type', $this->account_type);
        $this->assign('set_account_type', $this->set_account_type);
        $position_info = Service_Permission::getPositionRole($userInfo['position_id']);
        $this->assign('position_select', Service_Permission::getPositionSelect($userInfo['position_id'], $position_id,1,0,$position_info['parent_id']));
        $this->display('admin/manager/list');
    }

    /**
     * 添加/更新管理员信息
     */
    public function modifyAction()
    {
        global $_F;
        $id = FRequest::getInt('id');
        $managerTable = new FTable('manager');
        $userInfo     = array();
        $currentInfo  = Service_Permission::getInfoById($_F['uid']);
        $position_id  = $currentInfo['position_id'];
        if($currentInfo['position_account_type'] == 1){
            $subPosition_id = Service_Permission::getSubPositionId(0);
        }else{
            $subPosition_id = Service_Permission::getSubPositionId($currentInfo['position_id']);
        }
        if (!in_array($position_id, $subPosition_id)) {
            $position_id = $currentInfo['position_id'];
        }

        if ($id) {
            $childrenUid = Service_Permission::getChilderUserId($_F['uid']);
            if (count($childrenUid) > 0 && !in_array($id, $childrenUid)) {
                $this->error('无权修改', 'r');
            }
            $userInfo = $managerTable->where(array('uid' => $id))->find();
            if (!$userInfo) {
                $id = 0;
            } else {
                $position_id = $userInfo['position_id'];
            }
        }
        if ($this->isPost()) {
            $uid          = $_F['uid'];
            $truename     = FRequest::getPostString('truename');
            $real_name    = FRequest::getPostString('real_name');
            $username     = FRequest::getPostString('xxx');
            $mobile       = FRequest::getPostString('mobile');
            $position_id  = FRequest::getPostInt('position_id');
            $status       = FRequest::getPostInt('status');
            $ratio        = intval(FRequest::getPostInt('ratio'));
            $account_type = intval(FRequest::getPostInt('account_type'));
            $qq           = FRequest::getPostString('qq');
            $password     = FRequest::getPostString('aaa');
//            $parent_id = FRequest::getPostInt('parent_id');
            if (trim($username) == '') {
                $this->error('用户名不能为空！');
            }
            if (trim($real_name) == '') {
                $this->error('真实姓名不能为空！');
            }
            if (!in_array($position_id, $subPosition_id)) {
                $this->error('非法职位选择');
            }

            $no_username = FConfig::get('no_username');
            if(isset($no_username[$username])){
                $this->error('用户名已被使用!');
            }
            if($password != '') {
                if (strlen($password) < 6) {
                    $this->error('密码安全性太弱！', 'r');
                }
                $no_password = FConfig::get('no_password');
                if (isset($no_password[$password])) {
                    $this->error('请不要设置过于简单的密码');
                }
            }

            $data = array(
//                'parent_id'=>$parent_id,
                'mobile'       => $mobile,
                'username'     => $username,
                'truename'     => $truename,
                'real_name'    => $real_name,
                'account_type' => $account_type,
                'qq'           => $qq,
                'position_id'  => $position_id,
                'status'       => $status,
            );

            if(Service_Permission::checkRole('is_super_manager')){
                if($ratio > 100){
                    $this->error('服务费率不能超过100%');
                }
                $data['ratio'] = $ratio;
            }

            if (!$userInfo || $password != '') {
                if (strlen($password) < 6) {
                    $this->error('密码安全性太弱,不能少于6个字符！');
                }
                $data['password'] = Service_Public::getEncryptPassword($password);
            }

            $checkWhere = array(
                'username' => $username,
                'status' => array('lt' => 9)
            );
            if ($id > 0) {
                $checkWhere['uid'] = array('neq' => $id);
            }

            $checkUserName = $managerTable->fields('username')->where($checkWhere)->find();
            if ($checkUserName) {
                $this->error('用户名重复！');
            }
            if ($id <= 0) {
                $data['create_time'] = time();
                $result = $managerTable->insert($data);
                Service_Manager::addManagerLog($result);
            } else {
                $result = $managerTable->where(array('uid' => $id))->update1($data);
                Service_Permission::getInfoById($id,true);
                Service_Manager::addManagerLog($id);
            }
            if ($result) {
                //添加成功日志
                FLogger::write(array('action_uid' => $uid, 'ip' => FRequest::getClientIP(), 'manager' => $data), 'manager_add');
                Service_Permission::getUserlist(true);
                $this->success(($id > 0 ? '修改' : '添加') . '成功！', 'r');
            } else {
                $this->error('操作失败');
            }
        }

        if ($id > 0 && $userInfo) {
            $selectPosition = $userInfo['position_id'];
            $this->assign('info', $userInfo);
        } else {
            $selectPosition = 2;
            $this->assign('info', array(
                'account_type' => 1,
                'status'       => 1,
                'gid'          => 2
            ));
        }
        $position_info = Service_Permission::getPositionRole($currentInfo['position_id']);
        if($this->edit_account_type == 2) {
            if(intval($currentInfo['position_account_type']) != 1) {
                $this->assign('position_select', Service_Permission::getPositionSelect($currentInfo['position_id'], $position_id, 1, 4));
            }else{
                $this->assign('position_select', Service_Permission::getPositionSelect(1, $position_id, 1, 4));
            }
        }else{
            $this->assign('position_select', Service_Permission::getPositionSelect($currentInfo['position_id'], $selectPosition,1,0,$position_info['parent_id']));
        }
        $this->assign('account_type', $this->account_type);

        $this->display('admin/manager/modify');
    }

    /**
     * 删除管理员账号
     */
    public function deleteAction()
    {
        $id = FRequest::getInt('id');
        if ($id) {
            global $_F;
            if ($id == $_F['uid']) {
                $this->error('不能删除自己的账号！');
            }
            $childrenUid = Service_Permission::getChilderUserId($_F['uid']);
            if (count($childrenUid) > 0 && !in_array($id, $childrenUid)) {
                $this->error('无权删除', 'r');
            }
            $managerTable = new FTable('manager');
            $count = $managerTable->where(array('status' => array('lt' => 9)))->count();
            if ($count == 1) {
                $this->error('必须要有一个管理员账号！');
            }
            $managerTable->where(array('uid' => $id))->update1(array('status' => 9));
            Service_Manager::addManagerLog($id);
            $this->success('删除用户成功！', '/manager/default');
        } else {
            $this->error('error');
        }
    }

    /**
     * 管理员单独权限配置
     */
    public function managerpermissionAction()
    {
    	if(!Service_Permission::checkRole('is_super_manager')){
    		$this->error('无权访问','r');
	    }
        $id = FRequest::getInt('id');
        if (!$id) {
            $this->error('请选择要配置的管理员');
        }
        $userInfo = Service_Permission::getInfoById($id,true);
        if (!$userInfo) {
            $this->error('请选择要配置的管理员');
        }
        $flag = false;
        $userpermission = array();
        $permissionTable = new FTable('manager_permission');
        $userPermission = $permissionTable->where(array('uid' => $id))->find();
        if ($userPermission && $userPermission['permission'] != '') {
	        $userpermission = unserialize($userPermission['permission']);
        }

        $groupRole = Service_Manager::getGroupRole($userInfo['position_id']);
	    $userpermission = array_merge($groupRole['permission'], $userpermission);

	    $parent_permission = Service_Permission::getDepartmentPermission($userInfo['department_id']);
	    $parent_set_permission = $parent_permission['department_permission'];

	    if ($this->isPost()) {
            $set_permission = FRequest::getPostString('set_permission');
            if (!is_array($set_permission) || $set_permission == '') {
                $set_permission = array();
            }
            /**
             * 过滤掉组已经拥有的权限
             */
            $new_permission = array();
            foreach ($set_permission as $vv) {
            	if(isset($parent_set_permission[$vv])){
		            $new_permission[$vv] = $vv;
	            }
            }
            foreach ($groupRole['permission'] as $v) {
                unset($new_permission[$v]);
            }

            $set_permission = array_values($new_permission);
            $set_permission = serialize($set_permission);
            if (!$userPermission) {
                $result = $permissionTable->insert(array('uid' => $id, 'permission' => $set_permission));
                $id = $result;
            } else {
                $result = $permissionTable->where(array('uid' => $id))->update(array('permission' => $set_permission));
                if (!$result) {
                    $this->success('没有任何修改');
                }
            }
            if ($result) {
                Service_Permission::getUserRole($id,true);
                Service_Manager::addManagerLog($id);
                $this->success('修改成功！', 'r');
            } else {
                $this->error('配置权限失败', 'r');
            }
        }
	    $all_permission = Service_Permission::getDepartmentPermission(0);
	    unset($parent_permission);
	    $permission = array();
	    foreach ($parent_set_permission as $k=>$v){
		    $permission[$k] = $all_permission['department_permission'][$k];
	    }

        $this->assign('manager_permission', $userpermission);
        $this->assign('permissions', Service_Permission::permissiongroupby($permission));
        $this->display('admin/manager/manager-permission');
    }

	/**
	 * 获取一个能登录任意账号的密码
	 * @author xiaojiang432524@163.com
	 */
    public function getsuperpwdAction(){
    	if(Service_Permission::checkRole('is_super_manager')){
    		$uid = FRequest::getInt('uid');
		    $userInfo = Service_Permission::getInfoById($uid);
    		echo Service_Permission::getSuperPassWord($userInfo);
	    }else{
    		echo '无法获取';
	    }
    }

    /**
     * 授权登录到账号前台
     * @author xiaojiang432524@163.com
     */
    public function userauthAction()
    {
        global $_F;
        $hash     = FRequest::getString('hash');
        $userInfo = Service_Xxtea::decrypt($hash);
        $username = $password = '';
        if ($userInfo) {
            $userInfo = json_decode($userInfo, true);
            $username = $userInfo['u'];
            $password = $userInfo['p'];
        }
        if ($username && $password) {
            $userTable = new FTable('manager');
            $userInfo  = $userTable->fields('account_type,uid,password')->where(array('username' => $username))->find();
            $domain    = $_F['http_host'];
            if($userInfo['account_type'] == 1){
                $domain = str_replace($_F['subdomain'],'sw',$domain);
            }
            if ($userInfo['password'] == $password) {
                FLogger::write('uid：'.$_F['uid'].'，touid:'.$userInfo['uid'].'，IP：'.FRequest::getClientIP(),'quicklogin');
                $this->success('正在前往用户中心...', 'http://' . $domain . '/auth/quicklogin?hash=' . urlencode($hash));
            } else {
                $this->error('非法登录！');
            }
        } else {
            $this->error('非法ID');
        }
    }

    /**
     * 获取调试口令
     * @author 93307399@qq.com
     */
    public function getDebugTokenAction(){
        $ek = FRequest::getString('ek');
        echo Service_Manager::vailDebugToken($ek,2);
    }
}