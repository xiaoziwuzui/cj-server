<?php

$_config['cache']['memcache'] = array(
    'enable' => false,
    'server' => array(
        'cache_1' => array('ip' => '127.0.0.1', 'port' => 11211, 'p_connect' => true),
    )
);

$_config['cache']['redis'] = array(
    'enable' => false,
    'server' => array(
        array('ip' => '192.168.1.146', 'port' => 6379, 'time_out' => 0, 'db' => 'local_redis', 'conn_type' => 2)
    )
);
