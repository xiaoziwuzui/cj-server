<?php

/**
 * Created by PhpStorm.
 * User: 93307399@qq.com
 * Date: 2018/5/11
 * Time: 14:22
 */
class Controller_Admin_Main extends Controller_Admin_Abstract
{

    /**
     * 后台框架主体
     */
    public function indexAction()
    {
        global $_F;
        $allMenu = FConfig::get('menu.manager');
        $menuItem = array();
        $mainInfo = array(
            'url' => '/admin/main/total',
            'name' => '汇总数据',
        );
        $flag = 0;
        foreach ($allMenu as $k=>$v){
            if(isset($v['menu'])){
                $childMenu = array();
                foreach ($v['menu'] as $vk=>$vv){
                    if(Service_Manager::checkRole($vv['url'])){
                        if($flag == 0){
                            $mainInfo = $v;
                            $flag ++;
                        }
                        $childMenu[] = $vv;
                    }
                }
                if(count($childMenu) > 0){
                    $menuItem[] = array('name' => $v['name'],'menu' => $childMenu,'icon'=>$v['icon']);
                }
            }else{
                if(Service_Manager::checkRole($v['url'])){
                    if($flag == 0){
                        $mainInfo = $v;
                        $flag ++;
                    }
                    $menuItem[] = $v;
                }
            }
        }
        $permission = Service_Permission::getPositionRole($_F['position_id']);
        $this->assign('permission_name',$permission['name']);
        $this->assign('mainInfo',$mainInfo);
        $this->assign('menu',$menuItem);
        $this->assign('manager',$_F['member']);
        unset($permission);
        $this->display('admin/frame/index');
    }

    /**
     * 修改登录密码
     */
    public function editAction()
    {
        global $_F;
        $manager = $_F['member'];
        if ($this->isPost()) {
            $truename     = FRequest::getPostString('truename');
            $old_password = FRequest::getPostString('old_password');
            $password     = FRequest::getPostString('password');
            $re_password  = FRequest::getPostString('re_password');
            $post_setting = FRequest::getPostString('setting');
            $data = array(
                'truename' => $truename
            );
            if ($old_password != "") {
                if (Service_Manager::getEncryptPassword($old_password) != $manager['password']) {
                    $this->error('原始密码错误！');
                }
                if($password == ''){
                    $this->error('请输入新密码!');
                }
                if(strlen($password) < 6){
                    $this->error('密码长度太低,最低要求6位!');
                }
                if ($password != $re_password) {
                    $this->error('两次密码不一致，请重新确认');
                }
                if (Service_Manager::getEncryptPassword($password) == $manager['password']) {
                    $this->error('新密码与原始密码相同');
                }
                $data['password'] = Service_Manager::getEncryptPassword($password);
            }

            $default = Service_Permission::getUserDefault();
            $setting = array();
            foreach ($default as $key => $value){
                if(!isset($post_setting[$key])){
                    $setting[$key] = $value;
                }else{
                    $setting[$key] = trim($post_setting[$key]);
                }
            }

            $Table = new FTable('manager');
            $Table->where(array('uid' => $_F['uid']))->update1($data);

            $setTable = new FTable('manager_setting');
            $result   = $setTable->fields('uid')->where(array('uid'=>$_F['uid']))->find();
            if($result){
                $setTable->where(array('uid'=>$_F['uid']))->update1($setting);
            }else{
                $setting['uid'] = $_F['uid'];
                $setTable->insert($setting);
            }

            Service_Manager::clearInfoById($_F['uid']);
            $this->success('修改个人信息成功','r');
        }
        $this->assign('info', $manager);
        $this->display('manager/editpwd');
    }

    /**
     * 数据概况
     * @author 93307399@qq.com
     */
    public function totalAction(){
        /**
         * 获取统计数据
         */
        global $_F;
        $this->set_assets[] = 'plugins/echarts/echarts.min.js';
        $date         = date('Y-m-d');
        $month_date   = date('Y-m');
        $update       = isset($_GET['update']) ? intval($_GET['update']) : false;
        if($update === 99520){
            $update = true;
        }
        //管理员总数据获取
        $total = Services_Cache::getAllTotal($update);
        //当日数据
        $day   = Services_Cache::getReportDay($date,$update);
        //当月总数据
        $month = Services_Cache::getReportMonth($month_date,$update);
        //设置图表默认显示当前往前七天的数据
        $this->assign('start_date',date('Y-m-d',time() - 86400 * 7) . ' - '.date('Y-m-d'));
        $this->assign('start_day',date('Y-m-d'));
        $this->assign('total',$total);
        $this->assign('day',$day);
        $this->assign('month',$month);
        $this->display('main/total');
    }

    /**
     * H5参数设置
     * @author 93307399@qq.com
     */
    public function settingAction(){
        $config = FConfig::get('setting');
        $config = Service_Public::formatConfig($config);
        if($this->isPost()){
            $setting = FRequest::getPostString('setting');
            $data = array();
            foreach ($setting as $k=>$v){
                if(isset($config[$k]) && $v != ''){
                    $item = $config[$k];
                    if(isset($item['format'])){
                        switch ($item['format']){
                            case 'trim':
                                $v = trim($v);
                                break;
                            case 'int':
                                $v = intval($v);
                                break;
                        }
                    }
                    $data[$k] = $v;
                }
            }
            if(!empty($data)){
                $Table = new FTable('system_config');
                $Table->where(array('item'=>'setting'))->update(array('item'=>'setting','data'=>json_encode($data)));
                Services_Cache::getConfig(true);
                $this->success('设置更新成功','r');
            }else{
                $this->error('设置保存失败,没有提交任何有效数据s');
            }
        }
        $this->assign('_config',$config);
        $this->assign('data',Services_Cache::getConfig());
        $this->display('main/setting');
    }

    public function testAction(){
//        $param = array(
//            'plate_order' => '20190404024011579210',
//            'trade_order' => '4200000282201904044466918754',
//            'pay_money'   => 2,
//            'pay_type'    => 'wechat',
//            'status'      => 'SUCCESS'
//        );
//        dump($param,false);
//        Service_Plate::pushPayInfo($param);
//发送微信模板消息
//            $table      = new FTable('pay_order','o');
//            $orderInfo  = $table->fields('o.uid,o.id,o.order_no,o.money,o.data,o.status,p.plate,o.create_time')->leftJoin('plate','p','o.plate_id=p.id')->where(array('o.id'=>83))->find();
//            $plate_data = json_decode($orderInfo['data'],true);
//            $config     = Services_Cache::getConfig();
//            $remark     = $config['fee_push_msg'];
//            $url        = $config['fee_push_url'];
//            Service_Member::sendNotice($orderInfo['uid'],array(
//                'type'        => 'push_fee',
//                'plate'       => $orderInfo['plate'],
//                'income_time' => date('Y-m-d H:i:s',$plate_data['income_time']),
//                'money'       => Service_Public::formatMoney($orderInfo['money']),
//                'remark'      => $remark,
//            ),$url);
        dump(Service_Plate::getUserHistory(62));
    }

    /**
     * 查询菜单
     * @author 93307399@qq.com
     */
    public function getMenuAction(){
        require_once(APP_ROOT . 'lib/weixin/Wechat.class.php');
        $weixin = new Wechat(FConfig::get('pay.gtxc'));
        $weixin->checkAuth();
        $result = $weixin->getMenu();
//        echo json_encode($result);
        dump($result);
    }

    /**
     * 提供选定时间内统计数据
     * @throws Exception
     * @author 93307399@qq.com
     */
    public function getChartDataAction(){
        $where    = array();
        $date = FRequest::getString('date');
        list($startime,$endtime) = explode(' - ',$date);
        $startime = strtotime($startime);
        $endtime  = strtotime($endtime);
        $startime = $startime === false ? date('Y-m-d',strtotime('-7 day'))  : date('Y-m-d',$startime) ;
        $endtime  = $endtime === false ? date('Y-m-d') : date('Y-m-d',$endtime) ;
        $where['r.date'] = array(
            'gte' => $startime,
            'lte' => $endtime,
        );
        $table = 'report_day_channel';

        $chargeTable = new FTable($table,'r');
        $chargeTable->fields('date,success_order,total_money,am_total,am_money,pm_total,pm_money')->where($where)->order(array('r.id'=>'desc'));
        $charge_list = $chargeTable->select();

        //格式化数据
        $data = array();
        foreach ($charge_list as $item){
            $item['total_money'] = Service_Public::formatMoney($item['total_money']);
            $item['am_money'] = Service_Public::formatMoney($item['am_money']);
            $item['pm_money'] = Service_Public::formatMoney($item['pm_money']);
            $item['date'] = date('m-d',strtotime($item['date']));
            $data[$item['date']] = $item;
        }
        $this->output($data);
    }

    /**
     * 提供日期的小时统计数据
     * @throws Exception
     * @author 93307399@qq.com
     */
    public function getChartDayAction(){
        $where    = array();
        $endtime = strtotime(FRequest::getString('day'));
        $endtime  = $endtime === false ? date('Y-m-d') : date('Y-m-d',$endtime) ;
        $where['r.date'] = $endtime;
        $table = 'report_day_channel_detail';

        $chargeTable = new FTable($table,'r');
        $chargeTable->fields('date,success_order,total_money,am_total,am_money,pm_total,pm_money,pm_data')->where($where);
        $charge_list = $chargeTable->select();

        //格式化数据
        $data = array();
        if($charge_list){
            foreach ($charge_list as $item){
                $item['pm_data'] = json_decode($item['pm_data'],true);
                $data['凌晨'] = array(
                    'o'=>intval($item['am_total']),'m'=>intval($item['am_money'])
                );
                for($i=0;$i<=23;$i++){
                    $key = sprintf('%02d', $i);
                    if(isset($item['pm_data'][$key])){
                        $value = $item['pm_data'][$key];
                        $value['o'] = intval($value['o']);
                        $value['m'] = intval($value['m']);
                    }else{
                        $value = array('o'=>0,'m'=>0);
                    }
                    if(isset($data[$key])){
                        $data[$key]['o'] += intval($value['o']);
                        $data[$key]['m'] += intval($value['m']);
                    }else{
                        $data[$key] = $value;
                    }
                }
            }
        }else{
            for($i=0;$i<=23;$i++){
                $key = sprintf('%02d', $i);
                if($i < 6){
                    $key = '凌晨';
                }
                $data[$key] = array('o'=>0,'m'=>0);
            }
        }
        //直接处理好给前端
        $Total = array();
        $Money = array();
        foreach ($data as $key => $item){
            $Total[$key] = $item['o'];
            $Money[$key] = $item['m'];
        }
        $format = array(
            'category' => array(),
            'total' => array(),
            'money' => array(),
        );
        foreach ($Total as $key => $item){
            $format['category'][] = trim($key);
            $format['total'][] = $item;
            $format['money'][] = Service_Public::formatMoney($Money[$key]);
        }

        $this->output($format);
    }
}
