<?php

$_config['router'] = array(
    '/'                        => array('controller' => 'Main',   'action' => 'index'),
    '/index'                   => array('controller' => 'Main',   'action' => 'index'),
    '/index.html'              => array('controller' => 'Main',   'action' => 'index'),
    '/code/(.+?)'              => array('controller' => 'Public', 'action' => 'authCode', 'params' => 'name'),
    //支付回调配置
    '/pay/notify_(.+?).html'   => array('controller' => 'Public', 'action' => 'notify',   'params' => 'pay'),
    '/pay/return_(.+?).html'   => array('controller' => 'Public', 'action' => 'return',   'params' => 'pay'),
    '/me.html'                 => array('controller' => 'Main',   'action' => 'me'),
    /**
     * 以下为引导非法尝试记录配置
     */
    '/admin'                   => array('controller' => 'Public', 'action' => 'fail'),
    '/phpMyAdmin'              => array('controller' => 'Public', 'action' => 'fail'),
    '/phpmyadmin'              => array('controller' => 'Public', 'action' => 'fail'),
    '/pma'                     => array('controller' => 'Public', 'action' => 'fail'),
    '/wcm'                     => array('controller' => 'Public', 'action' => 'fail'),
    '/index.jsp'               => array('controller' => 'Public', 'action' => 'fail'),
    '/solr'                    => array('controller' => 'Public', 'action' => 'fail'),
    '/explicit_not_exist_path' => array('controller' => 'Public', 'action' => 'fail'),
);