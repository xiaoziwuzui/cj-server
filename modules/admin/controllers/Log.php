<?php

/**
 * Created by PhpStorm.
 * User: 93307399@qq.com
 * Date: 2018/05/10
 * Time: 11:14
 * 后台操作记录查看
 * 显示操作记录
 */
class Controller_Admin_Log extends Controller_Admin_Abstract
{
    /**
     * @var array $ip_search_type IP搜索类型
     */
    private $ip_search_type = array();

    private $action_type = array();
    /**
     * @var string 指定数据库
     */
    private $DB_SERVER = '';

    public function beforeAction()
    {
        $flag = parent::beforeAction();
        if ($flag) {
            $this->action_type    = FConfig::get('type.action_type');
            $this->ip_search_type = FConfig::get('type.ip_search_type');
        }
        return $flag;
    }

    /**
     * 显示操作记录
     */
    public function listAction()
    {
        $page      = FRequest::getInt('page');
        $where     = array();
        $s_intro   = trim(FRequest::getString('s_intro'));
        $s_mid     = trim(FRequest::getString('mid'));
        $s_ip      = trim(FRequest::getString('s_ip'));
        $s_ip_type = FRequest::getInt('s_ip_type');
        $s_action  = FRequest::getString('s_action');
        $startime  = FRequest::getString('begin_date');
        $endtime   = FRequest::getString('end_date');
        if ($s_ip_type === null) {
            $s_ip_type = 3;
        }
        /**
         * 默认搜索当天的数据
         */
        if ($startime < 1) {
            $startime = date('Y-m-d', time());
        }
        if ($endtime < 1) {
            $endtime = date('Y-m-d', time());
        }
        $where['create_time']['gte'] = $startime.' 00:00:00';
        $where['create_time']['lte'] = $endtime.' 23:59:59';

        if ($s_mid != "") {
            $where['mid'] = $s_mid;
        }

        if ($s_intro != "") {
            $where['comment']['like'] = $s_intro;
        }

        if ($s_action != "") {
            $where['action'] = $s_action;
        }
        $s_ip_array  = explode('.', $s_ip);
        $s_ip_length = count($s_ip_array);
        if ($s_ip != "" && $s_ip_length > 1) {
            /**
             * 新增 IP转int并进行二段,三段查询
             */
            if ($s_ip_length !== 4) {
                //如果用户输入的IP段不是符合规范的,
                if ($s_ip_length == 1) {
                    $s_ip_array[1] = 0;
                    $s_ip_array[2] = 0;
                    $s_ip_array[3] = 0;
                    $s_ip_type = 1;
                } else if ($s_ip_length == 2) {
                    $s_ip_array[2] = 0;
                    $s_ip_array[3] = 0;
                    $s_ip_type = 1;
                } else if ($s_ip_length == 3) {
                    $s_ip_array[3] = 0;
                    $s_ip_type = 2;
                }
            }
            if ($s_ip_type === null) $s_ip_type = 3;
            if ($s_ip_type == 1) {
                //取二段的范围进行搜索.  *.*.0.0 至 *.*.255.255
                $where['ip']['like'] = implode('.', array($s_ip_array[0], $s_ip_array[1]));
            } else if ($s_ip_type == 2) {
                //取三段的范围进行搜索.  *.*.*.0 至 *.*.*.255
                $where['ip']['like'] = implode('.', array($s_ip_array[0], $s_ip_array[1], $s_ip_array[2]));
            } else if ($s_ip_type == 3) {
                //全匹配.
                $where['ip'] = $s_ip;
            }
        } else {
            $s_ip = '';
            $s_ip_type = 3;
        }

        $Table = new FTable('manager_log', '', $this->DB_SERVER);
        $Table->fields('id,mid,action,create_time,comment,ip')->where($where);
        $order = array(
            'id'=>'desc'
        );

        $Table->order($order);
        $list = $Table->page($page)->limit(25)->select();
        $pagerInfo = $Table->getPagerInfo();
        $mid = array();
        foreach ($list as $k => $v) {
            if($v['mid']){
                $mid[$v['mid']] = $v['mid'];
            }
            $list[$k]['manager'] = '--';
            $list[$k]['action'] = isset($this->action_type[$v['action']]) ? $this->action_type[$v['action']] : $v['action'];
        }
        /**
         * 取出关联管理员
         */
        if(count($mid) > 0){
            $managerTable = new FTable('manager');
            $mList = $managerTable->fields('truename,uid')->where(array('uid'=>array('in'=>implode(',',array_values($mid)))))->select();
            $mData = array();
            foreach ($mList as $v){
                $mData[$v['uid']] = $v['truename'];
            }
            foreach ($list as $key=>$v){
                if($v['mid'] && isset($mData[$v['mid']])){
                    $list[$key]['manager'] = $mData[$v['mid']];
                }
            }
        }
        $this->assign('page_info', $pagerInfo);
        $this->assign('list', $list);
        $this->assign('action_type', $this->action_type);
        $this->assign('s_action', $s_action);
        $this->assign('s_intro', $s_intro);
        $this->assign('s_ip', $s_ip);
        $this->assign('s_ip_type', $s_ip_type);
        $this->assign('ip_search_type', $this->ip_search_type);
        $this->assign('begin_date', $startime);
        $this->assign('end_date', $endtime);
        $this->display('log/list');
    }

    /**
     * 查看日志详情
     * @throws Exception
     * @author 93307399@qq.com
     */
    public function viewAction(){
        $id = intval(FRequest::getInt('id'));
        if(!$id){
            $this->error('请选择要查看的日志数据');
        }
        $table = new FTable('manager_log');
        $info = $table->where(array('id'=>$id))->find();
        $data = str_replace('GET:{"s":"\/member\/send","id":"32753"}，POST:','',$info['op_data']);
        $data = json_decode($data,true);
        $content = Service_Public::fixContent($data['content']);
        print_r($content);
    }

    /**
     * 清除多余日志
     * 只保留近一个星期的操作记录
     * @author 93307399@qq.com
     */
    public function flushAction(){
        if(Service_Permission::checkRole('is_super_manager')){
            $first_time = strtotime('-10 days',time());
            echo date('Y-m-d H:i:s',$first_time);

        }
    }
}