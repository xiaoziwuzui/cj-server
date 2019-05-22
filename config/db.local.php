<?php
$_config ['db'] = array(
    'table_pre' => '',
    'charset'   => 'utf8',
    'server'    => array(
        'default' => array(
            'dsn'      => 'mysql:dbname=gtxc;host=127.0.0.1',
            'user'     => 'root',
            'password' => 'root',
        ),
    ),
    'server_read' => array(
        'read_1'  => array(
            'dsn'      => 'mysql:dbname=gtxc;host=127.0.0.1',
            'user'     => 'root',
            'password' => 'root',
        ),
    ),
    'server_others' => array(
    )
);
