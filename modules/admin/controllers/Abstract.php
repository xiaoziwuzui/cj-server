<?php

/**
 * Created by PhpStorm.
 * User: 93307399@qq.com
 * Date: 2017/2/23
 * Time: 14:57
 */
class Controller_Admin_Abstract extends FController
{
    /**
     * 数据库配置
     * @var string
     */
    public $db = '';
    /**
     * 状态类型定义
     * @var array $status_type
     */
    public $status_type = array();
    /**
     * 页面表单基本数据
     * @var array
     */
    public $formData = array();
    /**
     * 控制器权限配置
     * @var array
     */
    public $permission = array();
    /**
     * 控制器内容表
     * @var string
     */
    public $table = '';
    /**
     * 允许继承使用的方法
     * @var array
     */
    public $extendAction = array();

    public $set_assets = array();

    public function beforeAction()
    {
        global $_F;
        define('is_admin',1);
        Service_Manager::setManagerCookieName();
	    if(isset($_GET['d'])){
		    $this->openDebug();
	    }
        $flag = $this->checkAuth();
        if(!$flag) return $flag;
        $_F['in_manage'] = true;

        $this->status_type = FConfig::get('type.status_type');
        $this->permission  = Service_Permission::setControllerPermission();
        $this->formData    = Service_Permission::getFormData();
        $this->assign('page_title',Service_Permission::getPageTitle());
        $this->assign('formData',$this->formData);
        $this->assign('status_type',$this->status_type);
        $this->assign('ssl_assets','http://'.$_F['http_host'] . '/'.FConfig::get('global.ui_assets'));
        return true;
    }

    /**
     * 检查登录状态
     * @author 93307399@qq.com
     * @return bool
     */
    public function checkAuth() {
        global $_F;
        $auth_info = Service_Manager::getSessionData();
        if (!$auth_info) {
            Service_Manager::removeSession();
            echo '<script type="text/javascript">top.location.href="http://' . $_F['http_host'] . '/auth/login";</script>';
            exit(0);
        }
        $flag = Service_Permission::checkRole();
        if(!$flag){
            $this->error('您没有权限访问');
        }
        return true;
    }

    /**
     * 删除记录
     * @author 93307399@qq.com
     */
    public function deleteAction(){
        if($this->table == '' || !in_array('delete',$this->extendAction)){
            $this->error('非法请求');
        }
        $id = FRequest::getInt('id');
        if ($id) {
            $Table = new FTable($this->table);
            $info = $Table->where(array('id' => $id))->find();
            if(!$info){
                $this->error('没有要删除的记录!','r');
            }
            $result = $Table->where(array('id' => $id))->update1(array('status' => 9));
            if($result){
                Service_Manager::addManagerLog($id);
                $this->success('删除成功！', 'r');
            }else{
                $this->error('删除失败');
            }
        } else {
            $this->error('请选择要删除的记录');
        }
    }

    public function showMessage($message, $messageType = 'success', $jumpUrl = null) {
        if ($messageType == 'error') {
            $messageType = 'warning';
        }
        $this->assign('messageType', $messageType);
        $this->assign('message_content', $message);
        $this->assign('jump_url', $jumpUrl);
        $this->display('admin/message');
        exit(0);
    }

    public function display($tpl = null)
    {
        $this->assign('permission',$this->permission);
        $this->assign('set_assets',$this->set_assets);
        parent::display(str_replace('admin/', '', $tpl));
    }
}