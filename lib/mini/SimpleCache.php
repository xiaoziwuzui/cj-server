<?php
/**
 * Created by PhpStorm.
 * Date: 2019-05-22 17:35
 * @author: 93307399@qq.com
 */

namespace JiaweiXS;

class SimpleCache{

    public static $pre = 'mini_';


    public static function init(){

    }

    public static function get($key,$flag){
        return \FCache::get(self::$pre . $key);
    }

    public static function set($key,$data,$expire){
        \FCache::set(self::$pre . $key,$data,$expire);
        return true;
    }
}