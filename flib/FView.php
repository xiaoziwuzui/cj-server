<?php
class FView{
    /**
     * @var null|Smarty
     */
    private $view = null;

    public function __construct()
    {

    }

    /**
     * 获取模板解析对象
     * @author 93307399@qq.com
     */
    public function getView(){
        if($this->view === null){
            global $_F;
            if (!class_exists('Smarty',false)) {
                $smarty_class_file = FLIB_ROOT . '../smarty/Smarty.class.php';
                if (!file_exists($smarty_class_file)) {
                    $smarty_class_file = FLIB_ROOT . 'smarty/Smarty.class.php';
                }
                require_once $smarty_class_file;
            }
            $this->view = new Smarty();
            if ($_F['module']) {
                $this->view->cache_dir    = APP_ROOT . "data/{$_F['module']}/cache";
                $this->view->compile_dir  = APP_ROOT . "data/{$_F['module']}/template/";
                $this->view->template_dir = APP_ROOT . "modules/{$_F['module']}/tpl/";
                if (!is_dir($this->view->template_dir)) {
                    $this->view->template_dir = APP_ROOT . "modules/{$_F['module']}/templates/";
                    if (!is_dir($this->view->template_dir)) {
                        $this->view->template_dir = APP_ROOT . "modules/{$_F['module']}/templates/";
                    }
                }
            } else {
                $this->view->cache_dir = APP_ROOT . "data/cache";
                $this->view->compile_dir = APP_ROOT . 'data/template/';
                $this->view->template_dir = APP_ROOT . 'tpl/';
                if (!is_dir($this->view->template_dir)) {
                    $this->view->template_dir = APP_ROOT . "templates/";
                    if (!is_dir($this->view->template_dir)) {
                        $this->view->template_dir = APP_ROOT . "template/";
                    }
                }
            }

            if (defined('TPL_ROOT')) {
                $this->view->template_dir = TPL_ROOT;
            }

            $this->view->caching = false;
            $this->view->debugging = false;
            $this->view->cache_lifetime = 300;
        }
    }

    /**
     * 设置模板路径
     * @param $dir
     * @author 93307399@qq.com
     */
    public function setTemplateDir($dir)
    {
        $this->getView();
        if (!$dir) {
            $dir = APP_ROOT . 'template/';
        }
        $this->view->template_dir = $dir;
    }

    /**
     * 设置模板变量
     * @param $val
     * @param $value
     * @author 93307399@qq.com
     */
    public function set($val, $value)
    {
        $this->getView();
        $this->view->assign($val, $value);
    }

    public function displaySysPage($tpl)
    {
        global $_F;
        $this->getView();
        if ($_F['run_in'] == 'shell') {
            $content = $this->getDebugInfo();
            echo $content;
            exit;
        } else {
            $this->view->template_dir = FLIB_ROOT . 'View/';
            $content = $this->view->fetch($tpl);
        }

        if ($_F['debug'] && !$_F['in_ajax']) {
            $content .= $this->getDebugInfo();
        }
        echo $content;
        exit;
    }

    public function disp($tpl = null)
    {
        global $_F;
        if (!$tpl) {
            if ($_F['app']) {
                $c = str_replace('Controller_' . ucfirst($_F['app']) . '_', '', $_F['controller']);
                $c = strtolower($c);
                $tpl = "{$_F['app']}/{$c}/{$_F['action']}";
            } else {
                $c = strtolower(str_replace('Controller_', '', $_F['controller']));
                $c = str_replace($_F['module'] . '_', '', $c);
                $a = $_F['action'];
                $a = preg_replace('#([A-Z])#e', "_\\1", $a);
                $a = strtolower($a);
                $tpl = "{$c}/{$a}";
            }
        }
        $contents = $this->load($tpl . '.tpl');
        echo $contents;
    }

    public function load($tpl)
    {
        global $_F;
        $this->set('_F', $_F);
        $compress = FConfig::get('global.output_compress');
        $contents = $this->view->fetch($tpl);

        if ($compress) {
            // 会有 http:// 这样的都替换没了
            $contents = preg_replace('#^\s*/' . '/.*$#im', '', $contents);
            $contents = preg_replace('#<!--.+?-->#si', '', $contents);
            $contents = preg_replace('/^\s+/im', '', $contents);
            $contents = preg_replace('/>\s+/im', '>', $contents);
            $contents = preg_replace('/\s*([{};,])\s*/im', '\1', $contents);
//            $contents = preg_replace('/\s+/im', ' ', $contents);
        }
        if ($_F['debug'] && !$_F['in_ajax']) {
            $contents .= $this->getDebugInfo();
        }
        return $contents;
    }

    /**
     * 获取调试信息
     * @return string
     * @author 93307399@qq.com
     */
    public function getDebugInfo()
    {
        global $_F;
        unset($_F['db']);
        if ($_F['run_in'] == 'shell') {
            $debug_contents = "DEBUG INFO:\n";
        } else {
            $debug_contents = '<style>
            .debug_info { clear: both; position: relative; margin-top:300px; }
            .debug_table { border-collapse: collapse;margin:20px; border:1px solid #000;} .debug_table th, .debug_table td { padding:5px; border:1px solid #000; } </style>';
        }

        // SQL DEBUG
        if ($_F['debug_info']['sql']) {
            $debug_contents .= '<table class="debug_table" rules="none" cellspacing="0" cellpadding="5"><tr><td colspan="2">SQL：</td></tr>';
            foreach ($_F['debug_info']['sql'] as $key => $item) {
                if (is_array($item)) {
                    $debug_contents .= "<tr><th>{$key}</th><td>{$item['sql']}<br/><pre>" .
                        var_export($item['params'], true) . "</pre></td></tr>";

                } else {
                    $debug_contents .= "<tr><th>{$key}</th><td>{$item}</td></tr>";
                }
            }
            $debug_contents .= '</table>';
        }

        // COOKIES DEBUG
        $debug_contents .= '<table class="debug_table" rules="none" cellspacing="0" cellpadding="5"><tr><td colspan="2">COOKIES：</td></tr>';
        foreach ($_COOKIE as $key => $item) {
            $debug_contents .= "<tr><th>{$key}</th><td>{$item}</td></tr>";
        }
        $debug_contents .= '</table>';

        // ERRORS
        if ($_F['errors']) {

            $debug_contents .= '<table class="debug_table" rules="none" cellspacing="0" cellpadding="5"><tr><td colspan="2"><span style="background: #ff0000; color: #fff; padding:5px;"> ERRORS：</span></td></tr>';
            foreach ($_F['errors'] as $key => $item) {
                foreach ($item as $skey => $sItem) {
                    $debug_contents .= "<tr><th>{$key}</th><td>{$sItem}</td></tr>";
                }
            }
            $debug_contents .= '</table>';
            unset($_F['errors']);
        }

        // $_F DEBUG
        $debug_F = $_F;
        unset($debug_F['debug_info']);
        $debug_contents .= '<table class="debug_table" rules="none" cellspacing="0" cellpadding="5"><tr><td colspan="2">$_F：</td></tr>';
        foreach ($debug_F as $key => $item) {
            if (is_string($item)) {
                $item_text = $item;
            } else {
                $item_text = '' . var_export($item, true) . '';
            }
            $debug_contents .= "<tr><th>{$key}</th><td><pre>" . $item_text . "</pre></td></tr>";
        }
        $debug_contents .= '</table>';
        // FILE DEBUG
        $debug_contents .= '<table class="debug_table" rules="none" cellspacing="0" cellpadding="5"><tr><td colspan="2">引用文件：</td></tr>';
        if (is_array($_F['debug_info']['autoload_files'])) {
            foreach ($_F['debug_info']['autoload_files'] as $key => $item) {
                $key_show = $key + 1;
                $debug_contents .= "<tr><th>{$key_show}</th><td>{$item}</td></tr>";
            }
        }
        $debug_contents .= '</table>';

        if ($_F['run_in'] == 'shell') {
            $debug_contents = str_replace('</tr>', "\n", $debug_contents);
            $debug_contents = preg_match('/<.+?>/', '', $debug_contents);
        }
        return "<div class=\"debug_info clearfix\">" . $debug_contents . "</div>";
    }

}