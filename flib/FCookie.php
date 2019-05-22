<?php

class FCookie
{
    public static function set($var, $value, $life = 7200, $domain = '',$path = '/',$http_only = false)
    {
        global $_F;

        if (!$domain) {
            $domain = $_F['cookie_domain'];
        }

        $timestamp = time();
        $secure = $_SERVER['SERVER_PORT'] == 443 ? true : false;
        $life = $life > 0 ? $timestamp + $life : ($life < 0 ? $timestamp - 31536000 : 0);

        setcookie($var, $value, $life, $path, $domain,$secure,$http_only);

        if(1 > 2){
            if (PHP_VERSION < '5.2.0') {
                setcookie($var, $value, $life, $path, $domain, $secure);
            } else {
                setcookie($var, $value, $life, $path, $domain, $secure, $http_only);
            }
        }
        return;
    }

    public static function get($key)
    {

        return $_COOKIE[$key];
    }


    public static function remove($key, $domain = '')
    {
        self::set($key, null, 0, $domain);
    }
}
