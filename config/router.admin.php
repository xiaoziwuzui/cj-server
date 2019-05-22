<?php

$_config['router'] = array(
    '/'                        => array('controller' => 'Main', 'action' => 'index'),
    '/index'                   => array('controller' => 'Main', 'action' => 'index'),
    '/auth.html'               => array('controller' => 'Auth', 'action' => 'wechat'),
    '/index.html'              => array('controller' => 'Main', 'action' => 'index'),
    '/admin'                   => array('controller' => 'Admin', 'action' => 'index'),
    '/phpMyAdmin'              => array('controller' => 'Auth',  'action' => 'fail'),
    '/phpmyadmin'              => array('controller' => 'Auth',  'action' => 'fail'),
    '/pma'                     => array('controller' => 'Auth', 'action' => 'fail'),
    '/wcm'                     => array('controller' => 'Auth', 'action' => 'fail'),
    '/index.jsp'               => array('controller' => 'Auth', 'action' => 'fail'),
    '/solr'                    => array('controller' => 'Auth', 'action' => 'fail'),
    '/explicit_not_exist_path' => array('controller' => 'Auth', 'action' => 'fail'),
    '/scan/(.+?)'              => array('controller' => 'Auth', 'action' => 'scan', 'params' => 'token'),
 );