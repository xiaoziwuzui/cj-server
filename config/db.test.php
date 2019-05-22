<?php
$_config ['db'] = array(
    'table_pre' => '',
    'charset'   => 'utf8mb4',
    'server'    => array(
        'default' => array(
            'dsn'      => 'mysql:dbname=cj;host=127.0.0.1',
            'user'     => 'root',
            'password' => 'root',
            'charset' => 'utf8mb4'
        ),
    ),
    'server_read' => array(
        'read_1'  => array(
            'dsn'      => 'mysql:dbname=cj;host=127.0.0.1',
            'user'     => 'root',
            'password' => 'root',
            'charset' => 'utf8mb4'
        ),
    ),
    'server_others' => array(
    )
);
