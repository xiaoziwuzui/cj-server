<?php
//使用memcache缓存
//$_config['cache']['memcache'] = array(
//    'enable' => false,
//    'server' => array(
//        'cache_1' => array('ip' => '127.0.0.1', 'port' => 11211, 'p_connect' => true),
//    )
//);

//使用redis缓存
$_config['cache']['redis'] = array(
    'enable' => true,
    'server' => array(
        'cache_1' => array('ip' => '127.0.0.1', 'port' => 6379, 'time_out' => 0, 'conn_type' => 2,'db' => 4),
    )
);