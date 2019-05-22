<?php

/**
 * Created by PhpStorm.
 * User: wyr
 * Date: 14-10-10
 * Time: 下午12:15
 */
class Service_Iplocation
{
    public static function getIpLocation($ip)
    {
        $cache_key = 'getIpLocation-' . $ip;
        $cache_content = FCache::get($cache_key);

        if ($cache_content) {
            if ($cache_content['code'] == '1') {
                $cache_content['data'] = array(
                    "region" => "", "region_id" => "0", "city" => "", "city_id" => "0"
                );
            }
            return $cache_content;
        }

        $opts = array(
            'http' => array(
                'method' => "GET",
                'timeout' => 2,
            )
        );

        $context = stream_context_create($opts);

        $json = file_get_contents('http://ip.taobao.com/service/getIpInfo.php?ip=' . $ip, false, $context);
        $arr = json_decode($json, true);

        if ($arr['code'] == '1') {
            $arr['data'] = array(
                "region" => "", "region_id" => "0", "city" => "", "city_id" => "0"
            );
        }

        FCache::set($cache_key, $arr, 86400);

        return $arr;
    }

    public static function getIpLocationByBaidu($ip){

        $cache_key = 'getIpLocationByBaidu01-' . $ip;
        $cache_content = FCache::get($cache_key);

        if ($cache_content) {
            return $cache_content;
        }

        $opts = array(
            'http' => array(
                'method'  => "GET",
                'timeout' => 2,
            )
        );

        $context = stream_context_create($opts);

        $json = file_get_contents('http://api.map.baidu.com/location/ip?ak=3da6e7ccd407395106fc19d6770613b2&coor=bd09ll&ip=' . $ip, false, $context);
        $arr = json_decode($json,true);
//        $data = array();
//        $data['code'] = $arr['status'];
//        if($arr['status'] == 0){
//            $data['data'] = $arr['content']['address_detail'];
//        }
        FCache::set($cache_key, $arr, 86400);

        return $arr;
    }

    public static function getIpLocationBySina($ip){

        $cache_key = 'getIpLocationBySina-' . $ip;
        $cache_content = FCache::get($cache_key);

        if ($cache_content) {
            return $cache_content;
        }

        $opts = array(
            'http' => array(
                'method'  => "GET",
                'timeout' => 2,
            )
        );

        $context = stream_context_create($opts);

        $json = file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=' . $ip, false, $context);
        $arr = json_decode($json,true);
        $data = array();
        $data['code'] = $arr['ret'];
        if($arr['ret'] == 0){
            $data['data'] = $arr;
        }
        FCache::set($cache_key, $data, 86400);

        return $data;
    }

} 