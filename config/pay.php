<?php
/**
 * Created by PhpStorm.
 * User: 93307399@qq.com
 * Date: 2018/5/11
 * Time: 14:22
 */
$_config['pay'] = array(
    //微信公众号配置
    'cj' => array(
        'type'      => 1,
        'platform'   => 'weixin',
        'url'        => 'http://cj.bchhm.com/',
        'token'      => 'cj99520',
        'app_id'     => 'wxebb7db4fb10a3c4c',
        'mch_id'     => '',
        'key'        => '3a7c1a3bd38336deffaace2daf2e478a',
        'app_secret' => '73f6a30119cbde4c728a28ea954f1ae5',
        'options'    => '',
        'notify_url' => "/pay/notify_cj.html",
        'return_url' => "/pay/return_cj.html",
    ),
);