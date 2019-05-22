<?php
/**
 * Created by PhpStorm.
 * User: 93307399@qq.com
 * Date: 2018/5/11
 * Time: 14:22
 */
$_config['menu'] = array(
    'manager' => array(
        array('name'=>'汇总数据','url'=>'/admin/main/total','icon'=>'fa-bar-chart'),
        array('name'=>'会员列表','url'=>'/admin/member/default','icon'=>'fa-users'),
//        array('name'=>'商家管理','icon'=>'fa-users','menu'=>array(
//            array('name'=>'商家列表','url'=>'/admin/merchants/default'),
//            array('name'=>'抽奖活动','url'=>'/admin/card/default'),
//        )),
        array('name'=>'系统功能','icon'=>'fa-cogs','menu'=>array(
            array('name'=>'参数设置','url'=>'/admin/main/setting'),
//            array('name'=>'公众号菜单','url'=>'/admin/menu/default'),
//            array('name'=>'公告管理','url'=>'/admin/notice/default'),
            array('name'=>'后台用户','url'=>'/admin/manager/default'),
            array('name'=>'权限设置','url'=>'/admin/permission/department'),
            array('name'=>'操作日志','url'=>'/admin/log/list'),
            array('name'=>'个人信息','url'=>'/admin/main/edit'),
        )),
    )
);
