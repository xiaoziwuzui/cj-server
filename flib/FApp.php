<?php

class FApp {
    /**
     * +----------------------------------------------------------
     * 应用程序初始化
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @return void
    +----------------------------------------------------------
     */
    public static function init() {
        global $_F;

        if (FConfig::get('global.debug')) {
            $_F['debug'] = true;
        }
        $session_type = FConfig::get('global.session.type');
        if ($session_type == 'db') {
            $handler = new FSession();
            session_set_save_handler(
                array(&$handler, "open"),
                array(&$handler, "close"),
                array(&$handler, "read"),
                array(&$handler, "write"),
                array(&$handler, "destroy"),
                array(&$handler, "gc"));

            $handler->start();
        } elseif ($session_type == 'memcache') {
            ini_set('session.save_handler', 'memcache');
            ini_set('session.save_path', FConfig::get('global.memcache.ip'));
            $handler = new FSession();
            $handler->start();
        }
    }

    public static function getController(){
        global $_F;
        return str_replace('controller_' . $_F['module'] . '_', '', strtolower($_F['controller']));
    }

    /**
     * +----------------------------------------------------------
     * 运行应用实例 入口文件使用的快捷方法
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @throws Exception
     * @return void
     */
    public static function run() {
        FApp::init();
        FDispatcher::dispatch();
    }
}
