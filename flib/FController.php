<?php

abstract class FController {
    /**
     * @var $view FView
     */
    protected $view;

    protected $autoInitView = true;

    abstract function showMessage($message, $msgType, $url = null);

    public function setView() {
        $this->view = new FView;
    }

    public function defaultAction() {
        echo 'is work!';
    }

    public function __construct() {
        if($this->autoInitView === true){
            $this->setView();
            $this->assign('ssl_domain',FConfig::get('global.ssl_domain'));
            $this->assign('_assets',FConfig::get('global.ui_assets'));
            $this->assign('ssl_assets',FConfig::get('global.ssl_domain').FConfig::get('global.ui_assets'));
            $this->assign('unit_version',FConfig::get('global.unit_version'));
            $this->assign('editor_version',FConfig::get('global.editor_version'));
            $this->assign('title',FConfig::get('global.title'));
            $this->assign('company_name',FConfig::get('global.company_name'));
        }
    }

    /**
     * 前置检测
     * @author 93307399@qq.com
     * @return bool
     */
    public function beforeAction(){
        return true;
    }

    protected function isPost() {
        return FRequest::isPost();
    }

    public function ajaxRedirect($url) {
        $this->output(301,'','ok',false,$url);
    }

    protected function success($message, $url = '') {
        global $_F;
        if ($url == 'r'){
            $url = $_SERVER['HTTP_REFERER'];
        }
        if ($_F['in_ajax']){
            $this->output(200,$message,'ok',false,$url);
        }else{
            $this->showMessage($message, 'success', $url);
        }
        return true;
    }

    protected function error($message, $url = '') {
        global $_F;
        if ($url == 'r'){
            $url = $_SERVER['HTTP_REFERER'];
        }
        if ($_F['in_ajax']){
            $this->output(500,$message,'error',false,$url);
        }else{
            $this->showMessage($message, 'error', $url);
        }
        return false;
    }

    /**
     * 返回Grid数据
     * @param array $data
     * @param array $pager
     * @param array $total
     * @param int $code
     * @param string $msg
     * @author 93307399@qq.com
     */
    public function outputGrid($data = array(),$pager = array(),$total = array(),$code = 200,$msg = ''){
        $pageInfo = array(
            'page' => intval($pager['current']),
            'recTotal' => intval($pager['total']),
            'recPerPage' => intval($pager['per_page']),
        );
        if(!is_array($data)){
            $data = array();
        }
        $this->output($code,$msg,$code == 200 ? 'ok' : 'error',$data,false,$pageInfo,$total);
    }

    /**
     * 输出JSON内容
     * @param int|array $code 状态码|真实内容
     * @param string $msg 描述信息
     * @param string $status 返回标识，成功或失败
     * @param array|bool $data 额外数据
     * @param string|bool $url 跳转路径
     * @param array|bool $pager 分页信息
     * @param array|bool $total 汇总数据
     */
    public function output($code = 200, $msg = '', $status = '', $data = false,$url = false,$pager = false,$total = false)
    {
        $msgMap = FConfig::get('message_code');
        $output = array();
        if (is_array($code)) {
            $data = $code;
            $code = 200;
        }
        $output['code']   = $code;
        $output['status'] = $status == '' ? ($code == 200 ? 'ok' : 'error') : $status;
        $output['tm']     = time();
        if($url != false){
            $output['url'] = $url;
        }
        if (strlen($msg) != 0) {
            $output['msg'] = $msg;
        } else {
            if ($output['code'] > 0 && $output['code'] < 1000) {
                $output['msg'] = isset($msgMap[$output['code']]) ? $msgMap[$output['code']] : 'success';
            }
        }
        if ($data !== false) {
            $output['data'] = $data;
        }
        if ($pager !== false) {
            $output['pager'] = $pager;
        }
        if ($total !== false) {
            $output['total'] = $total;
        }
        ob_clean();
        header('Content-Type:text/json;charset=utf-8');
        echo json_encode($output);
        exit(0);
    }

    protected function load($tpl = null) {
        return $this->view->load($tpl);
    }

    protected function display($tpl = null) {
        if(isset($this->formData) && !empty($this->formData)){
            if(isset($this->formData['fix_id']) && isset($this->formData['url'])){
                $this->formData['url'] = str_replace('id='.$this->formData['fix_id'],'',$this->formData['url']);
            }
            if(isset($this->formData['fix_edit']) && isset($this->formData['action'])){
                $this->formData['action'] = str_replace('_edit','',$this->formData['action']);
            }
            $this->assign('formData',$this->formData);
        }
        $this->view->disp($tpl);
    }

    protected function assign($key, $value) {
        $this->view->set($key, $value);
    }

    protected function ajaxReturn($mix) {
        $this->output($mix);
        return true;
    }

    protected function openDebug() {
        global $_F;
        $_F['debug'] = 1;
    }
}