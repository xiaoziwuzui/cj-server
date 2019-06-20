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
        'app_id'     => 'wx15c4c68c563bf8bd',
        'mch_id'     => '',
        'key'        => '3a7c1a3bd38336deffaace2daf2e478a',
        'app_secret' => '4dc82e246f76ee51b96b072c41b846d8',
        'options'    => '',
        'notify_url' => "/pay/notify_cj.html",
        'return_url' => "/pay/return_cj.html",
    ),
    //微信公众号配置
    'xhb' => array(
        'type'      => 1,
        'platform'   => 'weixin',
        'url'        => 'http://cj.bchhm.com/',
        'token'      => 'cj99520',
        'app_id'     => 'wx481fd6893c0634b1',
        'mch_id'     => '',
        'key'        => 'dcbc23d3a190c38bb8f48802ee72390e',
        'app_secret' => 'dcbc23d3a190c38bb8f48802ee72390e',
        'options'    => '',
        'notify_url' => "/pay/notify_cj.html",
        'return_url' => "/pay/return_cj.html",
    ),
);