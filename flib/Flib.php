<?php

class Flib
{
    /**
     * 系统自动加载Flib类库，并且支持配置自动加载路径
     * @param $className
     * @throws Exception
     * @return mixed
     * @internal param string $class 对象类名
     */
    public static function autoLoad($className)
    {
        global $_F;
        // if autoload Smarty, return false;
        if (strpos($className, 'Smarty') === 0) {
            return false;
        }

        /************************************************************
         *
         * 添加对 namespace 简单支持
         *
         ************************************************************/
        $className = ltrim($className, "\\");
        if (strpos($className, '\\')) {
            $filePpath = APP_ROOT . str_replace('\\', '/', $className) . '.php';
            var_dump($filePpath);
            if (file_exists($filePpath)) {
                return require_once $filePpath;
            }
        }

        /************************************************************
         *
         * 结束
         *
         ************************************************************/

        $class_path = str_replace('_', '/', $className) . ".php";

        // 查是不是 flib 的 class
        $file = $class_path;
        $inc_file = FLIB_ROOT . $file;
        if (file_exists($inc_file)) {
            if (isset($_F ['debug'])) {
                $_F ['debug_info'] ['autoload_files'] [] = $inc_file;
            }

            return require_once($inc_file);
        }

        // 检查项目文件
        $className = str_replace(
            array('Service/', 'DAO/', 'Controller/'),
            array('services/', 'dao/', 'controllers/'),
            $class_path);

        $class_explode = explode('/', $className);
        $class_explode_len = sizeof($class_explode);
        foreach ($class_explode as $key => $item) {
            if ($key < ($class_explode_len - 1)) {
                $class_explode [$key] = strtolower($item);
            }
        }
        $file = join('/', $class_explode);

        // 查是不是 App 的 class
        if ($_F['module']) {
            $file = str_replace(strtolower($_F['module']) . '/', '', $file);

            if (strpos($file, 'controller') !== false) {
                $inc_file = APP_ROOT . 'modules/' . $_F['module'] . '/' . $file;
            } else {
                $inc_file = APP_ROOT . $file;
            }
        } else {
            $inc_file = APP_ROOT . $file;
        }
        if (file_exists($inc_file)) {
            if ($_F ['debug']) {
                $_F ['debug_info'] ['autoload_files'] [] = $inc_file;
            }
            return require_once($inc_file);
        }
        if (count(spl_autoload_functions()) == 1) {
            if ($_F ['debug']) {
                $_F ['debug_info'] ['autoload_files'] [] = "<span style='color:red'>{$inc_file} <strong>[ FAILED ]</strong></span><br /> Class: {$className}";
            }
            throw new Exception('File no found: ' . $inc_file, 404);
        }
        return false;
    }

    /**
     * 自定义异常处理
     *
     * @access public
     *
     * @param mixed $e
     *            异常对象
     */
    static public function appException($e)
    {
        $exception = new FException ();
        $exception->traceError($e);
        exit ();
    }

    /**
     * 致命错误捕获
     */
    static public function fatalError()
    {
        if ($e = error_get_last()) {
            switch ($e['type']) {
                case E_ERROR:
                case E_PARSE:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_USER_ERROR:
                    ob_end_clean();
                    require_once FLIB_ROOT . 'FException.php';
                    $exception = new FException ();
                    $exception->traceError($e);
                    break;
            }
        }
    }

    /**
     * 自定义错误处理
     *
     * @param string $err_no 错误类型
     * @param string $err_str 错误信息
     * @param string $err_file 错误文件
     * @param string $err_line 错误行数
     */
    static public function appError($err_no, $err_str, $err_file, $err_line)
    {
        global $_F;

        switch ($err_no) {
            case E_ERROR :
            case E_USER_ERROR :
                $errorStr = "[$err_no] $err_str " . basename($err_file) . " 第 $err_line 行.";
                $exception = new FException ();
                $exception->traceError(new Exception($errorStr));
                break;
            case E_STRICT :
                $_F['errors']['STRICT'][] = "[$err_no] $err_str " . basename($err_file) . " 第 $err_line 行.";
                break;
            case E_WARNING:
            case E_USER_WARNING :
                $_F['errors']['WARNING'][] = "[$err_no] $err_str " . basename($err_file) . " 第 $err_line 行.";
                break;
            case E_NOTICE:
            case E_USER_NOTICE :
                // $_F['errors']['NOTICE'][] = "[$err_no] $err_str " . basename($err_file) . " 第 $err_line 行.";
                break;
            default :
                $_F['errors']['OTHER'][] = "[$err_no] $err_str " . basename($err_file) . " 第 $err_line 行.";
                break;
        }
    }

    public static function createFlibMin()
    {
        $files = "FConfig, FCookie,FDebug, FFile, FView, FDB, FTable, FException, FDispatcher, FController, FSession,FCache, FApp, FPager, FRequest, FRedis, FLogger";
        $files = explode(',', $files);

        $flib_str = '';
        foreach ($files as $class) {
            $class = trim($class);
            $f = FLIB_ROOT . trim($class) . '.php';
            $_content = file_get_contents($f);
            $flib_str .= "\nif (!class_exists('$class')) {" . $_content . ' } ';
        }

        $flib_str = str_replace('<?php', '', $flib_str);
        $flib_str = preg_replace('#/\*.+?\*/#si', '', $flib_str);
        $flib_str = preg_replace('#//.+?$#sim', '', $flib_str);
        $flib_str = preg_replace("#\s{2,}#si", ' ', $flib_str);

        file_put_contents(APP_ROOT . "data/_flib_min.php", "<?php {$flib_str}");
    }

    public static function init()
    {
        global $_F;

        $_F ['config'] = array();

        if (!defined('FLIB_ROOT')) {
            define('FLIB_ROOT', dirname(__FILE__) . '/');
        }

        date_default_timezone_set('Asia/Chongqing');
        ini_set("error_reporting", E_ALL & ~E_NOTICE);

        if (phpversion() < '5.3.0') set_magic_quotes_runtime(0);

        define('CURRENT_TIMESTAMP', time());
        define('CURRENT_DATE_TIME', date('Y-m-d H:i:s'));

        $_F['is_post'] = ($_POST) ? true : false;

        $_F['run_in'] = isset($_SERVER ['HTTP_HOST']) ? 'web' : 'shell';

        define('IS_POST', $_F['is_post']);

        // 注册AUTOLOAD方法，设定错误和异常处理
        spl_autoload_register(array('Flib', 'autoLoad'));
        register_shutdown_function(array('Flib', 'fatalError'));
        set_error_handler(array('Flib', 'appError'));
        set_exception_handler(array('Flib', 'appException'));
        if (FConfig::get('global.flib_compress')) {
            if (!file_exists(APP_ROOT . "data/_flib_min.php")) {
                self::createFlibMin();
            }
            include_once(APP_ROOT . "data/_flib_min.php");
        }
        $_F['module'] = FConfig::get('global.sub_domain.sub_domain_rewrite.*');
        require_once FLIB_ROOT . "functions/function_core.php";
        if (FLIB_RUN_MODE != 'manual') {
            $_F['user_agent'] = $_SERVER ['HTTP_USER_AGENT'];
            $_F['query_string'] = $_SERVER ['QUERY_STRING'];
            $_F['http_host'] ? $_F['http_host'] : $_F['http_host'] = $_SERVER ['HTTP_HOST'];
            $pareHost = self::parseHost($_F['http_host']);
            $last_part = $pareHost[2];
            if (strpos($last_part, '.con') !== false) {
                $_F['dev_mode'] = true;
            } elseif (strpos($last_part, 'test.') !== false) {
                $_F['test_mode'] = true;
            }

            if ($pareHost[3] === 'con') {
                $_F['dev_mode'] = true;
            }

            $_F['cookie_domain'] = '.' . $pareHost[1];
            $_F['domain'] = $pareHost[1];

            $_F['subdomain'] = trim($pareHost[2], '.');

            $_F['refer'] = $_REQUEST ['refer'] ? $_REQUEST ['refer'] : $_SERVER ['HTTP_REFERER'];

            $_F['in_ajax'] = ($_REQUEST['in_ajax'] || $_GET ['in_ajax'] || $_POST ['in_ajax']) ? true : false;

            if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
                $_F['in_ajax'] = true;
            }
            $sub_domain_status = FConfig::get('global.sub_domain.status');
            // 是否开了子域名
            if ($sub_domain_status == 'on') {
                $default_module = '';
                foreach (FConfig::get('global.sub_domain.sub_domain_rewrite') as $key => $value) {
                    if ($key == $_F['subdomain']) {
                        $_F['module'] = $value;
                    }

                    if ($key == '*') {
                        $default_module = $value;
                    }
                }

                if (!$_F['module']) {
                    $_F['module'] = $default_module;
                }
            }
            if (!$_F ['uri']) {
                FDispatcher::init();
            }
            FApp::run();
        }
    }

    public static function resetAll()
    {
        global $_F;

        $_F = array();
    }

    public static function destroy()
    {
        Flib::resetAll();
        spl_autoload_unregister(array('Flib', 'autoLoad'));
        restore_error_handler();
        restore_exception_handler();
    }

    public static function parseHost($httpurl)
    {
        $httpurl = strtolower(trim($httpurl));
        $subdomain = '';
        $last_part = '';
        if (empty($httpurl)) return array();
        $regx1 = '/(([^\/\?#]+\.)?([^\/\?#-\.]+\.)(com\.cn|org\.cn|net\.cn|com\.jp|co\.jp|com\.kr|com\.tw)(\:[0-9]+)?)/i';
        $regx2 = '/(([^\/\?#]+\.)?([^\/\?#-\.]+\.)(cn|con|com|org|net|cc|biz|hk|jp|kr|name|me|tw|la)(\:[0-9]+)?)/i';
        $host = $tophost = '';
        if (preg_match($regx1, $httpurl, $matches)) {
            $host = $matches[1];
        } elseif (preg_match($regx2, $httpurl, $matches)) {
            $host = $matches[1];
        }
        if ($matches) $tophost = $matches[2] == 'www.' ? $host : $matches[3] . $matches[4];
        if ($matches) $subdomain = $matches[2];
        if ($matches) $last_part = $matches[4];
        return array($host, $tophost, $subdomain, $last_part);
    }
}
Flib::init();