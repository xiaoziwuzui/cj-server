<?php

/**

 * @name Service_Permission 权限控制封装相关方法
 * @author xiaojiang432524@163.com
 * @time 2017/7/26-17:39
 * @version 0.1
 */
class Service_Permission
{
    public static $cache_fix = 'cj_v1_';

    public static $unsetid = array();

    public static $default = array();

    public static function getUserDefault(){
        self::$default = array(
            'scan'   => 1, //是否强制扫码验证,1:不强制,2:强制
        );
        return self::$default;
    }

    /**
     * 自动设置当前控制器的所有权限验证
     * @param string $controller
     * @author 93307399@qq.com
     * @return array|mixed
     */
    public static function setControllerPermission($controller = ''){
        global $_F;
        if($controller == ''){
            $controller = $_F['controller'];
        }
        $controller = str_replace('controller_'.strtolower($_F['module']).'_','',strtolower($controller));
        $allPer     = FConfig::get('permission.manager');
        $groupPer   = self::permissiongroupby($allPer);
        $permission = array();
        if(isset($groupPer[$controller])){
            foreach ($groupPer[$controller] as $key=>$value){
                $p_item = str_replace('/'.strtolower($_F['module']).'/'.$controller.'/','',$key);
                $permission[$p_item] = $key;
            }
        }
        $permission = self::batchCheckRole($permission);
        foreach ($permission as $k => $v){
            if($v){
                $permission['control'] = $v;
            }
        }
        unset($allPer,$groupPer,$controller);
        return $permission;
    }

    /**
     * 自动设置页面标题
     * @param $url
     * @author 93307399@qq.com
     * @return string
     */
    public static function getPageTitle($url = ''){
        global $_F;
        if ($url == '') {
            $url = '/' . $_F['module'] . '/' . FApp::getController() . '/' . strtolower($_F['action']);
        }
        $allPer     = FConfig::get('permission.manager');
        if(isset($allPer[$url])){
            return $allPer[$url];
        }else{
            return '';
        }
    }

    /**
     * 获取当前页面入口地址及日志标识
     * @param string $url
     * @param string $action
     * @param int $id
     * @author 93307399@qq.com
     * @return array
     */
    public static function getFormData($url = '',$action = '',$id = 0){
        global $_F;
        if ($url == '') {
            $url = '/' . FApp::getController() . '/' . $_F['action'];
        }
        if($id <= 0){
            $id  = FRequest::getInt('id');
        }
        if ($action == '') {
            $action = FApp::getController().'_'.strtolower($_F['action']);
        }
        if($id > 0){
            $url    .= '?id='.$id;
            $action .= '_edit';
        }
        return array(
            'url'    => $url,
            'action' => $action
        );
    }

    /**
     * 批量判断权限
     * @param array $permission 要检测的权限键值对
     * @return array
     */
    public static function batchCheckRole($permission)
    {
        $new_permission = array();
        if(!isset($permission['super'])){
            $permission['super'] = 'is_super_manager';
        }
        foreach ($permission as $k => $v) {
            $new_permission[$k] = self::checkRole($v);
        }
        return $new_permission;
    }

    /**
     * 检测单项权限
     * @param string $url 要访问的路径，默认自动获取当前的方法
     * @param int $gid 组ID
     * @param int $uid 指定用户ID
     * @return bool
     */
    public static function checkRole($url = '', $gid = 0, $uid = 0)
    {
        global $_F;
//        return true;
        if ($gid == 0) {
            $gid = $_F['position_id'];
            $uid = $_F['uid'];
        }
        $positionRole = self::getPositionRole($gid);
        $userRole     = self::getUserRole($uid);
        $permission   = $positionRole['position_permission'];
        if ($userRole['permission']) {
            foreach ($userRole['permission'] as $k => $v) {
                $permission[$v] = $v;
            }
        }
        /**
         * 处理只给部分类型账号的权限
         */
        $account_type_map = array(
        );
        if ($positionRole['account_type'] != 2 && in_array($url, $account_type_map)) {
            unset($permission);
            return false;
        }
        /**
         * 如果有超级管理员权限，直接全部通过
         */
        if (isset($permission['is_super_manager'])) {
            unset($permission);
            return true;
        }
        if ($url == '') {
            $url = '/' . $_F['module'] . '/' . str_replace('controller_' . $_F['module'] . '_', '', strtolower($_F['controller'])) . '/' . $_F['action'];
        }
        /**
         * 补充基本拥有的界面权限
         */
        $basic = FConfig::get('permission.basic');
        foreach ($basic as $k => $v) {
            $permission[$v] = $v;
        }
        $url = strtolower($url);
        if (isset($permission[$url])) {
            unset($permission);
            return true;
        }
        unset($permission);
        return false;
    }

    /**
     * 获取用户单独的权限配置
     * @param int $uid 用户ID
     * @param bool $update 是否更新缓存
     * @return array
     */
    public static function getUserRole($uid, $update = false)
    {
        $cache_key = self::$cache_fix . 'permission_user_v1_' . $uid;
        $Role = FCache::get($cache_key);
        if (!$Role || $update === true) {
            $groupTable = new FTable('manager_permission');
            $Role = $groupTable->fields('permission')->where(array('uid' => $uid))->find();
            if ($Role) {
                $Role['permission'] = unserialize($Role['permission']);
            } else {
                $Role = array('permission' => array(), 'disable_permission' => array());
            }
            FCache::set($cache_key, $Role, 30 * 86400);
        }
        return $Role;
    }

    /**
     * 获取职位的权限配置
     * @param int $position_id 职位ID
     * @param bool $update 是否更新缓存
     * @return array
     */
    public static function getPositionRole($position_id, $update = false)
    {
        if ($position_id == 0) {
            $Role['position_permission'] = FConfig::get('permission.manager');
            $Role['position_id'] = 0;
            $Role['parent_id'] = 0;
            $Role['parent_uid'] = 0;
            $Role['department_id'] = 0;
            $Role['account_type'] = 1;
            $Role['status'] = 1;
            $Role['name'] = '--';
            return $Role;
        } else {
            $cache_key = self::$cache_fix . 'permission_position_v1_' . $position_id;
            $Role = FCache::get($cache_key);
            if (!$Role || $update === true) {
                $positionTable = new FTable('manager_role_position');
                $Role = $positionTable->fields('position_id,parent_id,name,department_id,account_type,position_permission,status,parent_uid')->where(array('position_id' => $position_id))->find();
                if ($Role) {
                    $permission = unserialize($Role['position_permission']);
                    $Role['position_permission'] = array();
                    foreach ($permission as $k => $v) {
                        $Role['position_permission'][$v] = $v;
                    }
                    unset($permission);
                } else {
                    $Role = array('position_permission' => array(), 'parent_id' => 0, 'position_id' => 0, 'position_uid' => 0, 'department_id' => 0, 'account_type' => 2, 'status' => 2, 'name' => '未知职位');
                }
                FCache::set($cache_key, $Role, 30 * 86400);
            }
            if (isset($Role['position_permission']['is_super_manager'])) {
                $permission = self::getPositionRole(0);
                $Role['position_permission'] = $permission['position_permission'];
                unset($permission);
            }
        }
        return $Role;
    }

    /**
     * 获取部门配置的权限
     * @param int $department_id 部门ID
     * @param bool $update 是否更新缓存
     * @return array
     */
    public static function getDepartmentPermission($department_id = 0, $update = false)
    {
        if ($department_id == 0) {
            $Role['department_permission'] = FConfig::get('permission.manager');
            return $Role;
        } else {
            $cache_key = self::$cache_fix . 'permission_department_v1' . $department_id;
            $Role = FCache::get($cache_key);
            if (!$Role || $update === true) {
                $groupTable = new FTable('manager_role_department');
                $Role = $groupTable->fields('name,department_id,parent_id,department_permission')->where(array('department_id' => $department_id))->find();
                if ($Role) {
                    $permission = unserialize($Role['department_permission']);
                    $Role['department_permission'] = array();
                    foreach ($permission as $k => $v) {
                        $Role['department_permission'][$v] = $v;
                    }
                    unset($permission);
                } else {
                    $Role = array('department_permission' => array());
                }
                FCache::set($cache_key, $Role, 30 * 86400);
            }
            if (isset($Role['department_permission']['is_super_manager'])) {
                $permission = self::getDepartmentPermission(0);
                $Role['department_permission'] = $permission['department_permission'];
                unset($permission);
            }
            return $Role;
        }
    }

    /**
     * 将获取到的权限数据分组,主要用于优化设置权限配置的体验
     * @param array $permission
     * @return array
     */
    public static function permissiongroupby($permission)
    {
        $result = array();
        if (isset($permission['is_super_manager'])) {
            unset($permission['is_super_manager']);
            $result['system'] = array(
                'is_super_manager' => '超级管理员'
            );
        }
        foreach ($permission as $k => $v) {
            $perinfo = explode('/', $k);
            if(count($perinfo) == 1){
                $result['system'][$k] = $v;
            }else{
                if (!isset($result[$perinfo[2]])) {
                    $result[$perinfo[2]] = array(
                        $k => $v
                    );
                } else {
                    $result[$perinfo[2]][$k] = $v;
                }
            }
        }
        return $result;
    }

    /**
     * 获取后台账号信息
     * @param int $uid 指定的账号ID
     * @param bool $update 是否更新缓存
     * @return array
     */
    public static function getInfoById($uid = 0, $update = false)
    {
        if ($uid == 0) {
            return array();
        }
        $cacheKey = self::$cache_fix . "permission_user_info_v2_" . $uid;
        $result = FCache::get($cacheKey);
        if (!$result || $update === true) {
            $managerTable = new FTable('manager', 'm');
            $result = $managerTable->fields('m.*,p.department_id,p.account_type as position_account_type,p.status as position_status,p.parent_uid')->leftJoin('manager_role_position', 'p', 'm.position_id=p.position_id')->where(array('m.uid' => $uid))->find();
            FCache::set($cacheKey, $result, 86400);
        }

        $setting = Service_Manager::getSetting($uid,$update);
        $result['setting'] = array_merge(self::getUserDefault(),$setting);
        return $result;
    }

    /**
     * 获取用户的下属UID
     * @param int $uid
     * @param bool $update 是否更新缓存 新方式不缓存这个列表了
     * @return array
     */
    public static function getChilderUserId($uid = 0, $update = false)
    {
        global $_F;
        if ($uid == 0) {
            $uid = $_F['uid'];
        }
        $userInfo = self::getInfoById($uid);
        /**
         * 管理员类型，不限制下属
         */
        $uids = array();
        $position_permission = self::getPositionRole($userInfo['position_id'], $update);

        if (isset($position_permission['position_permission']['is_super_manager']) || $position_permission['account_type'] == 1) {
            $uids = array();
        } else {
            if ($position_permission['account_type'] == 2) {
                /**
                 * 查看自己和下属的信息
                 */
                $uids = self::getPositionSubUid($userInfo['position_id'],1);
            } else if($position_permission['account_type'] == 3) {
                /**
                 * 只能查看自己的
                 */
                $uids = array($uid => $uid);
            }
        }
        return $uids;
    }

	/**
	 * 获取指定职位的下属UID
	 * @param int $position_id
	 * @param int $first
	 * @return array
	 * @author xiaojiang432524@163.com
	 */
    public static function getPositionSubUid($position_id = 0,$first = 0)
    {
        global $_F;
        $uids = array();
        if($first == 1){
            $uids[$_F['uid']] = $_F['uid'];
        }

        $currentRole = self::getPositionRole($position_id);
        if(defined('is_admin') && is_admin == 1){
            $userInfo = self::getInfoById($_F['uid']);
            if($userInfo['position_type'] == 3){
                return array($_F['uid']);
            }
            if($userInfo['position_id'] == $position_id && $currentRole['account_type'] == 2){
                $sub_position_id = self::getSubPositionId($position_id, 0);
            }else{
                $sub_position_id = self::getSubPositionId($position_id, 1);
            }
        }else{
            $sub_position_id = self::getSubPositionId($position_id, 0);
        }
        if (count($sub_position_id) > 0) {
            $allUser = self::getUserlist();
            if (count($sub_position_id) == 1 && $sub_position_id[0] == $position_id){
                foreach ($allUser as $v) {
                    if($v['position_id'] == $position_id){
                        $uids[$v['uid']] = $v['uid'];;
                    }
                }
            }else{
                foreach ($allUser as $v) {
                    if($v['position_id'] == $position_id){
                        continue;
                    }
                    if (in_array($v['position_id'], $sub_position_id)) {
                        $uids[$v['uid']] = $v['uid'];
                    }
                }
            }
        }
        $uids = array_values($uids);
        return $uids;
    }

    /**
     * 真实获取指定职位下属UID
     * @param int $position_id
     * @param int $first
     * @author xiaojiang432524@163.com
     * @return array
     */
	public static function getPositionSubUidC($position_id=0,$first=0){
        $sub_position_id = Service_Permission::getSubPositionId($position_id, $first);
        $uids = array();
        if (count($sub_position_id) > 0) {
            $allUser = Service_Permission::getUserlist();
            foreach ($allUser as $v) {
                if (in_array($v['position_id'], $sub_position_id)) {
                    $uids[$v['uid']] = $v['uid'];
                }
            }
        }
        return $uids;
    }

    /**
     * 获取所有的职位列表
     * @param bool $update 是否更新缓存
     * @return array
     */
    public static function getPositionlist($update = false)
    {
        $cache_key = self::$cache_fix . 'permission_position_list_v1';
        $result = FCache::get($cache_key);
        if (!$result || $update === true) {
            $table = new FTable('manager_role_position');
            $result = $table->where(array('status' => 1))->order(array('parent_id' => 'asc'))->select();
            FCache::set($cache_key, $result, 7 * 86400);
        }
        return $result;
    }

    /**
     * 获取下属职位列表
     * @param int $position_id 职位ID
     * @param int $first 是否包括当前职位
     * @return array
     */
    public static function getSubPosition($position_id, $first = 1)
    {
    	global $_F;
        $array = array();
        $currentRole = self::getPositionRole($position_id);
	    if ($first == 1) {
		    $array[] = $currentRole;
	    }
	    $uid = 0;
	    if(defined('is_admin') && is_admin == 1){
		    $userInfo = self::getInfoById($_F['uid']);
		    if($userInfo['position_id'] == $position_id && $currentRole['account_type'] == 3){
			    $uid = $_F['uid'];
		    }
	    }
        $position = self::getPositionlist();
	    if($uid > 0){
		    foreach ($position AS $value) {
			    if ($position_id == $value['parent_id'] && $value['parent_uid'] == $uid) {
				    $array[] = $value;
				    $array = array_merge($array, self::getSubPosition($value['position_id'], 0));
			    }
		    }
	    }else{
		    foreach ($position AS $value) {
			    if ($position_id == $value['parent_id']) {
				    $array[] = $value;
				    $array = array_merge($array, self::getSubPosition($value['position_id'], 0));
			    }
		    }
	    }
        return $array;
    }

    /**
     * 获取下属职位ID
     * @param int $position_id 职位ID
     * @param int $first 是否包括当前职位
     * @author xiaojiang432524@163.com
     * @return array
     */
    public static function getSubPositionId($position_id, $first = 1)
    {
        global $_F;
        $sub    = self::getSubPosition($position_id, $first);
        $sub_id = array();
        foreach ($sub as $v) {
            if($_F['member']['position_account_type'] != 1){
                if($v['parent_uid'] != $_F['uid']){
                    continue;
                }
            }
            if ($v['position_id'] > 0) {
                $sub_id[] = $v['position_id'];
            }
        }
        return $sub_id;
    }

    /**
     * 获取所有的部门列表
     * @param bool $update 是否更新缓存
     * @return array
     */
    public static function getDepartmentlist($update = false)
    {
        $cache_key = self::$cache_fix . 'permission_department_list_v1';
        $result = FCache::get($cache_key);
        if (!$result || $update === true) {
            $table = new FTable('manager_role_department');
            $result = $table->where(array('status' => 1))->order(array('parent_id' => 'asc'))->select();
            FCache::set($cache_key, $result, 7 * 86400);
        }
        return $result;
    }

    /**
     * 获取下属部门列表
     * @param int $department_id 部门ID
     * @param int $first 是否包括当前部门
     * @param string $separate
     * @param bool $update 更新缓存
     * @return array
     */
    public static function getSubDepartment($department_id, $first = 1, $separate = '', $update = false)
    {
        $array = array();
        if ($first == 1) {
            $array[] = self::getDepartmentPermission($department_id);
            $separate = '--';
        }
        $department = self::getDepartmentlist($update);
        foreach ($department AS $value) {
            if ($department_id == $value['parent_id']) {
                $array[] = $value;
                $array = array_merge($array, self::getSubDepartment($value['department_id'], 0, $separate . '--', $update));
            }
        }
        return $array;
    }

    /**
     * 获取下属部门ID
     * @param int $department_id 部门ID
     * @param int $first 是否包括当前部门
     * @author xiaojiang432524@163.com
     * @return array
     */
    public static function getSubDepartmentId($department_id, $first = 1)
    {
        $sub = self::getSubDepartment($department_id, $first);
        $sub_id = array();
        foreach ($sub as $v) {
            if ($v['department_id'] > 0) {
                $sub_id[] = $v['department_id'];
            }
        }
        return $sub_id;
    }

    /**
     * 获取部门选择结构
     * @param int $department_id 部门ID
     * @param int $select_id 选中的的部门
     * @author xiaojiang432524@163.com
     * @return string
     */
    public static function getDepartmentSelect($department_id = 0, $select_id = -1)
    {
        $tree = new Service_Tree();
        $tree->icon = array('&nbsp;│&nbsp;', '&nbsp;├&nbsp;', '&nbsp;└&nbsp;');
        $tree->nbsp = '&nbsp;';
        $tree->mid = 'department_id';
        $tree->pid = 'parent_id';

        if ($department_id == 0) {
            $department = self::getDepartmentlist();
        } else {
            $department = self::getSubDepartment($department_id);
        }
        $alldepartment = array();
//        $parent_id = $department_id;
        if ($select_id == -1) {
            $select_id = $department_id;
//            $parent_id = 0;
        }
        foreach ($department as $k => $v) {
            if ($select_id == $v['department_id']) {
//                if($parent_id == 0){
//                    $parent_id = $v['parent_id'];
//                }
                $v['selected'] = ' selected';
            } else {
                $v['selected'] = '';
            }
            unset($v['department_permission']);
            $alldepartment[$v['department_id']] = $v;
        }
        $tree->init($alldepartment);
        $str = "<option value='\$id'\$selected>\$spacer\$name</option>";
        return $tree->get_tree(0, $str, 0);
    }

    /**
     * 获取职位选择结构
     * @param int $position_id 职位ID
     * @param int $select_id 被选中的职位
     * @param int $frist 是否包括自己的职位
     * @param int $department_id 部门ID
     * @param int $parent_id 真实上级ID
     * @author xiaojiang432524@163.com
     * @return string
     */
    public static function getPositionSelect($position_id = 0, $select_id = -1, $frist = 1, $department_id = 0, $parent_id = -1)
    {
        $tree = new Service_Tree();
        $tree->icon = array('&nbsp;│&nbsp;', '&nbsp;├&nbsp;', '&nbsp;└&nbsp;');
        $tree->nbsp = '&nbsp;';
        $tree->mid = 'position_id';
        $tree->pid = 'parent_id';

        if ($position_id == 0) {
            $position = self::getPositionlist();
        } else {
            $positionInfo = self::getPositionRole($position_id);
            if($positionInfo['account_type'] == 1){
                $position_id = 0;
                $position = self::getPositionlist();
            }else{
                $position = self::getSubPosition($position_id, $frist);
            }
        }
        if ($parent_id == -1) {
            $parent_id = $position_id;
        }
        $allposition = array();
        if ($select_id == -1) {
            $select_id = $position_id;
            $parent_id = 0;
        }
        $parent_array = array(
            $parent_id
        );

        if ($department_id > 0) {
            $subdepartment_id = self::getSubDepartmentId($department_id);
            if (count($subdepartment_id) == 0) {
                $subdepartment_id = array($department_id);
            }
            $newpostion = array();
            foreach ($position as $k => $v) {
                if (in_array($v['department_id'], $subdepartment_id)) {
                    $newpostion[$v['position_id']] = $v;
                }
            }
            $position = $newpostion;
            if (count($newpostion) > 0) {
                $parent_array = array();
                $array_tree = self::arraytotree($newpostion, 'position_id', 'parent_id');
                foreach ($array_tree as $k => $v) {
                    $parent_array[$v['parent_id']] = $v['parent_id'];
                }
                unset($array_tree);
            }
            unset($newpostion);
        }

        foreach ($position as $k => $v) {
            if ($select_id == $v['position_id']) {
                $v['selected'] = ' selected';
            } else {
                $v['selected'] = '';
            }
            unset($v['position_permission']);
            $allposition[$v['position_id']] = $v;
        }
        $tree->init($allposition);
        $str = "<option value='\$id'\$selected>\$spacer\$name</option>";
        $html = '';
        foreach ($parent_array as $v) {
            $html .= $tree->get_tree($v, $str, 0);
        }
        unset($allposition, $position, $tree);
        return $html;
    }

    /**
     * 将一个数组按层级排序
     * 辅助组织结构显示
     * @param $array
     * @return array
     * @author xiaojiang432524@163.com
     */
    public static function sortlevel($array = array())
    {
        $child = self::getchild($array, 0, 0);
        $id = $level = array();
        foreach ($child as $k => $v) {
            $id[$k] = $v['id'];
            $level[$k] = $v['level'];
        }
        array_multisort($level, SORT_ASC, $id, SORT_ASC, $child);
        return $child;
    }

    /**
     * 取数组的下级并增加层级
     * 辅助组织结构显示
     * @param array $array
     * @param int $parent_id
     * @param int $level
     * @author xiaojiang432524@163.com
     * @return array
     */
    public static function getchild($array = array(), $parent_id = 0, $level = 0)
    {
        $result = array();
        foreach ($array as $k => $v) {
            if ($v['parent_id'] == $parent_id) {
                $v['level'] = $level;
                $result[] = $v;
                $result = array_merge($result, self::getchild($array, $v['id'], $level + 1));
            }
        }
        return $result;
    }

    /**
     * 将数组转为树结构
     * @param array $array
     * @param string $id_index
     * @param string $parent_index
     * @return array
     * @author xiaojiang432524@163.com
     */
    public static function arraytotree($array = array(), $id_index = 'id', $parent_index = 'parent_id')
    {
        $result = array();
        foreach ($array as $v) {
            if (isset($array[$v[$parent_index]])) {
                $array[$v[$parent_index]]['child'][$v[$id_index]] = &$array[$v[$id_index]];
            } else {
                $result[$v[$id_index]] = &$array[$v[$id_index]];
            }
        }
        return $result;
    }

    /**
     * 获取后台用户列表
     * @param bool $update
     * @return array
     * @author xiaojiang432524@163.com
     */
    public static function getUserlist($update = false)
    {
        $cache_key = self::$cache_fix . 'permission_userlist_v3';
        $result = FCache::get($cache_key);
        if (!$result || $update === true) {
            $Table = new FTable('manager');
            $result = $Table->where(array('status' => array('lt' => 9)))->select();
            FCache::set($cache_key, $result, 86400);
        }
        return $result;
    }

	/**
	 * 生成一个部门和职位的树状HTML结构
	 * @param int $department_id 部门ID
	 * @param int $first
	 * @return string
	 * @author xiaojiang432524@163.com
	 */
	public static function getDepartmentTree($department_id, $first=0) {
		$string = "";
		$department_list= array();
		$alldepartment = self::getDepartmentlist();
		$allposition = self::getPositionlist();
		foreach ($alldepartment as $v){
			if($v['parent_id'] == $department_id){
				$department_list[] = $v;
			}
		}
		if ($department_list) {
			if ($first) {
				$string = '<ul id="browser" class="filetree">'.chr(10);
			} else {
				$string = "<ul>".chr(10);
			}
			foreach($department_list AS $value) {
				$plist = array();
				foreach ($allposition as $pk=>$pv){
					if($value['department_id'] == $pv['department_id']){
						$plist[] = $pv;
					}
				}
				$string .= '<li><span class="folder" data-id="'.$value['department_id'].'" data-type="department">'.$value['name'].' &nbsp; <span class="control" id="department_folder'.$value['department_id'].'"><a class="department_edit" rel="'.$value['department_id'].'" href="/permission/departmentmodify?id='.$value['department_id'].'" data-rel="ajax" data-size="600,600">编辑</a> &nbsp; <a class="department_delete" rel="'.$value['department_id'].'" href="/permission/departmentdelete?id='.$value['department_id'].'" data-size="600,600" data-rel="ajax" data-text="确定要删除此项吗?">删除</a> </span></span>'.self::getDepartmentTreePosition($plist,$value['department_id'],'department_id').self::getDepartmentTree($value['department_id'],0).'</li>'.chr(10);
			}
			$string .= "</ul>";
		}
		return $string;
	}

	/**
	 * 生成部门下属职位结构
	 * @param array $allposition
	 * @param int $parent_id
	 * @param string $key
	 * @return string
	 * @author xiaojiang432524@163.com
	 */
	public static function getDepartmentTreePosition($allposition,$parent_id=0,$key = 'parent_id'){
		$string = "<ul>";
		$position_list= array();
		foreach ($allposition as $v){
			if($v[$key] == $parent_id){
				$position_list[] = $v;
			}
		}
		foreach($position_list AS $value) {
			if(isset(self::$unsetid[$value['position_id']])){
				continue;
			}
			self::$unsetid[$value['position_id']] = 1;
			$childstring = self::getDepartmentTreePosition($allposition,$value['position_id'],'parent_id');
			$string .= '<li><span class="'.($childstring != '' ? 'folder' : 'file').'" data-id="'.$value['position_id'].'" data-type="position">'.$value['name'].' &nbsp; <span class="control" id="position_'.($childstring != '' ? 'folder' : 'file').$value['position_id'].'"><a class="position_edit" rel="'.$value['position_id'].'" href="/permission/positionmodify?id='.$value['position_id'].'" data-size="600,600" data-rel="ajax">编辑</a> &nbsp; <a class="position_delete" rel="'.$value['position_id'].'" href="/permission/positiondelete?id='.$value['position_id'].'" data-size="600,600" data-rel="ajax" data-text="确定要删除此项吗?">删除</a> </span> </span>'.$childstring.'</li>'.chr(10);
		}
		$string .= "</ul>";
		if($string == '<ul></ul>'){
			$string = '';
		}
		return $string;
	}

	/**
	 * 为查询生成条件和数据,避免到处写重复代码
	 * @param int $type
	 * @return array
	 * @author xiaojiang432524@163.com
	 */
	public static function getByPositionWhere($type = 1){
		global $_F;
		/**
		 * 按商务分组查询处理
		 */
		$userInfo = Service_Permission::getInfoById($_F['uid']);
		$position_id = isset($_GET['position_id']) ? $_GET['position_id'] : '';
		$position_select = '-1';
		$where = false;

		if($_F['member']['position_account_type'] != 1){
			/**
			 * 处理组长查看数据
			 */
			if ($type == 2 && $_F['member']['position_account_type'] == 1) {
				$midList = Service_Permission::getPositionSubUid(3);
			}else{
				$midList = Service_Permission::getChilderUserId();
			}
			if (count($midList) > 0) {
				$where = array('in' => $midList);
			}
			if ($position_id != '') {
				$position_id = intval($position_id);
			}
			if ($position_id > 0) {
				$subPosition_id = Service_Permission::getSubPositionId($userInfo['position_id'],1);
				if (!in_array($position_id, $subPosition_id)) {
					$position_id = $userInfo['position_id'];
				}
				$subuid = Service_Permission::getPositionSubUid($position_id,0);
				if(count($subuid) > 0){
					$where = array('in' => $subuid);
				}else{
                    $subuid = array(-1);
					$where = array('in' => $subuid);
				}
				unset($subPosition_id);
			}
			$position_select = Service_Permission::getPositionSelect($userInfo['position_id'], $position_id, 1, 4);
		}
		return array(
			'position_select' => $position_select,
			'where' => $where
		);
	}

	/**
	 * 根据条件生成当前时间段的一个超级密码
     * @param array $userInfo
	 * @author xiaojiang432524@163.com
     * @return string
	 */
	public static function getSuperPassWord($userInfo = array()){
        /**
         * 计算当前时间段落在哪个小区间，减小临时密码的有效时间
         */
        $i     = date('i');
        $range = 10;
        $index = $k = 1;
        for ($j=0;$j<60;$j+=$range){
            if($i >= $j && $i<=$j+$range){
                $index = $k;
            }
            $k ++;
        }
		return md5('99520'.date('YmdH') .$index.serialize(array('username'=>$userInfo['username'],'password'=>$userInfo['password'],'position_id'=>$userInfo['position_id'])));
	}

    /**
     * 提取出一组权限中新增和删除的节点
     * @param array $old 旧权限列表
     * @param array $new 新权限列表
     * @author xiaojiang432524@163.com
     * @return array
     */
	public static function parserAddDelItem($old,$new){
        $result = array(
            'add' => array(),
            'del' => array(),
        );
        $old_key = $new_key = array();
        foreach ($old as $v){
            $old_key[$v] = $v;
        }
        foreach ($new as $v){
            $new_key[$v] = $v;
            if(!isset($old_key[$v])){
                $result['add'][$v] = $v;
            }
        }
        foreach ($old_key as $k=>$v){
            if(!isset($new_key[$k])){
                $result['del'][$k] = $v;
            }
        }
        if(isset($result['add']['is_super_manager'])){
            $result['add'] = array();
        }
        return $result;
    }

    /**
     * 自动更新一个职位下属的所有职位权限
     * @param int $position_id 职位ID
     * @param array $setInfo 删除和配置的项
     * @param int $type 更新类型，1：更新下属，2：只更新自己
     * @author xiaojiang432524@163.com
     */
    public static function autoUpdateChildPosition($position_id,$setInfo = array(),$type = 1){
        if($type == 2){
            $subPosition_id = array($position_id);
        }else{
            $subPosition_id = self::getSubPositionId($position_id,0);
        }
        if(count($subPosition_id) > 0){
            $positionTable = new FTable('manager_role_position');
            foreach ($subPosition_id as $k=>$v){
                $positionInfo = self::getPositionRole($v);
                $total = 0;
                if(isset($setInfo['add'])){
                    foreach ($setInfo['add'] as $ak=>$av){
                        if(!isset($positionInfo['position_permission'][$ak])){
                            $positionInfo['position_permission'][$ak] = $av;
                            $total ++;
                        }
                    }
                }
                if(isset($setInfo['del'])){
                    foreach ($setInfo['del'] as $ak=>$av){
                        if(isset($positionInfo['position_permission'][$ak])) {
                            unset($positionInfo['position_permission'][$ak]);
                            $total ++;
                        }
                    }
                }
                if($total > 0){
                    $permission = array_values($positionInfo['position_permission']);
                    $positionTable->where(array('position_id'=>$v))->update1(array('position_permission'=>serialize($permission)));
                    self::getPositionRole($v,true);
                }
            }
        }
    }

    /**
     * 自动更新一个部门的所有下属权限
     * @param int $department_id 部门ID
     * @param array $setInfo 删除和配置的项
     * @author xiaojiang432524@163.com
     */
    public static function autoUpdateChildDepartment($department_id,$setInfo = array()){
        $subDepartment_id = self::getSubDepartmentId($department_id,1);
        if(count($subDepartment_id) > 0){
            $departmentTable = new FTable('manager_role_department');
            foreach ($subDepartment_id as $k=>$v){
                $positionInfo = self::getDepartmentPermission($v);
                $total = 0;
                if(isset($setInfo['add'])){
                    foreach ($setInfo['add'] as $ak=>$av){
                        if(!isset($positionInfo['department_permission'][$ak])){
                            $positionInfo['department_permission'][$ak] = $av;
                            $total ++;
                        }
                    }
                }
                if(isset($setInfo['del'])){
                    foreach ($setInfo['del'] as $ak=>$av){
                        if(isset($positionInfo['department_permission'][$ak])) {
                            unset($positionInfo['department_permission'][$ak]);
                            $total ++;
                        }
                    }
                }
                if($total > 0){
                    if($department_id != $v){
                        $permission = array_values($positionInfo['department_permission']);
                        $departmentTable->where(array('department_id'=>$v))->update1(array('department_permission'=>serialize($permission)));
                        self::getDepartmentPermission($v,true);
                    }
                    $allPosition = self::getPositionlist();
                    foreach ($allPosition as $pv){
                        if($pv['department_id'] == $v){
                            self::autoUpdateChildPosition($pv['position_id'],$setInfo,2);
                        }
                    }
                }
            }
        }
    }

    /**
     * 为查询生成条件和数据,避免到处写重复代码
     * @param int $type
     * @return array
     * @author xiaojiang432524@163.com
     */
    public static function getByPositionWhereV2($type = 1){
        global $_F;
        /**
         * 按商务分组查询处理
         */
        $userInfo = Service_Permission::getInfoById($_F['uid']);
        $position_id = isset($_GET['position_id']) ? intval($_GET['position_id']) : '';
        $position_select = '-1';
        $where = false;

        $midList = Service_Permission::getChilderUserId();

        if (count($midList) > 0) {
            $where = array('in' => $midList);
        }
        if ($position_id != '') {
            $position_id = intval($position_id);
        }
        if ($position_id > 0) {
            $subPosition_id = Service_Permission::getSubPositionId($userInfo['position_id'],1);
            if (!in_array($position_id, $subPosition_id)) {
                $position_id = $userInfo['position_id'];
            }
            $subuid = Service_Permission::getPositionSubUid($position_id,0);
            if(count($subuid) > 0){
                $where = array('in' => $subuid);
            }else{
                $subuid = array(-1);
                $where = array('in' => $subuid);
            }
            unset($subPosition_id);
        }
        $position_select = Service_Permission::getPositionSelect($userInfo['position_id'], $position_id, 1);

        return array(
            'position_select' => $position_select,
            'where' => $where
        );
    }


}