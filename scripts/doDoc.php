<?php
/**

 * User: xiaojiang432524@163.com
 * Date: 2017/7/3-15:35
 * 用于自动生成权限配置（未完成）
 */
define('APP_ROOT', dirname(dirname(__FILE__)) . '/');
define('FLIB_RUN_MODE', 'manual');
define('PUBLIC_ROOT', APP_ROOT . 'public/');
define('UPLOAD_ROOT', APP_ROOT . 'public/uploads/');

require_once APP_ROOT . "flib/Flib.php";

class doDoc{
    /**
     * @var array $fixMap 要过滤的方法
     */
    public static $fixMap = array(
        'Abstract/*',
        'Auth/*',
        'Api/*',
        '*/__construct',
        '*/beforeAction',
        '*/testAction',
        '*/*BackAction',
        '*/*bakAction',
    );
    /**
     * @var string $permission_path 要写入的权限文件
     */
    public static $permission_path = 'config/permission.php';
    /**
     * @var string $parser_module 要解析的模块
     */
    public static $parser_module = 'admin';
    /**
     * @var int $clear 是否全部重新创建
     */
    public static $clear = 0;
    /**
     * 默认要加载的文件
     * @var array
     */
    public static $load_file = array(
        'Abstract'
    );
    /**
     * 初始化脚本
     * @author xiaojiang432524@163.com
     */
    public static function Init(){
        $router = self::parser_argparam();
        if(isset($router['a']) && $router['a'] == 'token'){
            echo md5($router['id'] . 'g!jrWrW2');
            exit(0);
        }
        if(isset($router['module'])){
            self::$parser_module = $router['module'];
        }
        if(isset($router['filepath'])){
            self::$permission_path = $router['filepath'];
        }
        if(isset($router['clear'])){
            self::$clear = $router['clear'];
        }
        if(isset($router['fixmap'])){
            $fixmap = explode('|',$router['fixmap']);
            self::$fixMap = array_merge(self::$fixMap,$fixmap);
        }
        if(isset($router['loadfile'])){
            $loadfile = explode('|',$router['loadfile']);
            self::$load_file = array_merge(self::$load_file,$loadfile);
        }
        if(!is_dir(APP_ROOT.'modules/'.self::$parser_module.'/controllers/')){
            exit('module path no found!');
        }
        if(!is_file(APP_ROOT.self::$permission_path)){
            exit('permission_path no found!');
        }
        $permission = self::parserModule();
        if(count($permission) > 0){
            self::savePermission($permission);
        }
        echo 'parser done.',chr(10);
    }

    /**
     * 解析指定模块下的所有方法树
     * @author xiaojiang432524@163.com
     * @return array
     */
    public static function parserModule(){
        $dir = APP_ROOT.'modules/'.self::$parser_module.'/controllers/';
        $class_pre = 'Controller_'.ucfirst(self::$parser_module).'_';
        $permission = array();
        foreach (self::$load_file as $v){
            $init_file = $dir.$v.'.php';
            if(is_file($init_file)){
                include_once $init_file;
            }
        }
        foreach (glob($dir.'*.php') as $file){
            $class_name = str_replace('.php','',end(explode('/',$file)));
            $full_name = $class_pre.$class_name;
            $file_path = $dir.$class_name.'.php';
            include_once $file_path;
            $parseClass = new ReflectionClass($full_name);
            $methods = $parseClass->getMethods();

            foreach ($methods as $method){
                $flag = false;
                if($method->class != $full_name){
                    continue;
                }
                if($method->isPrivate()){
                    continue;
                }
                if(strpos($method->getName(),'Action') === false){
                    continue;
                }
                foreach (self::$fixMap as $mapper){
                    $route = '/admin/'.$class_name.'/'.$method->getName();
                    $mapper = '/'.str_ireplace(array('/','*'),array('\/','(\w+)'), $mapper).'/i';
                    if(preg_match($mapper, $route, $matchs)){
                        $flag = true;
                        break;
                    }
                }
                if($flag){
                    continue;
                }
                $action = str_replace('Action','',$method->getName());
                $url = '/admin/'.$class_name.'/'.$action;
                $url = strtolower($url);
                $comment = self::formatComment($method->getDocComment());
                if($comment == '测试'){
                    continue;
                }
                $permission[$url] = $comment;
                if($permission[$url] == ''){
                    $permission[$url] = strtolower($class_name.'_'.$action);
                }else{
                    $parseword = $permission[$url];
                    $parseword = str_replace(array('，','。'),array('{|}','{|}'),$parseword);
                    $permission[$url] = current(explode('{|}',$parseword));
                }
            }
        }
        return $permission;
    }

    /**
     * 保存权限到配置文件
     * @param $permission
     * @author xiaojiang432524@163.com
     */
    public static function savePermission($permission){
        $_config = array();
        include APP_ROOT.self::$permission_path;
        $old_permission = $_config['permission'];
        $all_permission = array();

        foreach ($old_permission['basic'] as $k=>$v){
            $all_permission[strtolower($v)] = $v;
        }
        if(self::$clear == 0){
            foreach ($old_permission['manager'] as $k=>$v){
                $all_permission[strtolower($k)] = $v;
            }
        }elseif (self::$clear == 1){
            $old_permission['manager'] = array(
                'is_super_manager' => '超级管理员'
            );
        }
        $new_permission = array();
        foreach ($permission as $pk=>$pv){
            if(!isset($all_permission[$pk])){
                $new_permission[$pk] = $pv;
            }
        }
        if(count($new_permission) > 0){

            $old_permission['manager'] = array_merge($old_permission['manager'],$new_permission);
            $exval = var_export($old_permission,true);
            $flag = file_put_contents(APP_ROOT.self::$permission_path,'<?php '.chr(10).'/**'.PHP_EOL.' * 权限配置文件'.PHP_EOL.' */'.PHP_EOL.'$_config[\'permission\'] = '.$exval.';'.chr(10));
            if($flag){
                echo 'add '.count($new_permission).' item.',chr(10);
            }else{
                echo 'write fail.',chr(10);
            }
        }else{
            echo 'no add permission.',chr(10);
        }
    }

    /**
     * 解析命令行输入的命令
     * 如果当前值是个带 - 的
     * 判断下一个key 是否有 -，没有就是当前参数的值，有就算当前参数为空值
     * 如果当前值不带- 直接使用
     * @return array
     */
    public static function parser_argparam(){
        global $argv;
        $result = array();
        unset($argv[0]);
        if(count($argv) > 0){
            $new_arg = array_values($argv);
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
     * 格式化注释
     * @param $comment
     * @author xiaojiang432524@163.com
     * @return string
     */
    public static function formatComment($comment){
        $doc = explode(chr(10), $comment);
        return isset($doc[1])? trim(str_replace('*','',$doc[1])) : '';
    }
}
doDoc::Init();