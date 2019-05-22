<?php
//使用memcache缓存
//$_config['cache']['memcache'] = array(
//    'enable' => false,
//    'server' => array(
//        'cache_1' => array('ip' => '127.0.0.1', 'port' => 11211, 'p_connect' => true),
//    )
//);

//使用redis缓存
/**
 * redis库使用说明
 * 1号库后台系统缓存
 * 2号库测算项目程序缓存
 * 3号库PV_UV值统计数
 * 4号库UV库缓存
 */
$_config['cache']['redis'] = array(
    'enable' => true,
    'server' => array(
        'cache_1' => array('ip' => '127.0.0.1', 'port' => 6378, 'time_out' => 0, 'conn_type' => 2,'db' => 1),
    )
);