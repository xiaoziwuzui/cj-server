<?php
/**

 * User: xiaojiang432524@163.com
 * Date: 2017/7/26-17:36
 * @name 新权限设置模块
 * @version 0.1
 */
class Controller_Admin_Permission extends Controller_Admin_Abstract
{
    /**
     * @var array $account_type 职位类型，1-管理员，2-普通用户
     */
    private $account_type = array();
	/**
	 * @var array 没办法了,写不出好代码
	 */
    private $unset_position = array();

    public function beforeAction()
    {
        $flag = parent::beforeAction();
        if($flag){
            $this->account_type = array(
                1 => '所有人',
                2 => '自己和下属',
                3 => '仅自己',
            );
            $this->assign('account_type',$this->account_type);
        }
        return $flag;
    }

    /**
     * 部门管理
     * @author xiaojiang432524@163.com
     */
    public function departmentAction(){
	    $tree = new Service_Tree();
	    $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
	    $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
	    $tree->mid = 'department_id';
	    $tree->pid = 'parent_id';

	    $html = Service_Permission::getDepartmentTree(0,1);
	    $this->assign('department',$html);
	    $this->display('admin/permission/departmentv2');
    }

    /**
     * 编辑/添加部门
     * @author xiaojiang432524@163.com
     */
    public function departmentmodifyAction(){
    	global $_F;
	    $parent_id = FRequest::getInt('parent_id');
	    $id = FRequest::getInt('id');
	    if(!$parent_id){
	    	$parent_id = 0;
	    }
	    $Table = new FTable('manager_role_department');
	    $info = array();
	    $currentUserInfo = Service_Permission::getInfoById($_F['uid']);
	    if ($id) {
		    $info = $Table->where(array('department_id' => $id))->find();
		    if (!$info) {
			    $id = 0;
		    } else {
			    $parent_id = $info['parent_id'];
			    $info['department_permission'] = unserialize($info['department_permission']);
		    }
	    }

	    if($this->isPost()){
		    $name = FRequest::getPostString('name');
		    $parent_id = FRequest::getPostInt('parent_id');
		    $status = FRequest::getPostInt('status');
		    $set_permission = FRequest::getPostString('set_permission');
		    if(!is_array($set_permission) || $set_permission == ''){
			    $set_permission = array();
		    }
		    if(!is_int($parent_id)){
		    	$parent_id = $currentUserInfo['department_id'];
		    }
		    if($id > 0 && $parent_id == $id){
		    	$this->error('禁止设置上级为自己!');
		    }
		    $parent_permission = Service_Permission::getDepartmentPermission($parent_id);
		    if (trim($name) == '') {
			    $this->error('部门标题不能为空！');
		    }

		    $data = array(
			    'name' => $name,
			    'parent_id' => $parent_id,
			    'status' => $status,
		    );

		    $checkWhere = array(
			    'name' => $name
		    );
		    if ($id > 0) {
			    $checkWhere['department_id'] = array('neq' => $id);
		    }

		    $checkUserName = $Table->fields('department_id')->where($checkWhere)->find();
		    if ($checkUserName) {
			    $this->error('部门标题重复！');
		    }
		    /**
		     * 过滤掉上级组没有的权限
		     */
		    $set_new_permission = array();
		    foreach ($set_permission as $v){
			    if(isset($parent_permission['department_permission'][$v])){
				    $set_new_permission[] = $v;
			    }
		    }
		    $set_permission = serialize($set_new_permission);
		    $data['department_permission'] = $set_permission;

		    if(!$id){
			    $result = $Table->insert($data);
			    $id = $result;
		    }else{
			    $result = $Table->where(array('department_id'=>$id))->update1($data);
			    if(!$result){
                    Service_Permission::getDepartmentPermission($id,true);
				    $this->success('没有任何修改,已更新缓存');
			    }else{
                    /**
                     * 处理自动更新下级权限
                     */
                    $childer_add = isset($_POST['childer_add']) ? intval($_POST['childer_add']) : 0;
                    $childer_delete = isset($_POST['childer_delete']) ? intval($_POST['childer_delete']) : 0;
                    $setInfo = Service_Permission::parserAddDelItem($info['department_permission'],$set_new_permission);
                    if($childer_add == 0){
                        unset($setInfo['add']);
                    }
                    if($childer_delete == 0){
                        unset($setInfo['del']);
                    }
                    if($id != 1){
                        Service_Permission::autoUpdateChildDepartment($id,$setInfo);
                    }
                    Service_Permission::getDepartmentPermission($id,true);
                }
		    }
            unset($set_new_permission,$parent_permission);
		    if ($result) {
			    Service_Permission::getDepartmentlist(true);
                Service_Manager::addManagerLog($id);
			    $this->success(($id > 0 ? '修改' : '添加') . '成功！', 'r');
		    } else {
			    $this->error('操作失败','r');
		    }
	    }else{
	    	$all_permission = Service_Permission::getDepartmentPermission(0);
		    $parent_permission = Service_Permission::getDepartmentPermission($parent_id,true);
		    $permission = array();
		    foreach ($parent_permission['department_permission'] as $k=>$v){
		    	$permission[$k] = $all_permission['department_permission'][$k];
		    }
		    if ($id > 0 && $info) {
			    $this->assign('info', $info);
		    } else {
			    $this->assign('info', array(
				    'parent_id' => $parent_id,
				    'status' => 1
			    ));
		    }
		    $permissions = Service_Permission::permissiongroupby($permission);
		    $html = Service_Permission::getDepartmentSelect($currentUserInfo['department_id'],$parent_id);
		    $this->assign('department_select',$html);
		    $this->assign('permissions',$permissions);
		    $this->display('admin/permission/departmentmodify');
	    }
    }

    /**
     * 删除部门
     * @author xiaojiang432524@163.com
     */
    public function departmentdeleteAction(){
	    $id = FRequest::getInt('id');
	    if(!$id){
		    $this->error('请选择要删除的部门ID','r');
	    }
	    $Table = new FTable('manager_role_department');
	    $subid = Service_Permission::getSubDepartment($id,0,'',true);
	    if(count($subid) > 0){
		    $this->error('还有下属部门,不能删除!','r');
	    }else{
		    $positionTable = new FTable('manager_role_position');
		    $check = $positionTable->where('department_id='.$id)->find();
		    if($check){
		    	$this->error('还有下属职位,不能删除','r');
		    }
		    $info = Service_Permission::getDepartmentPermission($id,true);
		    if(!$info){
			    $this->error('请选择要删除的部门ID','r');
		    }
		    $flag = $Table->where(array('department_id'=>$id))->update(array('status'=>9));
		    if($flag){
			    Service_Permission::getSubDepartment($info['parent_id']);
			    Service_Permission::getDepartmentlist(true);
			    Service_Manager::addManagerLog($id);
			    $this->success('删除部门成功','r');
		    }else{
			    $this->success('删除失败!','r');
		    }
	    }
    }

    /**
     * 编辑/添加职位
     * @author xiaojiang432524@163.com
     */
    public function positionmodifyAction(){
    	global $_F;
	    $department_id = FRequest::getInt('department_id');
	    $parent_id = FRequest::getInt('parent_id');
	    $id = FRequest::getInt('id');
	    if(!$department_id){
		    $department_id = 0;
	    }
	    if(!$parent_id){
		    $parent_id = 0;
	    }
	    $currentUserInfo = Service_Permission::getInfoById($_F['uid']);
	    $Table = new FTable('manager_role_position');
	    $info = array();
	    if ($id) {
		    $info = $Table->where(array('position_id' => $id))->find();
		    if (!$info) {
			    $id = 0;
		    } else {
			    $parent_id = $info['parent_id'];
			    $info['position_permission'] = unserialize($info['position_permission']);
			    if(isset($_GET['d'])){
				    dump($info,false);
			    }
		    }
	    }

	    if($this->isPost()){
		    $name = FRequest::getPostString('name');
		    $department_id = FRequest::getPostInt('department_id');
		    $parent_id = FRequest::getPostInt('parent_id');
		    $parent_uid = FRequest::getPostInt('parent_uid');
		    $status = FRequest::getPostInt('status');
		    $account_type = FRequest::getPostInt('account_type');
		    $set_permission = FRequest::getPostString('set_permission');
		    if(!is_array($set_permission) || $set_permission == ''){
			    $set_permission = array();
		    }
		    if(!$department_id){
			    $department_id = $currentUserInfo['department_id'];
		    }
		    if(!is_int($parent_id)){
			    $parent_id = $currentUserInfo['position_id'];
		    }
		    if(!$account_type){
			    $account_type = 2;
		    }
		    if($parent_id == 0){
			    $parent_permission = Service_Permission::getDepartmentPermission($department_id);
			    $parent_set_permission = $parent_permission['department_permission'];
		    }else{
			    $parent_permission = Service_Permission::getPositionRole($parent_id);
			    $parent_set_permission = $parent_permission['position_permission'];
		    }
		    if($id > 0 && $parent_id == $id){
			    $this->error('禁止设置上级为自己!');
		    }
		    unset($parent_permission);
		    if (trim($name) == '') {
			    $this->error('职位标题不能为空！');
		    }
		    $data = array(
			    'name' => $name,
			    'parent_id' => $parent_id,
			    'parent_uid' => $parent_uid,
			    'department_id' => $department_id,
			    'account_type' => $account_type,
			    'status' => $status,
		    );

		    /**
		     * 过滤掉上级组没有的权限
		     */
		    $set_new_permission = array();
		    foreach ($set_permission as $v){
			    if(isset($parent_set_permission[$v])){
				    $set_new_permission[] = $v;
			    }
		    }
		    $set_permission = serialize($set_new_permission);
		    $data['position_permission'] = $set_permission;

		    if(!$id){
			    $result = $Table->insert($data);
			    $id = $result;
		    }else{
			    $result = $Table->where(array('position_id'=>$id))->update1($data);
			    if(!$result){
                    Service_Permission::getPositionRole($id,true);
				    $this->success('没有任何修改,已更新缓存');
			    }else{
                    /**
                     * 处理自动更新下级权限
                     */
			        $childer_add = isset($_POST['childer_add']) ? intval($_POST['childer_add']) : 0;
			        $childer_delete = isset($_POST['childer_delete']) ? intval($_POST['childer_delete']) : 0;
			        $setInfo = Service_Permission::parserAddDelItem($info['position_permission'],$set_new_permission);
			        if($childer_add == 0){
			            unset($setInfo['add']);
                    }
			        if($childer_delete == 0){
			            unset($setInfo['del']);
                    }
                    if($id != 1){
                        Service_Permission::autoUpdateChildPosition($id,$setInfo);
                    }
                    Service_Permission::getPositionRole($id,true);
                }
		    }
            unset($set_new_permission,$parent_set_permission);
		    if ($result) {
			    Service_Permission::getPositionlist(true);
			    Service_Manager::addManagerLog($id);
			    $this->success(($id > 0 ? '修改' : '添加') . '成功！', 'r');
		    } else {
			    $this->error('操作失败','r');
		    }
	    }else{
		    $all_permission = Service_Permission::getDepartmentPermission(0,true);
		    if($parent_id == 0){
			    $parent_permission = Service_Permission::getDepartmentPermission($department_id,true);
			    $parent_set_permission = $parent_permission['department_permission'];
		    }else{
                if(!$department_id){
                    $parent_permission = Service_Permission::getPositionRole($parent_id,true);
                    $department_id = $parent_permission['department_id'];
                    $parent_set_permission = $parent_permission['position_permission'];
                }else{
                    $parent_permission = Service_Permission::getDepartmentPermission($department_id,true);
                    $parent_set_permission = $parent_permission['department_permission'];
                }
		    }
		    unset($parent_permission);
		    $permission = array();
		    foreach ($parent_set_permission as $k=>$v){
			    $permission[$k] = $all_permission['department_permission'][$k];
		    }
		    unset($parent_set_permission);
		    $default_partment_id = $currentUserInfo['department_id'];
		    $default_position_id = $currentUserInfo['position_id'];
		    if ($id > 0 && $info) {
			    $default_partment_id = $info['department_id'];
			    $default_position_id = $info['parent_id'];
			    $user_position_id = $default_position_id;
			    $this->assign('info', $info);
		    } else {
			    $this->assign('info', array(
				    'department_id' => $department_id,
				    'parent_id'     => $parent_id,
				    'status'        => 1
			    ));
			    $user_position_id = $parent_id;
		    }

		    $usertable = new FTable('manager');
		    $userlist = $usertable->fields('uid,truename')->where(array('status'=>1,'position_id'=> $user_position_id))->order(array('uid'=>'asc'))->select();
		    $this->assign('userlist',$userlist);

		    $currentUser_position_parent = Service_Permission::getPositionRole($currentUserInfo['position_id'],true);
		    $permissions = Service_Permission::permissiongroupby($permission);
            $this->assign('department_select',Service_Permission::getDepartmentSelect($currentUserInfo['department_id'],$default_partment_id));
            $this->assign('position_select',Service_Permission::getPositionSelect($currentUser_position_parent['parent_id'],$default_position_id));
		    $this->assign('permissions',$permissions);
		    $this->display('admin/permission/positionmodify');
	    }
    }

    /**
     * 删除职位
     * @author xiaojiang432524@163.com
     */
    public function positiondeleteAction(){
	    $id = FRequest::getInt('id');
	    if(!$id){
	    	$this->error('请选择要删除的职位ID','r');
	    }
	    $Table = new FTable('manager_role_position');
	    $subid = Service_Permission::getSubPosition($id,0);
	    if(count($subid) > 0){
	    	$this->error('还有下属职位,不能删除!','r');
	    }
	    Service_Permission::getUserlist(true);
	    $subuid = Service_Permission::getPositionSubUid($id);
	    if(count($subuid) > 0){
		    $this->error('还有下属用户,不能删除!','r');
	    }
        $info = Service_Permission::getPositionRole($id,true);
        if(!$info){
            $this->error('请选择要删除的职位ID','r');
	    }
        $flag = $Table->where(array('position_id'=>$id))->update(array('status'=>9));
        if($flag){
            Service_Permission::getSubPosition($info['parent_id']);
            Service_Permission::getPositionlist(true);
            Service_Manager::addManagerLog($id);
		    $this->success('删除职位成功','r');
	    }else{
            $this->success('删除失败!','r');
	    }

    }

	/**
	 * 辅助方法
	 * 获取一个数组的子元素1
	 */
    private function getchildrendepartment($department = array(),$position=array(),$parent_id=0,$key = 'parent_id',$adds = ''){
    	$html = array();
    	$list = array();
    	foreach ($department as $v){
    		if($v[$key] == $parent_id){
    			$list[] = $v;
		    }
	    }
	    $total = count($list);
	    $number = 1;
	    $icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
	    $nbsp = '&nbsp;&nbsp;&nbsp;';
	    foreach ($list as $v){
		    $plist = array();
		    $j=$k='';
		    if($number==$total){
			    $j .= $icon[2];
		    }else{
			    $j .= $icon[1];
			    $k = $adds ? $icon[0] : '';
		    }
		    $spacer = $adds ? $adds.$j : '';
		    $html[] = '<tr> <td class="text-left">'.$spacer.'<i class="glyphicon glyphicon-tasks" title="部门"></i> '.$v['name'].'</td> <td>'.$v['statushtml'].'</td> <td class="text-left">'.$v['manage'].'</td> </tr>';
		    foreach ($position as $pk=>$pv){
			    if($pv['department_id'] == $v['department_id']){
				    $plist[] = $pv;
			    }
		    }
		    $html[] = $this->getchildrenposition($plist,$v['department_id'],'department_id',$adds.$k.$nbsp);
		    $html[] = $this->getchildrendepartment($department,$position,$v['department_id'],'parent_id',$adds.$k.$nbsp);
		    $number ++;
	    }
	    return implode(chr(10),$html);
    }

	/**
	 * 辅助方法
	 * 获取一个数组的子元素2
	 */
    private function getchildrenposition(&$position = array(),$parent_id=0,$key = 'parent_id',$adds = ''){
    	$html = $list = array();
    	foreach ($position as $k=>$v){
		    if($v[$key] == $parent_id){
			    $list[] = $v;
		    }
	    }
	    $total = count($list);
	    $number = 1;
	    $icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
	    $nbsp = '&nbsp;&nbsp;&nbsp;';

	    foreach ($list as $v){
	    	if(isset($this->unset_position[$v['position_id']])){
	    		continue;
		    }
		    $j=$k='';
		    if($number==$total){
			    $j .= $icon[2];
		    }else{
			    $j .= $icon[1];
			    $k = $adds ? $icon[0] : '';
		    }
		    $spacer = $adds ? $adds.$j : '';

		    $html[] = '<tr> <td class="text-left">'.$spacer.'<i class="glyphicon glyphicon-user" title="职位"></i> '.$v['name'].'</td> <td>'.$v['statushtml'].'</td> <td class="text-left">'.$v['manage'].'</td> </tr>';
		    $this->unset_position[$v['position_id']] = 1;
		    $html[] = $this->getchildrenposition($position,$v['position_id'],'parent_id',$adds.$k.$nbsp);
		    $number ++;
	    }
	    return implode(chr(10),$html);
    }

	/**
	 * 更新配置缓存
	 */
    public function upcacheAction(){
    	Service_Permission::getPositionlist(true);
    	Service_Permission::getDepartmentlist(true);
	    $this->success('列表缓存更新完成');
    }

    public function testAction(){
        $table = new FTable('manager_role_position');
        $flag = $table->where(array('position_id'=> 1))->update1(array('parent_id'=>0));
        var_dump($flag);
        Service_Permission::getPositionlist(true);
        Service_Permission::getPositionRole(1,true);
    }

    public function getpositionuserAction(){
    	$position_id = intval(FRequest::getInt('position_id'));
    	$result = array();
    	if($position_id > 0){
		    $table = new FTable('manager');
		    $result = $table->fields('uid,truename')->where(array('status'=>1,'position_id'=> $position_id))->order(array('uid'=>'asc'))->select();
	    }
	    FResponse::outputJSON(0,'success',$result);
    }

    /**
     * 复制权限值
     * @author 93307399@qq.com
     */
    public function copypositionAction(){
        $table = new FTable('manager_role_position');
        if($this->isPost()){
            $setid = intval(FRequest::getPostInt('setid'));
            $copyid = intval(FRequest::getPostInt('copyid'));
            $info = $table->where(array('position_id'=>$copyid))->find();
            $msg = '提交处理成功，';
            if($info){
                $flag = $table->where(array('position_id'=>$setid))->update1(array('position_permission'=>$info['position_permission']));
                if($flag){
                    $msg .= '复制成功';
                    Service_Permission::getPositionRole($setid,true);
                }else{
                    $msg .= '复制失败，什么也没做';
                }
            }
            $this->success($msg,'r');
        }else{
            $list = $table->select();
            $this->assign('list',$list);
            $this->assign('formUrl','/admin/permission/copyposition');
            $this->display('admin/permission/copy');
        }
    }
}