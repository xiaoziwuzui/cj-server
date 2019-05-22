<?php
/**
 * redis 服务配置文件
 */
$_config ['redis_config_codis'] = array(
    'REDIS_HOST' => '127.0.0.1',//REDIS服务主机IP
    'REDIS_PORT' => '9056',//redis服务端口
    'REDIS_TIMEOUT' => '0',//连接时长 默认为0 不限制时长
    'REDIS_CTYPE' => '2'//连接类型 1普通连接 2长连接
);