<?php
$_config['global'] = array(
    'debug'           => false,
    'flib_compress'   => false,
    'output_compress' => false,
    'unit_version' => 'e9ee064710499a8e66b9940a27773a2a', //资源文件版本号
    'editor_version'  => 'c56f74bae1f339af665aa8122a09c783', //编辑器版本号
    'session' => array(
        'type'      => 'sys',
        'table'     => 'sys_session',
        'life_time' => ''
    ),
    'cache_dir'          => APP_ROOT.'data/cache/file', // 文件缓存路径
    'top_domains'        => '', // 主域名，多个用 | 分开
    'session_time'       => 3600 * 24 * 30,
    'session_check_time' => 600, // 用户在线检查时间，120秒内有更新，统计用户在线时长
    'upload_file_ext'    => '',
    'super_gid'          => 1,                                                     //后台超级管理员组ID
    'sub_domain' => array(
        'status'             => 'on',
        'default'            => 'member',
        'sub_domain_rewrite' => array(
            '*'   => 'api',  //接口模块
            'cjm' => 'admin', //管理员后台
        ),
    ),
    'access_domain' => array(
        'gtxc.web.con'  => 1,
    ),
    'title'               => '抽奖管理系统',
    'member_cookie_name'  => 'user_token',
    'admin_cookie_name'   => 'token_m_auth',
    'scan_cookie_name'    => 'scan_auth',
    'encrypt_key'         => 'cj_tt_666',
    'token_key'           => 'cj993746',
    'file_key'            => 'cj666999',
    'ui_assets'           => '/assets/',
    'wechat_push'         => 1,
    'file_domain'         => 'cj.bchhm.com',                //资源文件域名
    'admin_domain'        => 'cjm.bchhm.com',                //后台管理员域名
    'ssl_domain'          => 'http://cj.bchhm.com',      //ssl域名
);