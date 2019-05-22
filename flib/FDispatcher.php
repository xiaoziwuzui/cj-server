<?php

class FDispatcher {

    public static function init() {
        global $_F;
        $dispatcher = new FDispatcher;
        if (!isset($_F['uri'])) {
            self::getURI();
        }
        $c = isset($_GET['c']) ? $_GET['c'] : null;
        $a = isset($_GET['a']) ? $_GET['a'] : null;
        if (!$c || !$a) {
            $dispatcher->_checkRouter($c, $a);
        }
        if (!$c || !$a) {
            if ($_F['uri'] == '/' || $_F['uri'] == '' || $_F['uri'] == '/index') {
                $c = 'index';
                $a = 'default';
            } else {
                $path_info = explode('/', $_F['uri']);
                if (isset($path_info[3])) {
                    $_F['app'] = $app = $path_info[1];
                    $c = $path_info[2];
                    $a = $path_info[3];
                } elseif (isset($path_info[2])) {
                    $c = $path_info[1];
                    $a = $path_info[2];
                } elseif (isset($path_info[1])) {
                    $c = $path_info[1];
                    $a = 'default';
                }
            }
        }

        if (!empty($_F['app'])) {
            $_F['controller'] = 'Controller_' . ucfirst($_F['app']) . '_' . ucfirst($c);
        } elseif ($_F['module']) {
            $_F['controller'] = 'Controller_' . ucfirst($_F['module']) . '_' . ucfirst($c);
        } else {
            $_F['controller'] = 'Controller_' . ucfirst($c);
        }

        $_F['action'] = $a;
    }

    /**
     * @return bool
     * @throws Exception
     * @author 93307399@qq.com
     */
    public static function dispatch() {
        global $_F;

        if (!$_F['controller'] || !$_F['action']) {
            throw new Exception("访问路径不正确，没有找到 {$_F['uri']}", 404);
        }

        if (!class_exists($_F['controller'])) {
            if ($_F['module']) {
                $c_backup = str_replace(ucfirst($_F['module']) . '_', '', $_F['controller']);
                if (class_exists($c_backup)) {
                    $_F['controller'] = $c_backup;
                } else {
                    Service_Public::failLog(2);
                    throw new Exception("找不到控制器：{$_F['controller']}", 404);
                }
            } else {
                Service_Public::failLog(2);
                throw new Exception("找不到控制器：{$_F['controller']}", 404);
            }
        }

        $controller = new $_F['controller'];
        $action = $_F['action'] . 'Action';

        if (method_exists($controller, $action . "")) {
            if (method_exists($controller, 'beforeAction')) {
                // IMPORTANT beforeAction must return true
                if (!$controller->beforeAction()) {
                    return false;
                }
            }
            header("Content-type: text/html; charset=utf-8");
            $controller->$action();
        } else {
            Service_Public::failLog(2);
            throw new Exception("找不到 {$_F['action']}Action ", 404);
        }

        return true;
    }

    private function _checkRouter(&$c, &$a) {
        global $_F;
        $_config = array();
        $router_config_file = APP_ROOT . "config/router.php";

        if (isset($_F['module'])) {
            $_router_config_file = APP_ROOT . "config/router.{$_F['module']}.php";
            if (file_exists($_router_config_file)) {
                $router_config_file = $_router_config_file;
            }
        }

        if (!file_exists($router_config_file)) {
            return false;
        }
        require $router_config_file;

        $uri = strtolower($_F['uri']);

        if (isset($_config['router'][$uri])) {

            if ($_config['router'][$uri]['url']) {
                redirect($_config['router'][$uri]['url']);
            }

            $c = $_config['router'][$uri]['controller'];
            $a = $_config['router'][$uri]['action'];
            if(isset($_config['router'][$uri]['values'])){
                parse_str($_config['router'][$uri]['values'],$value);
                foreach ($value as $k => $v){
                    if(!isset($_GET[$k])) $_GET[$k] = $v;
                }
            }

            return true;
        } else {

            foreach ($_config['router'] as $key => $item) {
                if (strpos($key, '(') === false) {
                    continue;
                }

                if (preg_match("#^{$key}$#i", $_F['uri'], $res)) {

                    if ($_config['router'][$uri]['url']) {
                        redirect($_config['router'][$uri]['url']);
                    }

                    $c = $_config['router'][$key]['controller'];
                    $a = $_config['router'][$key]['action'];

                    $params = explode(',', $_config['router'][$key]['params']);

                    foreach ($params as $k => $p) {
                        $p = trim($p);
                        $_GET[$p] = $res[$k + 1];
                    }
                    if(isset($_config['router'][$key]['values'])){
                        parse_str($_config['router'][$key]['values'],$value);
                        foreach ($value as $k => $v){
                            if(!isset($_GET[$k])) $_GET[$k] = $v;
                        }
                    }
                    break;
                }
            }

            return false;
        }
    }

    public static function getURI() {
        global $_F;
        $_F['uri'] = isset($_SERVER['HTTP_X_ORIGINAL_URL']) ? $_SERVER['HTTP_X_ORIGINAL_URL'] : null;
        if (!$_F['uri']) {
            $_F['uri'] = $_SERVER['REQUEST_URI'];
        }

        $_F['uri'] = preg_replace('/\?.*$/', '', $_F['uri']);

        if ($_F['uri']) {
            $_F['uri'] = rtrim($_F['uri'], '/');
        }

        if (!$_F['uri']) {
            $_F['uri'] = '/';
        }
    }
}
