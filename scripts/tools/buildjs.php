<?php
/**
 * Created by PhpStorm.
 * User: lucy
 * Date: 2016/11/4
 * Time: 9:34
 */
//file_put_contents('D:/phpStudy/www/commile_js.php',CompileFile::html('D:/phpStudy/www/birthDate.js'));
define('APP_ROOT', dirname(dirname(dirname(__FILE__))) . '/');
define('FLIB_RUN_MODE', 'manual');
define('PUBLIC_ROOT', APP_ROOT . 'public/');
define('UPLOAD_ROOT', APP_ROOT . 'public/upload/');
if (defined('STDIN')) {
    chdir(dirname(__FILE__));
}

require 'compile.php';

class build{
    /**
     * 解析参数
     * @var array
     */
    public $param = array();

    public function init(){
        $this->param = $this->parser_argparam();
        if(!isset($this->param['m'])){
            $this->param['m'] = 'unit';
        }
        $method = $this->param['m'];
        if(method_exists($this,$method)){
            $this->$method();
        }else{
            echo 'no method',chr(10);
        }
    }

    public function neditor(){
        $files = array(
            'ueditor.config.js',
            'editor.js',
            'core/browser.js',
            'core/utils.js',
            'core/EventBase.js',
            'core/dtd.js',
            'core/domUtils.js',
            'core/Range.js',
            'core/Selection.js',
            'core/Editor.js',
            'core/Editor.defaultoptions.js',
            'core/loadconfig.js',
            'core/ajax.js',
            'core/filterword.js',
            'core/node.js',
            'core/htmlparser.js',
            'core/filternode.js',
            'core/plugin.js',
            'core/keymap.js',
            'core/localstorage.js',
            'plugins/defaultfilter.js',
            'plugins/inserthtml.js',
            'plugins/autotypeset.js',
            'plugins/autosubmit.js',
            'plugins/image.js',
            'plugins/justify.js',
            'plugins/font.js',
            'plugins/removeformat.js',
            'plugins/blockquote.js',
            'plugins/convertcase.js',
            'plugins/indent.js',
            'plugins/preview.js',
            'plugins/selectall.js',
            'plugins/paragraph.js',
            'plugins/wordcount.js',
            'plugins/dragdrop.js',
            'plugins/copy.js',
            'plugins/paste.js',
            'plugins/list.js',
            'plugins/source.js',
            'plugins/enterkey.js',
            'plugins/keystrokes.js',
            'plugins/fiximgclick.js',
            'plugins/autolink.js',
            'plugins/autoheight.js',
            'plugins/autofloat.js',
            'plugins/table.core.js',
            'plugins/table.cmds.js',
            'plugins/table.action.js',
            'plugins/table.sort.js',
//            'plugins/contextmenu.js',
            'plugins/shortcutmenu.js',
            'plugins/basestyle.js',
            'plugins/elementpath.js',
            'plugins/formatmatch.js',
            'plugins/customstyle.js',
            'plugins/catchremoteimage.js',
            'plugins/insertparagraph.js',
            'plugins/template.js',
            'plugins/autoupload.js',
            'plugins/horizontal.js',
//            'plugins/autosave.js',
            'plugins/section.js',
            'plugins/serverparam.js',
            'plugins/xssFilter.js',
            'ui/ui.js',
            'ui/uiutils.js',
            'ui/uibase.js',
            'ui/separator.js',
            'ui/mask.js',
            'ui/popup.js',
            'ui/colorpicker.js',
            'ui/tablepicker.js',
            'ui/stateful.js',
            'ui/button.js',
            'ui/splitbutton.js',
            'ui/colorbutton.js',
            'ui/tablebutton.js',
            'ui/autotypesetpicker.js',
            'ui/autotypesetbutton.js',
            'ui/cellalignpicker.js',
            'ui/pastepicker.js',
            'ui/toolbar.js',
            'ui/menu.js',
            'ui/combox.js',
            'ui/dialog.js',
            'ui/menubutton.js',
            'ui/multiMenu.js',
            'ui/shortcutmenu.js',
            'ui/breakline.js',
            'ui/message.js',
            'ui/iconfont.js',
            'adapter/editorui.js',
            'adapter/editor.js',
            'adapter/message.js',
            'adapter/autosave.js',
            'lang/zh-cn/zh-cn.js',
        );
        $output = APP_ROOT .'public/assets/plugins/neditor/';
        foreach ($files as $k=>$v){
            $files[$k] = APP_ROOT.'public/assets/plugins/neditor/src/'.$v;
        }
        if(!isset($this->param['o'])){
            $this->param['o'] = 'neditor';
        }
        if(!isset($this->param['ot'])){
            $this->param['ot'] = 'notadd';
        }

        $file_content = array();
        foreach ($files as $v){
            if(strpos($v,'.min') !== false){
                $file_content[] = file_get_contents($v);
            }else{
                $compiles = new CompileFile();
                $file_content[] = $compiles->js($v);
            }
        }

        file_put_contents($output.$this->param['o'].'.release.js',implode('',$file_content));
        $js_md5 = md5_file($output.$this->param['o'].'.release.js');
        echo 'implode js file success,count '.count($file_content),chr(10);

        $files = array(
            'uibase.css',
            'toolbar.css',
            'neditor.css',
            'menubutton.css',
            'menu.css',
            'combox.css',
            'button.css',
            'buttonicon.css',
            'buttoniconex.css',
            'splitbutton.css',
            'popup.css',
            'message.css',
            'dialog.css',
            'paragraphpicker.css',
            'tablepicker.css',
            'colorpicker.css',
            'autotypesetpicker.css',
            'cellalignpicker.css',
            'separtor.css',
            'colorbutton.css',
            'multiMenu.css',
            'contextmenu.css',
            'shortcutmenu.css',
            'pastepicker.css',
        );
        foreach ($files as $k=>$v){
            $files[$k] = APP_ROOT.'public/assets/plugins/neditor/themes/notadd/css/'.$v;
        }
        $output = APP_ROOT .'public/assets/plugins/neditor/themes/notadd/css/';
        $file_content = array();
        foreach ($files as $v){
            $compiles = new CompileFile();
            $file_content[] = $compiles->css($v);
        }
        file_put_contents($output.$this->param['ot'].'.release.css',implode('',$file_content));
        $css_md5 = md5_file($output.$this->param['ot'].'.release.css');
        echo 'implode css file success,count '.count($file_content),chr(10);
        $md5 = md5($js_md5 . $css_md5);

        $this->update_version('editor_version',$md5);
        unset($file_content);
    }

    /**
     * 生成工具整合JS
     * php buildjs.php -m unit -o set.js
     * @author 93307399@qq.com
     */
    public function unit(){
        $files = array(
            'plugins/jquery/jquery.min.js',
            'plugins/jquery/jquery.form.js',
            'plugins/bootstrap/js/bootstrap.min.js',
            'plugins/layui/layui.min.js',
            'js/unit.js',
            'js/app/plate.js',
            'js/app/user.js',
            'js/app/pull.js',
            'js/imageUpload.js',
//            'js/app/original.js',
        );
        $output = APP_ROOT .'public/assets/js/';
        foreach ($files as $k=>$v){
            $files[$k] = APP_ROOT.'public/assets/'.$v;
        }

        if(!isset($this->param['o'])){
            $this->param['o'] = 'set.js';
        }

        $content = array();
        foreach ($files as $v){
            if(strpos($v,'.min') !== false){
                $content[] = file_get_contents($v);
            }else{
                $compiles = new CompileFile();
                $content[] = $compiles->js($v);
            }
        }

        file_put_contents($output.$this->param['o'],implode(chr(10),$content));
        $file_md5 = md5_file($output.$this->param['o']);

        $this->update_version('unit_version',$file_md5);

        echo 'compile success,md5:'.$file_md5.',total:'.count($files).',compile:'.count($content),chr(10);
        unset($content);
    }

    /**
     * 解析命令行输入的命令
     * @return array
     */
    public function parser_argparam(){
        global $argv;
        $result = array();
        unset($argv[0]);
        if(count($argv) > 0){
            $new_arg   = array_values($argv);
            $arg_total = count($new_arg);
            for($i=0;$i<count($new_arg);$i++){
                $v = $new_arg[$i];
                if(substr($v,0,1) == '-'){
                    $key = substr($v,1);
                    $result[$key] = '';
                    if(($i + 1 < $arg_total) && substr($new_arg[$i+1],0,1) != '-'){
                        $result[$key] = $new_arg[$i+1];
                        $i++;
                    }
                }else{
                    $result[$v] = $v;
                }
            }
        }
        return $result;
    }

    /**
     * 自动更新配置文件版本号
     * @param string $key
     * @param string $value
     * @author 93307399@qq.com
     */
    public function update_version($key = 'unit_version',$value = ''){
        foreach (glob(APP_ROOT.'config/global.*') as $g){
            $config = file_get_contents($g);
            $config = preg_replace('/\''.$key.'\' \=> \'(.*?)\',/is','\''.$key.'\' => \''.$value.'\',',$config);
            file_put_contents($g,$config);
            echo 'auto set '.$g.':'.$key,chr(10);
        }
    }
}
$b = new build();
$b->init();