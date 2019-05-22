<?php

/**
 * Created by PhpStorm.
 * User: wyr
 * Date: 14-10-10
 * Time: 上午11:42
 */
class Service_City
{
    const CACHE_PREFIX_Province_BY_CODE = 'province-code-1';
    const CACHE_PREFIX_CITY_BY_CODE = 'city-code-';

    /**
     * 取所有的省份
     * @return mixed|null
     */
    public static function getProvince()
    {
        $cache_key = 'getProvince';
        $cache_data = FCache::get($cache_key);
        if (!$cache_data) {
            $province = new FTable('province');
            $cache_data = $province->select();
            FCache::set($cache_key, $cache_data, 30 * 24 * 60 * 60);
        }
        //去年台湾，香港，澳门
        unset($cache_data[31]);
        unset($cache_data[32]);
        unset($cache_data[33]);
        return $cache_data;
    }

    /**
     * 根据省份ID取城市列表
     * @param int $provinceid
     * @return mixed|null
     */
    public static function getCity($provinceid)
    {
        $cache_key = 'getCity_' . $provinceid;
        $cache_data = FCache::get($cache_key);
        if (!$cache_data) {
            $city = new FTable('city');
            $cache_data = $city->where(array('father' => $provinceid))->order(array('cityID' => 'ASC'))->select();
            FCache::set($cache_key, $cache_data, 30 * 24 * 60 * 60);
        }

        return $cache_data;
    }

    public static function getArea($cityid)
    {
        $area = new FTable('area');
        return $area->where(array('father' => $cityid))->select();
    }

    /**
     * @param int $provinceId
     * @return string
     */
    public static function getProvinceById($provinceId)
    {
        if (!$provinceId) {
            return '';
        }
        $cache_key = self::CACHE_PREFIX_Province_BY_CODE . $provinceId;
        $cache_content = FCache::get($cache_key);

        if ($cache_content) {
            return $cache_content;
        }

        $province = new FTable('province');
        $province = $province->where(array('provinceID' => $provinceId))->find();

        $province = $province['province'];

        if (strpos($province, '区')) {
            $province = mb_substr($province, 0, 2);
        }
        FCache::set($cache_key, $province, 3600000);
        return $province;
    }

    public static function getCityById($city)
    {
        if (!$city) {
            return '';
        }
        $cache_key = self::CACHE_PREFIX_CITY_BY_CODE . $city;
        $cache_content = FCache::get($cache_key);

        if ($cache_content) {
            return $cache_content;
        }

        $province = new FTable('city');
        $province = $province->where(array('cityID' => $city))->find();

        FCache::set($cache_key, $province['city'], 3600000);
        return $province['city'];

    }

    /**
     * 根据省份名称取省份ID
     * @param $province string 省份名称
     * @return int ProvinceId
     */
    public static function getProvinceIdByName($province)
    {
        $ProvinceId = 0;
        $province_list = self::getProvince();
        if ($province_list) {
            foreach ($province_list as $v) {
                if (strpos($v['province'], $province) !== false) {
                    $ProvinceId = $v['provinceID'];
                    break;
                }
            }
        }
        return $ProvinceId;
    }

    /**
     * 根据城市名称取城市ID
     * @param int $provinceId 所属省份ID
     * @param int $city 城市名
     * @return int 城市 ID
     */
    public static function getCityIdByName($provinceId, $city)
    {
        $cityId = 0;
        if ($provinceId && $city) {
            $city_list = self::getCity($provinceId);
            foreach ($city_list as $v) {
                if (strpos($v['city'], $city) !== false) {
                    $cityId = $v['cityID'];
                    break;
                }
            }
        }
        return $cityId;
    }
} 